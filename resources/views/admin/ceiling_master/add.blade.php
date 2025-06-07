@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('ceiling-master-store')}}">
                @csrf
            <div class="card-body">
                <h4 class="card-title">Ceiling Master Info</h4>
                <div class="form-group row">
                <label for="fname" class="col-sm-3 text-end control-label col-form-label">sku</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control"  name="sku_id" placeholder="Enter id">
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Discount percent</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="discount_percent" placeholder="Discount percent">
                </div>
                </div>
                
            </div>
            <div class="border-top">
                <div class="card-body">
                <button type="submit" class="btn btn-primary">
                    Submit
                </button>
                </div>
            </div>
            </form>
        </div>
    </div>
    </div>
</div>
@endsection