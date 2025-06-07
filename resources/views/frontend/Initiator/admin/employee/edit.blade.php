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
                  <li class="nav-item active"><a class="nav-link" href="{{route('initiator-employee-list')}}"> <img src="../../admin/images/back.svg">Employee Master - Edit Employee</a></li>
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
          Edit Employee
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

                <form class="form-horizontal" method="post" action="{{route('initiator-employee-update')}}">
                    @csrf
                    <div class="card-body">
                        <h4 class="card-title">Employee Info</h4>
                        <input type="hidden" class="form-control" name="id"  value={{$data['id']}}>
                        
                        <div class="form-group row">
                        <label for="lname" class="col-sm-3 text-end control-label col-form-label">Employee Code</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control text_input" id="emp_code" name="emp_code" placeholder="Employee Code" value="{{$data['emp_code']}}" maxlength="20" readonly="">
                        </div>
                        </div>
                        <div class="form-group row">
                        <label for="email1" class="col-sm-3 text-end control-label col-form-label">Employee Name</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control name_input" id="emp_name" name="emp_name" placeholder="Employee Name" value="{{$data['emp_name']}}" maxlength="50">
                        </div>
                        </div>
                        <div class="form-group row">
                        <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Email</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control text_input_email" id="emp_email" name="emp_email" placeholder="Employee Email" value="{{$data['emp_email']}}"  maxlength="50">
                        </div>
                        </div>
                        <div class="form-group row">
                        <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Manager Name</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control name_input" id="manager_name" name="manager_name" placeholder="Manager Name" value="{{$data['manager_name']}}"  maxlength="50">
                        </div>
                        </div>
                        <div class="form-group row">
                        <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Manager Email</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control text_input_email" id="manager_email" name="manager_email" placeholder="Manager Email" value="{{$data['manager_email']}}"  maxlength="50">
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
                            <!-- <input type="text" class="form-control text_input" id="div_name" name="div_name" placeholder="Division Name" value="{{$data['div_name']}}" maxlength="20"> -->
                            <select  class="js-example-basic-single" name="div_code" id="div_code">
                              <option value="" >Select Division Name</option>
                              @foreach($division_name as $singleDiv)
                                  <option value="{{ $singleDiv->div_id }}" <?php if ($singleDiv->div_id == $data['div_code']) echo ' selected="selected"'; ?>>{{ $singleDiv->div_name }}</option>
                              @endforeach
                            </select>
                        </div>
                        </div>
                        <input type="hidden" name="div_name" value="{{ $data['div_name'] }}" id="div_name">
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
                        <!-- <div class="form-group row hidden-cap">
                        <label for="div_code" class="col-sm-3 text-end control-label col-form-label">Division Code</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control text_input" id="div_code" name="div_code" placeholder="Division Code" value="{{$data['div_code']}}" maxlength="20">
                        </div>
                        </div> -->

                        
                      <!--  <div class="form-group row">
                        <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Type</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="emp_type" name="emp_type" placeholder="Employee Type" value={{$data['emp_type']}}>
                        </div>
                        </div>

                        <div class="form-group row">
                        <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Division Type</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="div_type" name="div_type" placeholder="Division Type" value={{$data['div_type']}}>
                        </div>
                        </div>
                        <div class="form-group row">
                        <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Category</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="emp_category" name="emp_category" placeholder="Employee Category" value={{$data['emp_category']}}>
                        </div>
                        </div>
                        <div class="form-group row">
                        <label for="cono1" class="col-sm-3 text-end control-label col-form-label">Employee Level</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="emp_level" name="emp_level" placeholder="Employee Level" value={{$data['emp_level']}}>
                        </div>
                        </div>-->
                        
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
    
<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script src="{{asset('frontend/js/select2.js')}}"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $('.js-example-basic-single').select2({ width: '100%' });
    $("#zero_config").DataTable(
    {  
      "pageLength": 50,
      
      'language': {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },
      scrollX: true,
    })
    $('body').on('input','.text_input',function(){
      this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
    })
    $('body').on('input','.name_input',function(){
      this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '');
    })
    $('body').on('input','.number_input',function(){
      this.value = this.value.replace(/[^0-9]/g, '');
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