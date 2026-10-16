<x-layout title="Detail Mata Kuliah">
    <x-breadcrumb><a href="{{ route('courses.index') }}">Mata Kuliah</a><span>{{ $course->code }}</span></x-breadcrumb>
    <x-page-header title="{{ $course->name }}" subtitle="{{ $course->code }} · {{ $course->sks }} SKS">
        <x-slot:actions>
            @can('update', $course)
                <a class="button button--secondary" href="{{ route('courses.edit', $course) }}">Ubah mata kuliah</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if (auth()->user()->role === 'admin' || auth()->user()->role === 'dosen')
        <x-tabs :tabs="[
            ['label' => 'Ringkasan', 'route' => 'courses.show', 'parameters' => [$course]],
            ['label' => 'Peserta', 'route' => 'courses.show', 'parameters' => [$course], 'anchor' => 'peserta', 'active' => false],
            ['label' => 'Materi', 'route' => 'dosen.courses.materials.index', 'parameters' => [$course], 'active' => 'dosen.courses.materials.*'],
            ['label' => 'Tugas', 'route' => 'dosen.courses.assignments.index', 'parameters' => [$course], 'active' => 'dosen.courses.assignments.*'],
            ['label' => 'Rekap nilai', 'route' => 'dosen.courses.grades.index', 'parameters' => [$course], 'active' => 'dosen.courses.grades.*'],
        ]" />
    @else
        <x-tabs :tabs="[
            ['label' => 'Materi', 'route' => 'dosen.courses.materials.index', 'parameters' => [$course], 'active' => 'dosen.courses.materials.*'],
            ['label' => 'Tugas', 'route' => 'dosen.courses.assignments.index', 'parameters' => [$course], 'active' => 'dosen.courses.assignments.*'],
            ['label' => 'Nilai saya', 'route' => 'mahasiswa.grades.index'],
        ]" />
    @endif

    <div class="dashboard-grid">
        <x-card title="Ringkasan mata kuliah">
            <p>{{ $course->description ?: 'Belum ada deskripsi mata kuliah.' }}</p>
            <p><strong>Dosen pengampu:</strong> {{ $course->lecturer?->name ?? 'Belum ditentukan' }}</p>
            <x-badge :variant="$course->status === 'active' ? 'success' : 'neutral'">{{ ['draft' => 'Draf', 'active' => 'Aktif', 'archived' => 'Diarsipkan'][$course->status] ?? $course->status }}</x-badge>
        </x-card>
        @if ($canManageEnrollment)
            <x-card title="Daftarkan mahasiswa">
                <form action="{{ route('courses.enrollments.store', $course) }}" method="POST" class="form-stack">
                    @csrf
                    <x-form-field name="user_id" label="Mahasiswa" :required="true">
                        <x-select id="user_id" name="user_id" required>
                            <option value="">Pilih mahasiswa</option>
                            @foreach ($availableStudents as $student)
                                <option value="{{ $student->id }}" @selected(old('user_id') == $student->id)>{{ $student->name }} ({{ $student->email }})</option>
                            @endforeach
                        </x-select>
                    </x-form-field>
                    @error('student')<p class="field-error">{{ $message }}</p>@enderror
                    <x-button :disabled="$availableStudents->isEmpty()">Daftarkan mahasiswa</x-button>
                </form>
            </x-card>
        @endif
    </div>

    @if ($canManageEnrollment)
        <x-card title="Peserta terdaftar" id="peserta">
            <x-table>
                <thead><tr><th>Nama mahasiswa</th><th>Email</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($course->students->where('role', 'mahasiswa') as $student)
                        <tr>
                            <td data-label="Nama mahasiswa">{{ $student->name }}</td>
                            <td data-label="Email">{{ $student->email }}</td>
                            <td data-label="Aksi">
                                <form action="{{ route('courses.enrollments.destroy', [$course, $student]) }}" method="POST"
                                    onsubmit="return confirm('Keluarkan mahasiswa ini dari mata kuliah?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button button--danger button--small" type="submit">Keluarkan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><x-empty-state title="Belum ada mahasiswa terdaftar" /></td></tr>
                    @endforelse
                </tbody>
            </x-table>
        </x-card>
    @endif

    {{-- Kartu Daftar Materi Perkuliahan --}}
    <x-card title="Materi perkuliahan">
        @forelse ($course->materials as $material)
            <div class="list-row">
                <div>
                    <strong>{{ $material->title }}</strong>
                    @if ($material->description)
                        <p class="muted">{{ $material->description }}</p>
                    @endif
                </div>
                <div>
                    @if ($material->type === 'file')
                        <a class="button button--small" href="{{ route('materials.download', $material) }}">Unduh berkas</a>
                    @else
                        <a class="button button--secondary button--small" href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer">Buka tautan</a>
                    @endif
                </div>
            </div>
        @empty
            <x-empty-state title="Belum ada materi perkuliahan" />
        @endforelse
    </x-card>

    {{-- Kartu Daftar Tugas --}}
    <x-card title="Tugas">
        @forelse ($course->assignments as $assignment)
            @php($submission = $assignment->submissions->first())
            <div class="list-row">
                <div>
                    {{-- Tautan dinamis ke detail tugas sesuai role user --}}
                    <a href="{{ auth()->user()->role === 'mahasiswa' ? route('mahasiswa.assignments.show', $assignment) : route('dosen.assignments.show', $assignment) }}">
                        <strong>{{ $assignment->title }}</strong>
                    </a>
                    <p class="muted">Tenggat: {{ $assignment->due_at->format('d M Y, H:i') }}</p>
                    @if (auth()->user()->role === 'mahasiswa')
                        <x-badge :variant="!$submission ? 'neutral' : ($submission->grade ? 'success' : ($submission->is_late ? 'danger' : 'info'))">
                            {{ !$submission ? 'Belum mengumpulkan' : ($submission->grade ? 'Dinilai — '.$submission->grade->score.' / '.$assignment->max_score : ($submission->is_late ? 'Terkumpul terlambat' : 'Terkumpul tepat waktu')) }}
                        </x-badge>
                    @endif
                </div>
                @if (auth()->user()->role !== 'mahasiswa')
                    <a href="{{ route('dosen.courses.assignments.index', $course) }}">Kelola tugas</a>
                @endif
            </div>
        @empty
            <x-empty-state title="Belum ada tugas" />
        @endforelse
    </x-card>
</x-layout>