@extends('layouts.app')

@section('content')

<div class="row justify-content-center">
	<div class="col-md-7 col-lg-5">
		<h1 class="h2">Buy internet</h1>
		<p class="fs-4 fw-semibold mb-1">KSh 10</p>
		<p class="text-secondary mb-4">12 hours of 10bob WiFi</p>

		<form aria-label="WiFi payment details">
			<div class="mb-3">
				<label for="phone" class="form-label">Phone number</label>
				<input
					id="phone"
					name="phone"
					type="tel"
					class="form-control"
					autocomplete="tel"
					placeholder="07XX XXX XXX"
					required
				>
			</div>

			<button type="button" class="btn btn-success" disabled>
				M-Pesa payments unavailable
			</button>
		</form>

		<p class="small text-secondary mt-3" role="status">
			Payments are unavailable while phone-only checkout is confirmed with Paystack.
		</p>
	</div>
</div>

@endsection