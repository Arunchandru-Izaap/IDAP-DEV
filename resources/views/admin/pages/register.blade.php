@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="#">
               
            <div class="card-body">
                <h4 class="card-title">User Registration Info</h4>
                <div class="form-group row">
                <label for="fname" class="col-sm-3 text-end control-label col-form-label">User name</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="stockist_name" name="stockist_name" placeholder="User name">
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Email</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="email" name="email_id" placeholder="Email ID">
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Password</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="stockist_type_flag" name="stockist_type_flag" placeholder="Password">
                </div>
                </div>

                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Confirm password</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="stockist_type_flag" name="stockist_type_flag" placeholder="Confirm password">
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