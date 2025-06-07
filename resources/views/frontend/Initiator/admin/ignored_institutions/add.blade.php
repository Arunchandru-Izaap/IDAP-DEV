@extends('layouts.frontend.app')
@section('content')
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
          <div class="collapse navbar-collapse show" id="navbarSupportedContent">
              <ul class="navbar-nav dashboard-nav">
                  <li class="nav-item active"><a class="nav-link" href="{{route('initiator-ignored-institutions-list')}}"> <img src="../../admin/images/back.svg">Ignored Institutions Master - Add Ignored Institutions Master</a></li>
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
        <a href="{{route('initiator-ignored-institutions-list')}}">
          Ignored Institutions Master
        </a>
      </li>
      <li class="active">
        <a href="">
          Add Ignored Institutions Master
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
            <form class="form-horizontal" method="post" action="{{route('initiator-ignored-institutions-store')}}">
                @csrf
                <div class="card-body">
                    <h4 class="card-title">Institution Info</h4>
                    
                    <div class="form-group row">
                    <label for="parent_institution_id" class="col-sm-3 text-end control-label col-form-label">Parent Institution Code</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control text_input" id="parent_institution_id" name="parent_institution_id" placeholder="Parent Institution Code" maxlength="20" required="">
                    </div>
                    </div>

                    <div class="form-group row">
                    <label for="lname" class="col-sm-3 text-end control-label col-form-label">Institution Code</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control text_input" id="institution_id" name="institution_id" placeholder="Institution Code" maxlength="20" required="">
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
    $('body').on('input','.text_input_email',function(){
      this.value = this.value.replace(/[^a-zA-Z0-9@.-]/g, '');
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