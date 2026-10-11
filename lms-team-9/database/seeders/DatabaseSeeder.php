<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([DemoAccountSeeder::class]);

        $dosenList = collect([User::where('email', 'dosen@kampuslms.test')->firstOrFail()])
            ->merge(User::factory(2)->create(['role' => 'dosen']));
        $mahasiswaList = collect([User::where('email', 'mahasiswa@kampuslms.test')->firstOrFail()])
            ->merge(User::factory(29)->create(['role' => 'mahasiswa']));

        $storage = Storage::disk('local');
        $dummyPath = 'submissions/dummy.pdf';

        if (! $storage->exists($dummyPath)) {
            $content = 'BT /F1 18 Tf 40 80 Td (KampusLMS - Berkas contoh) Tj ET';
            $objects = [
                '<< /Type /Catalog /Pages 2 0 R >>',
                '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 300 144] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
                '<< /Length '.strlen($content)." >>\nstream\n".$content."\nendstream",
                '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            ];
            $pdf = "%PDF-1.4\n";
            $offsets = [0];

            foreach ($objects as $index => $object) {
                $offsets[] = strlen($pdf);
                $pdf .= ($index + 1)." 0 obj\n".$object."\nendobj\n";
            }

            $xrefOffset = strlen($pdf);
            $pdf .= "xref\n0 ".count($offsets)."\n0000000000 65535 f \n";

            foreach (array_slice($offsets, 1) as $offset) {
                $pdf .= sprintf("%010d 00000 n \n", $offset);
            }

            $pdf .= "trailer\n<< /Size ".count($offsets)." /Root 1 0 R >>\nstartxref\n".$xrefOffset."\n%%EOF\n";

            if (! $storage->put($dummyPath, $pdf)) {
                throw new RuntimeException('Gagal membuat berkas submission contoh.');
            }
        }

        $courses = Course::factory(5)->recycle($dosenList)->create();

        foreach ($courses as $course) {
            $enrolledStudents = $mahasiswaList->random(18);
            foreach ($enrolledStudents as $student) {
                $course->students()->attach($student->id, ['enrolled_at' => now()]);
            }

            $assignments = collect([
                Assignment::factory()->overdue()->create([
                    'course_id' => $course->id,
                    'created_by' => $course->lecturer_id,
                ]),
                Assignment::factory()->active()->create([
                    'course_id' => $course->id,
                    'created_by' => $course->lecturer_id,
                ]),
                Assignment::factory()->draft()->create([
                    'course_id' => $course->id,
                    'created_by' => $course->lecturer_id,
                ]),
            ]);

            foreach ($assignments as $assignment) {
                foreach ($enrolledStudents->random(10)->values() as $index => $student) {
                    $submission = Submission::create([
                        'assignment_id' => $assignment->id,
                        'user_id' => $student->id,
                        'file_path' => 'submissions/dummy.pdf',
                        'original_name' => 'tugas.pdf',
                        'file_size' => 1024,
                        'submitted_at' => now(),
                        'is_late' => false,
                    ]);

                    if ($index < 6) {
                        Grade::create([
                            'submission_id' => $submission->id,
                            'graded_by' => $course->lecturer_id,
                            'score' => rand(70, 100),
                            'feedback' => 'Bagus!',
                            'graded_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
