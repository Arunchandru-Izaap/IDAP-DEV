@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
  .copy-center{
    position:inherit !important;margin: 20px 0;
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

  .disabled-link {
      pointer-events: none; /* Prevent clicking */
      cursor: not-allowed; /* Show not-allowed cursor */
      text-decoration: none; /* Optional: Remove underline */
      opacity: 0.5; /* Optional: Reduce opacity */
  }
</style>
<div id="page-content-wrapper">
                <!-- Top navigation-->
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                            <ul class="navbar-nav dashboard-nav">
                                <li class="nav-item active"><a class="nav-link" href="{{route('initiator_dashboard')}}"> <img src="{{ asset('admin/images/back.svg') }}">VQ Request Listing</a></li>
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
                          
                       @if(isset($data[0]))
                           {{ $data[0]->institution_name }}
                       @else
                           Data not found
                       @endif

                      </a>
                    </li>
                    <li class="active">
                      <a href="">
                        Quotation Recipients
                      </a>
                    </li>
                </ul>
                <!-- Page content-->
                <div class="container-fluid">
                    <!-- <h1 class="mt-4">Simple Sidebar</h1> -->
                    <div id='loader1' style='display: none;'></div>
                    <div class="row">
                            <div class="col-md-8 d-flex send-quotation">
                                <h3 class="pl-15">@if(isset($data[0]))
                                        {{ $data[0]->institution_name }}
                                    @else
                                        Data not found
                                    @endif </h3>
                            <div class="cancel-btn ml-auto">
                            @if(isset($data[0]))
                                <a href="" id="approve_final" class="orange-btn <?php echo ($idap_disc_tran_exist['exists_flag'] == 1)? 'disabled-link' : '';?>">
                                Send Quotation
                                </a>
                            @else
                                Cannot send quotation, please add POC
                            @endif
                               
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="actions-dashboard table-ct quotation-details">
                                <h2>Quotation Recipients</h2>
                                @if(isset($data[0]))
                                    <table class="table VQ Request Listing vq-request-listing-tb" id="">
                                        <thead>
                                          <tr>
                                            <th>POC Name</th>
                                            <th>POC Designation</th>
                                            <th>Email</th>
                                            <th>Number</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                          @if(($data[0]->fsm_name) != 'VACANT' && ($data[0]->fsm_name) !='NULL' && ($data[0]->fsm_name) != 0)
                                            <tr class="even">
                                                <td>{{$data[0]->fsm_name}}</td>
                                                <td>FSM</td>
                                                <td>{{$data[0]->fsm_email}}</td>
                                                <td>{{$data[0]->fsm_number}}</td>
                                            </tr>
                                          @endif
                                          @if(($data[0]->rsm_name) != 'VACANT' && ($data[0]->rsm_name) !='NULL' && ($data[0]->rsm_name) != 0)
                                            <tr class="odd">
                                                <td>{{$data[0]->rsm_name}}</td>
                                                <td>RSM</td>
                                                <td>{{$data[0]->rsm_email}}</td>
                                                <td>{{$data[0]->rsm_number}}</td>
                                            </tr>
                                            @endif
                                            @if(($data[0]->zsm_name) != 'VACANT' && ($data[0]->zsm_name) !='NULL' && ($data[0]->zsm_name) != 0)
                                            <tr class="even">
                                                <td>{{$data[0]->zsm_name}}</td>
                                                <td>ZSM</td>
                                                <td>{{$data[0]->zsm_email}}</td>
                                                <td>{{$data[0]->zsm_number}}</td>
                                            </tr>
                                            @endif
                                        </tbody>
                                      </table>

                                @else
                                    Data not found
                                @endif

                              

                            </div>
                        </div><!-- col close -->

<div class="modal show" id="idap_disc_tran_exist_modal" style="display:<?php echo ($idap_disc_tran_exist['exists_flag'] == 1)? 'block' : 'none';?>">
    <div class="modal-dialog modal-dialog-centered model-pop-ct" style="max-width: 1000px;">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header border-0">
            <div class="tich-logo">
            <img style="width: 50px;" src="{{ asset('admin/images/ticket.svg') }}" alt="">
            </div>
          <button type="button" class="close cancel_btn1" data-dismiss="modal">
              <img src="{{ asset('admin/images/close.svg') }}" alt="">
          </button>
        </div>
        <!-- Modal body -->
        <div class="modal-body">
          @if($idap_disc_tran_exist['html'] != '')
          <p>Following item(s) have Duplicate information:</p>
          <div id="pendingMessage">
            <ul>
              <?php echo $idap_disc_tran_exist['html']; ?>
            </ul>
          </div>
          @endif
          <br>
          @if($idap_disc_tran_exist['html2'] != '')
          <p>Following item(s) have missing datas:</p>
          <div id="pendingMessage">
            <ul>
              <?php echo $idap_disc_tran_exist['html2']; ?>
            </ul>
          </div>
          @endif
        </div>
        <p id="pendingMessage">Your send to Quotation failed. Please connect with admin</p>
      </div>
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
            @if(isset($data[0]))
                <p class="border-0">Quotation is send to all POC's for {{$data[0]->institution_name}}, successfully.</p>
            @else
                Data not found
            @endif

            <a class="btn orange-btn big-btn cancel_btn">Go to <?php if(!empty($vq_id_Session_data)): echo 'Pay Mode'; elseif(!empty($edit_paymode_vq_id_listing)): echo 'VQ List'; else: echo 'VQ List'; endif;?> </a>
        </div>
      </div>
    </div>
</div>

<input type="hidden" id="level_progress_session" value="">
<input type="hidden" id="approve_vq_response" value="">
<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script>

    $('#approve_final').click(function(e){
        e.preventDefault();
        $('#loader1').show();
      var disc = $("#entervalue").val();
        var settings = {
          "url": "/initiator/approve_vq",
          "method": "GET",
          "timeout": 0,
          "dataType": 'json',
          "headers": {
            "Accept-Language": "application/json",
          },
          "data": {
            "_token": "{{ csrf_token() }}",
            "vq_id": "{{ app('request')->route('id') }}",
          }
        };
        $.ajax(settings).done(function (response) {
          console.log(response.data);
          console.log(response.level_progress);
          
          if(response.data.length >= 0){ // Added by arun 25112024
            var arr_first_element = response.data.length - response.data.length;
            $('#approve_vq_response').val(response.data[arr_first_element]);
            // console.log(response.data[arr_first_element]);
          }
          if(response.data != ''){
            $('#level_progress_session').val(response.level_progress);
            if(response.level_progress == 'Paymode')
            {
              $('.big-btn').text('Go to Pay Mode');
            }
            else if(response.level_progress == 'EditPaymode')
            {
              // $('.big-btn').text('Go to Edit Pay Mode'); // hide by arunchandru 05122024
              $('.big-btn').text('Go to VQ List'); // added by arunchandru 05122024
            }
          }
          else{
            // $('#level_progress_session').val();
            $('.big-btn').text('Go to VQ List');
          }
          
          $('#loader1').hide(); 
          $('#submited').modal('show');

          // console.log(response); /** hide by arun 02122024 */
          // $('#submited').modal('show'); /** hide by arun 02122024 */
          //window.location.href ='{{route("initiator_listing")}}';
        });
    });
	
    $('.cancel_btn').click(function(){
      var level = $('#level_progress_session').val();
      var vq_id = $('#approve_vq_response').val();
      //window.history.back(); commented on 09052024 
      if(level == 'Paymode'){
        if(vq_id != '') // Added by arun 25112024
        {
          window.location.href ="{{ route('paymentMode', ':id') }}".replace(':id', vq_id);
        }else{
          window.location.href ='{{route("initiator_listing")}}';
        }
      }else if(level == 'EditPaymode'){
        if(vq_id != '') // Added by arun 28112024
        {
          // window.location.href ="{{ route('editPaymentMode', ':id') }}".replace(':id', vq_id); // hide by arunchandru 05122024
          window.location.href ='{{route("initiator_listing")}}'; // added by arunchandru 05122024
        }else{
          window.location.href ='{{route("initiator_listing")}}';
        }
      }else{
        window.location.href ='{{route("initiator_listing")}}';
      }
      //window.history.back(); commented on 09052024 
      // window.location.href ='{{route("initiator_listing")}}'; // hide by Arunchandru 02122024
    });

    $('.cancel_btn1').click(function(){
      // if(confirm('Do you want close the popup?')){
        $('#idap_disc_tran_exist_modal').css('display', 'none');
      // }
    });

      /****************************************
       *       Basic Table                   *
       ****************************************/
      $("#zero_config").DataTable(

        {  language: {    'paginate': {      'previous': "<img src='{{ asset('admin/images/left.svg') }}' class='left-block' alt=''> <img src='{{ asset('admin/images/left1.svg') }}' class='disbpr' alt=''>",      'next': "<img src='{{ asset('admin/images/right.svg') }}' class='neblk' alt=''> <img src='{{ asset('admin/images/right1.svg') }}' class='disbn' alt=''>"    }  }}

        );

    </script>
    <style type="text/css">
        .copy-center{
            position:inherit !important;margin: 20px 0;
        }
    </style>
@endsection