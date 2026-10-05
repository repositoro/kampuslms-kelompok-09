<x-layout title="Halaman Tidak Ditemukan - 404">
    <div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
        <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mb-6 shadow-sm border border-slate-200">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
        
        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-800 tracking-tight mb-4">404</h1>
        <h2 class="text-2xl font-bold text-slate-700 mb-2">Halaman Tidak Ditemukan</h2>
        
        <p class="text-slate-500 mb-8 max-w-md mx-auto leading-relaxed">
            Maaf, halaman atau sumber daya yang kamu cari tidak tersedia atau mungkin telah dipindahkan.
        </p>
        
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center px-8 py-3.5 rounded-2xl bg-blue-600 text-white font-bold text-sm shadow-sm hover:bg-blue-700 hover:shadow-md transition-all">
            Kembali ke Dashboard
        </a>
    </div>
</x-layout>