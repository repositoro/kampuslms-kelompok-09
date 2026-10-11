@php($fieldName = $attributes->get('name'))
@php($fieldId = $attributes->get('id', $fieldName))
@php($hasFieldError = $fieldName && $errors->has($fieldName))
<input type="file" {{ $attributes->class(['form-control', 'file-control'])->merge($hasFieldError ? [
    'aria-invalid' => 'true',
    'aria-describedby' => $fieldId.'-error',
] : []) }}>
