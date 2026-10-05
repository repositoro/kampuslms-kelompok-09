<x-layout title="Akses Ditolak - 403">
    <div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
        <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center text-red-500 mb-6 shadow-sm border border-red-100">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        
        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-800 tracking-tight mb-4">403</h1>
        <h2 class="text-2xl font-bold text-slate-700 mb-2">Akses Ditolak</h2>
        
        <p class="text-slate-500 mb-8 max-w-md mx-auto leading-relaxed">
            Maaf, Anda tidak memiliki izin untuk mengakses sumber daya ini. Tindakan ini mungkin dibatasi untuk peran pengguna Anda atau Anda mencoba mengakses data yang bukan milik Anda.
        </p>
        
        <a href="{{ url('/') }}"
           class="inline-flex items-center px-8 py-3.5 rounded-2xl bg-blue-600 text-white font-bold text-sm shadow-sm hover:bg-blue-700 hover:shadow-md transition-all">
            Kembali ke Beranda
        </a>
    </div>
</x-layout>
