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
	<li class="active">
      <a href="{{url('initiator/initiate_date')}}">
        Vq Initiate Date
      </a>
    </li>
    
  </ul>
  <!-- Page content-->
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            @if(!$vq_checker ||  date('Y-m-d') >= $year_arr[0].'-11-01')
            <h5 class="card-title"><a id="add_date_btn" class="orange-btn-bor" href="{{url('/initiator/initiate_date_add')}}">@if(count($vq_ini_dates) != 0) Update Date   @else Add Date @endif</a></h5>
            @endif
            <div class="table-responsive">
              <table
                id="zero_config"
                class="table table-striped table-bordered"
              >
                <thead>
                  <tr>
                    <th>No.</th>
                    <th>Date</th>
                    <th><strong>Action</strong></th>
                  </tr>
                </thead>
                <tbody>
                  
                  @foreach($vq_ini_dates as $k => $date)
                  @php
                    $init_date = new DateTime($date->date);
                    $formattedTime = $init_date->format('h:i A');
                    $formattedDate = $init_date->format('d-m-Y');
                      @endphp
                  <tr>
                    <td>{{$k+1}}</td>
                    <td>{{$formattedDate}}</td>
                    <td>
                      @if(!$vq_checker ||  date('Y-m-d') >= $year_arr[0].'-11-01')
                      <a href="{{url('/initiator/initiate_date_add')}}" class="btn btn-primary btn-sm">Edit</a>
                      @else
                      <a href="" class="btn btn-primary btn-sm disabled">Edit</a>
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div> 
      @if(count($vq_ini_dates) ==0)
        <div class="no_date_list text-center">Vq initiate date not found <span class="ml-1" id="selectedFilter"></span></div>
      @endif
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
      </script>
@endsection