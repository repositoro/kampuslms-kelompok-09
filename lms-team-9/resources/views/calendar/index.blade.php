<x-layout title="Kalender Akademik">
    <x-page-header title="Kalender" subtitle="Tenggat tugas dan acara akademik dalam satu tampilan.">
        <x-slot:actions>
            <a class="button button--secondary" href="{{ route('calendar.export') }}">Ekspor .ics</a>
            @if (auth()->user()->role === 'admin')
                <a class="button button--primary" href="{{ route('admin.academic-events.create') }}">Acara baru</a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <section class="calendar" aria-label="Kalender akademik">
        <div class="calendar-toolbar">
            <form class="calendar-filter" action="{{ route('calendar.index') }}" method="GET">
                <div class="calendar-control">
                    <label for="calendar-course">Mata kuliah</label>
                    <select class="form-control" id="calendar-course" name="course_id">
                        <option value="">Semua mata kuliah</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" @selected((string) $selectedCourseId === (string) $course->id)>
                                {{ $course->code }} - {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="calendar-control">
                    <label for="calendar-view">Tampilan</label>
                    <select class="form-control" id="calendar-view" name="view">
                        <option value="month" @selected($view === 'month')>Bulanan</option>
                        <option value="day" @selected($view === 'day')>Harian</option>
                    </select>
                </div>
                <div class="calendar-control">
                    <label for="calendar-date">Tanggal</label>
                    <input class="form-control" id="calendar-date" name="date" type="date" value="{{ $selectedDate->format('Y-m-d') }}">
                </div>
                <div class="calendar-control">
                    <label for="calendar-month">Bulan dan tahun</label>
                    <input class="form-control" id="calendar-month" name="month" type="month" value="{{ $month->format('Y-m') }}">
                </div>
                <button class="button button--secondary" type="submit">Terapkan</button>
            </form>

            <nav class="calendar-month-nav" aria-label="Navigasi kalender">
                <a href="{{ route('calendar.index', $previousPeriodQuery) }}">
                    <span aria-hidden="true">&larr;</span> {{ $previousPeriodTitle }}
                </a>
                <h2>{{ $periodTitle }}</h2>
                <a href="{{ route('calendar.index', $nextPeriodQuery) }}">
                    {{ $nextPeriodTitle }} <span aria-hidden="true">&rarr;</span>
                </a>
            </nav>
        </div>

        <div class="calendar-scroll">
            <div class="calendar-grid {{ $view === 'day' ? 'calendar-grid--day' : '' }}" role="grid" aria-label="{{ $periodTitle }}">
                @if ($view === 'month')
                    @foreach (['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $weekday)
                        <div class="calendar-weekday" role="columnheader">{{ $weekday }}</div>
                    @endforeach
                @endif

                @foreach ($days as $day)
                    @php
                        $date = $day['date'];
                        $isCurrentMonth = $date->month === $month->month;
                        $isToday = $date->isToday();
                        $isSelectedDate = $date->isSameDay($selectedDate);
                    @endphp
                    <div
                        class="calendar-day {{ $isCurrentMonth ? '' : 'calendar-day--outside' }} {{ $isToday ? 'calendar-day--today' : '' }} {{ $isSelectedDate ? 'calendar-day--selected' : '' }}"
                        role="gridcell"
                        @if ($isToday) aria-current="date" @endif
                    >
                        <time class="calendar-date" datetime="{{ $date->toDateString() }}">{{ $date->day }}</time>
                        <ul class="calendar-events">
                            @foreach ($day['assignments'] as $assignment)
                                @php
                                    $assignmentRoute = auth()->user()->role === 'mahasiswa'
                                        ? 'mahasiswa.assignments.show'
                                        : 'dosen.assignments.show';
                                @endphp
                                <li>
                                    <a class="calendar-event" href="{{ route($assignmentRoute, $assignment) }}">
                                        <span class="calendar-event-dot" aria-hidden="true"></span>
                                        <span class="calendar-event-content">
                                            <span class="calendar-event-title">{{ $assignment->title }}</span>
                                            <span class="calendar-event-course">{{ $assignment->course->code }} · {{ $assignment->due_at->format('H:i') }}</span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                            @foreach ($day['events'] as $event)
                                <li>
                                    <div class="calendar-event calendar-event--academic" title="{{ $event->description }}">
                                        <span class="calendar-event-dot" aria-hidden="true"></span>
                                        <span class="calendar-event-content">
                                            <span class="calendar-event-title">{{ $event->title }}</span>
                                            <span class="calendar-event-course">
                                                {{ $event->start_at->format('H:i') }}
                                                @if ($event->location) · {{ $event->location }} @endif
                                            </span>
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
        @if ($assignments->isEmpty() && $events->isEmpty())
            <p class="calendar-empty">
                {{ $courses->isEmpty() ? 'Belum ada mata kuliah atau acara untuk ditampilkan.' : ($view === 'day' ? 'Tidak ada agenda pada tanggal ini.' : 'Tidak ada agenda pada rentang tanggal ini.') }}
            </p>
        @endif
    </section>
</x-layout>
