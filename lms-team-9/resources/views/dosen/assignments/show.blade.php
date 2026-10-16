<x-layout title="Detail Tugas">
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
                    onsubmit="return confirm('Hapus tugas ini? {{ $submissionCount }} pengumpulan dan {{ $gradedSubmissionCount }} nilai akan ikut terhapus.')">
                    @csrf
                    @method('DELETE')
                    <button class="button button--danger" type="submit">Hapus Tugas</button>
                </form>
            @endcan
        </x-slot:actions>
    </x-page-header>
    <x-card title="Informasi tugas">
        <div class="status-row">
            <x-badge :variant="$assignment->status === 'published' ? 'success' : 'neutral'">{{ $assignment->status === 'published' ? 'Terbit' : 'Draf' }}</x-badge>
            <x-badge variant="info">Nilai maksimal {{ $assignment->max_score }}</x-badge>
            <x-badge :variant="$assignment->allow_late ? 'warning' : 'danger'">
                {{ $assignment->allow_late ? 'Terlambat diizinkan' : 'Terlambat tidak diizinkan' }}
            </x-badge>
        </div>
        <p class="muted">Tenggat: {{ $assignment->due_at->format('d M Y, H:i') }}</p>
        <div class="prose">{{ $assignment->instructions }}</div>
    </x-card>

    @if (auth()->user()->role === 'mahasiswa')
        @php($isPastDue = now()->greaterThan($assignment->due_at))
        <x-card title="Pengumpulan tugas">
            @if ($submission && $submission->grade)
                <x-alert variant="success">Tugas sudah dinilai. Pengumpulan tidak dapat diubah.</x-alert>
            @elseif ($submission && $submission->is_late)
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
                    <dd>{{ $submission->original_name }} · {{ number_format($submission->file_size / 1024, 0) }} KB</dd>
                    <dt>Waktu dikumpulkan</dt>
                    <dd>{{ $submission->submitted_at->format('d M Y, H:i') }}</dd>
                    <dt>Catatan</dt>
                    <dd>{{ $submission->note ?: '—' }}</dd>
                </dl>
                <a class="button button--secondary" href="{{ route('submissions.download', $submission) }}">Unduh berkas saya</a>
                @if ($submission->grade)
                    <div class="grade-result">
                        <h2>Hasil penilaian</h2>
                        <p class="grade-score">{{ $submission->grade->score }} <span>/ {{ $assignment->max_score }}</span></p>
                        <p><strong>Umpan balik dosen:</strong> {{ $submission->grade->feedback ?: 'Belum ada umpan balik.' }}</p>
                    </div>
                @endif
            @endif

            @if ($canSubmit)
                <form action="{{ route('mahasiswa.assignments.submissions.store', $assignment) }}" method="POST"
                    enctype="multipart/form-data" class="form-stack submission-form">
                    @csrf
                    <h2>{{ $submission ? 'Kumpul Ulang' : 'Kumpul Tugas' }}</h2>
                    @if ($isPastDue && $assignment->allow_late)
                        <x-alert variant="warning">Pengumpulan ini akan tercatat sebagai terlambat.</x-alert>
                    @endif
                    <x-form-field name="file" label="Berkas tugas" hint="PDF, DOC, DOCX, atau ZIP. Ukuran maksimal 10 MB." :required="true">
                        <x-file-input id="file" name="file" accept=".pdf,.doc,.docx,.zip" required />
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
        <x-card title="Pengumpulan mahasiswa">
            <p class="muted">{{ $submissionCount }} berkas terkumpul · {{ $gradedSubmissionCount }} sudah dinilai</p>
            @if ($assignment->submissions->isEmpty())
                <x-empty-state title="Belum ada pengumpulan" />
            @else
                <x-table>
                    <thead><tr><th>Mahasiswa</th><th>Waktu kumpul</th><th>Status</th><th>Nilai</th><th>Unduh</th></tr></thead>
                    <tbody>
                        @foreach ($assignment->submissions as $studentSubmission)
                            <tr>
                                <td data-label="Mahasiswa">{{ $studentSubmission->student->name }}</td>
                                <td data-label="Waktu kumpul">{{ $studentSubmission->submitted_at->format('d M Y, H:i') }}</td>
                                <td data-label="Status"><x-badge :variant="$studentSubmission->is_late ? 'danger' : 'success'">{{ $studentSubmission->is_late ? 'Terlambat' : 'Tepat waktu' }}</x-badge></td>
                                <td data-label="Nilai">
                                    @if ($studentSubmission->grade)
                                        <x-badge variant="success">Dinilai · {{ $studentSubmission->grade->score }} / {{ $assignment->max_score }}</x-badge>
                                    @else
                                        <x-badge variant="warning">Belum dinilai</x-badge>
                                    @endif
                                </td>
                                <td data-label="Unduh"><a href="{{ route('submissions.download', $studentSubmission) }}">Unduh berkas</a></td>
                            </tr>
                            @if (auth()->user()->role === 'dosen')
                                <tr>
                                    <td colspan="5">
                                        <form action="{{ route('dosen.submissions.grade.update', $studentSubmission) }}" method="POST" class="form-grid">
                                            @csrf
                                            @method('PUT')
                                            <x-form-field name="score" id="score-{{ $studentSubmission->id }}" label="Nilai (maks. {{ $assignment->max_score }})" :required="true">
                                                <x-input id="score-{{ $studentSubmission->id }}" name="score" type="number" min="0"
                                                    max="{{ $assignment->max_score }}" step="0.01"
                                                    value="{{ old('score', $studentSubmission->grade?->score) }}" required />
                                            </x-form-field>
                                            <x-form-field name="feedback" id="feedback-{{ $studentSubmission->id }}" label="Umpan balik">
                                                <x-textarea id="feedback-{{ $studentSubmission->id }}" name="feedback">{{ old('feedback', $studentSubmission->grade?->feedback) }}</x-textarea>
                                            </x-form-field>
                                            <button class="button button--primary" type="submit">{{ $studentSubmission->grade ? 'Ubah Nilai' : 'Beri Nilai' }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </x-table>
            @endif
            @if (Route::has('dosen.courses.grades.index'))
                <p><a href="{{ route('dosen.courses.grades.index', $assignment->course) }}">Buka rekap nilai mata kuliah</a></p>
            @endif
        </x-card>
    @endif
</x-layout>
