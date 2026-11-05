@extends('layouts.portal')

@section('title', 'Checkout | 10bob WiFi')

@section('content')
<section class="portal-panel checkout-panel" aria-labelledby="checkout-title">
    <a class="back-link" href="{{ route('home', $portalContext) }}"><span aria-hidden="true">&#8592;</span> All packages</a>

    <header class="checkout-heading">
        <p class="eyebrow">SECURE CHECKOUT</p>
        <h1 id="checkout-title">{{ $package->name }}</h1>
        <p class="intro-copy">{{ $package->duration }} {{ $package->unit }} of Wi-Fi access</p>
    </header>

    <div class="checkout-total">
        <span>Total to pay</span>
        <strong>KSh {{ number_format((float) $package->price, 0) }}</strong>
    </div>

    <form method="POST" action="{{ route('payments.initialize', $package) }}" class="checkout-form" aria-label="Wi-Fi payment details">
        @csrf
        @if (!empty($portalContext['ip']))
            <input type="hidden" name="device_ip" value="{{ $portalContext['ip'] }}">
        @endif
        @if (!empty($portalContext['mac']))
            <input type="hidden" name="device_mac" value="{{ $portalContext['mac'] }}">
        @endif
        @foreach (['link-login-only', 'link-orig'] as $contextKey)
            @if (!empty($portalContext[$contextKey]))
                <input type="hidden" name="{{ $contextKey }}" value="{{ $portalContext[$contextKey] }}">
            @endif
        @endforeach

        <div class="field-group">
            <label for="phone">Phone number</label>
            <input id="phone" name="phone" type="tel" inputmode="tel" class="@error('phone') input-error @enderror" value="{{ old('phone') }}" autocomplete="tel" placeholder="07XX XXX XXX" required>
            @error('phone')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <div class="field-group">
            <label for="email">Email for payment receipt</label>
            <input id="email" name="email" type="email" inputmode="email" class="@error('email') input-error @enderror" value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com" required>
            @error('email')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        @error('payment')
            <p class="form-alert" role="alert">{{ $message }}</p>
        @enderror

        <button class="primary-button" type="submit">Continue to Paystack <span aria-hidden="true">&#8594;</span></button>
    </form>

    <p class="portal-legal">Your connection is activated after payment verification.</p>
</section>
@endsection