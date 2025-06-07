@extends('layouts.frontend.app')
@section('content')
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
          <div class="collapse navbar-collapse show" id="navbarSupportedContent">
              <ul class="navbar-nav dashboard-nav">
                  <li class="nav-item active"><a class="nav-link" href="{{route('initiator-duration-list')}}"> <img src="../../admin/images/back.svg">Duration Master - Edit Duration Master</a></li>
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
        <a href="{{ route('initiator-duration-list') }}">
          Duration Master
        </a>
      </li>
      <li class="active">
        <a href="">
         Edit Duration Master
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
            <form class="form-horizontal" method="post" action="{{route('initiator-duration-update')}}">
                @csrf
            <div class="card-body">
                <h4 class="card-title">Duration Info</h4>
                <input type="hidden" class="form-control" name="id"  value={{$data['id']}}>
                <div class="form-group row">
                <label for="fname" class="col-sm-3 text-end control-label col-form-label">Level</label>
                <div class="col-sm-4">
                    <select name="level" id="level" class="form-control">
                        <option value="1" <?php if ($data['level'] == '1') echo ' selected="selected"'; ?>>RSM</option>
                        <option value="2" <?php if ($data['level'] == '2') echo ' selected="selected"'; ?>>ZSM</option>
                        <option value="3" <?php if ($data['level'] == '3') echo ' selected="selected"'; ?>>NSM</option>
                        <option value="4" <?php if ($data['level'] == '4') echo ' selected="selected"'; ?>>SBU</option>
                        <option value="5" <?php if ($data['level'] == '5') echo ' selected="selected"'; ?>>Semi Cluster</option>
                        <option value="6" <?php if ($data['level'] == '6') echo ' selected="selected"'; ?>>Cluster</option>
                    </select>
                </div>
                </div>
                <div class="form-group row">
                <label for="fname" class="col-sm-3 text-end control-label col-form-label">Type</label>
                <div class="col-sm-4">
                    <select name="type" id="type" class="form-control">
                        <option value="vq" <?php if ($data['type'] == 'vq') echo ' selected="selected"'; ?>>VQ</option>
                        <option value="reinitvq_normal" <?php if ($data['type'] == 'reinitvq_normal') echo ' selected="selected"'; ?>>Reinitation Normal Workflow</option>
                        <option value="reinitvq_fast" <?php if ($data['type'] == 'reinitvq_fast') echo ' selected="selected"'; ?>>Reinitation Fast Workflow</option>
                        
                    </select>
                </div>
                </div>
                <div class="form-group row date_section">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Start Date</label>
                <div class="col-sm-4">
                    <input type="date" class="form-control" id="start_date" name="start_date" placeholder="start_date" value="{{ isset($data['start_date']) ? \Carbon\Carbon::parse($data['start_date'])->format('Y-m-d') : '' }}" required readonly>
                </div>
                </div>
                <div class="form-group row date_section">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">End Date</label>
                <div class="col-sm-4">
                    <input type="date" class="form-control" id="end_date" name="end_date" placeholder="end_date" value="{{ isset($data['end_date']) ? \Carbon\Carbon::parse($data['end_date'])->format('Y-m-d') : '' }}" min="{{ isset($date['minDate']) ? $date['minDate'] : '' }}" required>
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Days</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control number_input_field" id="days" name="days" placeholder="Days" value="{{$data['days']}}" onkeypress="if(this.value.length==3) return false;" readonly="" required="">
                </div>
                </div>
                
            </div>
            <div class="border-top">
                <div class="card-body">
                <button type="submit" class="btn btn-primary">
                    Submit
                </button>
                <a href="{{ route('initiator-duration-list') }}" class="btn btn-warning">Cancel</a>
                </div>
            </div>
            </form>
        </div>
    </div>
<script type="text/javascript">
  $(document).ready(function(){
    $('body').on('input','.number_input_field',function(){
        this.value = this.value.replace(/[^0-9]/gi, '')
    })
    /*$('.copyright').addClass('copyright_inc');
    $('#admin_main_menu').on('click', function(){
      $('.copyright').toggleClass('copyright_inc');
    })*/
    //var minDate = '{{ isset($data['start_date']) ? \Carbon\Carbon::parse($data['start_date'])->format('Y-m-d') : '' }}';
    var minDate = '{{ $date['minDate'] }}';
    var maxDate = '{{ $date['maxDate'] }}';
    var setDays = parseInt('{{isset($data['end_date']) ? $data['days'] : 0}}');
    var setEndDate = '{{ isset($data['end_date']) ? \Carbon\Carbon::parse($data['end_date'])->format('Y-m-d') : '' }}';

    $("#start_date").attr("min", minDate);
    $("#end_date").attr("max", maxDate);
    $('#start_date').on('change', function(){
      var startDate = $(this).val()
      $("#end_date").val('')
      $('#days').val('')
      $("#end_date").attr("min", startDate)
    })
    if($('#type').val() !='vq')
    {
      $('.date_section').addClass('d-none')
      $('#start_date').removeAttr('required')
      $('#end_date').removeAttr('required')
      $('#days').removeAttr('readonly')
      $('#start_date').val('')
      $('#end_date').val('')
    }
    else
    {
      $('.date_section').removeClass('d-none')
      $('#start_date').attr('required', true);
      $('#end_date').attr('required', true);
      $('#days').attr('readonly', true)
    }
    $('#type').on('change', function(){
      if($(this).val() !='vq')
      {
        $('.date_section').addClass('d-none')
        $('#start_date').removeAttr('required')
        $('#end_date').removeAttr('required')
        $('#days').removeAttr('readonly')
        $('#start_date').val('')
        $('#end_date').val('')
        $('#days').val('')
      }
      else
      {
        $('.date_section').removeClass('d-none')
        $('#start_date').attr('required', true);
        $('#end_date').attr('required', true);
        $('#days').attr('readonly', true)
      }
    })
    $('#end_date').on('change', calculateDateDifference);
    function calculateDateDifference() {
        let startDateVal = $('#start_date').val();
        let endDateVal = $('#end_date').val();

        if (startDateVal !== '' && endDateVal !== '') {
            let startDate = new Date(startDateVal);
            let endDate = new Date(endDateVal);
            let dbSetEndDate = new Date(setEndDate);
            if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
              if (startDate.getTime() === endDate.getTime()) {
                /*alert('Start date and end date cannot be the same.');
                $('#days').val('');
                $('#end_date').val('')
                return;*/
              }
              if(endDate >= dbSetEndDate )
              {
                let diffTime = endDate - dbSetEndDate;
                let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                $('#days').val(diffDays + setDays);
              }
              else if(endDate < dbSetEndDate )
              {
                let diffTime =  dbSetEndDate - endDate;
                let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                $('#days').val(setDays - diffDays);
              }
              if(endDate.getTime() < startDate.getTime())
              {
                alert('Selected End Date is less than Start Date');
                $('#days').val("{{ isset($data['days']) ? $data['days'] : '' }}");
                $('#end_date').val("{{ isset($data['end_date']) ? \Carbon\Carbon::parse($data['end_date'])->format('Y-m-d') : '' }}");

              }
              
            } else {
              $('#days').val('');
            }
        } else {
            $('#days').val('');
        }
    }
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