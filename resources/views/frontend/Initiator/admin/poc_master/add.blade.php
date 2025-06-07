@extends('layouts.frontend.app')
@section('content')
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
          <div class="collapse navbar-collapse show" id="navbarSupportedContent">
              <ul class="navbar-nav dashboard-nav">
                  <li class="nav-item active"><a class="nav-link" href="{{route('initiator-poc-master-list')}}"> <img src="../../admin/images/back.svg">Poc Master - Bulk Add/ Update Poc Master</a></li>
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
        <a href="{{route('initiator-poc-master-list')}}">
          Poc Master
        </a>
      </li>
      <li class="active">
        <a href="">
        Bulk Add/ Update Poc Master
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
            <form class="form-horizontal" method="post" action="{{route('initiator-poc-master-store')}}">
                @csrf
            <div class="card-body">
                <h4 class="card-title">Poc Master Info</h4>
                <div class="form-group row">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">Institution Name</label>
                    <div class="col-sm-4">
                        <select name="institution_id[]" id="institution_id" class="form-control js-example-basic-single" multiple="" data-placeholder="Select institutions" required="">
                            <option value="">Select Insititution</option>
                            @foreach($data['institution'] as $item)
                                <option value="{{ $item->INST_ID }}" 
                                    {{ is_array(old('institution_id')) && in_array($item->INST_ID, old('institution_id')) ? 'selected' : '' }}>
                                    {{ $item->INST_ID }} - {{ $item->INST_NAME }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">FSM Name</label>
                    <div class="col-sm-4">
                        <select name="fsm_code" id="fsm_code" class="form-control js-example-basic-single" required="">
                            <option value="">Select FSM Name</option>
                            @foreach($data['poc'] as $item)
                            <option value="{{$item->fsm_code}}" {{ old('fsm_code') == $item->fsm_code ? 'selected' : '' }}>{{$item->fsm_code}}-{{$item->fsm_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">RSM Name</label>
                    <div class="col-sm-4">
                        <select name="rsm_code" id="rsm_code" class="form-control js-example-basic-single" required="">
                            <option value="">Select RSM Name</option>
                            @foreach($data['rsm'] as $item)
                            <option value="{{$item->rsm_code}}" {{ old('rsm_code') == $item->rsm_code ? 'selected' : '' }}>{{$item->rsm_code}}-{{$item->rsm_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">ZSM Name</label>
                    <div class="col-sm-4">
                        <select name="zsm_code" id="zsm_code" class="form-control js-example-basic-single" required="">
                            <option value="">Select ZSM Name</option>
                            @foreach($data['zsm'] as $item)
                            <option value="{{$item->zsm_code}}" {{ old('zsm_code') == $item->zsm_code ? 'selected' : '' }}>{{$item->zsm_code}}-{{$item->zsm_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
              
            </div>
            <div class="border-top">
                <div class="card-body">
                <button type="submit" id="pos_mater_btn" class="btn btn-primary">
                    Submit
                </button>
                <a href="{{route('initiator-poc-master-list')}}" class="btn btn-warning">Cancel</a>
                </div>
            </div>
            </form>
        </div>
    </div>
<script src="{{asset('frontend/js/select2.js')}}"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $('.js-example-basic-single').select2({ width: '100%' }/*,{placeholder: 'Select Item Name'}*/);
    /*$('.copyright').addClass('copyright_inc');
    $('#admin_main_menu').on('click', function(){
      $('.copyright').toggleClass('copyright_inc');
    })*/
  });
  //   added by Arunchandru 29-01-2025
  $('#pos_mater_btn').click(function(){
    if (confirm('This will Add(if exist update) POC records of choose hospital. Click OK to confirm')) {
        return true;
    } else {
        return false;
    }
  });
</script>
<style type="text/css">
  /*.copyright_inc
  {
    top:62rem !important;
  }*/
    .copy-center{
        position:inherit !important;
        margin: 20px  0;
    }
    div.dataTables_wrapper {
        max-width: 1357px;
        width: 100%;
        margin: 0 auto;
    }
</style> 
@endsection