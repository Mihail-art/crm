@props([
    'name' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'id' => null,
    'extraOptions' => [],
])

@php
    $options = collect($options);
    $extraOptions = collect($extraOptions);

    $selectedValue = (string) ($selected ?? '');
    $selectedLabel = $placeholder;
    $matched = false;

    foreach ($options as $value => $label) {
        if ((string) $value === $selectedValue) {
            $selectedLabel = $label;
            $matched = true;
        }
    }

    // Mimic native <select> behaviour: with no placeholder and no match,
    // the browser defaults to (and displays) the first option.
    if (! $matched && ! $placeholder && $options->isNotEmpty()) {
        $selectedValue = (string) $options->keys()->first();
        $selectedLabel = $options->first();
    }
@endphp

<div class="custom-select" data-custom-select>
    <button type="button" class="btn form-select-custom dropdown-toggle w-100 text-start" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="custom-select-label">{{ $selectedLabel }}</span>
    </button>
    <ul class="dropdown-menu w-100">
        @if ($placeholder)
            <li><a class="dropdown-item @if ($selectedValue === '') active @endif" href="#" data-value="">{{ $placeholder }}</a></li>
        @endif
        @foreach ($options as $value => $label)
            <li><a class="dropdown-item @if ((string) $value === $selectedValue) active @endif" href="#" data-value="{{ $value }}">{{ $label }}</a></li>
        @endforeach
        @if (count($extraOptions))
            <li><hr class="dropdown-divider"></li>
            @foreach ($extraOptions as $value => $label)
                <li><a class="dropdown-item text-primary fw-semibold" href="#" data-value="{{ $value }}">{{ $label }}</a></li>
            @endforeach
        @endif
    </ul>
    <select
        @if ($name) name="{{ $name }}" @endif
        @if ($id) id="{{ $id }}" @endif
        {{ $attributes->merge(['class' => 'd-none']) }}
    >
        @if ($placeholder)
            <option value=""></option>
        @endif
        @foreach ($options as $value => $label)
            <option value="{{ $value }}" @selected((string) $value === $selectedValue)>{{ $label }}</option>
        @endforeach
        @foreach ($extraOptions as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>
