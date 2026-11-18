<?php

namespace App\Http\Controllers;

use App\Models\AcademicEvent;
use App\Models\Assignment;
use App\Models\Course;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
            'course_id' => ['nullable', 'integer'],
        ]);
        $user = $request->user();
        $courses = match ($user->role) {
            'admin' => Course::query()->orderBy('name')->get(),
            'dosen' => $user->taughtCourses()->orderBy('name')->get(),
            'mahasiswa' => $user->courses()->orderBy('name')->get(),
            default => abort(403),
        };
        $selectedCourseId = $validated['course_id'] ?? null;

        if ($selectedCourseId !== null && ! $courses->contains('id', (int) $selectedCourseId)) {
            abort(404);
        }

        $month = CarbonImmutable::createFromFormat('!Y-m', $validated['month'] ?? now()->format('Y-m'));
        $gridStart = $month->startOfMonth()->startOfWeek(CarbonImmutable::SUNDAY);
        $gridEnd = $month->endOfMonth()->endOfWeek(CarbonImmutable::SATURDAY);
        $courseIds = $selectedCourseId === null
            ? $courses->modelKeys()
            : [(int) $selectedCourseId];

        $assignments = Assignment::query()
            ->with('course')
            ->whereIn('course_id', $courseIds)
            ->whereBetween('due_at', [$gridStart->startOfDay(), $gridEnd->endOfDay()])
            ->when($user->role === 'mahasiswa', fn ($query) => $query->where('status', 'published'))
            ->orderBy('due_at')
            ->get()
            ->groupBy(fn (Assignment $assignment) => $assignment->due_at->toDateString());
        $events = AcademicEvent::query()
            ->whereBetween('start_at', [$gridStart->startOfDay(), $gridEnd->endOfDay()])
            ->orderBy('start_at')
            ->get()
            ->groupBy(fn (AcademicEvent $event) => $event->start_at->toDateString());

        $days = [];
        for ($date = $gridStart; $date <= $gridEnd; $date = $date->addDay()) {
            $days[] = [
                'date' => $date,
                'assignments' => $assignments->get($date->toDateString(), collect()),
                'events' => $events->get($date->toDateString(), collect()),
            ];
        }

        $monthNames = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];
        $monthTitle = $monthNames[$month->month].' '.$month->year;
        $previousMonth = $month->subMonth();
        $nextMonth = $month->addMonth();
        $previousMonthTitle = $monthNames[$previousMonth->month];
        $nextMonthTitle = $monthNames[$nextMonth->month];

        return view('calendar.index', compact(
            'courses',
            'days',
            'assignments',
            'events',
            'month',
            'monthTitle',
            'previousMonth',
            'previousMonthTitle',
            'nextMonth',
            'nextMonthTitle',
            'selectedCourseId',
        ));
    }
}
