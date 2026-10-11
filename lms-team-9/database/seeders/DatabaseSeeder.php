<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([DemoAccountSeeder::class]);

        $dosenList = collect([User::where('email', 'dosen@kampuslms.test')->firstOrFail()])
            ->merge(User::factory(2)->create(['role' => 'dosen']));
        $mahasiswaList = collect([User::where('email', 'mahasiswa@kampuslms.test')->firstOrFail()])
            ->merge(User::factory(29)->create(['role' => 'mahasiswa']));

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
