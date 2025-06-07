@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('employee-store')}}">
                @csrf
                <div class="card-body">
                    <h4 class="card-title">Employee Info</h4>
                    
                    <div class="form-group row">
                    <label for="lname" class="col-sm-3 text-end control-label col-form-label">Employee Code</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="emp_code" name="emp_code" placeholder="Employee Code" value="{{ old('emp_code') }}">
                    </div>
                    </div>
                    <div class="form-group row">
                    <label for="email1" class="col-sm-3 text-end control-label col-form-label">Employee Name</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="emp_name" name="emp_name" placeholder="Employee Name" value="{{ old('emp_name') }}">
                    </div>
                    </div>
                    <div class="form-group row">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Email</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="emp_email" name="emp_email" placeholder="Employee Email" value="{{ old('emp_email') }}">
                    </div>
                    </div>
                     <div class="form-group row">
                        <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Manager Name</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="manager_name" name="manager_name" placeholder="Manager Name" value="{{ old('manager_name') }}"  maxlength="50">
                        </div>
                        </div>
                        <div class="form-group row">
                        <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Manager Email</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="manager_email" name="manager_email" placeholder="Manager Email" value="{{ old('manager_email') }}"  maxlength="50">
                        </div>
                        </div>
                    <div class="form-group row">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Category</label>
                    <div class="col-sm-4">
                            <select name="emp_category" id="emp_category" class="form-control">
                                <option value="approver" {{ old('emp_category') == 'approver' ? 'selected' : '' }}>Approver</option>
                                <option value="initiator" {{ old('emp_category') == 'initiator' ? 'selected' : '' }}>Initiator</option>
                                <option value="admin" {{ old('emp_category') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="distribution" {{ old('emp_category') == 'distribution' ? 'selected' : '' }}>Distributor</option>
                                <option value="poc" {{ old('emp_category') == 'poc' ? 'selected' : '' }}>POC</option>
                                <option value="ho" {{ old('emp_category') == 'ho' ? 'selected' : '' }}>HO</option>
                                <option value="ceo" {{ old('emp_category') == 'ceo' ? 'selected' : '' }}>CEO</option>
                            </select>
                    </div>
                    </div>
                    <div class="form-group row hidden-cap distribution-hidden">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Type</label>
                    <div class="col-sm-4">
                            <select name="emp_type" id="emp_type" class="form-control">
                                <option value="FSM" {{ old('emp_type') == 'FSM' ? 'selected' : '' }}>FSM</option>
                                <option value="RSM" {{ old('emp_type') == 'RSM' ? 'selected' : '' }}>RSM</option>
                                <option value="ZSM" {{ old('emp_type') == 'ZSM' ? 'selected' : '' }}>ZSM</option>
                                <option value="NSM" class="poc-hidden" {{ old('emp_type') == 'NSM' ? 'selected' : '' }}>NSM</option>
                                <option value="SBU" class="poc-hidden" {{ old('emp_type') == 'SBU' ? 'selected' : '' }}>SBU</option>
                                <option value="Semi Cluster" class="poc-hidden" {{ old('emp_type') == 'Semi Cluster' ? 'selected' : '' }}>Semi Cluster</option>
                                <option value="Cluster" class="poc-hidden" {{ old('emp_type') == 'Cluster' ? 'selected' : '' }}>Cluster</option>
                            </select>
                    </div>
                    </div>

                    <div class="form-group row hidden-cap distribution-hidden poc-hidden">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Division Type</label>
                    <div class="col-sm-4">
                            <select name="div_type" id="div_type" class="form-control">
                                <option value="SPLL" {{ old('div_type') == 'SPLL' ? 'selected' : '' }}>SPLL</option>
                                <option value="SPIL" {{ old('div_type') == 'SPIL' ? 'selected' : '' }}>SPIL</option>
                            </select>
                    </div>
                    </div>
                    <div class="form-group row hidden-cap distribution-hidden poc-hidden">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">Division Name</label>
                    <div class="col-sm-4">
                       <!--  <input type="text" class="form-control" id="div_name" name="div_name" placeholder="Division Name" value="{{ old('div_name') }}"> -->
                        <select  class="js-example-basic-single" name="div_code" id="div_code" class="form-control">
                          <option value="" >Select Division Name</option>
                          @foreach($data['division_name'] as $singleDiv)
                              <option value="{{ $singleDiv->div_id }}" {{ old('div_code') == $singleDiv->div_id ? 'selected' : '' }}>{{ $singleDiv->div_name }}</option>
                          @endforeach
                        </select>
                    </div>
                    </div>
                    <input type="hidden" name="div_name" value="{{ old('div_name') }}" id="div_name">
                    <!-- <div class="form-group row hidden-cap">
                    <label for="div_code" class="col-sm-3 text-end control-label col-form-label">Division Code</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="div_code" name="div_code" placeholder="Division Code" value="{{ old('div_code') }}">
                    </div>
                    </div> -->
                   <!-- <div class="form-group row">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Level</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="emp_level" name="emp_level" placeholder="Employee Level">
                    </div>
                    </div>-->
                    <div class="form-group row hidden-cap distribution-hidden poc_addl" style="display: none;">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Mobile Number</label>
                    <div class="col-sm-9">
                            <input type="text" class="form-control number_input" id="emp_number" name="emp_number" placeholder=" Mobile Number" maxlength="10" value="{{ old('emp_number') }}">
                    </div> 
                    </div>
                    <div class="form-group row hidden-cap distribution-hidden poc_addl" style="display: none;">
                    <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee HO</label>
                    <div class="col-sm-9">
                            <input type="text" class="form-control text_input" id="emp_ho" name="emp_ho" placeholder=" Employee HO" maxlength="30" value="{{ old('emp_ho') }}">
                    </div>
                    </div>
                    
                </div>
                <div class="border-top">
                    <div class="card-body">
                    <button type="submit" class="btn btn-primary">
                        Submit
                    </button>
                    <a href="{{url('/admin/employee')}}" class="btn btn-warning">Cancel</a>
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