<x-layout title="Tambah Acara Akademik">
    <x-breadcrumb>
        <a href="{{ route('calendar.index') }}">Kalender</a>
        <span>Tambah acara</span>
    </x-breadcrumb>
    <x-page-header title="Acara akademik baru" subtitle="Acara ini akan tampil di kalender semua pengguna." />

    <x-card>
        <form class="form-stack" action="{{ route('admin.academic-events.store') }}" method="POST">
            @csrf
            <x-form-field name="title" label="Nama acara" required>
                <x-input name="title" :value="old('title')" required maxlength="255" />
            </x-form-field>

            <x-form-field name="description" label="Deskripsi">
                <x-textarea name="description" rows="4" maxlength="5000">{{ old('description') }}</x-textarea>
            </x-form-field>

            <x-form-field name="location" label="Lokasi">
                <x-input name="location" :value="old('location')" maxlength="255" />
            </x-form-field>

            <div class="form-grid">
                <x-form-field name="start_at" label="Waktu mulai (UTC)" required>
                    <x-input type="datetime-local" name="start_at" :value="old('start_at')" required />
                </x-form-field>
                <x-form-field name="end_at" label="Waktu selesai (UTC)" required>
                    <x-input type="datetime-local" name="end_at" :value="old('end_at')" required />
                </x-form-field>
            </div>

            <div class="cluster">
                <button class="button button--primary" type="submit">Simpan acara</button>
                <a class="button button--secondary" href="{{ route('calendar.index') }}">Batal</a>
            </div>
        </form>
    </x-card>
</x-layout>
