@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('special-price-update')}}">
                @csrf
            <div class="card-body">
                <h4 class="card-title">Special Price Info</h4>
                <input type="hidden" class="form-control" name="id"  value={{$data['id']}}>
                <div class="form-group row">
                <label for="fname" class="col-sm-3 text-end control-label col-form-label">sku id</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="sku_id" placeholder="sku id" value="{{$data['sku_id']}}">
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Discount percent</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="email" name="discount_percent" placeholder="Discount percent" value="{{$data['discount_percent']}}">
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Stockist type flag</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="discount_rate" placeholder="Discount rate" value="{{$data['discount_rate']}}">
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