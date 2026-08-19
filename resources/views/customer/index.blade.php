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

<hr>

<h3 class="mt-5">

Available Packages

</h3>

<table class="table table-striped">

<thead>

<tr>

<th>Price</th>

<th>Duration</th>

</tr>

</thead>

<tbody>

<tr>

<td>10 KES</td>

<td>1 Hour</td>

</tr>

<tr>

<td>20 KES</td>

<td>3 Hours</td>

</tr>

<tr>

<td>50 KES</td>

<td>24 Hours</td>

</tr>

<tr>

<td>100 KES</td>

<td>3 Days</td>

</tr>

<tr>

<td>200 KES</td>

<td>7 Days</td>

</tr>

</tbody>

</table>

@endsection