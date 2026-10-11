<a class="nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">
    <span class="nav-indicator" aria-hidden="true"></span>Dashboard
</a>
@if (auth()->user()->role === 'admin')
    @if (Route::has('admin.users.index'))
        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" href="{{ route('admin.users.index') }}">
            <span class="nav-indicator" aria-hidden="true"></span>Pengguna
        </a>
    @endif
    @if (Route::has('courses.index'))
        <a class="nav-link {{ request()->routeIs('courses.*') ? 'is-active' : '' }}" href="{{ route('courses.index') }}">
            <span class="nav-indicator" aria-hidden="true"></span>Mata Kuliah
        </a>
    @endif
@elseif (in_array(auth()->user()->role, ['dosen', 'mahasiswa'], true))
    @if (Route::has('courses.index'))
        <a class="nav-link {{ request()->routeIs('courses.*', 'dosen.courses.*') ? 'is-active' : '' }}" href="{{ route('courses.index') }}">
            <span class="nav-indicator" aria-hidden="true"></span>Mata Kuliah Saya
        </a>
    @endif
    @if (auth()->user()->role === 'mahasiswa' && Route::has('mahasiswa.grades.index'))
        <a class="nav-link {{ request()->routeIs('mahasiswa.grades.*') ? 'is-active' : '' }}" href="{{ route('mahasiswa.grades.index') }}">
            <span class="nav-indicator" aria-hidden="true"></span>Nilai Saya
        </a>
    @endif
    @if (Route::has('notifications.index'))
        <a class="nav-link {{ request()->routeIs('notifications.*') ? 'is-active' : '' }}" href="{{ route('notifications.index') }}">
            <span class="nav-indicator" aria-hidden="true"></span>Notifikasi
        </a>
    @endif
@endif
