@extends('layouts.app')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        @if ($success)
            <h1 class="h2 text-success">Payment received and verified</h1>
            <p>Your {{ $payment->package->duration }} {{ $payment->package->unit }} package has been paid for.</p>
            <p class="mb-1">Payment reference: <strong>{{ $payment->reference }}</strong></p>
            <p>Access period ends: <strong>{{ $payment->access_expires_at?->toDayDateTimeString() }}</strong></p>
            <p class="small text-secondary" role="status">
                Wi-Fi access still requires hotspot provisioning by the administrator.
            </p>
        @else
            <h1 class="h2">Payment not confirmed</h1>
            <p role="status">{{ $message }}</p>
            <p class="small text-secondary">Payment reference: {{ $payment->reference }}</p>
        @endif

        <a href="{{ route('home') }}#packages" class="btn btn-outline-primary">Back to packages</a>
    </div>
</div>

@endsection