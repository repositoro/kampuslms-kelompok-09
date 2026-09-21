<x-layout title="Daftar Mata Kuliah">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 700; margin: 0; color: #1e293b;">Daftar Mata Kuliah</h1>
            <p style="margin: 0.25rem 0 0 0; color: #64748b; font-size: 0.875rem;">Kelola data mata kuliah, SKS, dan dosen pengampu.</p>
        </div>
        <div>
            <a href="{{ route('courses.create') }}" 
               style="background-color: #2563eb; color: #ffffff; padding: 0.5rem 1rem; border-radius: 6px; text-decoration: none; font-weight: 500; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                + Tambah Mata Kuliah
            </a>
        </div>
    </div>

    {{-- Filter dan Pencarian (State disimpan di Query String) --}}
    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
        <form method="GET" action="{{ route('courses.index') }}" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 220px;">
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       placeholder="Cari berdasarkan kode atau nama..." 
                       style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.875rem; box-sizing: border-box;">
            </div>
            <div style="min-width: 160px;">
                <select name="status" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.875rem; box-sizing: border-box; background-color: #ffffff;">
                    <option value="">-- Semua Status --</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" 
                        style="background-color: #334155; color: #ffffff; border: none; padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; cursor: pointer;">
                    Filter & Cari
                </button>
                @if (request()->hasAny(['q', 'status']))
                    <a href="{{ route('courses.index') }}" 
                       style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 0.875rem; text-decoration: none; display: inline-flex; align-items: center;">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Mata Kuliah --}}
    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
            <thead>
                <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569;">
                    <th style="padding: 0.75rem 1rem; font-weight: 600;">Kode</th>
                    <th style="padding: 0.75rem 1rem; font-weight: 600;">Nama Mata Kuliah</th>
                    <th style="padding: 0.75rem 1rem; font-weight: 600;">SKS</th>
                    <th style="padding: 0.75rem 1rem; font-weight: 600;">Dosen Pengampu</th>
                    <th style="padding: 0.75rem 1rem; font-weight: 600;">Status</th>
                    <th style="padding: 0.75rem 1rem; font-weight: 600; text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($courses as $course)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 0.75rem 1rem; font-family: monospace; font-weight: 600; color: #1e293b;">
                            {{ $course->code }}
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            <a href="{{ route('courses.show', $course) }}" style="color: #2563eb; text-decoration: none; font-weight: 500;">
                                {{ $course->name }}
                            </a>
                        </td>
                        <td style="padding: 0.75rem 1rem; color: #475569;">
                            {{ $course->sks }}
                        </td>
                        <td style="padding: 0.75rem 1rem; color: #475569;">
                            {{ $course->lecturer ? $course->lecturer->name : 'Belum Ditentukan' }}
                        </td>
                        <td style="padding: 0.75rem 1rem;">
                            @if ($course->status === 'active')
                                <span style="background-color: #dcfce7; color: #15803d; padding: 0.2rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Active</span>
                            @elseif ($course->status === 'draft')
                                <span style="background-color: #fef9c3; color: #854d0e; padding: 0.2rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Draft</span>
                            @else
                                <span style="background-color: #f1f5f9; color: #475569; padding: 0.2rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Archived</span>
                            @endif
                        </td>
                        <td style="padding: 0.75rem 1rem; text-align: right; white-space: nowrap;">
                            <a href="{{ route('courses.edit', $course) }}" 
                               style="color: #d97706; text-decoration: none; font-weight: 500; margin-right: 0.75rem;">
                                Edit
                            </a>
                            <form action="{{ route('courses.destroy', $course) }}" 
                                  method="POST" 
                                  style="display:inline;" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah {{ $course->code }} - {{ $course->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        style="background: none; border: none; color: #dc2626; cursor: pointer; font-size: 0.875rem; font-weight: 500; padding: 0;">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem 1rem; color: #94a3b8;">
                            Tidak ada mata kuliah yang sesuai dengan kriteria pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Berkelanjutan (Membawa Query String) --}}
    <div style="margin-top: 1.5rem;">
        {{ $courses->links() }}
    </div>
</x-layout>