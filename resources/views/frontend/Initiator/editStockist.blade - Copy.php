@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
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
.red-btn
{
  border-radius: 24px;
  box-shadow: 0 4px 12px 0 rgba(208, 210, 214, 0.99);
  background: red;
  font-size: 14px;
  font-weight: 800;
  font-stretch: normal;
  font-style: normal;
  line-height: 1.43;
  letter-spacing: 0.2px;
  text-align: center;
  color: #fff;
  border: none;
  padding: 7px 20px;
  text-transform: uppercase;
}
.red-btn:disabled {
    background: #faf9f7;
    color: #b4b3b1;
}
.red-btn:hover {
    /*color: #fff;*/
    text-decoration: none;
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
      <div id='loader1' style='display: none;'> </div>
      <div class="col-md-12 d-flex send-quotation">
        <h3>You can select a maximum of 3 Stockist. Un-check an existing Stockist to change.</h3>
        <button id="approve" class="orange-btn ml-auto" disabled>
          Add Stockist
        </button>
        <button id="delete" class="red-btn ml-auto" disabled>
          Delete Stockist
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
          <input type="checkbox" id="{{$item->id}}" class="single-checkbox" name="stockist" data-custom-state="unchanged" value="{{$item->stockist_code}}" @if($item->stockist_type_flag == 1) checked @endif  >
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

          <a class="btn orange-btn big-btn cancel_btn">Go to Edit Paymode</a>
        </div>
      </div>
    </div>
</div>


      <script>
           $('#approve').click(function(){
               checkboxData = [];
            $("input:checkbox[name=stockist]:checked").each(function () {
              let checkboxValue = $(this).val();
              let checkboxState = $(this).attr('data-custom-state');
              let checkboxId = $(this).attr('id');
              checkboxData.push({ value: checkboxValue, state: checkboxState, id: checkboxId });
                //alert("Id: " + $(this).attr("id") + " Value: " + $(this).val());
            });
            $("input:checkbox[name=stockist]").each(function () {
              if (!$(this).is(':checked')) {
                  let checkboxValue = $(this).val();
                  let checkboxState = $(this).attr('data-custom-state');
                  let checkboxId = $(this).attr('id');
                  checkboxData.push({ value: checkboxValue, state: checkboxState, id: checkboxId });
              }
            });
      // Add ajax call for updating price here
      var settings = {
        "url": "/initiator/edit_stockist",
        "method": "POST",
        "timeout": 0,
        "headers": {
          "Accept-Language": "application/json",
        },
        "data": {
          "_token": "{{ csrf_token() }}",
          "vq_id": "{{ app('request')->route('id') }}",
          "institution_code":"{{ $vq->institution_id }}",
          "stockist_change_data": checkboxData,
        }
      };
      $.ajax(settings).done(function (response) {
        console.log(response);
        if(response.success==true)
        {
          $('#submited').modal('show');
        }
      });
    });
	$('.cancel_btn').click(function(){
		//window.history.back();
    window.location.href = `{{ url('initiator/edit_payment_mode') }}/{{ app('request')->route('id') }}`;
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
   $(document).on({
     ajaxStart: function() { $('#loader1').show();    },
     ajaxStop: function() { $('#loader1').hide(); }    
  }); 
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
    else
    {
      if ($(this).is(':checked')) {
          $(this).attr('data-custom-state', 'checked');
      } else {
          $(this).attr('data-custom-state', 'unchecked');
      }
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