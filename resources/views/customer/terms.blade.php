@extends('layouts.portal')

@section('title', 'Terms & conditions | 10bob WiFi')

@section('content')
<section class="portal-panel terms-panel" aria-labelledby="terms-title">
    <a class="back-link" href="{{ route('home') }}"><span aria-hidden="true">&#8592;</span> Packages</a>
    <header class="terms-heading">
        <p class="eyebrow">10BOB WIFI</p>
        <h1 id="terms-title">Terms &amp; conditions</h1>
        <p class="intro-copy">Terms for using the 10bob WiFi hotspot.</p>
    </header>

    <div class="terms-content">
        <section>
            <h2>Access and duration</h2>
            <p>Access starts after a successful payment is verified or a free trial is accepted by the hotspot. Each package is limited to its stated duration and device count. Trial availability and expiry are enforced by the MikroTik hotspot.</p>
        </section>
        <section>
            <h2>Payments</h2>
            <p>Package prices are shown in Kenyan shillings. Online payments are processed by Paystack. Access is enabled only after the payment is verified. Keep your payment reference if you need help.</p>
        </section>
        <section>
            <h2>Fair use</h2>
            <p>Do not use the service for unlawful activity, interfere with network operations, or share account credentials beyond the device allowance shown on your package. We may suspend access to protect the network or other customers.</p>
        </section>
        <section>
            <h2>Availability</h2>
            <p>WiFi availability, speed, and coverage can vary by location, device, signal, and network load. We may briefly interrupt service for maintenance or to address technical issues.</p>
        </section>
        <section>
            <h2>Help</h2>
            <p>For payment or connection support, call <a href="tel:+254743980340">+254743980340</a> or <a href="https://wa.me/254743980340" target="_blank" rel="noopener noreferrer">WhatsApp us</a>.</p>
        </section>
    </div>

    <a class="primary-button terms-return" href="{{ route('home') }}">Back to packages</a>
</section>
@endsection