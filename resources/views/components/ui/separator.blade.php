@props([
    'orientation' => 'horizontal',
    'className' => '',
])

@php
    $classes = "shrink-0 bg-border " . ($orientation === 'horizontal' ? 'h-[1px] w-full' : 'h-full w-[1px]') . " " . $className;
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}></div>
