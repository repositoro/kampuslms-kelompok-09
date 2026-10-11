@php($fieldName = $attributes->get('name'))
@php($fieldId = $attributes->get('id', $fieldName))
@php($hasFieldError = $fieldName && $errors->has($fieldName))
<select {{ $attributes->class(['form-control'])->merge($hasFieldError ? [
    'aria-invalid' => 'true',
    'aria-describedby' => $fieldId.'-error',
] : []) }}>{{ $slot }}</select>
