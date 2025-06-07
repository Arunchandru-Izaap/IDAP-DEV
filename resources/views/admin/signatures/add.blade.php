@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('signature-store')}}" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <h4 class="card-title">Signature Info</h4>
                    <div class="form-group row">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">Employee Code</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="employee_code" name="employee_code" placeholder="Employee Code" value="{{ old('employee_code') }}" required="">
                    </div>
                    </div>
                    <div class="form-group row">
                    <label for="lname" class="col-sm-3 text-end control-label col-form-label">Spll Sign</label>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" id="spll_sign" name="spll_sign" required="">
                    </div>
                    </div>
                    <div class="form-group row">
                    <label for="lname" class="col-sm-3 text-end control-label col-form-label">Spli Sign</label>
                    <div class="col-sm-9">
                    <input type="file" class="form-control" id="spil_sign" name="spil_sign" required="">
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