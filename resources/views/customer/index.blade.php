@extends('layouts.portal')

@section('title', 'Choose a package | 10bob WiFi')

@section('content')
<section class="portal-panel" aria-labelledby="portal-title">
	<header class="portal-heading">
		<div class="brand-mark" aria-hidden="true"><span></span><span></span><span></span></div>
		<p class="eyebrow">CONNECTED TO 10BOB</p>
		<h1 id="portal-title">Internet, on your terms.</h1>
		<p class="intro-copy">Choose a package to get online.</p>
	</header>

	<div class="connection-note">
		<span class="connection-dot" aria-hidden="true"></span>
		<span>Choose a plan. Pay securely. Get connected.</span>
	</div>

	<div class="support-strip">
		<strong>Need help?</strong>
		<a href="tel:+254743980340">Call</a>
		<span>or WhatsApp</span>
		<a href="https://wa.me/254743980340" target="_blank" rel="noopener noreferrer">+254743980340</a>
	</div>

	@if ($trialUrl)
		<div class="trial-offer">
			<div>
				<div class="trial-title">Free trial <span>12 HOURS</span></div>
				<p>Try the network before choosing a package.</p>
			</div>
			<a class="trial-button" href="{{ $trialUrl }}">Start trial <span aria-hidden="true">&#8594;</span></a>
		</div>
	@endif

	<div class="package-heading">
		<span>Available packages</span>
		<span class="package-count">{{ $packages->count() }} PLANS</span>
	</div>

	<div class="package-list">
		@forelse ($packages as $package)
			<article class="package-row">
				<div class="package-info">
					<h2>{{ $package->name }}</h2>
					<p>{{ $package->duration }} {{ $package->unit }}@if (($package->device_limit ?? 1) > 1) <span class="device-count">· {{ $package->device_limit }} devices</span>@endif</p>
				</div>
				<div class="package-action">
					<strong>KSh {{ number_format((float) $package->price, 0) }}</strong>
					<a class="buy-button" href="{{ route('payments.show', array_merge(['package' => $package->id], $portalContext)) }}" aria-label="Buy {{ $package->name }} for KSh {{ number_format((float) $package->price, 0) }}">
						Buy <span aria-hidden="true">&#8599;</span>
					</a>
				</div>
			</article>
		@empty
			<p class="empty-state">No packages are available right now.</p>
		@endforelse
	</div>

	<p class="portal-legal">Payments are verified before Wi-Fi access is enabled.<br><a class="terms-link" href="{{ route('terms') }}">Terms &amp; conditions</a></p>
</section>
@endsection