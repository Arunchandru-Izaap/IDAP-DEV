@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">

            <form class="form-horizontal" method="post" action="{{route('ignored-institutions-update')}}">
                @csrf
                <div class="card-body">
                    <h4 class="card-title">Ignored Institution Info</h4>
                    <input type="hidden" class="form-control" name="id"  value={{$data['id']}}>
                    
                    <div class="form-group row">
                    <label for="lname" class="col-sm-3 text-end control-label col-form-label">Parent Institution Code</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="parent_institution_id" name="parent_institution_id" placeholder="Parent Institution Code" value={{$data['parent_institution_id']}}>
                    </div>
                    </div>

                    <div class="form-group row">
                    <label for="lname" class="col-sm-3 text-end control-label col-form-label">Institution Code</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="institution_id" name="institution_id" placeholder="Institution Code" value={{$data['institution_id']}}>
                    </div>
                    </div>
                    
                </div>
                <div class="border-top">
                    <div class="card-body">
                    <button type="submit" class="btn btn-primary">
                        Submit
                    </button>
                    <a href="{{ route('ignored-institutions-list') }}" class="btn btn-warning">Cancel</a>
                    </div>
                </div>
            </form>
            
        </div>
    </div>
    </div>
</div>
@endsection