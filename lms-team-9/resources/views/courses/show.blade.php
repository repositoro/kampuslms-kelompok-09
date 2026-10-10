<x-layout title="Detail Mata Kuliah">
    <h1 class="mb-4 text-xl font-bold">Detail Mata Kuliah</h1>

    <p><strong>Kode:</strong> {{ $course->code }}</p>
    <p><strong>Nama:</strong> {{ $course->name }}</p>
    <p><strong>SKS:</strong> {{ $course->sks }}</p>
    <p><strong>Dosen Pengampu:</strong> {{ $course->lecturer ? $course->lecturer->name : 'Belum Ditentukan' }}</p>
    <p><strong>Status:</strong> {{ ucfirst($course->status) }}</p>
    <p><strong>Deskripsi:</strong> {{ $course->description ?? '-' }}</p>

    <p class="my-4">
        @can('update', $course)
            <a href="{{ route('courses.edit', $course) }}">Edit</a> |
        @endcan
        <a href="{{ route('courses.index') }}">Kembali ke Daftar</a>
    </p>

    @if ($canManageEnrollment)
        <section class="mt-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">Peserta</h2>

            <ul class="mb-6 space-y-2">
                @forelse ($course->students->where('role', 'mahasiswa') as $student)
                    <li class="flex items-center justify-between gap-4">
                        <span>{{ $student->name }} ({{ $student->email }})</span>
                        <form
                            action="{{ route('courses.enrollments.destroy', [$course, $student]) }}"
                            method="POST"
                            onsubmit="return confirm('Keluarkan mahasiswa ini dari mata kuliah?')"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-700 hover:underline">Keluarkan</button>
                        </form>
                    </li>
                @empty
                    <li class="text-sm text-gray-500">Belum ada mahasiswa terdaftar.</li>
                @endforelse
            </ul>

            <form action="{{ route('courses.enrollments.store', $course) }}" method="POST" class="flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label for="user_id" class="mb-1 block text-sm font-medium">Daftarkan mahasiswa</label>
                    <select id="user_id" name="user_id" required class="rounded-md border-gray-300">
                        <option value="">Pilih mahasiswa</option>
                        @foreach ($availableStudents as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button
                    type="submit"
                    @disabled($availableStudents->isEmpty())
                    class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Daftarkan
                </button>
            </form>
            @error('student')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </section>
    @endif

    <section class="mt-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold">Tugas</h2>
        <ul class="space-y-3">
            @forelse ($course->assignments as $assignment)
                @php($submission = $assignment->submissions->first())
                <li class="flex flex-wrap items-center justify-between gap-3 border-b pb-3 last:border-0">
                    <div>
                        <a href="{{ route('dosen.assignments.show', $assignment) }}" class="font-medium text-blue-700 underline">
                            {{ $assignment->title }}
                        </a>
                        <p class="text-sm text-gray-600">Tenggat: {{ $assignment->due_at->format('d M Y, H:i') }}</p>
                        @if (auth()->user()->role === 'mahasiswa')
                            <p class="text-sm">
                                Status:
                                @if (!$submission)
                                    Belum mengumpulkan
                                @elseif ($submission->grade)
                                    Dinilai — {{ $submission->grade->score }} / {{ $assignment->max_score }}
                                @elseif ($submission->is_late)
                                    Terkumpul terlambat
                                @else
                                    Terkumpul tepat waktu
                                @endif
                            </p>
                        @endif
                    </div>
                    @if (auth()->user()->role !== 'mahasiswa')
                        <a href="{{ route('dosen.courses.assignments.index', $course) }}" class="text-sm text-blue-700 underline">
                            Kelola tugas
                        </a>
                    @endif
                </li>
            @empty
                <li class="text-sm text-gray-500">Belum ada tugas.</li>
            @endforelse
        </ul>
    </section>
</x-layout>