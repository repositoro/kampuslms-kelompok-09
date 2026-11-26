#!/usr/bin/env bash
set -Eeuo pipefail

APP_CONTAINER="kampuslms"
QUEUE_CONTAINER="kampuslms-queue"
PROXY_CONTAINER="kampuslms-proxy"
NETWORK="kampuslms"
IMAGE="kampuslms:latest"
PROXY_IMAGE="caddy:2-alpine"
DATABASE_VOLUME="kampuslms-database"
STORAGE_VOLUME="kampuslms-storage"
CADDY_DATA_VOLUME="kampuslms-caddy-data"
CADDY_CONFIG_VOLUME="kampuslms-caddy-config"
SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${ENV_FILE:-$SCRIPT_DIR/.env.production}"
DOMAIN="${DOMAIN:-}"

fail() {
    printf 'Error: %s\n' "$*" >&2
    exit 1
}

if [[ -z "$DOMAIN" ]]; then
    fail "Set DOMAIN to the public DNS name, for example: DOMAIN=lms.example.edu bash ./deploy.sh"
fi

if [[ ! "$DOMAIN" =~ ^([A-Za-z0-9]([A-Za-z0-9-]*[A-Za-z0-9])?\.)+[A-Za-z]{2,}$ ]]; then
    fail "DOMAIN must be a public DNS name, not localhost or an IP address."
fi

if [[ ! -f "$ENV_FILE" ]]; then
    fail "Production environment file not found: $ENV_FILE (copy .env.example to .env.production first)."
fi

if ! command -v docker >/dev/null 2>&1; then
    fail "Docker is required. Install and start Docker Engine on the deployment server."
fi

if ! command -v curl >/dev/null 2>&1; then
    fail "curl is required to verify the HTTPS health endpoint."
fi

env_value() {
    local key="$1"
    local line

    line="$(grep -m 1 "^${key}=" "$ENV_FILE" || true)"
    printf '%s' "${line#*=}"
}

[[ "$(env_value APP_ENV)" == "production" ]] || fail "Set APP_ENV=production in $ENV_FILE."
[[ "$(env_value APP_DEBUG)" == "false" ]] || fail "Set APP_DEBUG=false in $ENV_FILE."
[[ -n "$(env_value APP_KEY)" ]] || fail "Set a unique, non-empty APP_KEY in $ENV_FILE."
[[ "$(env_value APP_URL)" == "https://$DOMAIN" ]] || fail "Set APP_URL=https://$DOMAIN in $ENV_FILE."
[[ "$(env_value DB_CONNECTION)" == "sqlite" ]] || fail "This deployment script requires DB_CONNECTION=sqlite."
[[ "$(env_value DB_DATABASE)" == "/var/www/html/database/data/database.sqlite" ]] \
    || fail "Set DB_DATABASE=/var/www/html/database/data/database.sqlite in $ENV_FILE."
[[ "$(env_value SESSION_SECURE_COOKIE)" == "true" ]] \
    || fail "Set SESSION_SECURE_COOKIE=true in $ENV_FILE."

docker info >/dev/null 2>&1 || fail "Docker is not running or the current user cannot access the Docker daemon."

# Build before replacing the running app so a build failure leaves the current container untouched.
printf 'Building %s from %s\n' "$IMAGE" "$SCRIPT_DIR"
docker build --tag "$IMAGE" "$SCRIPT_DIR"
docker pull "$PROXY_IMAGE"

docker network inspect "$NETWORK" >/dev/null 2>&1 || docker network create "$NETWORK" >/dev/null
# Named volumes keep database, uploads, and TLS certificates across container replacements.
docker volume create "$DATABASE_VOLUME" >/dev/null
docker volume create "$STORAGE_VOLUME" >/dev/null
docker volume create "$CADDY_DATA_VOLUME" >/dev/null
docker volume create "$CADDY_CONFIG_VOLUME" >/dev/null

if docker container inspect "$APP_CONTAINER" >/dev/null 2>&1; then
    docker rm --force "$APP_CONTAINER" >/dev/null
fi

# Mount only the SQLite data subdirectory so the image's migration files remain visible.
docker run --detach \
    --name "$APP_CONTAINER" \
    --restart unless-stopped \
    --network "$NETWORK" \
    --env-file "$ENV_FILE" \
    --mount "type=volume,source=$DATABASE_VOLUME,target=/var/www/html/database/data" \
    --mount "type=volume,source=$STORAGE_VOLUME,target=/var/www/html/storage" \
    "$IMAGE"

docker exec --user root "$APP_CONTAINER" chown -R www-data:www-data /var/www/html/database/data
docker exec --user www-data "$APP_CONTAINER" php artisan migrate --force
docker exec --user www-data "$APP_CONTAINER" php artisan optimize

if docker container inspect "$QUEUE_CONTAINER" >/dev/null 2>&1; then
    docker rm --force "$QUEUE_CONTAINER" >/dev/null
fi

docker run --detach \
    --name "$QUEUE_CONTAINER" \
    --restart unless-stopped \
    --network "$NETWORK" \
    --user www-data \
    --env-file "$ENV_FILE" \
    --mount "type=volume,source=$DATABASE_VOLUME,target=/var/www/html/database/data" \
    --mount "type=volume,source=$STORAGE_VOLUME,target=/var/www/html/storage" \
    "$IMAGE" \
    php artisan queue:work database --sleep=3 --tries=3 --timeout=90

if docker container inspect "$PROXY_CONTAINER" >/dev/null 2>&1; then
    docker rm --force "$PROXY_CONTAINER" >/dev/null
fi

docker run --detach \
    --name "$PROXY_CONTAINER" \
    --restart unless-stopped \
    --network "$NETWORK" \
    --publish 80:80 \
    --publish 443:443 \
    --publish 443:443/udp \
    --mount "type=volume,source=$CADDY_DATA_VOLUME,target=/data" \
    --mount "type=volume,source=$CADDY_CONFIG_VOLUME,target=/config" \
    "$PROXY_IMAGE" \
    reverse-proxy --from "$DOMAIN" --to "$APP_CONTAINER:8080"

printf 'Waiting for https://%s/up\n' "$DOMAIN"
for ((attempt = 1; attempt <= 30; attempt++)); do
    if curl --fail --location --silent --output /dev/null --max-time 5 "https://$DOMAIN/up"; then
        printf 'Deployment succeeded: https://%s\n' "$DOMAIN"
        exit 0
    fi
    sleep 2
done

printf 'HTTPS health check failed. Check DNS, firewall ports 80/443, and proxy logs:\n' >&2
docker logs --tail 50 "$PROXY_CONTAINER" >&2
exit 1
