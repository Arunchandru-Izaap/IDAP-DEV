@extends('layouts.frontend.app')
@section('content')
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
                    <div class="row">
                            <div class="col-md-8 d-flex send-quotation">
                                <h3 class="pl-15">@if(isset($data[0]))
                                        {{ $data[0]->institution_name }}
                                    @else
                                        Data not found
                                    @endif </h3>
                            <div class="cancel-btn ml-auto">
                            @if(isset($data[0]))
                                <a href="" id="approve_final" class="orange-btn">
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

          <a class="btn orange-btn big-btn cancel_btn">Go to VQ Details</a>
        </div>
      </div>
    </div>
</div>


<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script>

    $('#approve_final').click(function(e){
        e.preventDefault();
      var disc = $("#entervalue").val();
        var settings = {
          "url": "/initiator/approve_vq",
          "method": "GET",
          "timeout": 0,
          "headers": {
            "Accept-Language": "application/json",
          },
          "data": {
            "_token": "{{ csrf_token() }}",
            "vq_id": "{{ app('request')->route('id') }}",
          }
        };
        $.ajax(settings).done(function (response) {
          console.log(response);
          $('#submited').modal('show');
          //window.location.href ='{{route("initiator_listing")}}';
        });
    });
	
	$('.cancel_btn').click(function(){
		window.history.back();
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