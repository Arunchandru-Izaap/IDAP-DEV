@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
 
  .filter_section
  {
    margin-top: 1rem;
  }
  .no_activity_list{
    display: flex;
    width: 100%;
    justify-content: center;
    align-items: center;
    padding: 20px;
  }
  #loader1
    {
      background-image: url(../../images/loader.gif);
        background-repeat: no-repeat;
        background-position: center;
        background-size: 70px;
        height: 100vh;
        width: 100vw;
        display: block;
        position: fixed;
        top: 0;
        left: 0;
        overflow: hidden;
        background-color: rgba(241, 242, 243, 0.9);
        z-index: 9999;
    }
</style>
<!-- <div class="container"><h2>Initiator Dashboard demo</h2></div> -->
<!-- Page content wrapper-->
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
      <div class="collapse navbar-collapse show" id="navbarSupportedContent">
        <ul class="navbar-nav dashboard-nav">
          <li class="nav-item active">
            <li class="nav-item active"><a class="nav-link" href=""> <img src="{{ asset('admin/images/back.svg')}}">Activity Tracker</a></li>
          </li>
        </ul>
        <ul class="d-flex ml-auto user-name">
          <li>
            <h3>{{Session::get('emp_name')}}</h3>
            <p>Initiator</p>
          </li>
          <li>
            <img src="{{ asset('admin/images/Sun_Pharma_logo.png') }}">
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <ul class="bradecram-menu">
    <li>
      <a href="{{url('initiator/dashboard')}}"> Home </a>
    </li>
	<li class="">
      <a href="{{url('initiator/initiate_date')}}">
        Vq Initiate Date
      </a>
    </li>
    <li class="active">
      <a href="{{url('initiator/initiate_date_add')}}">
        @if($vq_ini_dates) Update @else Add @endif
      </a>
    </li>
    
  </ul>
  <!-- Page content-->
  <div class="container-fluid">
    <div class="row">
      <div id='loader1' style='display: none;'>
                              
      </div>
      <div class="col-md-12 send-quotation">
      </div>
      <div class="col">
      <div class="col-md-12 d-flex ">
    
        </div>
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
      <div class="actions-dashboard table-ct quotation-details">
      <div class="card">
              <form class="form-horizontal" method="post" action="{{route('initiate_date_update')}}">
                  @csrf
              <div class="card-body">
                  <h4 class="card-title"> @if($vq_ini_dates) Update @else Add @endif Date</h4>
                  @if($vq_ini_dates)<input type="hidden" id="date_id" name="id" value="{{$vq_ini_dates->id}}">@endif
                  <div class="form-group row">
                  <label for="fname" class="col-sm-3 text-end control-label col-form-label">Date</label>
                  <div class="col-sm-4">
                    <input type="date" id="form20_expiry_date" name="date" min="<?php echo date('Y-m-d'); ?>" value="{{$vq_ini_dates?$vq_ini_dates->date:old('date')}}">
                  </div>
                  </div>
                  
              </div>
              <div class="border-top">
                  <div class="card-body">
                  @if(!$vq_checker ||  date('Y-m-d') >= $year_arr[0].'-11-01')
                  <button type="submit" class="btn btn-primary">
                      Submit
                  </button>
                  @endif
                  </div>
              </disv>
              </form>
          </div>
      </div>
      <style type="text/css">
        p.copy-center.copyright{
          position: inherit;
          padding: 10px 0;
        }
        #page-content-wrapper nav{
	        background: #fff;
        }
      </style>
      <script src="{{asset('frontend/js/select2.js')}}"></script>
      <script type="text/javascript">
        const dateInput = document.getElementById("form20_expiry_date");
        // Get today's date
        const today = new Date();
        const year = today.getFullYear();
        let minDate, maxDate;
        // Define date ranges based on the current month
        if (today.getMonth() + 1 >= 11) { // November or later
            minDate = today.toISOString().split('T')[0]; // Current date
            maxDate = `${year + 1}-03-31`; // March 31st of next year
        } else if (today.getMonth() + 1 <= 3) { // January to March
            minDate = today.toISOString().split('T')[0]; // Current date
            maxDate = `${year}-03-31`; // March 31st of the current year
        } else {
            // If not within the range (for completeness, though this won't trigger in Nov-Mar cases)
            minDate = "";
            maxDate = "";
        }
        // Set the min and max attributes
        dateInput.min = minDate;
        dateInput.max = maxDate;
        // Optional: Add placeholder for clarity
        dateInput.placeholder = `Select a date between ${minDate} and ${maxDate}`;
      </script>
@endsection