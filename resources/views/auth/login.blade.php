@extends('layouts.app')

@section('content')

<div class="row justify-content-center">

<div class="col-md-4">

<div class="card">

<div class="card-header">

Administrator Login

</div>

<div class="card-body">

<form>

<div class="mb-3">

<label>Email</label>

<input class="form-control">

</div>

<div class="mb-3">

<label>Password</label>

<input type="password"
class="form-control">

</div>

<button class="btn btn-success w-100">

Login

</button>

</form>

</div>

</div>

</div>

</div>

@endsection