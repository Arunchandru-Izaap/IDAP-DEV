@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">

            <form class="form-horizontal" method="post" action="{{route('max-discount-cap-update')}}">
                @csrf
                <div class="card-body">
                    <h4 class="card-title">Max Discount Info</h4>
                    <input type="hidden" class="form-control" name="id"  value="{{$data['id']}}">
                    
                    <div class="form-group row">
                    <label for="lname" class="col-sm-3 text-end control-label col-form-label">Division Code</label>
                    <div class="col-sm-9">
                        <input type="text" disabled class="form-control" id="div_id" name="div_id" placeholder="Division Code" value="{{$data['div_id']}}">
                    </div>
                    </div>

                    <div class="form-group row">
                    <label for="lname" class="col-sm-3 text-end control-label col-form-label">Max Discount</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="max_discount" name="max_discount" placeholder="Max Discount" value="{{$data['max_discount']}}">
                    </div>
                    </div>
                    
                </div>
                <div class="border-top">
                    <div class="card-body">
                    <button type="submit" class="btn btn-primary">
                        Submit
                    </button>
                    <a href="{{ route('max-discount-cap-list') }}" class="btn btn-warning">Cancel</a>
                    </div>
                </div>
            </form>
            
        </div>
    </div>
    </div>
</div>
@endsection