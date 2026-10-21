@component('mail::message', ['email' => $email])
    {!! $text !!}
    @if ($signature)
        @component('mail::subcopy')
            {!! $signature !!}
        @endcomponent
    @endif
@endcomponent
