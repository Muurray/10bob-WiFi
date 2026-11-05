@extends('layouts.app')

@section('content')

<h2>Internet Packages</h2>

<a href="/admin/packages/create" class="btn btn-success mb-3">

New Package

</a>

<table class="table table-bordered">

<thead>

<tr>

<th>Name</th>

<th>Price</th>

<th>Duration</th>

<th>Devices</th>

<th>Status</th>

</tr>

</thead>

<tbody>

@foreach($packages as $package)

<tr>

<td>{{ $package->name }}</td>

<td>KES {{ number_format($package->price,2) }}</td>

<td>{{ $package->duration }} {{ $package->unit }}</td>

<td>{{ $package->device_limit }} {{ Str::plural('device', $package->device_limit) }}</td>

<td>
@if($package->active)
<span class="badge bg-success">Active</span>
@else
<span class="badge bg-danger">Disabled</span>
@endif
</td>

</tr>

@endforeach

</tbody>

</table>

@endsection