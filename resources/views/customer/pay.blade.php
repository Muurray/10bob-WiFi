@extends('layouts.app')

@section('content')

<h2>Buy Internet</h2>

<form>

<div class="mb-3">

<label>Phone Number</label>

<input
class="form-control"
placeholder="2547XXXXXXXX">

</div>

<div class="mb-3">

<label>Select Package</label>

<select class="form-control">

<option>10 Bob - 1 Hour</option>

<option>20 Bob - 3 Hours</option>

<option>50 Bob - 1 Day</option>

<option>100 Bob - 3 Days</option>

</select>

</div>

<button class="btn btn-success">

Pay with M-Pesa

</button>

</form>

@endsection