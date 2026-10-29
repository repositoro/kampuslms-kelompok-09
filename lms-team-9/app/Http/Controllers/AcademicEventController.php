<?php

namespace App\Http\Controllers;

use App\Models\AcademicEvent;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class AcademicEventController extends Controller
{
    public function create()
    {
        return view('calendar.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
        ]);

        $event = AcademicEvent::create([
            ...$validated,
            'start_at' => CarbonImmutable::parse($validated['start_at'])->format('Y-m-d H:i:s'),
            'end_at' => CarbonImmutable::parse($validated['end_at'])->format('Y-m-d H:i:s'),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('calendar.index', ['month' => $event->start_at->format('Y-m')])
            ->with('success', 'Acara akademik berhasil ditambahkan.');
    }
}
