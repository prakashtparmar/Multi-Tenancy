@props([
    'disabled' => false,
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'value' => '',
    'required' => false,
])

@php
    $id = $id ?? $name;
@endphp

<div class="flex flex-col">
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <input 
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ $value }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $required ? 'required' : '' }}
        {!! $attributes->merge(['class' => 'w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm']) !!}
    >
    
    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
