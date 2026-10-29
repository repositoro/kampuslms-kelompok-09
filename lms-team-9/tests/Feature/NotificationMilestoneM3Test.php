<?php

namespace Tests\Feature;

use App\Events\AssignmentPublished;
use App\Events\GradeRecorded;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\GradeNotification;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationMilestoneM3Test extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_published_dispatches_event_and_notifies_enrolled_students(): void
    {
        Notification::fake();
        Event::fake([AssignmentPublished::class]);

        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $response = $this->actingAs($lecturer)->post("/dosen/courses/{$course->id}/assignments", [
            'title' => 'Tugas 1 Pemrograman Web',
            'instructions' => 'Kerjakan tugas sesuai modul.',
            'due_at' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'max_score' => 100,
            'allow_late' => true,
            'status' => 'published',
        ]);

        $response->assertRedirect();
        Event::assertDispatched(AssignmentPublished::class);

        // Uji listener secara nyata
        Notification::fake();
        $assignment = Assignment::where('title', 'Tugas 1 Pemrograman Web')->first();
        $listener = new \App\Listeners\SendAssignmentNotification();
        $listener->handle(new AssignmentPublished($assignment));

        Notification::assertSentTo($student, NewAssignmentNotification::class, function ($notification) use ($assignment) {
            $data = $notification->toDatabase($assignment);
            $this->assertEquals($assignment->id, $data['assignment_id']);
            $this->assertEquals($assignment->title, $data['title']);
            return true;
        });
    }

    public function test_grade_recorded_dispatches_event_and_notifies_student_with_safe_payload(): void
    {
        Notification::fake();
        Event::fake([GradeRecorded::class]);

        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->active()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Tugas Milestone M3',
        ]);

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => "submissions/{$assignment->id}/submission.pdf",
            'original_name' => 'submission.pdf',
            'file_size' => 2048,
            'submitted_at' => now(),
            'is_late' => false,
        ]);

        $response = $this->actingAs($lecturer)->put("/dosen/submissions/{$submission->id}/grade", [
            'score' => 95,
            'feedback' => 'Sangat rapi dan aman.',
        ]);

        $response->assertRedirect();
        Event::assertDispatched(GradeRecorded::class);

        // Uji Listener dan keamanan payload (Milestone M3 Poin 8)
        Notification::fake();
        $grade = Grade::where('submission_id', $submission->id)->first();
        $listener = new \App\Listeners\SendGradeNotification();
        $listener->handle(new GradeRecorded($grade));

        Notification::assertSentTo($student, GradeNotification::class, function ($notification) use ($grade) {
            $data = $notification->toDatabase($grade);

            // Verifikasi payload TIDAK memuat nilai maupun feedback
            $this->assertArrayNotHasKey('score', $data);
            $this->assertArrayNotHasKey('feedback', $data);

            // Verifikasi hanya memuat identitas dan judul
            $this->assertEquals($grade->id, $data['grade_id']);
            $this->assertEquals($grade->submission->assignment->title, $data['assignment_title']);
            return true;
        });
    }

    public function test_notification_listing_and_mark_as_read_with_idor_prevention(): void
    {
        $studentA = User::factory()->create(['role' => 'mahasiswa']);
        $studentB = User::factory()->create(['role' => 'mahasiswa']);

        // Buat notifikasi langsung di database untuk studentA
        $studentA->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\Notifications\NewAssignmentNotification',
            'data' => [
                'title' => 'Tugas Percobaan A',
                'message' => 'Tugas baru: Tugas Percobaan A',
                'url' => '/assignments/1',
            ],
            'read_at' => null,
        ]);

        $notificationA = $studentA->unreadNotifications()->first();

        // 1. Student A bisa melihat halaman notifikasi miliknya
        $this->actingAs($studentA)
            ->get('/notifications')
            ->assertOk()
            ->assertSee('Tugas Percobaan A')
            ->assertSee('Tandai dibaca');

        // 2. Student B mencoba menandai notifikasi milik Student A (IDOR Test)
        $this->actingAs($studentB)
            ->patch("/notifications/{$notificationA->id}/read")
            ->assertNotFound();

        $this->assertNull($notificationA->fresh()->read_at);

        // 3. Student A menandai notifikasi miliknya sendiri
        $this->actingAs($studentA)
            ->patch("/notifications/{$notificationA->id}/read")
            ->assertRedirect();

        $this->assertNotNull($notificationA->fresh()->read_at);

        // 4. Test unread count endpoint
        $response = $this->actingAs($studentA)->getJson('/notifications/unread-count');
        $response->assertOk()->assertJson(['count' => 0]);
    }

    public function test_course_cascade_deletion_removes_physical_storage_files(): void
    {
        Storage::fake('local');

        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        // Buat materi file fisik
        $materialPath = "materials/{$course->id}/slide.pdf";
        Storage::disk('local')->put($materialPath, 'konten materi');
        Material::create([
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'title' => 'Slide Kuliah',
            'type' => 'file',
            'file_path' => $materialPath,
            'original_name' => 'slide.pdf',
            'file_size' => 1234,
            'mime_type' => 'application/pdf',
        ]);

        // Buat assignment dan submission file fisik
        $assignment = Assignment::factory()->active()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
        ]);
        $submissionPath = "submissions/{$assignment->id}/tugas.pdf";
        Storage::disk('local')->put($submissionPath, 'konten tugas mahasiswa');
        Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => $submissionPath,
            'original_name' => 'tugas.pdf',
            'file_size' => 5678,
            'submitted_at' => now(),
            'is_late' => false,
        ]);

        Storage::disk('local')->assertExists($materialPath);
        Storage::disk('local')->assertExists($submissionPath);

        // Hapus Course (Cascade)
        $course->delete();

        // Pastikan seluruh file fisik terhapus dari disk lokal (tidak tertinggal orphan)
        Storage::disk('local')->assertMissing($materialPath);
        Storage::disk('local')->assertMissing($submissionPath);
    }

    public function test_submission_submitted_dispatches_event_and_notifies_lecturer(): void
    {
        Storage::fake('local');
        Notification::fake();
        Event::fake([\App\Events\SubmissionSubmitted::class]);

        $lecturer = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $course = Course::factory()->create(['lecturer_id' => $lecturer->id]);
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        $assignment = Assignment::factory()->active()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Tugas Coding',
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('jawaban.pdf', 100, 'application/pdf');

        $response = $this->actingAs($student)->post("/mahasiswa/assignments/{$assignment->id}/submissions", [
            'file' => $file,
            'note' => 'Ini jawaban saya.',
        ]);

        $response->assertRedirect();
        Event::assertDispatched(\App\Events\SubmissionSubmitted::class);

        // Uji listener pengiriman ke Dosen
        Notification::fake();
        $submission = Submission::where('assignment_id', $assignment->id)->first();
        $listener = new \App\Listeners\SendSubmissionNotification();
        $listener->handle(new \App\Events\SubmissionSubmitted($submission));

        Notification::assertSentTo($lecturer, \App\Notifications\SubmissionSubmittedNotification::class, function ($notification) use ($submission, $student) {
            $data = $notification->toDatabase($submission);
            $this->assertEquals($submission->id, $data['submission_id']);
            $this->assertEquals($student->name, $data['student_name']);
            return true;
        });
    }
}
