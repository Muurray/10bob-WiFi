@extends('layouts.app')

@section('content')

<h2>Create Internet Package</h2>

<form method="POST" action="{{ route('admin.packages.store') }}">

@csrf

<div class="mb-3">

<label>Name</label>

<input name="name" class="form-control">

</div>

<div class="mb-3">

<label>Price</label>

<input type="number" step="0.01" name="price" class="form-control">

</div>

<div class="mb-3">

<label>Duration</label>

<input type="number" name="duration" class="form-control">

</div>

<div class="mb-3">

<label>Unit</label>

<select name="unit" class="form-control">

<option>Hour</option>

<option>Hours</option>

<option>Day</option>

<option>Days</option>

</select>

</div>

<div class="mb-3">

<label for="device_limit">Simultaneous devices</label>

<select id="device_limit" name="device_limit" class="form-control" required>

<option value="1" selected>1 device</option>

<option value="2">2 devices</option>

</select>

</div>

<button class="btn btn-primary">

Save Package

</button>

</form>

@endsection