@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
  .no_activity_list
  {
    margin-top: 1rem;
  }
  .filter_section
  {
    margin-top: 1rem;
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
            <li class="nav-item active"><a class="nav-link" href="{{url('initiator/listing',$vq->id)}}"> <img src="{{ asset('admin/images/back.svg')}}"> {{$vq->hospital_name}} Activity Tracker</a></li>
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
      <a href="{{url('initiator')}}"> Home </a>
    </li>
    <li>
      <a href="{{url('initiator/listing')}}"> VQ Request Listing </a>
    </li>
    <li>
      <a href="{{url('initiator/listing',$vq->id)}}"> VQ Request Details </a>
    </li>
    <li class="">
      <a href="{{url('initiator/listing',$vq->id)}}">
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
      <div id='loader1' style='display: none;'>
                              
      </div>
      <div class="col-md-12 send-quotation">
        <!-- <h3>Activity Tracker - {{$url_id}}</h3> -->
      </div>
      <div class="col">
        <div class="actions-dashboard">
          <!-- <h5>Select Action</h5> -->
          <div class="filter_section  float-right1">
            <label>Filter Activity: </label>
            <select name="activity_type" class="js-example-basic-single" id="activity_filter" multiple="" data-placeholder="Select Activity To Filter">
              <option value=''> Select </option>
              @foreach($formattedTypes as $type)
                  <option value="{{ $type['db_value'] }}">{{ $type['display_value'] }}</option>
              @endforeach
            </select>
            <input type="hidden" id="vq_id" value="{{$url_id}}">
          </div>
          <div class="activity-tracker">
            <ul id="myList">
	        @foreach($data as $activity)
            <?php $activity_date = new DateTime($activity->created_at);  
	            
				  $tz = new DateTimeZone('Asia/Kolkata');
				  $activity_date->setTimezone($tz);
            ?>
                <li>
                  <div>
                  <h5>@if($activity->json_data)
                <a href="{{ url()->current() }}?download=true&activity_id={{ $activity->id }}" class="btn btn-link" title="Download JSON">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                      <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                      <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                    </svg> <!-- FontAwesome download icon -->
                </a>
                @endif At {{$activity_date->format('h:i A') }}, {{$activity_date->format('d-m-Y')}} - {{$activity->activity}} </h5>
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
        <div class="no_activity_list d-none">No Activity found <!-- for <span id="selectedFilter"></span> --></div>
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
        $('.js-example-basic-single').select2();
        $('#activity_filter').on('change', function(){
          var settings = {
            "url": "/initiator/activity_filter",
            "method": "POST",
            "timeout": 0,
            "headers": {
              "Accept-Language": "application/json",
              "Content-Type": "application/json"
            },
            "data": JSON.stringify({
              "_token": "{{ csrf_token() }}",
              "activity_value": $(this).val(),
              "vq_id": $('#vq_id').val(),
            })
          };
          $('#loader1').show(); 
          $('.no_activity_list').addClass('d-none')
          $.ajax(settings).done(function (response) {
            $('.activity-tracker').removeClass('d-none');
            $('#loader1').hide(); 
            if(response.status == true)
            {
              $('#myList').empty();
              $.each(response.data, function(index, activity) {
                var activityDate = new Date(activity.created_at);
                var formattedTime = activityDate.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit' });
                var formattedDate = activityDate.toLocaleDateString('en-IN', { day: '2-digit', month: '2-digit', year: 'numeric' });

                // Check if JSON data exists and create the download link
                var downloadLink = '';
                if (activity.json_data) {
                    downloadLink = `
                    <a href="${window.location.href}?download=true&activity_id=${activity.id}" class="btn btn-link" data-title="Download JSON">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                        </svg>
                    </a>`;
                }

                // Meta data, if available
                var metaData = activity.meta_data != null ? `<p>${activity.meta_data}</p>` : '';

                // Create the list item with formatted activity and metadata
                var li = `
                <li>
                    <div>
                        <h5>${downloadLink} At ${formattedTime}, ${formattedDate} - ${activity.activity}</h5>
                        ${metaData}
                    </div>
                </li>`;
                $('#myList').append(li);
              });
              size_li = $("#myList li").length;
              x = 3;
              $('#myList li:lt(' + x + ')').show();
              $('#loadMore').hide();
              if(size_li > 3)
              {
                $('#loadMore').show();
              }
            }
            else
            {
              $('.no_activity_list').removeClass('d-none')
              $('.activity-tracker').addClass('d-none');
              $('#selectedFilter').text($('#activity_filter option:selected').text())
            }
          });
        })
      </script>
@endsection