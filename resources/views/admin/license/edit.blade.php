@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">

        <form class="form-horizontal" method="post" action="{{route('license-update')}}" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <h4 class="card-title">License Info</h4>

                    <input type="hidden" class="form-control" name="id"  value="{{$data['id']}}">

                    <div class="form-group row">
                    <label for="institutionId" class="col-sm-3 text-end control-label col-form-label">Institution Code</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" value="{{$data->institution_id}}" disabled placeholder="Institution Name">   
                    </div>
                    </div>

                    <div class="form-group row">
                    <label for="institutionName" class="col-sm-3 text-end control-label col-form-label">Institution Name</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="selected_institution_name" disabled placeholder="Institution Name" value="{{$data->institution_name}}">
                    </div>
                    </div>
                    
                    <div class="form-group row">
                    <label for="gst" class="col-sm-3 text-end control-label col-form-label">GST</label>
                    <div class="col-sm-9">
                    <a href="{{asset('images/license/gst/'.$data->gst)}}">Preview previous file</a>
                    <input type="file" class="form-control" id="gst" name="gst">
                    </div>
                    </div>

                    

                    <div class="form-group row">
                    <label for="form20" class="col-sm-3 text-end control-label col-form-label">Form 20</label>
                    <div class="col-sm-9">
                        <a href="{{asset('images/license/form20/'.$data->form20)}}">Preview previous file</a>
                        <input type="file" class="form-control" id="form20" name="form20">
                    </div>
                    </div>

                    <div class="form-group row">
                    <label for="form20_expiry_date" class="col-sm-3 text-end control-label col-form-label">Form 20 expiry date</label>
                    <div class="col-sm-9">
                        <input type="date" id="form20_expiry_date" name="form20_expiry_date" placeholder="Institution Id" value="{{$data->form20_expiry_date}}">
                    </div>
                    </div>

                    <div class="form-group row">
                    <label for="form21" class="col-sm-3 text-end control-label col-form-label">Form 21</label>
                    <div class="col-sm-9">
                        <a href="{{asset('images/license/form21/'.$data->form21)}}">Preview previous file</a>
                        <input type="file" class="form-control" id="form21" name="form21">
                    </div>
                    </div>
                   
                    <div class="form-group row">
                    <label for="form21_expiry_date" class="col-sm-3 text-end control-label col-form-label">Form 21 expiry date</label>
                    <div class="col-sm-9">
                        <input type="date" id="form21_expiry_date" name="form21_expiry_date" placeholder="Institution Id" value="{{$data->form21_expiry_date}}">
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