{{--
    Session Alerts Component
    Automatically displays Laravel session flash messages

    Usage:
    <x-session-alerts />

    Supports these session keys:
    - success
    - error
    - warning
    - info
    - status (mapped to info)

    Also displays validation errors automatically.
--}}

@props(['dismissible' => true])

@if(session('success'))
    <x-alert type="success" :message="session('success')" :dismissible="$dismissible" />
@endif

@if(session('error'))
    <x-alert type="error" :message="session('error')" :dismissible="$dismissible" />
@endif

@if(session('warning'))
    <x-alert type="warning" :message="session('warning')" :dismissible="$dismissible" />
@endif

@if(session('info'))
    <x-alert type="info" :message="session('info')" :dismissible="$dismissible" />
@endif

@if(session('status'))
    <x-alert type="info" :message="session('status')" :dismissible="$dismissible" />
@endif

@if($errors->any())
    <div class="alert alert-error" role="alert">
        <div class="alert-content">
            <i class="fas fa-exclamation-circle alert-icon"></i>
            <div class="alert-message">
                @if($errors->count() === 1)
                    {{ $errors->first() }}
                @else
                    <ul style="margin: 0; padding-left: 16px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        @if($dismissible)
            <button type="button" class="alert-dismiss" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        @endif
    </div>
@endif
