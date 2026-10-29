<x-layout title="Daftar Notifikasi">
    <x-page-header title="Daftar Notifikasi" subtitle="Pemberitahuan aktivitas perkuliahan Anda.">
        <x-slot:actions>
            @if ($notifications->whereNull('read_at')->count() > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                    @csrf
                    <button class="button button--secondary" type="submit">Tandai semua sudah dibaca</button>
                </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    <x-card title="Notifikasi Saya">
        @forelse ($notifications as $notification)
            <div class="list-row" style="align-items: flex-start; justify-content: space-between; border-bottom: 1px solid var(--border-color, #e5e7eb); padding: 1rem 0; {{ !$notification->read_at ? 'background-color: var(--highlight-bg, rgba(59, 130, 246, 0.04)); border-radius: 6px; padding: 1rem;' : '' }}">
                <div style="flex: 1; padding-right: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                        <x-badge :variant="$notification->read_at ? 'secondary' : 'info'">
                            {{ $notification->read_at ? 'Dibaca' : 'Baru' }}
                        </x-badge>
                        <span class="muted" style="font-size: 0.85rem;">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>

                    <h4 style="margin: 0.25rem 0;">{{ $notification->data['title'] ?? ($notification->data['message'] ?? 'Pemberitahuan') }}</h4>
                    <p class="muted" style="margin: 0.25rem 0; font-size: 0.9rem;">
                        {{ $notification->data['course_name'] ?? '' }}
                        @if (isset($notification->data['due_at']))
                            · Tenggat: {{ \Carbon\Carbon::parse($notification->data['due_at'])->format('d M Y, H:i') }}
                        @endif
                    </p>
                </div>

                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    @if (isset($notification->data['url']))
                        <a class="button button--secondary button--small" href="{{ $notification->data['url'] }}">Buka</a>
                    @endif

                    @if (!$notification->read_at)
                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="button button--primary button--small" type="submit">Tandai dibaca</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <x-empty-state title="Belum ada notifikasi" />
        @endforelse

        <div style="margin-top: 1.5rem;">
            {{ $notifications->links() }}
        </div>
    </x-card>
</x-layout>
