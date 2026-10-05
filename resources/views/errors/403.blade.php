<x-layout title="Akses Ditolak">
    <div class="flex flex-col items-center justify-center text-center py-24">
        {{-- Ikon gembok --}}
        <div class="mb-6">
            <svg class="mx-auto h-20 w-20 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>

        <p class="text-6xl font-bold text-slate-300 mb-4">403</p>
        <h1 class="text-2xl font-bold mb-2">Akses Ditolak</h1>
        <p class="text-gray-500 mb-6 max-w-md">
            Kamu tidak memiliki izin untuk mengakses halaman ini.
            Pastikan kamu sudah login dengan akun yang benar.
        </p>

        <div class="flex gap-3">
            <a href="{{ url()->previous() }}"
               class="border border-slate-300 text-slate-700 px-4 py-2 rounded hover:bg-slate-50 transition">
                &larr; Kembali
            </a>
            <a href="{{ route('dashboard') }}"
               class="bg-slate-800 text-white px-4 py-2 rounded hover:bg-slate-700 transition">
                Ke Dashboard
            </a>
        </div>
    </div>
</x-layout>
