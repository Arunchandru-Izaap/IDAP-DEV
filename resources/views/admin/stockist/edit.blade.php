@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('stockist-update')}}">
                @csrf
            <div class="card-body">
                <h4 class="card-title">Stockist Info</h4>
                <input type="hidden" class="form-control" name="id"  value={{$data['id']}}>
                <div class="form-group row">
                <label for="fname" class="col-sm-3 text-end control-label col-form-label">Stockist name</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="stockist_name" name="stockist_name" placeholder="Stockist name" value="{{$data['stockist_name']}}">
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Email</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="email" name="email_id" placeholder="Email ID" value="{{$data['email_id']}}">
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Stockist type flag</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="stockist_type_flag" name="stockist_type_flag" placeholder="Stockist type flag" value="{{$data['stockist_type_flag']}}">
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