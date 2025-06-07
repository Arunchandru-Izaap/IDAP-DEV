@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
  .select2-container--default .select2-selection--single {
    height: calc(1.5em + 0.75rem + 2px);
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: calc(1.5em + 0.75rem + 2px);
}
.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #495057;
}
</style>
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
          <div class="collapse navbar-collapse show" id="navbarSupportedContent">
              <ul class="navbar-nav dashboard-nav">
                  <li class="nav-item active"><a class="nav-link" href="{{route('initiator-employee-list')}}"> <img src="../../admin/images/back.svg">Employee Master - Add Employee</a></li>
              </ul>

              <ul class="d-flex ml-auto user-name">
                  <li>
                      <h3>{{Session::get('emp_name')}}</h3>
                      <p>Initiator</p>
                  </li>

                  <li>
                      <img src="../../admin/images/Sun_Pharma_logo.png">
                  </li>
              </ul>
          </div>
          
      </div>

  </nav>
  <ul class="bradecram-menu">
      <li><a href="{{route('initiator_dashboard')}}">
          Home
      </a></li>
      <li class="">
        <a href="{{route('initiator-employee-list')}}">
          Employee master
        </a>
      </li>
      <li class="active">
        <a href="">
          Add Employee
        </a>
      </li>
  </ul>
  <div class="container-fluid">
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
        </ul>
      </div><br />
      @endif
      @if(session()->has('message'))
        <div class="alert alert-success">
            {{ session()->get('message') }}
        </div>
      @endif
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form class="form-horizontal" method="post" action="{{route('initiator-employee-store')}}">
                    @csrf
                    <div class="card-body">
                        <h4 class="card-title">Employee Info</h4>
                        
                        <div class="form-group row">
                        <label for="lname" class="col-sm-3 text-end control-label col-form-label">Employee Code</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control text_input" id="emp_code" name="emp_code" placeholder="Employee Code" maxlength="20" value="{{ old('emp_code') }}">
                        </div>
                        </div>
                        <div class="form-group row">
                        <label for="email1" class="col-sm-3 text-end control-label col-form-label">Employee Name</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control name_input" id="emp_name" name="emp_name" placeholder="Employee Name" maxlength="50" value="{{ old('emp_name') }}">
                        </div>
                        </div>
                        <div class="form-group row">
                        <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Email</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control text_input_email" id="emp_email" name="emp_email" placeholder="Employee Email" maxlength="50" value="{{ old('emp_email') }}">
                        </div>
                        </div>
                        <div class="form-group row">
                        <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Manager Name</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control name_input" id="manager_name" name="manager_name" placeholder="Manager Name" value="{{ old('manager_name') }}"  maxlength="50">
                        </div>
                        </div>
                        <div class="form-group row">
                        <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Manager Email</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control text_input_email" id="manager_email" name="manager_email" placeholder="Manager Email" value="{{ old('manager_email') }}"  maxlength="50">
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
                            <!-- <input type="text" class="form-control name_input" id="div_name" name="div_name" placeholder="Division Name" maxlength="20" value="{{ old('div_name') }}"> -->
                            <select  class="js-example-basic-single" name="div_code" id="div_code">
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
                        <div class="col-sm-4">
                            <input type="text" class="form-control text_input" id="div_code" name="div_code" placeholder="Division Code" maxlength="20" value="{{ old('div_code') }}">
                        </div>
                        </div> -->
                       <!-- <div class="form-group row">
                        <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Level</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control text_input" id="emp_level" name="emp_level" placeholder="Employee Level">
                        </div>
                        </div>-->
                        <div class="form-group row hidden-cap distribution-hidden poc_addl" style="display: none;">
                        <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Mobile Number</label>
                        <div class="col-sm-4">
                                <input type="text" class="form-control number_input" id="emp_number" name="emp_number" placeholder=" Mobile Number" maxlength="10" value="{{ old('emp_number') }}">
                        </div>
                        </div>
                        <div class="form-group row hidden-cap distribution-hidden poc_addl" style="display: none;">
                        <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee HO</label>
                        <div class="col-sm-4">
                                <input type="text" class="form-control text_input" id="emp_ho" name="emp_ho" placeholder=" Employee HO" maxlength="30" value="{{ old('emp_ho') }}">
                        </div>
                        </div>
                        
                    </div>
                    <div class="border-top">
                        <div class="card-body">
                        <button type="submit" class="btn btn-primary">
                            Submit
                        </button>
                        <a href="{{route('initiator-employee-list')}}" class="btn btn-warning">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
<script src="{{asset('frontend/js/select2.js')}}"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $('.js-example-basic-single').select2({ width: '100%' });
    $('body').on('input','.text_input',function(){
      this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
    })
    $('body').on('input','.number_input',function(){
      this.value = this.value.replace(/[^0-9]/g, '');
    })
    $('body').on('input','.name_input',function(){
      this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '');
    })
    $('body').on('input','.text_input_email',function(){
      this.value = this.value.replace(/[^a-zA-Z0-9@.-]/g, '');
    })
    $('#emp_email').on('change', function() {
        var email = $(this).val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailRegex.test(email)) {
          $(this).val('')
          $(this).focus()
          alert("Invalid email address.");
        }
    });
    //$('.copyright').addClass('copyright_inc');
    $('#admin_main_menu').on('click', function(){
      //$('.copyright').toggleClass('copyright_inc');
    })
     if($('#emp_category').val() == 'initiator' || $('#emp_category').val() == 'admin'|| $('#emp_category').val() == 'ho'|| $('#emp_category').val() == 'ceo'){
        $('.hidden-cap').css('display','none');
      }
      if($('#emp_category').val() == 'poc'){
        $('.poc-hidden').css('display','none');
        $('.poc_addl').css('display','flex');
      }
      if($('#emp_category').val() == 'distribution'){
        $('.distribution-hidden').css('display','none');
        $('label[for="div_code"]').text('CFA Code');
        $('#div_code').attr('placeholder','CFA Code');
      }
    // jQuery methods go here...
    $('#emp_category').on('change', function() {
      if(this.value == 'approver' || this.value == 'distribution'){
          $('.hidden-cap').css('display','flex');
          $('.poc_addl').css('display','none');
          $('.poc-hidden').css('display','flex');
      }else{
        $('.hidden-cap').css('display','none');
      }
      if(this.value == 'poc'){
        $('.hidden-cap').css('display','flex');
        $('.poc-hidden').css('display','none');
      }
      $('label[for="div_code"]').text('Division Code');
      $('#div_code').attr('placeholder','Division Code');
      if(this.value == 'distribution'){
        $('.hidden-cap').css('display','flex');
        $('.distribution-hidden').css('display','none');
        $('label[for="div_code"]').text('CFA Code');
        $('#div_code').attr('placeholder','CFA Code');
      }
    });
    $('#div_code').on('change', function(){
      if($(this).val()!='')
      {
        var selectedText = $('#div_code option:selected').text();
        $('#div_name').val(selectedText);
      }
    })
  })
</script>
<style type="text/css">

    .copy-center{
        position:inherit !important;
        margin: 20px 0;
    }
    div.dataTables_wrapper {
        max-width: 1357px;
        width: 100%;
        margin: 0 auto;
    }
    .copyright_inc
    {
      position: absolute !important;
      top:62rem !important;
    }
</style> 
@endsection