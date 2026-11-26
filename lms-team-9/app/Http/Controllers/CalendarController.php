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
            'date' => ['nullable', 'date_format:Y-m-d'],
            'view' => ['nullable', 'in:day,month'],
            'course_id' => ['nullable', 'integer'],
        ]);
        $view = $validated['view'] ?? 'month';
        $selectedDate = CarbonImmutable::createFromFormat('!Y-m-d', $validated['date'] ?? now()->toDateString());
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

        $month = $view === 'day'
            ? $selectedDate->startOfMonth()
            : CarbonImmutable::createFromFormat('!Y-m', $validated['month'] ?? now()->format('Y-m'));
        $gridStart = $month->startOfMonth()->startOfWeek(CarbonImmutable::SUNDAY);
        $gridEnd = $month->endOfMonth()->endOfWeek(CarbonImmutable::SATURDAY);
        $periodStart = $view === 'day' ? $selectedDate : $gridStart;
        $periodEnd = $view === 'day' ? $selectedDate : $gridEnd;
        $courseIds = $selectedCourseId === null
            ? $courses->modelKeys()
            : [(int) $selectedCourseId];

        $assignments = Assignment::query()
            ->with('course')
            ->whereIn('course_id', $courseIds)
            ->whereBetween('due_at', [$periodStart->startOfDay(), $periodEnd->endOfDay()])
            ->when($user->role === 'mahasiswa', fn ($query) => $query->where('status', 'published'))
            ->orderBy('due_at')
            ->get()
            ->groupBy(fn (Assignment $assignment) => $assignment->due_at->toDateString());
        $events = AcademicEvent::query()
            ->whereBetween('start_at', [$periodStart->startOfDay(), $periodEnd->endOfDay()])
            ->orderBy('start_at')
            ->get()
            ->groupBy(fn (AcademicEvent $event) => $event->start_at->toDateString());

        $days = [];
        for ($date = $periodStart; $date <= $periodEnd; $date = $date->addDay()) {
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
        $periodTitle = $view === 'day'
            ? $selectedDate->locale('id')->translatedFormat('l, d F Y')
            : $monthTitle;
        $previousPeriod = $view === 'day' ? $selectedDate->subDay() : $month->subMonth();
        $nextPeriod = $view === 'day' ? $selectedDate->addDay() : $month->addMonth();
        $previousPeriodTitle = $view === 'day'
            ? $previousPeriod->locale('id')->translatedFormat('D, d M Y')
            : $monthNames[$previousPeriod->month].' '.$previousPeriod->year;
        $nextPeriodTitle = $view === 'day'
            ? $nextPeriod->locale('id')->translatedFormat('D, d M Y')
            : $monthNames[$nextPeriod->month].' '.$nextPeriod->year;
        $previousPeriodQuery = ['view' => $view, 'course_id' => $selectedCourseId];
        $nextPeriodQuery = $previousPeriodQuery;

        if ($view === 'day') {
            $previousPeriodQuery['date'] = $previousPeriod->toDateString();
            $nextPeriodQuery['date'] = $nextPeriod->toDateString();
        } else {
            $previousPeriodQuery['month'] = $previousPeriod->format('Y-m');
            $nextPeriodQuery['month'] = $nextPeriod->format('Y-m');
        }

        return view('calendar.index', compact(
            'courses',
            'days',
            'assignments',
            'events',
            'view',
            'month',
            'monthTitle',
            'selectedDate',
            'periodTitle',
            'previousPeriodTitle',
            'nextPeriodTitle',
            'previousPeriodQuery',
            'nextPeriodQuery',
            'selectedCourseId',
        ));
    }
}
