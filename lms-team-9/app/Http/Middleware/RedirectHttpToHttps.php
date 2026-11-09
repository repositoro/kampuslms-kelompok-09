<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectHttpToHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production') && ! $request->isSecure()) {
            $appUrl = rtrim((string) config('app.url'), '/');

            if (! str_starts_with($appUrl, 'https://')) {
                throw new \RuntimeException('APP_URL must use HTTPS in production.');
            }

            return redirect()->to($appUrl.$request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
