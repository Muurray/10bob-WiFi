@extends('layouts.app')

@section('content')

<div class="row">

<div class="col-md-6">

<h1 class="display-4">
10bob WiFi
</h1>

<p class="lead">

Fast • Affordable • Reliable Internet

</p>

<p>

Connect instantly using M-Pesa or Voucher.

</p>

<a href="/buy" class="btn btn-success btn-lg">

Buy Internet

</a>

<a href="/voucher"
class="btn btn-outline-primary btn-lg">

Use Voucher

</a>
<a href="/admin/login"
class="btn btn-dark">

Administrator Login

</a>
</div>

<div class="col-md-6 text-center">

<img src="https://cdn-icons-png.flaticon.com/512/1041/1041916.png"

width="300">

</div>

</div>

<section class="container py-5" id="packages">
	<div class="text-center mb-4">
		<h2>Available Packages</h2>
		<p class="text-muted">Choose an affordable internet package.</p>
	</div>

	<div class="row g-4 justify-content-center">
		@forelse ($packages as $package)
			<div class="col-6 col-md-4 col-lg-2">
				<div class="card h-100 shadow-sm text-center">
					<div class="card-body d-flex flex-column">
						<h3 class="text-success">KSh {{ number_format((float) $package->price, 0) }}</h3>
						<p class="text-muted mb-2">{{ $package->duration }} {{ $package->unit }}</p>
						<span class="badge text-bg-success align-self-center mb-3">Available</span>
						<a href="{{ route('payments.show', $package) }}" class="btn btn-success w-100 mt-auto">
							Buy Now
						</a>
					</div>
				</div>
			</div>
		@empty
			<p class="text-center text-muted">No internet packages are available right now.</p>
		@endforelse
	</div>
</section>

@endsection