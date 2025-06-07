@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('duration-update')}}">
                @csrf
            <div class="card-body">
                <h4 class="card-title">Duration Info</h4>
                <input type="hidden" class="form-control" name="id"  value={{$data['id']}}>
                <div class="form-group row">
                <label for="fname" class="col-sm-3 text-end control-label col-form-label">Level</label>
                <div class="col-sm-9">
                    <select name="level" id="level">
                        <option value="1" <?php if ($data['level'] == '1') echo ' selected="selected"'; ?>>RSM</option>
                        <option value="2" <?php if ($data['level'] == '2') echo ' selected="selected"'; ?>>ZSM</option>
                        <option value="3" <?php if ($data['level'] == '3') echo ' selected="selected"'; ?>>NSM</option>
                        <option value="4" <?php if ($data['level'] == '4') echo ' selected="selected"'; ?>>SBU</option>
                        <option value="5" <?php if ($data['level'] == '5') echo ' selected="selected"'; ?>>Semi Cluster</option>
                        <option value="6" <?php if ($data['level'] == '6') echo ' selected="selected"'; ?>>Cluster</option>
                    </select>
                </div>
                </div>
                <div class="form-group row">
                <label for="fname" class="col-sm-3 text-end control-label col-form-label">Type</label>
                <div class="col-sm-9">
                    <select name="type" id="type">
                        <option value="vq" <?php if ($data['type'] == 'vq') echo ' selected="selected"'; ?>>VQ</option>
                        <option value="reinitvq_normal" <?php if ($data['type'] == 'reinitvq_normal') echo ' selected="selected"'; ?>>Reinitation Normal Workflow</option>
                        <option value="reinitvq_fast" <?php if ($data['type'] == 'reinitvq_fast') echo ' selected="selected"'; ?>>Reinitation Fast Workflow</option>
                        
                    </select>
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Days</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="days" name="days" placeholder="Days" value="{{$data['days']}}">
                </div>
                </div>
                
            </div>
            <div class="border-top">
                <div class="card-body">
                <button type="submit" class="btn btn-primary">
                    Submit
                </button>
                <a href="{{ route('duration-list') }}" class="btn btn-warning">Cancel</a>
                </div>
            </div>
            </form>
        </div>
    </div>
    </div>
</div>
@endsection