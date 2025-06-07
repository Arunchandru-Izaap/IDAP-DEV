@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('duration-store')}}">
                @csrf
            <div class="card-body">
                <h4 class="card-title">Duration Info</h4>
                <div class="form-group row">
                <label for="fname" class="col-sm-3 text-end control-label col-form-label">Level</label>
                <div class="col-sm-9">
                    <select name="level" id="level">
                        <option value="1">RSM</option>
                        <option value="2">ZSM</option>
                        <option value="3">NSM</option>
                        <option value="4">SBU</option>
                        <option value="5">Semi Cluster</option>
                        <option value="6">Cluster</option>
                    </select>
                </div>
                </div>
                <div class="form-group row">
                <label for="fname" class="col-sm-3 text-end control-label col-form-label">Type</label>
                <div class="col-sm-9">
                    <select name="type" id="type">
                        <option value="vq">VQ</option>
                        <option value="reinitvq_normal">Reinitation Normal Workflow</option>
                        <option value="reinitvq_fast">Reinitation Fast Workflow</option>
                        
                    </select>
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Days</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="days" name="days" placeholder="Days" required="">
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