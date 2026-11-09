<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table): void {
            // Supports the student's published/upcoming assignment query:
            // course_id equality, status equality, then due_at range and ordering.
            $table->index(
                ['course_id', 'status', 'due_at'],
                'assignments_course_status_due_index',
            );
        });

        Schema::table('submissions', function (Blueprint $table): void {
            // Supports the student grade summary and dashboard's user-scoped submission lookups.
            $table->index(
                ['user_id', 'assignment_id'],
                'submissions_user_assignment_index',
            );
        });

        Schema::table('grades', function (Blueprint $table): void {
            // Supports both grade summary pages, which order graded records newest first.
            $table->index(
                ['graded_at', 'id'],
                'grades_graded_at_id_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table): void {
            $table->dropIndex('grades_graded_at_id_index');
        });

        Schema::table('submissions', function (Blueprint $table): void {
            $table->dropIndex('submissions_user_assignment_index');
        });

        Schema::table('assignments', function (Blueprint $table): void {
            $table->dropIndex('assignments_course_status_due_index');
        });
    }
};
