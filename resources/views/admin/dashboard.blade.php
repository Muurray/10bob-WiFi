@extends('layouts.app')

@section('content')

<h2>Administrator Dashboard</h2>

<hr>

<div class="row">

    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
               <h3>{{ $customers }}</h3>
                Customers
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h3>{{ $active }}</h3>
                Active Users
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-warning">
            <div class="card-body">
              <h3>KES {{ number_format($sales,2) }}</h3>
                Today's Sales
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
               <h3>{{ $expired }}</h3>
                Expired Users
            </div>
        </div>
    </div>

</div>

@endsection