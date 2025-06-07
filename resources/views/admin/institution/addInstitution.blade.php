@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('store')}}">
                @csrf
            <div class="card-body">
                <h4 class="card-title">Institution Info</h4>
                <div class="form-group row">
                <label for="fname" class="col-sm-3 text-end control-label col-form-label">Institution name</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="institution_name" name="institution_name" placeholder="Institution name">
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Institution code</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="institution_code" name="institution_code" placeholder="Institution code">
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Key account name</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="key_account_name" name="key_account_name" placeholder="Key account name">
                </div>
                </div>
                <div class="form-group row">
                <label for="email1" class="col-sm-3 text-end control-label col-form-label">City</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="city" name="city" placeholder="City">
                </div>
                </div>
                <div class="form-group row">
                <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Region</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="region" name="region" placeholder="Region">
                </div>
                </div>
                <div class="form-group row">
                <label for="cono1" class="col-sm-3 text-end control-label col-form-label">HQ</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="hq" name="hq" placeholder="HQ">
                </div>
                </div>

                <div class="form-group row">
                <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Zone</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="zone" name="zone" placeholder="Zone">
                </div>
                </div>
                <div class="form-group row">
                <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Retailer name 1</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="retailer_name_1" name="retailer_name_1" placeholder="Retailer name 1">
                </div>
                </div>
                <div class="form-group row">
                <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Retailer name 2</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="retailer_name_2" name="retailer_name_2" placeholder="Retailer name 2">
                </div>
                </div>
                <div class="form-group row">
                <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Retailer name 3</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="retailer_name_3" name="retailer_name_3" placeholder="Retailer name 3">
                </div>
                </div>
                <div class="form-group row">
                <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Address</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="address" name="address" placeholder="Address"></textarea>
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