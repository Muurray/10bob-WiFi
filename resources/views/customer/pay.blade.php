@extends('layouts.app')

@section('content')

<div class="row justify-content-center">
	<div class="col-md-7 col-lg-5">
		<h1 class="h2">Buy internet</h1>
		<p class="fs-4 fw-semibold mb-1">KSh {{ number_format((float) $package->price, 0) }}</p>
		<p class="text-secondary mb-4">{{ $package->duration }} {{ $package->unit }} package</p>

		<form method="POST" action="{{ route('payments.initialize', $package) }}" aria-label="WiFi payment details">
			@csrf
			<div class="mb-3">
				<label for="email" class="form-label">Email address</label>
				<input
					id="email"
					name="email"
					type="email"
					class="form-control @error('email') is-invalid @enderror"
					value="{{ old('email') }}"
					autocomplete="email"
					required
				>
				@error('email')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
			</div>

			<div class="mb-3">
				<label for="phone" class="form-label">Phone number</label>
				<input
					id="phone"
					name="phone"
					type="tel"
					class="form-control @error('phone') is-invalid @enderror"
					value="{{ old('phone') }}"
					autocomplete="tel"
					placeholder="07XX XXX XXX"
					required
				>
				@error('phone')
					<div class="invalid-feedback">{{ $message }}</div>
				@enderror
			</div>

			@error('payment')
				<p class="text-danger" role="alert">{{ $message }}</p>
			@enderror

			<button type="submit" class="btn btn-success">
				Continue to payment
			</button>
		</form>

		<p class="small text-secondary mt-3" role="status">
			After payment confirmation, Wi-Fi access still requires hotspot provisioning by the administrator.
		</p>
	</div>
</div>

@endsection