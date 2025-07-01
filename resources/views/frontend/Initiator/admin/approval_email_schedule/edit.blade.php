@extends('layouts.frontend.app')
@section('content')
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
          <div class="collapse navbar-collapse show" id="navbarSupportedContent">
              <ul class="navbar-nav dashboard-nav">
                  <li class="nav-item active"><a class="nav-link" href="{{route('initiator-approval-email-schedule-list')}}"> <img src="../../admin/images/back.svg">Approval Email Schedule Master - Edit Approval Email Schedule Master</a></li>
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
        <a href="{{ route('initiator-approval-email-schedule-list') }}">
        Approval Email Schedule Master
        </a>
      </li>
      <li class="active">
        <a href="">
         Edit Approval Email Schedule Master
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

            <form class="form-horizontal" method="post" action="{{route('initiator-approval-email-schedule-update')}}">
                @csrf
                <div class="card-body">
                    <h4 class="card-title">Approval Email Schedule Info</h4>
                    <input type="hidden" class="form-control" name="id"  value={{$data['id']}}>
                    
                    <div class="form-group row">
                      <label for="level" class="col-sm-3 text-end control-label col-form-label">Employee Type</label>
                      <div class="col-sm-4">
                          <select name="level" id="level" class="form-control">
                            <option value="">Select</option>
                            <option value="1"<?php if ($data['level'] == '1') echo ' selected="selected"'; ?>>RSM</option>
                            <option value="2"<?php if ($data['level'] == '2') echo ' selected="selected"'; ?>>ZSM</option>
                            <option value="3"<?php if ($data['level'] == '3') echo ' selected="selected"'; ?> class="poc-hidden">NSM</option>
                            <option value="4"<?php if ($data['level'] == '4') echo ' selected="selected"'; ?> class="poc-hidden">SBU</option>
                            <option value="5"<?php if ($data['level'] == '5') echo ' selected="selected"'; ?> class="poc-hidden">Semi Cluster</option>
                            <option value="6"<?php if ($data['level'] == '6') echo ' selected="selected"'; ?> class="poc-hidden">Cluster</option>
                          </select>
                      </div>
                    </div>

                    <div class="form-group row">
                      <label for="fname" class="col-sm-3 text-end control-label col-form-label">Type</label>
                      <div class="col-sm-4">
                          <select name="type" id="type" class="form-control">
                            <option value="">Select</option>
                            <option value="vq" <?php if ($data['type'] == 'vq') echo ' selected="selected"'; ?>>VQ</option>
                            <!-- <option value="reinitvq_normal" <?php if ($data['type'] == 'reinitvq_normal') echo ' selected="selected"'; ?>>Reinitation Normal Workflow</option>
                            <option value="reinitvq_fast" <?php if ($data['type'] == 'reinitvq_fast') echo ' selected="selected"'; ?>>Reinitation Fast Workflow</option> -->
                          </select>
                      </div>
                    </div>
                    <div class="form-group row date_section">
                      <label for="lname" class="col-sm-3 text-end control-label col-form-label">Start Date</label>
                      <div class="col-sm-4">
                          <input type="text" class="form-control" id="start_date" name="start_date" placeholder="Start Date" readonly="" value="{{ isset($dmdata['start_date']) ? \Carbon\Carbon::parse($dmdata['start_date'])->format('d M Y') : '' }}">
                      </div>
                    </div>
                    <div class="form-group row date_section">
                      <label for="lname" class="col-sm-3 text-end control-label col-form-label">End Date</label>
                      <div class="col-sm-4">
                          <input type="text" class="form-control" id="end_date" name="end_date" placeholder="End Date" readonly="" value="{{ isset($dmdata['end_date']) ? \Carbon\Carbon::parse($dmdata['end_date'])->format('d M Y') : '' }}">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="lname" class="col-sm-3 text-end control-label col-form-label">Days</label>
                      <div class="col-sm-4">
                          <input type="text" class="form-control number_input_field" id="days" name="days" placeholder="Days" readonly="" value="{{$dmdata['days']}}">
                      </div>
                    </div>

                    <div class="form-group row">
                        <label for="start_days" class="col-sm-3 text-end control-label col-form-label">Start day</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="start_days" name="start_days" placeholder="Start day" required="" onkeypress="if(this.value.length==3) return false;" value="{{$data['start_days']}}">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="frequency_days" class="col-sm-3 text-end control-label col-form-label">Frequency day </label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="frequency_days" name="frequency_days" placeholder="Frequency day" value="{{$data['frequency_days']}}">
                        </div>
                    </div>
                    
                </div>
                <div class="border-top">
                    <div class="card-body">
                    <button type="submit" class="btn btn-primary" id='submit-btn'>
                        Submit
                    </button>
                    <a href="{{ route('initiator-approval-email-schedule-list') }}" class="btn btn-warning">Cancel</a>
                    </div>
                </div>
            </form>
            
        </div>
    </div>
    <script src="{{ asset('frontend/js/select2.js') }}"></script>
<script type="text/javascript">
  $('.js-example-basic-multiple').select2({ width: '100%' });

  // latest validation
  $('#start_days').on('keyup', function() {
    let stdays = parseInt($(this).val());
    var days = parseInt($('#days').val());
    var startdate = $('#start_date').val();
    var enddate = $('#end_date').val();

    // Define your two dates
    let date1 = new Date(startdate);
    let date2 = new Date(enddate);
    // Calculate the difference in milliseconds
    let timeDiff = date2.getTime() - date1.getTime();
    // Convert milliseconds to days
    let dayDiff = timeDiff / (1000 * 3600 * 24) + 1;

    console.log("Difference in days:", dayDiff); // Output: 4

    $('#submit-btn').prop("disabled", false);
    if(stdays == '' || days == ''){
      alert('Please Enter Start days');
      $('#submit-btn').prop("disabled", true);
    }else{
      if (stdays === 0 || isNaN(stdays)) {
        alert("Please enter a number greater than 0.");
        $('#submit-btn').prop("disabled", true);
      }else{
        if(stdays > parseInt(dayDiff)){
          alert('Start day should not be greater then configured Start Date and End Date');
          $('#submit-btn').prop("disabled", true);
        }
      }
    }
  });
  /*
  $('#start_days').on('keyup', function() {
    let stdays = parseInt($(this).val());
    var days = parseInt($('#days').val());
    $('#submit-btn').prop("disabled", false);
    if(stdays == '' || days == ''){
      alert('Please Enter start days');
      $('#submit-btn').prop("disabled", true);
    }else{
      if (stdays === 0 || isNaN(stdays)) {
        alert("Please enter a number greater than 0.");
        $('#submit-btn').prop("disabled", true);
      }else{
        if(stdays > days){
          alert('Start day should not be greater then configured days');
          $('#submit-btn').prop("disabled", true);
        }
      }
    }
  }); */
  
  $('#frequency_days').on('keyup', function() {
    let value = $(this).val();
    var startdate = $('#start_date').val();
    var enddate = $('#end_date').val();

    // Define your two dates
    let date1 = new Date(startdate);
    let date2 = new Date(enddate);
    // Calculate the difference in milliseconds
    let timeDiff = date2.getTime() - date1.getTime();
    // Convert milliseconds to days
    let dayDiff = timeDiff / (1000 * 3600 * 24) + 1;

    console.log("Difference in days:", dayDiff); // Output: 4
    if(value != ''){
      if (parseInt(value) === 0 || isNaN(value)) {
        alert("Please enter a number greater than 0.");
        $('#submit-btn').prop("disabled", true);
      }
      else{
        if(parseInt(value) > parseInt(dayDiff)){
          alert('Frequency days should not be greater then configured  Start Date and End Date');
          $('#submit-btn').prop("disabled", true);
        }else{
          $('#submit-btn').prop("disabled", false);
        }
      }
    }else{
      alert('Please Enter Frequency days');
      $('#submit-btn').prop("disabled", false);
    }
  });

  if($('#type').val() != 'vq'){
    $('.date_section').addClass('d-none');
  }else{
    $('.date_section').removeClass('d-none');
  }

  $('#level').on('change', function() {
    let level = $('#level').val();
    let type = $('#type').val();
    $('#submit-btn').prop("disabled", false);
    if(type != 'vq'){
      $('.date_section').addClass('d-none')
    }else{
      $('.date_section').removeClass('d-none')
    }
    if(level != '' && type != ''){
      var settings = {
        "url": "/initiator/approvalemailscheduleDays",
        "method": "POST",
        "timeout": 0,
        "headers": {
          "Accept-Language": "application/json",
        },
        "data": {
          "_token": "{{ csrf_token() }}",
          "level": level,
          "type": type,
        }
      };
      $.ajax(settings).done(function (response) {
        if(response.data != ''){
          console.log(response.data[0]);
          $('#days').val(response.data[0].days);
          $('#start_date').val(changedateformat(response.data[0].start_date));
          $('#end_date').val(changedateformat(response.data[0].end_date));
          if($('#start_days').val() != ''){
            $('#start_days').trigger('keyup'); 
          }
        }else{
          alert('Please update the duration master table based on level and type');
          $('#submit-btn').prop("disabled", true);
        }
      });
    }
  });

  $('#type').on('change', function () {
    $('#level').trigger('change'); // trigger logic connected to employeeType
  });

  function changedateformat(originalDate)
  {
    const dateObj = new Date(originalDate.replace(' ', 'T')); // Convert to Date object

    // Define month names
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    const day = dateObj.getDate();
    const month = monthNames[dateObj.getMonth()];
    const year = dateObj.getFullYear();

    const formattedDate = `${day} ${month} ${year}`;
    return formattedDate;
  }
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