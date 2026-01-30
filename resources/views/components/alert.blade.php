{{--
    Alert Component

    Usage:
    <x-alert type="success" message="Operation completed!" />
    <x-alert type="error" message="Something went wrong" />
    <x-alert type="warning" message="Please check your input" />
    <x-alert type="info" message="New updates available" />

    With dismissible:
    <x-alert type="success" message="Saved!" dismissible />

    With custom icon:
    <x-alert type="info" icon="fa-bell" message="Notification" />

    With slot content:
    <x-alert type="error">
        <strong>Error:</strong> Multiple issues found.
    </x-alert>
--}}

@props([
    'type' => 'info',
    'message' => null,
    'icon' => null,
    'dismissible' => false,
])

@php
    $icons = [
        'success' => 'fa-check-circle',
        'error' => 'fa-exclamation-circle',
        'warning' => 'fa-exclamation-triangle',
        'info' => 'fa-info-circle',
    ];

    $alertIcon = $icon ?? ($icons[$type] ?? 'fa-info-circle');
@endphp

<div {{ $attributes->merge(['class' => "alert alert-{$type}"]) }} role="alert">
    <div class="alert-content">
        <i class="fas {{ $alertIcon }} alert-icon"></i>
        <span class="alert-message">
            {{ $message ?? $slot }}
        </span>
    </div>
    @if($dismissible)
        <button type="button" class="alert-dismiss" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    @endif
</div>
