<x-layout title="Detail Tugas - {{ $assignment->title }}">
    <x-breadcrumb>
        <a href="{{ route('courses.show', $assignment->course) }}">{{ $assignment->course->name }}</a>
        <span>{{ $assignment->title }}</span>
    </x-breadcrumb>

    <x-page-header title="{{ $assignment->title }}" subtitle="Tenggat: {{ $assignment->due_at->format('d M Y, H:i') }}" />

    <div class="dashboard-grid">
        {{-- Deskripsi & Informasi Tugas --}}
        <x-card title="Instruksi Tugas">
            <p>{{ $assignment->description ?: 'Tidak ada deskripsi tambahan.' }}</p>
            <p><strong>Skor Maksimal:</strong> {{ $assignment->max_score }}</p>
            <p><strong>Izinkan Keterlambatan:</strong> {{ $assignment->allow_late ? 'Ya' : 'Tidak' }}</p>
        </x-card>

        {{-- Status Pengumpulan Saya --}}
        <x-card title="Pengumpulan Saya">
            @php($submission = $assignment->submissions->where('user_id', auth()->id())->first())

            @if ($submission)
                <div class="mb-4">
                    <p><strong>Status:</strong></p>
                    <x-badge :variant="$submission->grade ? 'success' : ($submission->is_late ? 'danger' : 'info')">
                        {{ $submission->grade ? 'Sudah Dinilai' : ($submission->is_late ? 'Terkumpul Terlambat' : 'Terkumpul Tepat Waktu') }}
                    </x-badge>

                    <div class="mt-3 form-stack">
                        <p class="muted">Berkas terunggah: <strong>{{ $submission->original_name }}</strong></p>
                        <p class="muted">Waktu dikirim: {{ $submission->submitted_at->format('d M Y, H:i') }}</p>

                        {{-- Tombol Unduh Jawaban Saya --}}
                        @if ($submission->file_path)
                            <div>
                                <a href="{{ route('submissions.download', $submission) }}" class="button button--secondary button--small">
                                    Unduh Berkas Saya
                                </a>
                            </div>
                        @endif

                        {{-- Informasi Nilai dan Catatan Dosen --}}
                        @if ($submission->grade)
                            <div class="alert alert-success mt-2">
                                <strong>Nilai: {{ $submission->grade->score }} / {{ $assignment->max_score }}</strong>
                                @if($submission->grade->feedback)
                                    <p class="mb-0 mt-1">Catatan Dosen: {{ $submission->grade->feedback }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Formulir Unggah / Ganti Berkas --}}
            @if (!$submission || !$submission->grade)
                <form action="{{ route('mahasiswa.assignments.submissions.store', $assignment) }}" method="POST" enctype="multipart/form-data" class="form-stack">
                    @csrf

                    <x-form-field name="file" label="{{ $submission ? 'Ganti Berkas Jawaban' : 'Unggah Berkas Jawaban' }}" hint="Format PDF, DOC, DOCX, ZIP, atau RAR. Maksimal 20 MB." :required="true">
                        <x-file-input id="file" name="file" accept=".pdf,.doc,.docx,.zip,.rar" required />
                    </x-form-field>

                    <x-form-field name="note" label="Catatan untuk Dosen (Opsional)">
                        <x-textarea id="note" name="note">{{ old('note', $submission?->note) }}</x-textarea>
                    </x-form-field>

                    <div class="cluster mt-3">
                        <x-button>{{ $submission ? 'Perbarui Pengumpulan' : 'Kumpulkan Tugas' }}</x-button>
                    </div>
                </form>
            @else
                <p class="muted mt-3">Tugas ini telah dinilai oleh dosen dan tidak dapat diubah kembali.</p>
            @endif
        </x-card>
    </div>
</x-layout>