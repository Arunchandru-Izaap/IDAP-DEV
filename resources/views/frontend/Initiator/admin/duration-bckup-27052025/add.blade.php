@extends('layouts.frontend.app')
@section('content')
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
          <div class="collapse navbar-collapse show" id="navbarSupportedContent">
              <ul class="navbar-nav dashboard-nav">
                  <li class="nav-item active"><a class="nav-link" href="{{route('initiator-duration-list')}}"> <img src="../../admin/images/back.svg">Duration Master - Add Duration</a></li>
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
         Duration Master - Add Duration
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
            <form class="form-horizontal" method="post" action="{{route('initiator-duration-store')}}">
                @csrf
            <div class="card-body">
                <h4 class="card-title">Duration Info</h4>
                <div class="form-group row">
                <label for="fname" class="col-sm-3 text-end control-label col-form-label">Level</label>
                <div class="col-sm-4">
                    <select name="level" id="level" class="form-control">
                        <option value="1">RSM</option>
                        <option value="2">ZSM</option>
                        <option value="3">NSM</option>
                        <option value="4">SBU</option>
                        <option value="5">Semi Cluster</option>
                        <option value="6">Cluster</option>
                    </select>
                </div>
                </div>
                <div class="form-group row">
                <label for="fname" class="col-sm-3 text-end control-label col-form-label">Type</label>
                <div class="col-sm-4">
                    <select name="type" id="type" class="form-control">
                        <option value="vq">VQ</option>
                        <option value="reinitvq_normal">Reinitation Normal Workflow</option>
                        <option value="reinitvq_fast">Reinitation Fast Workflow</option>
                        
                    </select>
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Start Date</label>
                <div class="col-sm-4">
                    <input type="date" class="form-control" id="start_date" name="start_date" placeholder="start_date" required>
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">End Date</label>
                <div class="col-sm-4">
                    <input type="date" class="form-control" id="end_date" name="end_date" placeholder="end_date" required>
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Days</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control number_input_field" id="days" name="days" placeholder="Days" onkeypress="if(this.value.length==3) return false;" required="" readonly="">
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
    $('body').on('input','.number_input_field',function(){
        this.value = this.value.replace(/[^0-9]/gi, '')
    })
    /*$('.copyright').addClass('copyright_inc');
    $('#admin_main_menu').on('click', function(){
      $('.copyright').toggleClass('copyright_inc');
    })*/
    var minDate = '{{ $date['minDate'] }}';
    var maxDate = '{{ $date['maxDate'] }}';

    $("#start_date").attr("min", minDate);
    $("#end_date").attr("max", maxDate);
    $('#start_date').on('change', function(){
      var startDate = $(this).val()
      $("#end_date").val('')
      $('#days').val('')
      $("#end_date").attr("min", startDate)
    })
    $('#end_date').on('change', calculateDateDifference);
    function calculateDateDifference() {
        let startDateVal = $('#start_date').val();
        let endDateVal = $('#end_date').val();

        if (startDateVal !== '' && endDateVal !== '') {
            let startDate = new Date(startDateVal);
            let endDate = new Date(endDateVal);

            if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
              if (startDate.getTime() === endDate.getTime()) {
                alert('Start date and end date cannot be the same.');
                $('#days').val('');
                $('#end_date').val('')
                return;
              }
              let diffTime = endDate - startDate;
              let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
              $('#days').val(diffDays);
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