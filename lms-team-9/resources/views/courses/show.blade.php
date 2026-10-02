<x-components.layout>
    <div class="min-h-screen bg-slate-50 p-6 md:p-10 font-sans text-slate-800">
        <div class="max-w-7xl mx-auto space-y-6">
            
            <!-- Breadcrumb / Back Link -->
            <a href="{{ route('courses.index') ?? '#' }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Daftar
            </a>

            <!-- Header Bento -->
            <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-full">{{ $course->code ?? 'KODE' }}</span>
                        <span class="px-3 py-1 bg-green-50 text-green-700 text-xs font-bold rounded-full">{{ $course->status == 'active' ? 'Aktif' : 'Tidak Aktif' }}</span>
                    </div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-800">{{ $course->name ?? 'Detail Mata Kuliah' }}</h1>
                    <p class="text-slate-500 mt-2 max-w-2xl">{{ $course->description ?? 'Deskripsi mata kuliah belum tersedia.' }}</p>
                </div>
                
                <div class="flex-shrink-0 grid grid-cols-2 gap-4">
                    <div class="bg-slate-50 p-4 rounded-2xl text-center border border-slate-100">
                        <p class="text-xs text-slate-500 font-semibold mb-1">SKS</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ $course->sks ?? '3' }}</p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl text-center border border-slate-100">
                        <p class="text-xs text-slate-500 font-semibold mb-1">Tugas</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $course->assignments_count ?? '0' }}</p>
                    </div>
                </div>
            </div>

            <!-- Content Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Left Sidebar (Dosen / Info) -->
                <div class="space-y-6">
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Pengajar</h3>
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xl">
                                {{ substr($course->lecturer->name ?? 'D', 0, 1) }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-700">{{ $course->lecturer->name ?? 'Nama Dosen' }}</p>
                                <p class="text-sm text-slate-500">{{ $course->lecturer->email ?? 'dosen@kampus.com' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-indigo-600 rounded-3xl p-6 shadow-sm text-white">
                        <h3 class="text-lg font-bold mb-2">Punya pertanyaan?</h3>
                        <p class="text-indigo-200 text-sm mb-4">Diskusikan materi di forum atau hubungi dosen yang bersangkutan.</p>
                        <a href="#" class="block w-full text-center py-2 bg-white text-indigo-600 rounded-xl font-semibold hover:bg-indigo-50 transition-colors">
                            Forum Diskusi
                        </a>
                    </div>
                </div>

                <!-- Main Content Area (Tabs / Lists) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Materi Section -->
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-slate-800">Materi Perkuliahan</h3>
                            <button class="px-4 py-2 bg-slate-50 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-100 transition-colors">
                                Lihat Semua
                            </button>
                        </div>
                        
                        <div class="space-y-3">
                            @forelse($course->materials ?? [] as $material)
                            <div class="group flex items-center p-4 rounded-2xl border border-slate-100 hover:border-indigo-200 hover:shadow-sm transition-all bg-slate-50">
                                <div class="w-10 h-10 rounded-xl bg-white text-indigo-500 flex items-center justify-center mr-4 shadow-sm border border-slate-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                </div>
                                <div class="flex-grow">
                                    <h4 class="font-semibold text-slate-700 group-hover:text-indigo-600 transition-colors">{{ $material->title ?? 'Judul Materi' }}</h4>
                                    <p class="text-xs text-slate-500">{{ $material->created_at ? $material->created_at->diffForHumans() : 'Baru saja' }}</p>
                                </div>
                                <a href="#" class="px-3 py-1 bg-white border border-slate-200 text-slate-600 rounded-lg text-sm hover:bg-slate-50 font-medium transition-colors">
                                    Buka
                                </a>
                            </div>
                            @empty
                            <div class="text-center py-6 text-slate-400 text-sm">
                                Belum ada materi yang diunggah.
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Assignment Section -->
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-slate-800">Tugas (Assignments)</h3>
                            <button class="px-4 py-2 bg-indigo-50 text-indigo-700 text-sm font-semibold rounded-xl hover:bg-indigo-100 transition-colors">
                                Tambah Tugas
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @forelse($course->assignments ?? [] as $assignment)
                            <div class="p-5 rounded-2xl border border-slate-100 bg-slate-50 hover:shadow-sm hover:bg-white transition-all group">
                                <h4 class="font-bold text-slate-800 mb-1 group-hover:text-indigo-600 transition-colors">{{ $assignment->title ?? 'Judul Tugas' }}</h4>
                                <p class="text-xs text-slate-500 mb-4">Tenggat: <span class="font-semibold text-red-500">{{ $assignment->due_at ? date('d M Y', strtotime($assignment->due_at)) : 'Belum diatur' }}</span></p>
                                
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-semibold text-slate-500 bg-white px-2 py-1 rounded-md border border-slate-100 shadow-sm">{{ $assignment->status == 'published' ? 'Published' : 'Draft' }}</span>
                                    <a href="#" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">Detail -></a>
                                </div>
                            </div>
                            @empty
                            <div class="col-span-full text-center py-6 text-slate-400 text-sm">
                                Belum ada tugas untuk mata kuliah ini.
                            </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
            
        </div>
    </div>
</x-components.layout>