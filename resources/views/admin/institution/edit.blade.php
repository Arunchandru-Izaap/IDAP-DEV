@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('update')}}">
                @csrf
            <div class="card-body">
                <h4 class="card-title">Institution Info</h4>
                <input type="hidden" class="form-control" name="id"  value={{$data['id']}}>
                <div class="form-group row">
                <label for="fname" class="col-sm-3 text-end control-label col-form-label">Institution name</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="institution_name" name="institution_name" placeholder="Institution name" value={{$data['institution_name']}}>
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Institution code</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="institution_code" name="institution_code" placeholder="Institution code" value={{$data['institution_code']}}>
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Key account name</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="key_account_name" name="key_account_name" placeholder="Key account name" value={{$data['key_account_name']}}>
                </div>
                </div>
                <div class="form-group row">
                <label for="email1" class="col-sm-3 text-end control-label col-form-label">City</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="city" name="city" placeholder="City" value={{$data['city']}}>
                </div>
                </div>
                <div class="form-group row">
                <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Region</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="region" name="region" placeholder="Region" value={{$data['region']}}>
                </div>
                </div>
                <div class="form-group row">
                <label for="cono1" class="col-sm-3 text-end control-label col-form-label">HQ</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="hq" name="hq" placeholder="HQ" value={{$data['hq']}}>
                </div>
                </div>

                <div class="form-group row">
                <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Zone</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="zone" name="zone" placeholder="Zone" value={{$data['zone']}}>
                </div>
                </div>
                <div class="form-group row">
                <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Retailer name 1</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="retailer_name_1" name="retailer_name_1" placeholder="Retailer name 1" value={{$data['retailer_name_1']}}>
                </div>
                </div>
                <div class="form-group row">
                <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Retailer name 2</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="retailer_name_2" name="retailer_name_2" placeholder="Retailer name 2" value={{$data['retailer_name_2']}}>
                </div>
                </div>
                <div class="form-group row">
                <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Retailer name 3</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="retailer_name_3" name="retailer_name_3" placeholder="Retailer name 3" value={{$data['retailer_name_3']}}>
                </div>
                </div>
                <div class="form-group row">
                <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Address</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="address" name="address" placeholder="Address" value={{$data['address']}}>{{$data['address']}}</textarea>
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