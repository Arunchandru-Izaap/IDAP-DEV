@extends('layouts.frontend.app')
@section('content')
<!-- <div class="container"><h2>Initiator Dashboard demo</h2></div> -->
<!-- Page content wrapper-->
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
      <div class="collapse navbar-collapse show" id="navbarSupportedContent">
        <ul class="navbar-nav dashboard-nav">
          <li class="nav-item active">
            <li class="nav-item active"><a class="nav-link" href="{{url('ho/listing',$vq->id)}}"> <img src="{{ asset('admin/images/back.svg')}}"> {{$vq->hospital_name}} Activity Tracker</a></li>
          </li>
        </ul>
        <ul class="d-flex ml-auto user-name">
          <li>
            <h3>{{Session::get('emp_name')}}</h3>
            <p>Ho</p>
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
      <a href="{{url('ho/dashboard')}}"> Home </a>
    </li>
    <li>
      <a href="{{url('ho/listing')}}"> VQ Request Listing </a>
    </li>
    <li>
      <a href="{{url('ho/listing',$vq->id)}}"> VQ Request Details </a>
    </li>
    <li class="">
      <a href="{{url('ho/listing',$vq->id)}}">
        {{$vq->hospital_name}}
      </a>
    </li>	
	<li class="active">
      <a href="">
        Activity Tracker
      </a>
    </li>
    
  </ul>
  <!-- Page content-->
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12 send-quotation">
        <!-- <h3>Activity Tracker - {{$url_id}}</h3> -->
      </div>
      <div class="col">
        <div class="actions-dashboard">
          <!-- <h5>Select Action</h5> -->
          <div class="activity-tracker">
            <ul id="myList">
	        @foreach($data as $activity)
            <?php $activity_date = new DateTime($activity->created_at);  
	            
				  $tz = new DateTimeZone('Asia/Kolkata');
				  $activity_date->setTimezone($tz);
            ?>
                <li>
                  <div>
                  <h5>At {{$activity_date->format('h:i A') }}, {{$activity_date->format('d-m-Y')}} - {{$activity->activity}} </h5>
                  @if($activity->meta_data != NULL)
                  <p>{{$activity->meta_data}}</p>
                  @endif
                  </div>
                </li>
            @endforeach
                
            </ul>
            @if($data->count()>3)
            <button id="loadMore" class="orange-btn-bor">VIEW MORE</button>
            @else
            @endif
        </div>
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
@endsection