@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('last-year-price-update')}}">
                @csrf
            <div class="card-body">
                <h4 class="card-title">Last Year Price Info</h4>
                <input type="hidden" class="form-control" name="id"  value={{$data['id']}}>
                <div class="form-group row">
                <label for="fname" class="col-sm-3 text-end control-label col-form-label">Sku name</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="sku_id" name="sku_id" placeholder="Sku Id" value={{$data['sku_id']}}>
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Institution name</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="institution_id" name="institution_id" placeholder="Institution name" value={{$data['institution_id']}}>
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Division name</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="division_id" name="division_id" placeholder="Division name" value={{$data['division_id']}}>
                </div>
                </div>
                <div class="form-group row">
                <label for="email1" class="col-sm-3 text-end control-label col-form-label">Discount Percent</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="discount_percent" name="discount_percent" placeholder="Discount Percent" value={{$data['discount_percent']}}>
                </div>
                </div>
                <div class="form-group row">
                <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Discount Rate</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="discount_rate" name="discount_rate" placeholder="Discount Rate" value={{$data['discount_rate']}}>
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