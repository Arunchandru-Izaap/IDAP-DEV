@extends('layouts.frontend.app')
@section('content')
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
          <div class="collapse navbar-collapse show" id="navbarSupportedContent">
              <ul class="navbar-nav dashboard-nav">
                  <li class="nav-item active"><a class="nav-link" href="{{route('initiator-max-discount-cap-list')}}"> <img src="../../admin/images/back.svg">Max Discount Cap - Add Max Discount For Division</a></li>
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
        <a href="{{ route('initiator-max-discount-cap-list') }}">
          Max Discount Cap
        </a>
      </li>
      <li class="active">
        <a href="">
         Add Max Discount Cap For Division
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
            <form class="form-horizontal" method="post" action="{{route('initiator-max-discount-cap-store')}}">
                @csrf
                <div class="card-body">
                    <h4 class="card-title">Max Discount Cap Info</h4>
                    
                    <div class="form-group row">
                    <label for="divCode" class="col-sm-3 text-end control-label col-form-label">Division Code</label>
                    <div class="col-sm-4">
                        <select name="div_id" id="div_id" class="form-control" required="">
                            <option value="">Select Division Code</option>
                            @foreach($divArr as $div)
                                <option value="{{$div->div_id}}">{{$div->div_id}}</option>
                            @endforeach
                        </select>
                    </div>
                    </div>

                    <div class="form-group row">
                    <label for="max_discount" class="col-sm-3 text-end control-label col-form-label">Max Discount</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="max_discount" name="max_discount" placeholder="Max discount" onkeypress="if(this.value.length==5) return false;" required="">
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
    $('#max_discount').on('input', function() {
        var value = $(this).val();
        // Use regex to allow only numbers and decimals
        var validValue = value.replace(/[^0-9.]/g, '');
        
        // Ensure that only one decimal point is allowed
        if ((validValue.match(/\./g) || []).length > 1) {
            validValue = validValue.replace(/\.+$/, "");
        }
        
        // Convert the valid value to a number and check if it’s less than 100
        var numericValue = parseFloat(validValue);
        if (numericValue > 100) {
            validValue = validValue.slice(0, -1);
        }
        
        // Update the input with the valid value
        $(this).val(validValue);
    });
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