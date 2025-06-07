@extends('layouts.frontend.app')
@section('content')
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
          <div class="collapse navbar-collapse show" id="navbarSupportedContent">
              <ul class="navbar-nav dashboard-nav">
                  <li class="nav-item active"><a class="nav-link" href="{{route('initiator-signature-list')}}"> <img src="../../admin/images/back.svg">Signature Master - Add Signature</a></li>
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
        <a href="{{route('initiator-signature-list')}}">
          Signature Master
        </a>
      </li>
      <li class="active">
        <a href="">
          Add Signature
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
            <form class="form-horizontal" method="post" action="{{route('initiator-signature-store')}}" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <h4 class="card-title">Signature Info</h4>
                    <div class="form-group row">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">Employee Code</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control text_input" id="employee_code" name="employee_code" placeholder="Employee Code" value="{{ old('employee_code') }}" required="">
                    </div>
                    </div>
                    <div class="form-group row">
                    <label for="lname" class="col-sm-3 text-end control-label col-form-label">Spll Sign</label>
                    <div class="col-sm-4">
                        <input type="file" class="form-control" id="spll_sign" name="spll_sign" required="" accept="image/*" >
                    </div>
                    </div>
                    <div class="form-group row">
                    <label for="lname" class="col-sm-3 text-end control-label col-form-label">Spli Sign</label>
                    <div class="col-sm-4">
                    <input type="file" class="form-control" id="spil_sign" name="spil_sign" required="" accept="image/*" >
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
<script type="text/javascript">
  $(document).ready(function(){
    $('body').on('input','.text_input',function(){
      this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
    })
    /*$('.copyright').addClass('copyright_inc');
    $('#admin_main_menu').on('click', function(){
      $('.copyright').toggleClass('copyright_inc');
    })*/
  })
  
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