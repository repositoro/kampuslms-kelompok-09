<x-components.layout>
    <div class="min-h-screen bg-slate-50 p-6 md:p-10 font-sans text-slate-800">
        <div class="max-w-7xl mx-auto space-y-8">
            
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-800">Daftar Mata Kuliah</h1>
                    <p class="text-slate-500 mt-1">Kelola dan jelajahi semua mata kuliah Anda di satu tempat.</p>
                </div>
                <div>
                    <a href="{{ route('courses.create') ?? '#' }}" class="inline-flex items-center px-6 py-3 rounded-2xl bg-indigo-600 text-white font-semibold shadow-sm hover:bg-indigo-700 hover:shadow transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Mata Kuliah
                    </a>
                </div>
            </div>

            <!-- Bento Grid Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                @forelse($courses ?? [] as $course)
                <div class="group bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-lg hover:border-indigo-100 transition-all flex flex-col h-full relative overflow-hidden">
                    <!-- Accent background top -->
                    <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-indigo-400 to-purple-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    
                    <div class="flex justify-between items-start mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                            {{ $course->code ?? 'KODE' }}
                        </span>
                        <div class="flex space-x-2">
                            <a href="{{ route('courses.edit', $course->id) ?? '#' }}" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </a>
                        </div>
                    </div>

                    <h2 class="text-xl font-bold text-slate-800 mb-2 line-clamp-2">
                        {{ $course->name ?? 'Nama Mata Kuliah' }}
                    </h2>
                    
                    <p class="text-sm text-slate-500 mb-6 flex-grow line-clamp-3">
                        {{ $course->description ?? 'Deskripsi singkat mengenai mata kuliah ini akan ditampilkan di sini agar pengguna mendapatkan gambaran.' }}
                    </p>

                    <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                        <div class="text-sm font-medium text-slate-600 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            {{ $course->sks ?? 3 }} SKS
                        </div>
                        <a href="{{ route('courses.show', $course->id) ?? '#' }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-50 text-indigo-600 text-sm font-semibold hover:bg-indigo-50 transition-colors">
                            Lihat Detail ->
                        </a>
                    </div>
                </div>
                @empty
                <div class="col-span-full bg-white rounded-3xl p-12 shadow-sm border border-slate-100 flex flex-col items-center justify-center text-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mb-4">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700">Belum Ada Mata Kuliah</h3>
                    <p class="text-slate-500 mt-2 max-w-sm">Anda belum menambahkan atau terdaftar di mata kuliah apa pun. Klik tombol tambah untuk memulai.</p>
                </div>
                @endforelse

            </div>
        </div>
    </div>
</x-components.layout>