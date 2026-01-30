{{--
    Spinner/Loading Component

    Usage:
    <x-spinner />
    <x-spinner size="sm" />
    <x-spinner size="lg" />
    <x-spinner size="xl" />

    With text:
    <x-spinner text="Loading..." />
    <x-spinner text="Processing" size="lg" />

    Different variants:
    <x-spinner variant="primary" />
    <x-spinner variant="light" />
    <x-spinner variant="fire" />

    Inline (for buttons):
    <x-spinner size="sm" inline />

    Full page overlay:
    <x-spinner overlay text="Please wait..." />
--}}

@props([
    'size' => 'md',
    'text' => null,
    'variant' => 'primary',
    'inline' => false,
    'overlay' => false,
])

@php
    $sizeClasses = [
        'xs' => 'spinner-xs',
        'sm' => 'spinner-sm',
        'md' => 'spinner-md',
        'lg' => 'spinner-lg',
        'xl' => 'spinner-xl',
    ];

    $sizeClass = $sizeClasses[$size] ?? 'spinner-md';
@endphp

@if($overlay)
<div class="spinner-overlay">
    <div class="spinner-overlay-content">
        <div {{ $attributes->merge(['class' => "spinner {$sizeClass} spinner-{$variant}"]) }}>
            <div class="spinner-circle"></div>
        </div>
        @if($text)
            <span class="spinner-text">{{ $text }}</span>
        @endif
    </div>
</div>
@elseif($inline)
<span {{ $attributes->merge(['class' => "spinner-inline {$sizeClass} spinner-{$variant}"]) }}>
    <span class="spinner-circle"></span>
</span>
@else
<div {{ $attributes->merge(['class' => "spinner-container"]) }}>
    <div class="spinner {{ $sizeClass }} spinner-{{ $variant }}">
        <div class="spinner-circle"></div>
    </div>
    @if($text)
        <span class="spinner-text">{{ $text }}</span>
    @endif
</div>
@endif
