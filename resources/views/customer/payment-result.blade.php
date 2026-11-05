@extends('layouts.portal')

@section('content')
<section class="portal-panel result-panel" aria-labelledby="result-title">
    @if ($success)
        @if ($autoLogin)
            <div class="result-icon result-icon-success" aria-hidden="true">&#10003;</div>
            <p class="eyebrow">PAYMENT VERIFIED</p>
            <h1 id="result-title">Connecting you now</h1>
            <p class="intro-copy">Your {{ $payment->package->duration }} {{ $payment->package->unit }} package is ready. Keep this page open for a moment.</p>

            <form id="hotspot-login" method="POST" action="{{ $payment->router_login_url }}" class="login-fallback">
                <input type="hidden" name="username" value="{{ $payment->hotspotUser->username }}">
                <input type="hidden" name="password" value="{{ $payment->hotspotUser->password }}">
                <input type="hidden" name="dst" value="{{ $payment->router_redirect_url ?: config('app.url') }}">
                <button class="primary-button" type="submit">Connect to Wi-Fi <span aria-hidden="true">&#8594;</span></button>
            </form>
            <p class="portal-legal">If you are not redirected, tap Connect to Wi-Fi.</p>
            <script>
                window.addEventListener('load', () => document.getElementById('hotspot-login')?.requestSubmit());
            </script>
        @elseif ($payment->hotspotUser?->status === 'Provisioned')
            <div class="result-icon result-icon-success" aria-hidden="true">&#10003;</div>
            <p class="eyebrow">PAYMENT VERIFIED</p>
            <h1 id="result-title">You’re almost online</h1>
            <p class="intro-copy">Connect to 10bob WiFi and enter these hotspot credentials.</p>
            <dl class="credential-list">
                <div><dt>Username</dt><dd>{{ $payment->hotspotUser->username }}</dd></div>
                <div><dt>Password</dt><dd>{{ $payment->hotspotUser->password }}</dd></div>
            </dl>
            <p class="portal-legal">Reference: {{ $payment->reference }}</p>
        @else
            <div class="result-icon result-icon-pending" aria-hidden="true">!</div>
            <p class="eyebrow">PAYMENT VERIFIED</p>
            <h1 id="result-title">Setting up your access</h1>
            <p class="intro-copy" role="status">Your payment is confirmed, but Wi-Fi account setup is delayed. Please contact support with your reference.</p>
            <p class="portal-legal">Reference: {{ $payment->reference }}</p>
        @endif
    @else
        <div class="result-icon result-icon-pending" aria-hidden="true">!</div>
        <p class="eyebrow">PAYMENT STATUS</p>
        <h1 id="result-title">Payment not confirmed</h1>
        <p class="intro-copy" role="status">{{ $message }}</p>
        <p class="portal-legal">Reference: {{ $payment->reference }}</p>
    @endif
    <a class="back-link result-back" href="{{ route('home') }}">Return to packages</a>
</section>
@endsection