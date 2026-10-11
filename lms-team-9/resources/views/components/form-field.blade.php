@props(['name', 'id' => null, 'label', 'required' => false, 'hint' => null])

@php($fieldId = $id ?? $name)
<div class="form-field">
    <label for="{{ $fieldId }}" class="form-label">
        {{ $label }} @if ($required)<span aria-hidden="true">*</span>@endif
    </label>
    {{ $slot }}
    @if ($hint)
        <p class="field-hint">{{ $hint }}</p>
    @endif
    @error($name)
        <p id="{{ $fieldId }}-error" class="field-error">{{ $message }}</p>
    @enderror
</div>
