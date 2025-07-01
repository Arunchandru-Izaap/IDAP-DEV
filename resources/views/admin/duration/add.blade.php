@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('duration-store')}}">
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
                <div class="form-group row date_section">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Start Date</label>
                <div class="col-sm-4">
                    <input type="date" class="form-control" id="start_date" name="start_date" placeholder="start_date" required>
                </div>
                </div>
                <div class="form-group row date_section">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">End Date</label>
                <div class="col-sm-4">
                    <input type="date" class="form-control" id="end_date" name="end_date" placeholder="end_date" required>
                </div>
                </div>
                <div class="form-group row">
                <label for="lname" class="col-sm-3 text-end control-label col-form-label">Days</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="days" name="days" placeholder="Days" required="" readonly="">
                </div>
                </div>
                
            </div>
            <div class="border-top">
                <div class="card-body">
                <button type="submit" class="btn btn-primary">
                    Submit
                </button>
                <a href="{{ route('duration-list') }}" class="btn btn-warning">Cancel</a>
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
  $(document).ready(function(){
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

            if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
              if (startDate.getTime() === endDate.getTime()) {
                /*alert('Start date and end date cannot be the same.');
                $('#days').val('');
                $('#end_date').val('')
                return;*/
              }
              let diffTime = endDate - startDate;
              let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
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
@endpush