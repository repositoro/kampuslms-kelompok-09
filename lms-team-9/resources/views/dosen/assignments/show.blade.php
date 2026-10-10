<x-layout title="Detail Tugas">
    <h1 class="mb-4 text-xl font-bold">{{ $assignment->title }}</h1>
    <p class="whitespace-pre-line">{{ $assignment->instructions }}</p>
    <p class="mt-2">Mata kuliah: {{ $assignment->course->name }}</p>
    <p>Batas waktu: {{ $assignment->due_at->format('d M Y, H:i') }}</p>
    <p>Status: {{ $assignment->status }}</p>
    @can('delete', $assignment)
        <form
            action="{{ route('dosen.assignments.destroy', $assignment) }}"
            method="POST"
            class="mt-3"
            onsubmit="return confirm(this.dataset.confirm)"
            data-confirm="Hapus tugas ini? {{ $submissionCount }} pengumpulan dan {{ $gradedSubmissionCount }} nilai akan ikut terhapus."
        >
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-700 underline">Hapus Tugas</button>
        </form>
    @endcan

    @if (auth()->user()->role === 'mahasiswa')
        <section class="mt-6 rounded-lg border border-gray-200 bg-white p-6">
            <h2 class="mb-3 text-lg font-semibold">Pengumpulan Tugas</h2>
            @if ($submission)
                <p>Status:
                    @if ($submission->grade)
                        Dinilai
                    @elseif ($submission->is_late)
                        Terkumpul terlambat
                    @else
                        Terkumpul tepat waktu
                    @endif
                </p>
                <p>File: {{ $submission->original_name }}</p>
                <p>Waktu pengumpulan: {{ $submission->submitted_at->format('d M Y, H:i') }}</p>
                <a href="{{ route('submissions.download', $submission) }}" class="text-blue-700 underline">Unduh pengumpulan</a>
                @if ($submission->grade)
                    <div class="mt-4 rounded bg-green-50 p-3">
                        <p>Nilai: {{ $submission->grade->score }} / {{ $assignment->max_score }}</p>
                        <p>Feedback: {{ $submission->grade->feedback ?? 'Belum ada feedback.' }}</p>
                    </div>
                @endif
            @else
                <p>Belum mengumpulkan.</p>
            @endif

            @if ($canSubmit)
                <form
                    action="{{ route('mahasiswa.assignments.submissions.store', $assignment) }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="mt-4 space-y-3"
                >
                    @csrf
                    <div>
                        <label for="file" class="block text-sm font-medium">File tugas (PDF, DOC, DOCX, ZIP; maks. 10 MB)</label>
                        <input id="file" name="file" type="file" accept=".pdf,.doc,.docx,.zip" required>
                        @error('file')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="note" class="block text-sm font-medium">Catatan (opsional)</label>
                        <textarea id="note" name="note" rows="3" class="w-full rounded border-gray-300">{{ old('note') }}</textarea>
                        @error('note')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="rounded bg-slate-800 px-4 py-2 text-white">
                        {{ $submission ? 'Kumpulkan Ulang' : 'Kumpul Tugas' }}
                    </button>
                </form>
            @elseif (!$assignment->allow_late && now()->greaterThan($assignment->due_at))
                <p class="mt-3 text-sm text-red-700">Batas waktu pengumpulan telah lewat.</p>
            @endif
        </section>
    @endif

    @if (auth()->user()->role !== 'mahasiswa')
        <section class="mt-6 rounded-lg border border-gray-200 bg-white p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold">Pengumpulan</h2>
                <a href="{{ route('dosen.courses.grades.index', $assignment->course) }}" class="text-sm text-blue-700 underline">
                    Rekap nilai mata kuliah
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="p-2">Mahasiswa</th>
                            <th class="p-2">Dikumpulkan</th>
                            <th class="p-2">Status</th>
                            <th class="p-2">Nilai</th>
                            <th class="p-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assignment->submissions as $studentSubmission)
                            <tr class="border-b align-top">
                                <td class="p-2">{{ $studentSubmission->student->name }}</td>
                                <td class="p-2">{{ $studentSubmission->submitted_at->format('d M Y, H:i') }}</td>
                                <td class="p-2">{{ $studentSubmission->is_late ? 'Terlambat' : 'Tepat waktu' }}</td>
                                <td class="p-2">
                                    {{ $studentSubmission->grade ? $studentSubmission->grade->score : 'Belum dinilai' }}
                                </td>
                                <td class="p-2">
                                    <a href="{{ route('submissions.download', $studentSubmission) }}" class="text-blue-700 underline">
                                        Unduh
                                    </a>
                                    @if (auth()->user()->role === 'dosen')
                                        <form
                                            action="{{ route('dosen.submissions.grade.update', $studentSubmission) }}"
                                            method="POST"
                                            class="mt-2 space-y-2"
                                        >
                                            @csrf
                                            @method('PUT')
                                            <label class="block">
                                                Nilai (maks. {{ $assignment->max_score }})
                                                <input
                                                    type="number"
                                                    name="score"
                                                    min="0"
                                                    max="{{ $assignment->max_score }}"
                                                    step="0.01"
                                                    value="{{ old('score', $studentSubmission->grade?->score) }}"
                                                    required
                                                    class="w-24 rounded border-gray-300"
                                                >
                                            </label>
                                            <textarea name="feedback" rows="2" placeholder="Feedback (opsional)" class="w-full rounded border-gray-300">{{ old('feedback', $studentSubmission->grade?->feedback) }}</textarea>
                                            @error('score')
                                                <p class="text-red-600">{{ $message }}</p>
                                            @enderror
                                            @error('feedback')
                                                <p class="text-red-600">{{ $message }}</p>
                                            @enderror
                                            <button type="submit" class="rounded bg-slate-800 px-3 py-1 text-white">
                                                {{ $studentSubmission->grade ? 'Ubah Nilai' : 'Beri Nilai' }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-2 text-gray-500">Belum ada pengumpulan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</x-layout>
