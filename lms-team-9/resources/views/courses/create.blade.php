<x-components.layout>
    <div class="min-h-screen bg-slate-50 p-6 md:p-10 font-sans text-slate-800 flex items-center justify-center">
        <div class="max-w-2xl w-full">
            
            <!-- Header -->
            <div class="mb-8 text-center">
                <a href="{{ route('courses.index') ?? '#' }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-700 mb-4 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali ke Daftar
                </a>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-800">Tambah Mata Kuliah</h1>
                <p class="text-slate-500 mt-2">Buat mata kuliah baru untuk mulai membagikan materi dan tugas.</p>
            </div>

            <!-- Bento Form Card -->
            <div class="bg-white rounded-3xl p-8 md:p-10 shadow-sm border border-slate-100">
                <form action="{{ route('courses.store') ?? '#' }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Kode MK -->
                        <div class="space-y-2">
                            <label for="code" class="block text-sm font-semibold text-slate-700">Kode Mata Kuliah</label>
                            <input type="text" name="code" id="code" placeholder="Mis: IF301" required
                                class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all outline-none">
                        </div>

                        <!-- SKS -->
                        <div class="space-y-2">
                            <label for="sks" class="block text-sm font-semibold text-slate-700">Jumlah SKS</label>
                            <input type="number" name="sks" id="sks" min="1" max="6" placeholder="3" required
                                class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all outline-none">
                        </div>
                    </div>

                    <!-- Nama MK -->
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-semibold text-slate-700">Nama Mata Kuliah</label>
                        <input type="text" name="name" id="name" placeholder="Mis: Pemrograman Web Lanjut" required
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all outline-none">
                    </div>

                    <!-- Deskripsi -->
                    <div class="space-y-2">
                        <label for="description" class="block text-sm font-semibold text-slate-700">Deskripsi</label>
                        <textarea name="description" id="description" rows="4" placeholder="Tuliskan deskripsi singkat..."
                            class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all outline-none resize-none"></textarea>
                    </div>

                    <!-- Status -->
                    <div class="space-y-2">
                        <label for="status" class="block text-sm font-semibold text-slate-700">Status</label>
                        <select name="status" id="status" class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all outline-none">
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit" class="w-full py-4 rounded-2xl bg-indigo-600 text-white font-bold text-lg shadow-sm hover:bg-indigo-700 hover:shadow-md transition-all">
                            Simpan Mata Kuliah
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-components.layout>