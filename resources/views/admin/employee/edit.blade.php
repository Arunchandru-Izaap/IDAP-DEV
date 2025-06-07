@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">

            <form class="form-horizontal" method="post" action="{{route('employee-update')}}">
                @csrf
                <div class="card-body">
                    <h4 class="card-title">Employee Info</h4>
                    <input type="hidden" class="form-control" name="id"  value={{$data['id']}}>
                    
                    <div class="form-group row">
                    <label for="lname" class="col-sm-3 text-end control-label col-form-label">Employee Code</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="emp_code" name="emp_code" placeholder="Employee Code" value="{{$data['emp_code']}}" readonly="">
                    </div>
                    </div>
                    <div class="form-group row">
                    <label for="email1" class="col-sm-3 text-end control-label col-form-label">Employee Name</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="emp_name" name="emp_name" placeholder="Employee Name" value="{{$data['emp_name']}}">
                    </div>
                    </div>
                    <div class="form-group row">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Email</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="emp_email" name="emp_email" placeholder="Employee Email" value="{{$data['emp_email']}}">
                    </div>
                    </div>
                    <div class="form-group row">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Manager Name</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="manager_name" name="manager_name" placeholder="Manager Name" value="{{$data['manager_name']}}"  maxlength="50">
                    </div>
                    </div>
                    <div class="form-group row">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Manager Email</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="manager_email" name="manager_email" placeholder="Manager Email" value="{{$data['manager_email']}}"  maxlength="50">
                    </div>
                    </div>
                    <div class="form-group row">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Category</label>
                    <div class="col-sm-4">
                            <select name="emp_category" id="emp_category" class="form-control">
                                <option value="approver"<?php if ($data['emp_category'] == 'approver') echo ' selected="selected"'; ?>>Approver</option>
                                <option value="initiator"<?php if ($data['emp_category'] == 'initiator') echo ' selected="selected"'; ?>>Initiator</option>
                                <option value="admin"<?php if ($data['emp_category'] == 'admin') echo ' selected="selected"'; ?>>Admin</option>
                                <option value="distribution"<?php if ($data['emp_category'] == 'distribution') echo ' selected="selected"'; ?>>Distributor</option>
                                <option value="poc"<?php if ($data['emp_category'] == 'poc') echo ' selected="selected"'; ?>>POC</option>
                                <option value="ho"<?php if ($data['emp_category'] == 'ho') echo ' selected="selected"'; ?>>HO</option>
                                <option value="ceo"<?php if ($data['emp_category'] == 'ceo') echo ' selected="selected"'; ?>>CEO</option>
                            </select>
                    </div>
                    </div>
                    <div class="form-group row hidden-cap distribution-hidden">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Type</label>
                    <div class="col-sm-4">
                            <select name="emp_type" id="emp_type" class="form-control">
                                <option value="FSM"<?php if ($data['emp_type'] == 'FSM') echo ' selected="selected"'; ?>>FSM</option>
                                <option value="RSM"<?php if ($data['emp_type'] == 'RSM') echo ' selected="selected"'; ?>>RSM</option>
                                <option value="ZSM"<?php if ($data['emp_type'] == 'ZSM') echo ' selected="selected"'; ?>>ZSM</option>
                                <option value="NSM"<?php if ($data['emp_type'] == 'NSM') echo ' selected="selected"'; ?> class="poc-hidden">NSM</option>
                                <option value="SBU"<?php if ($data['emp_type'] == 'SBU') echo ' selected="selected"'; ?> class="poc-hidden">SBU</option>
                                <option value="Semi Cluster"<?php if ($data['emp_type'] == 'Semi Cluster') echo ' selected="selected"'; ?> class="poc-hidden">Semi Cluster</option>
                                <option value="Cluster"<?php if ($data['emp_type'] == 'Cluster') echo ' selected="selected"'; ?> class="poc-hidden">Cluster</option>
                            </select>
                    </div>
                    </div>

                    <div class="form-group row hidden-cap distribution-hidden poc-hidden">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Division Type</label>
                    <div class="col-sm-4">
                            <select name="div_type" id="div_type" class="form-control">
                                <option value="SPLL"<?php if ($data['div_type'] == 'SPLL') echo ' selected="selected"'; ?>>SPLL</option>
                                <option value="SPIL"<?php if ($data['div_type'] == 'SPIL') echo ' selected="selected"'; ?>>SPIL</option>
                            </select>
                    </div>
                    </div>
                    <div class="form-group row hidden-cap distribution-hidden poc-hidden">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">Division Name</label>
                    <div class="col-sm-4">
                        <!-- <input type="text" class="form-control" id="div_name" name="div_name" placeholder="Division Name" value="{{$data['div_name']}}"> -->
                        <select  class="js-example-basic-single" name="div_code" id="div_code" class="form-control">
                            <option value="" >Select Division Name</option>
                          @foreach($division_name as $singleDiv)
                              <option value="{{ $singleDiv->div_id }}"}} <?php if ($singleDiv->div_id ==  $data['div_code']) echo ' selected="selected"'; ?>>{{ $singleDiv->div_name }}</option>
                          @endforeach
                        </select>
                    </div>
                    </div>
                    <input type="hidden" name="div_name" value="{{ $data['div_name'] }}" id="div_name">
                    <!-- <div class="form-group row hidden-cap">
                    <label for="div_code" class="col-sm-3 text-end control-label col-form-label">Division Code</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="div_code" name="div_code" placeholder="Division Code" value="{{$data['div_code']}}">
                    </div>
                    </div> -->

                    
                  <!--  <div class="form-group row">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Type</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="emp_type" name="emp_type" placeholder="Employee Type" value={{$data['emp_type']}}>
                    </div>
                    </div>

                    <div class="form-group row">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Division Type</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="div_type" name="div_type" placeholder="Division Type" value={{$data['div_type']}}>
                    </div>
                    </div>
                    <div class="form-group row">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Category</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="emp_category" name="emp_category" placeholder="Employee Category" value={{$data['emp_category']}}>
                    </div>
                    </div>
                    <div class="form-group row">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Level</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="emp_level" name="emp_level" placeholder="Employee Level" value={{$data['emp_level']}}>
                    </div>
                    </div>-->
                    <div class="form-group row hidden-cap distribution-hidden poc_addl" style="display: none;">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Mobile Number</label>
                    <div class="col-sm-4">
                            <input type="text" class="form-control number_input" id="emp_number" name="emp_number" placeholder=" Mobile Number" maxlength="10"  value="{{$data['emp_number']}}">
                    </div>
                    </div>
                    <div class="form-group row hidden-cap distribution-hidden poc_addl" style="display: none;">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee HO</label>
                    <div class="col-sm-4">
                            <input type="text" class="form-control text_input" id="emp_ho" name="emp_ho" placeholder=" Employee HO" maxlength="30"  value="{{$data['emp_ho']}}">
                    </div>
                    </div>
                </div>
                <div class="border-top">
                    <div class="card-body">
                    <button type="submit" class="btn btn-primary">
                        Submit
                    </button>
                    <a href="{{route('employee-list')}}" class="btn btn-warning">Cancel</a>
                    </div>
                </div>
            </form>
            
        </div>
    </div>
    </div>
</div>
@endsection
@push('scripts')
<script type="text/javascript">
    $('#div_code').on('change', function(){
      if($(this).val()!='')
      {
        var selectedText = $('#div_code option:selected').text();
        $('#div_name').val(selectedText);
      }
    })
    $('body').on('input','.number_input',function(){
      this.value = this.value.replace(/[^0-9]/g, '');
    })
</script>
@endpush