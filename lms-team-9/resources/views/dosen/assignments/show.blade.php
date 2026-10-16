<x-layout title="Detail Tugas">
    {{-- Breadcrumb Dinamis Sesuai Role --}}
    <x-breadcrumb>
        <a href="{{ route('courses.show', $assignment->course) }}">{{ $assignment->course->name }}</a>
        
        @if (auth()->user()->role === 'dosen')
            <a href="{{ route('dosen.courses.assignments.index', $assignment->course) }}">Tugas</a>
        @else
            <a href="{{ route('courses.show', $assignment->course) }}">Tugas</a>
        @endif

        <span>Detail</span>
    </x-breadcrumb>

    <x-page-header title="{{ $assignment->title }}" subtitle="{{ $assignment->course->name }}">
        <x-slot:actions>
            @can('update', $assignment)
                <a class="button button--secondary" href="{{ route('dosen.assignments.edit', $assignment) }}">Ubah tugas</a>
            @endcan
            @can('delete', $assignment)
                <form action="{{ route('dosen.assignments.destroy', $assignment) }}" method="POST"
                    onsubmit="return confirm('Hapus tugas ini? {{ $submissionCount }} pengumpulan dan {{$gradedSubmissionCount }} nilai akan ikut terhapus.')">
                    @csrf
                    @method('DELETE')
                    <button class="button button--danger" type="submit">Hapus Tugas</button>
                </form>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Kartu Ringkasan Informasi Tugas --}}
    <x-card title="Informasi tugas">
        <div class="status-row">
            <x-badge :variant="$assignment->status === 'published' ? 'success' : 'neutral'">
                {{ $assignment->status === 'published' ? 'Terbit' : 'Draf' }}
            </x-badge>
            <x-badge variant="info">Nilai maksimal {{ $assignment->max_score }}</x-badge>
            <x-badge :variant="$assignment->allow_late ? 'warning' : 'danger'">
                {{ $assignment->allow_late ? 'Terlambat diizinkan' : 'Terlambat tidak diizinkan' }}
            </x-badge>
        </div>
        <p class="muted">Tenggat: {{ $assignment->due_at->format('d M Y, H:i') }}</p>
        <div class="prose">{{ $assignment->instructions }}</div>
    </x-card>

    @if (auth()->user()->role === 'mahasiswa')
        {{-- Tampilan Khusus Mahasiswa --}}
        @php($isPastDue = now()->greaterThan($assignment->due_at))
        <x-card title="Pengumpulan tugas">
            @if ($submission &&$submission->grade)
                <x-alert variant="success">Tugas sudah dinilai. Pengumpulan tidak dapat diubah.</x-alert>
            @elseif ($submission &&$submission->is_late)
                <x-alert variant="warning">Tugas terkumpul terlambat.</x-alert>
            @elseif ($submission)
                <x-alert variant="info">Tugas terkumpul tepat waktu.</x-alert>
            @elseif ($isPastDue && !$assignment->allow_late)
                <x-alert variant="error">Tenggat telah lewat dan tugas tidak menerima pengumpulan terlambat.</x-alert>
            @else
                <x-badge variant="neutral">Belum mengumpulkan</x-badge>
            @endif

            @if ($submission)
                <dl class="details-grid">
                    <dt>Berkas</dt>
                    {{-- Menggunakan Accessor formatted_file_size (Langkah 7) --}}
                    <dd>{{ $submission->original_name }} · {{$submission->formatted_file_size }}</dd>
                    <dt>Waktu dikumpulkan</dt>
                    <dd>{{ $submission->submitted_at->format('d M Y, H:i') }}</dd>
                    <dt>Catatan</dt>
                    <dd>{{ $submission->note ?: '—' }}</dd>
                </dl>
                <a class="button button--secondary" href="{{ route('submissions.download', $submission) }}">Unduh berkas saya</a>

                @if ($submission->grade)
                    <div class="grade-result">
                        <h2>Hasil penilaian</h2>
                        <p class="grade-score">{{ $submission->grade->score }} <span>/ {{$assignment->max_score }}</span></p>
                        <p><strong>Umpan balik dosen:</strong> {{ $submission->grade->feedback ?: 'Belum ada umpan balik.' }}</p>
                    </div>
                @endif
            @endif

            @if ($canSubmit)
                <form action="{{ route('mahasiswa.assignments.submissions.store', $assignment) }}" method="POST"
                    enctype="multipart/form-data" class="form-stack submission-form">
                    @csrf
                    <h2>{{ $submission ? 'Kumpul Ulang' : 'Kumpul Tugas' }}</h2>
                    @if ($isPastDue &&$assignment->allow_late)
                        <x-alert variant="warning">Pengumpulan ini akan tercatat sebagai terlambat.</x-alert>
                    @endif
                    <x-form-field name="file" label="Berkas tugas" hint="PDF, DOC, DOCX, ZIP, atau RAR. Ukuran maksimal 20 MB." :required="true">
                        <x-file-input id="file" name="file" accept=".pdf,.doc,.docx,.zip,.rar" required />
                    </x-form-field>
                    <x-form-field name="note" label="Catatan (opsional)">
                        <x-textarea id="note" name="note">{{ old('note') }}</x-textarea>
                    </x-form-field>
                    <x-button>{{ $submission ? 'Kumpul Ulang' : 'Kumpul Tugas' }}</x-button>
                </form>
            @elseif (!$submission && $isPastDue && !$assignment->allow_late)
                <button class="button button--primary" type="button" disabled aria-describedby="deadline-reason">Pengumpulan ditutup</button>
                <p id="deadline-reason" class="field-error">Batas waktu pengumpulan telah lewat. Tugas ini tidak menerima keterlambatan.</p>
            @endif
        </x-card>
    @else
        {{-- Tampilan Pengumpulan & Form Penilaian Khusus Dosen / Admin (Langkah 5) --}}
        <x-card title="Pengumpulan mahasiswa">
            <p class="muted">{{ $submissionCount }} berkas terkumpul · {{ $gradedSubmissionCount }} sudah dinilai</p>
            @if ($assignment->submissions->isEmpty())
                <x-empty-state title="Belum ada pengumpulan" />
            @else
                <x-table>
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Waktu kumpul</th>
                            <th>Status</th>
                            <th>Nilai</th>
                            <th>Unduh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assignment->submissions as$studentSubmission)
                            <tr>
                                <td data-label="Mahasiswa">{{ $studentSubmission->student->name }}</td>