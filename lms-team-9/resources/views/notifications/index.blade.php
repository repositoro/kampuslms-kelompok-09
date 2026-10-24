<x-layout title="Notifikasi Saya">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: bold;">Notifikasi</h1>
            <p style="color: #6b7280; font-size: 0.875rem;">Daftar pemberitahuan aktivitas aplikasi kamu.</p>
        </div>
        
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                @csrf
                <x-button variant="secondary">Tandai Semua Dibaca</x-button>
            </form>
        @endif
    </div>

    <x-card>
        @forelse ($notifications as $notification)
            <div style="padding: 1rem; border-bottom: 1px solid #e5e7eb; background-color: {{ is_null($notification->read_at) ? '#f0f9ff' : 'transparent' }}; display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                <div>
                    <strong style="display: block; font-size: 1rem; color: #111827;">
                        {{ $notification->data['title'] ?? 'Pemberitahuan Baru' }}
                    </strong>
                    <span style="font-size: 0.875rem; color: #4b5563;">
                        {{ $notification->data['course_name'] ?? 'Aktivitas Perkuliahan' }} — {{ $notification->created_at->diffForHumans() }}
                    </span>
                </div>
                <div>
                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <x-button variant="secondary" type="submit">
                            {{ is_null($notification->read_at) ? 'Buka & Baca' : 'Lihat' }}
                        </x-button>
                    </form>
                </div>
            </div>
        @empty
            <p style="text-align: center; color: #6b7280; padding: 2rem 0;">Belum ada notifikasi.</p>
        @endforelse

        @if($notifications->hasPages())
            <div style="margin-top: 1rem;">
                {{ $notifications->links() }}
            </div>
        @endif
    </x-card>
</x-layout>