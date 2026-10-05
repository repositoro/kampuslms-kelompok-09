<x-layout title="Detail Pengguna">

    <div class="space-y-6">

        <!-- Back Link -->
        <a href="{{ route('users.index') }}" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Daftar
        </a>

        <!-- Profile Card -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

                <!-- Banner Header -->
                <div class="h-32 bg-gradient-to-r from-blue-800 to-blue-600 relative">
                    <div class="absolute -bottom-12 left-1/2 -translate-x-1/2">
                        <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-blue-800 flex items-center justify-center text-3xl font-bold text-white border-4 border-white shadow-lg">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                </div>

                <!-- Profile Info -->
                <div class="pt-16 pb-8 px-8 text-center">
                    <h1 class="text-2xl font-extrabold text-slate-800 mb-1">{{ $user->name }}</h1>
                    <p class="text-slate-500 mb-3">{{ $user->email }}</p>

                    @php
                        $badge = match($user->role) {
                            'admin'     => 'bg-blue-50 text-blue-900',
                            'dosen'     => 'bg-blue-50 text-blue-700',
                            default     => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-semibold {{ $badge }}">
                        {{ ucfirst($user->role) }}
                    </span>

                    <!-- Info Grid -->
                    <div class="grid grid-cols-2 gap-4 mt-8">
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                            <p class="text-xs text-slate-500 font-semibold mb-1">Peran</p>
                            <p class="text-base font-bold text-slate-800">{{ ucfirst($user->role) }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                            <p class="text-xs text-slate-500 font-semibold mb-1">Terdaftar Pada</p>
                            <p class="text-base font-bold text-slate-800">{{ $user->created_at->format('d M Y') }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-center gap-3 mt-8">
                        <a href="{{ route('users.edit', $user) }}"
                           class="inline-flex items-center px-6 py-3 rounded-2xl bg-blue-50 text-blue-700 font-semibold hover:bg-blue-100 transition-colors text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            Edit Pengguna
                        </a>
                        <a href="{{ route('users.index') }}"
                           class="inline-flex items-center px-6 py-3 rounded-2xl bg-slate-50 text-slate-600 font-semibold hover:bg-slate-100 transition-colors text-sm">
                            Kembali
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </div>

</x-layout>