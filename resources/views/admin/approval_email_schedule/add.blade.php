@extends('layouts.admin.app')
@section('content')
<style type="text/css">
     .select2-container--classic .select2-selection--multiple .select2-selection__choice, .select2-container--default .select2-selection--multiple .select2-selection__choice, .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    /*background-color: #2255a4;
    border-color: #2255a4;*/
    color: #000;
}
</style>
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('approval-email-schedule-store')}}">
                @csrf
                <div class="card-body">
                    <h4 class="card-title">Approval Email Schedule Info</h4>
                    
                    <div class="form-group row">
                        <label for="level" class="col-sm-3 text-end control-label col-form-label">Employee Type</label>
                        <div class="col-sm-4">
                            <select name="level" id="level"  class="form-control">
                                <option value="">Select</option>
                                <option value="1" {{ old('emp_type') == '1' ? 'selected' : '' }}>RSM</option>
                                <option value="2" {{ old('emp_type') == '2' ? 'selected' : '' }}>ZSM</option>
                                <option value="3" class="poc-hidden" {{ old('emp_type') == '3' ? 'selected' : '' }}>NSM</option>
                                <option value="4" class="poc-hidden" {{ old('emp_type') == '4' ? 'selected' : '' }}>SBU</option>
                                <option value="5" class="poc-hidden" {{ old('emp_type') == '5' ? 'selected' : '' }}>Semi Cluster</option>
                                <option value="6" class="poc-hidden" {{ old('emp_type') == '6' ? 'selected' : '' }}>Cluster</option>
                            </select>
                        </div>
                    </div>

                    
                    <div class="form-group row">
                      <label for="fname" class="col-sm-3 text-end control-label col-form-label">Type</label>
                      <div class="col-sm-4">
                          <select name="type" id="type" class="form-control">
                            <option value="">Select</option>
                            <option value="vq">VQ</option>
                            <!-- <option value="reinitvq_normal">Reinitation Normal Workflow</option>
                            <option value="reinitvq_fast">Reinitation Fast Workflow</option> -->
                              
                          </select>
                      </div>
                    </div>
                    <div class="form-group row date_section">
                      <label for="lname" class="col-sm-3 text-end control-label col-form-label">Start Date</label>
                      <div class="col-sm-4">
                          <input type="text" class="form-control" id="start_date" name="start_date" placeholder="Start Date" readonly="">
                      </div>
                    </div>
                    <div class="form-group row date_section">
                      <label for="lname" class="col-sm-3 text-end control-label col-form-label">End Date</label>
                      <div class="col-sm-4">
                          <input type="text" class="form-control" id="end_date" name="end_date" placeholder="End Date" readonly="">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="lname" class="col-sm-3 text-end control-label col-form-label">Days</label>
                      <div class="col-sm-4">
                          <input type="text" class="form-control number_input_field" id="days" name="days" placeholder="Days" readonly="">
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="start_days" class="col-sm-3 text-end control-label col-form-label">Start Day</label>
                      <div class="col-sm-4">
                        <input type="text" class="form-control text_input" id="start_days" name="start_days" placeholder="Start Days" required="" onkeypress="if(this.value.length==3) return false;" value="{{ old('start_days') }}">
                      </div>
                    </div>

                    <div class="form-group row">
                      <label for="frequency_days" class="col-sm-3 text-end control-label col-form-label">Frequency day </label>
                      <div class="col-sm-4">
                        <input type="text" class="form-control text_input" id="frequency_days" name="frequency_days" placeholder="Frequency day" value="{{ old('frequency_days') }}">
                      </div>
                    </div>
                    
                </div>
                <div class="border-top">
                    <div class="card-body">
                    <button type="submit" class="btn btn-primary" id='submit-btn'>
                        Submit
                    </button>
                    <a href="{{ route('approval-email-schedule-list') }}" class="btn btn-warning">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>

@endsection
@push('scripts')
<script type="text/javascript">
  $('.js-example-basic-multiple').select2({ width: '100%' },{placeholder: 'Select Brand Name'});

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
  });
  */
  
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
          alert('Frequency days should not be greater then configured days');
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

  $('#level').on('change', function() {
    let level = $('#level').val();
    let type = $('#type').val();
    $('#submit-btn').prop("disabled", false);
    if(type != 'vq' && type != ''){
      $('.date_section').addClass('d-none')
    }else{
      $('.date_section').removeClass('d-none')
    }
    if(level != '' && type != ''){
      var settings = {
        "url": "/admin/approvalemailscheduleDays",
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
@endpush