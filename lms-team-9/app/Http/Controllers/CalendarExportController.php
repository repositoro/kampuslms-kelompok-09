<?php

namespace App\Http\Controllers;

use App\Models\AcademicEvent;
use App\Models\Assignment;
use App\Models\Course;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CalendarExportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $courses = match ($user->role) {
            'admin' => Course::query()->pluck('id'),
            'dosen' => $user->taughtCourses()->pluck('courses.id'),
            'mahasiswa' => $user->courses()->pluck('courses.id'),
            default => abort(403),
        };

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//KampusLMS//Kalender Akademik//ID',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
        ];

        $assignments = Assignment::query()
            ->with('course')
            ->whereIn('course_id', $courses)
            ->when($user->role === 'mahasiswa', fn ($query) => $query->where('status', 'published'))
            ->orderBy('due_at')
            ->get();

        foreach ($assignments as $assignment) {
            $lines = [
                ...$lines,
                'BEGIN:VEVENT',
                'UID:assignment-'.$assignment->id.'@kampuslms',
                'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
                'DTSTART:'.$this->formatUtc($assignment->due_at),
                'SUMMARY:'.$this->escapeText('Tenggat tugas: '.$assignment->title),
                'DESCRIPTION:'.$this->escapeText('Mata kuliah: '.$assignment->course->code.' - '.$assignment->course->name),
                'END:VEVENT',
            ];
        }

        foreach (AcademicEvent::query()->orderBy('start_at')->get() as $event) {
            $lines = [
                ...$lines,
                'BEGIN:VEVENT',
                'UID:academic-event-'.$event->id.'@kampuslms',
                'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
                'DTSTART:'.$this->formatUtc($event->start_at),
                'DTEND:'.$this->formatUtc($event->end_at),
                'SUMMARY:'.$this->escapeText($event->title),
                ...($event->description ? ['DESCRIPTION:'.$this->escapeText($event->description)] : []),
                ...($event->location ? ['LOCATION:'.$this->escapeText($event->location)] : []),
                'END:VEVENT',
            ];
        }

        $lines[] = 'END:VCALENDAR';
        $calendar = implode("\r\n", array_map(fn (string $line) => $this->foldLine($line), $lines))."\r\n";

        return response($calendar, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="kampuslms-kalender.ics"',
        ]);
    }

    private function formatUtc(CarbonInterface $date): string
    {
        return $date->copy()->utc()->format('Ymd\THis\Z');
    }

    private function escapeText(string $text): string
    {
        return str_replace(
            ['\\', ';', ',', "\r\n", "\r", "\n"],
            ['\\\\', '\;', '\,', '\n', '\n', '\n'],
            $text,
        );
    }

    private function foldLine(string $line): string
    {
        $folded = [];
        $firstLine = true;

        while (strlen($line) > 75) {
            $limit = $firstLine ? 75 : 74;
            $chunk = mb_strcut($line, 0, $limit, 'UTF-8');
            $folded[] = ($firstLine ? '' : ' ').$chunk;
            $line = substr($line, strlen($chunk));
            $firstLine = false;
        }

        $folded[] = ($firstLine ? '' : ' ').$line;

        return implode("\r\n", $folded);
    }
}
