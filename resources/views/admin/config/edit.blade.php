@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">

            <form class="form-horizontal" method="post" action="{{route('config-data-update')}}">
                @csrf
                <div class="card-body">
                    <h4 class="card-title">Config Data</h4>
                    <input type="hidden" class="form-control" name="id"  value="{{$data['id']}}">
                    
                    <div class="form-group row">
                    <label for="name" class="col-sm-3 text-end control-label col-form-label">Name</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="meta_name"  placeholder="Parent Division Code" value="{{$data['meta_name']}}" disabled>
                    </div>
                    </div>

                    <div class="form-group row">
                    <label for="value" class="col-sm-3 text-end control-label col-form-label">Config Value</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="meta_value" name="meta_value" placeholder="Config" value="{{$data['meta_value']}}">
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