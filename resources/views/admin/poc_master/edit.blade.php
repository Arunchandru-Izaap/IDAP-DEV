@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('poc-master-update')}}">
                @csrf
            <div class="card-body">
                <h4 class="card-title">Poc Master Info</h4>
                <input type="hidden" class="form-control" name="id"  value={{$data['id']}}>
                <div class="form-group row">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">Institution Name</label>
                    <div class="col-sm-4">
                        <select name="institution_id" id="institution_id" class="form-control js-example-basic-single" disabled="">
                            @foreach($data1['institution'] as $item)
                            <option value="{{$item->INST_ID}}"<?php if ($data['institution_id'] == $item->INST_ID) echo ' selected="selected"'; ?>>{{$item->INST_NAME}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">FSM Name</label>
                    <div class="col-sm-4">
                        <select name="fsm_code" id="fsm_code" class="form-control js-example-basic-single">
                            @foreach($data1['poc'] as $item)
                            <option value="{{$item->fsm_code}}"<?php if ($data['fsm_code'] == $item->fsm_code) echo ' selected="selected"'; ?>>{{$item->fsm_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">RSM Name</label>
                    <div class="col-sm-4">
                        <select name="rsm_code" id="rsm_code" class="form-control js-example-basic-single">
                            <option value="">Select RSM Name</option>
                            @foreach($data1['rsm'] as $item)
                            <option value="{{$item->rsm_code}}" <?php if ($data['rsm_code'] == $item->rsm_code) echo ' selected="selected"'; ?>>{{$item->rsm_code}}-{{$item->rsm_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">ZSM Name</label>
                    <div class="col-sm-4">
                        <select name="zsm_code" id="zsm_code" class="form-control js-example-basic-single">
                            <option value="">Select ZSM Name</option>
                            @foreach($data1['zsm'] as $item)
                            <option value="{{$item->zsm_code}}" <?php if ($data['zsm_code'] == $item->zsm_code) echo ' selected="selected"'; ?>>{{$item->zsm_code}}-{{$item->zsm_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="border-top">
                <div class="card-body">
                <button type="submit" class="btn btn-primary">
                    Submit
                </button>
                <a href="{{route('poc-master-list')}}" class="btn btn-warning">Cancel</a>
                </div>
            </div>
            </form>
        </div>
    </div>
    </div>
</div>
@endsection