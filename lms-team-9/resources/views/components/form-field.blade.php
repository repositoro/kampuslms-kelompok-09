@props(['name', 'id' => null, 'label', 'required' => false, 'hint' => null])

<div class="form-field">
    <label for="{{ $id ?? $name }}" class="form-label">
        {{ $label }} @if ($required)<span aria-hidden="true">*</span>@endif
    </label>
    {{ $slot }}
    @if ($hint)
        <p class="field-hint">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
