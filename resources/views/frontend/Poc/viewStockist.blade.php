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
            <a class="nav-link" href="">Stockists</a>
          </li>
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
      <a href="{{url('initiator/listing')}}">
        VQ Request Listing
      </a>
    </li>
    <li class="">
      <a href="{{url('initiator/listing')}}">
        {{$vq->hospital_name}}
      </a>
    </li>
    <li class="active">
      <a href="">
        Stockists
      </a>
    </li>
  </ul>
  <!-- Page content-->
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12 d-flex send-quotation">
        <h3>You can select a maximum of 3 Stockist. Un-check an existing Stockist to change.</h3>
        <button id="approve" class="orange-btn ml-auto" disabled>
          SAVE CHANGES
        </button>
      </div>
    


<div class="col">
<div class="actions-dashboard table-ct quotation-details">
  <!-- <div class="col"> -->
    <h2>{{$vq->hospital_name}} Stockists</h2>
    <ul class="checkobx-ct d-flex">
      @foreach($data as $item)
        @if(!is_null($item->stockist_name) || !empty($item->stockist_name))
        <li class="checkbox">
          <input type="checkbox" id="{{$item->id}}" class="single-checkbox" name="stockist" value="{{$item->stockist_code}}" @if($item->stockist_type_flag == 1) checked @endif  >
              <label for="{{$item->id}}"> {{$item->stockist_name}}</label>
        </li>
        @endif
      @endforeach
      </ul>
  <!-- </div> -->
</div>
</div>

<div class="modal show" id="submited">
    <div class="modal-dialog modal-dialog-centered model-pop-ct">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header border-0">
            <div class="tich-logo">
            <img src="{{ asset('admin/images/check-double.svg') }}" alt="">
            </div>
          <button type="button" class="close cancel_btn" data-dismiss="modal">
              <img src="{{ asset('admin/images/close.svg') }}" alt="">
          </button>
        </div>
        <!-- Modal body -->
        <div class="modal-body">
          <p class="border-0">Stockist details for {{$vq->hospital_name}} successfully saved.</p>

          <a class="btn orange-btn big-btn cancel_btn">Go to VQ Details</a>
        </div>
      </div>
    </div>
</div>


      <script>
           $('#approve').click(function(){
               var arr = [];
            $("input:checkbox[name=stockist]:checked").each(function () {
                arr.push($(this).val());
                //alert("Id: " + $(this).attr("id") + " Value: " + $(this).val());
            });
      // Add ajax call for updating price here
      var settings = {
        "url": "/initiator/save_stockist",
        "method": "POST",
        "timeout": 0,
        "headers": {
          "Accept-Language": "application/json",
        },
        "data": {
          "_token": "{{ csrf_token() }}",
          "institution_code": "{{ app('request')->route('id') }}",
          "stockist_codes": arr,
        }
      };
      $.ajax(settings).done(function (response) {
        console.log(response);
        $('#submited').modal('show');
      });
    });
	$('.cancel_btn').click(function(){
		window.history.back();
	});


// $(function() {
/*
 var limit = 2;
  $('ul li input.single-checkbox').on('change', function(evt) {
    
    $('#approve').prop("disabled", !$("ul li input.single-checkbox").prop("checked")); 
    
     if($(this).siblings(':checked').length >= limit) {
         this.checked = false;
         
     }
     
  });
*/
// });


$(document).ready(function() {
   
  $length_check = $("input[type='checkbox']:checked").length;
  if($length_check>0){
    $('#approve').removeAttr('disabled');
  }
   // when user updates a checkbox.
// $("input[type='checkbox']").change(function(){
	$("input[type='checkbox']").on('change', function(evt) {
		
		$('#approve').prop("disabled", !$("input[type='checkbox']").prop("checked")); 
		
       // total allowed to be checked.
    var max_allowed = 3;
       // count how many boxes have been checked.
    var checked = $("input[type='checkbox']:checked").length;
       // perform test
    if ( checked > max_allowed ) {
           // is more than the max so uncheck.
        $(this).attr("checked", false);
//         $("input[type='checkbox']").removeAttr("checked");
        this.checked = false;
           // display error message.
    }
    if(checked>0){
      $('#approve').removeAttr('disabled');
    }else{
      $('#approve').attr('disabled','disabled');
    }
    
    
  });
});

      </script>
@endsection