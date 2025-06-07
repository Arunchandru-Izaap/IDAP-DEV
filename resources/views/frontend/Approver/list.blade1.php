@extends('layouts.frontend.app')
@section('content')
<style>
  tfoot input {
      width: 100%;
      padding: 3px;
      box-sizing: border-box;
  }
</style>
<div id="page-content-wrapper">
                <!-- Top navigation-->
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                            <ul class="navbar-nav dashboard-nav">
                                <li class="nav-item active"><a class="nav-link" href="{{route('approver_dashboard')}}"> <img src="../admin/images/back.svg">VQ Request Listing</a></li>
                            </ul>

                            <ul class="d-flex ml-auto user-name">
                                <li>
                                    <h3>{{Session::get('emp_name')}}</h3>
                                    <p>{{Session::get('division_name')}}</p>
                                </li>

                                <li>
                                    <img src="../admin/images/Sun_Pharma_logo.png">
                                </li>
                            </ul>
                        </div>
                        
                    </div>

                </nav>
                <ul class="bradecram-menu">
                    <li><a href="{{route('approver_dashboard')}}">
                        Home
                    </a></li>
                    <li class="active">
                      <a href="">
                        VQ Request Listing 
                      </a>
                    </li>
                </ul>
                <!-- Page content-->
                    <?php
                        $flag = '';
                        $x = 1;
                    
	                    foreach($data as $item){
		                  if(($item->status_vq=='1' || $item->current_level > preg_replace('/[^0-9.]+/', '', Session::get("level")) && $item->status_vq!='3')){
		                    $flag = 'disabled';
	                      }elseif($item->status_vq=='0' && $item->current_level == preg_replace('/[^0-9.]+/', '', Session::get("level"))){
		                    $flag = '';
		                    BREAK;
	                      }elseif($item->is_deleted=='1'){
		                     $flag = 'disabled'; 
	                      }
	                    }
                    ?>

                <div class="container-fluid">
                    <!-- <h1 class="mt-4">Simple Sidebar</h1> -->
                    <div class="row">
                    <div class="col-md-6"></div>
                    <div class="col-md-2">
                      <a href="{{route('history-report')}}" id="price-sheet" class="btn-grey">
                              <img src="{{ asset('admin/images/download.svg') }}">
                              <p>Generate Historical Report</p>
                      </a>
                      @if ($historical_file_link)
                      <a href="{{ url('/') }}/{{$historical_file_link}}" id="price-sheet" class="btn-grey">
                              <img src="{{ asset('admin/images/download.svg') }}">
                              <p>Historical Report Download</p>
                      </a>     
                      @endif
                    </div>
                    <div class="col-md-2">
                      <a href="{{route('latest-report')}}" id="price-sheet" class="btn-grey">
                              <img src="{{ asset('admin/images/download.svg') }}">
                              <p>Generate Latest Report</p>
                      </a> 
                      @if ($latest_file_link)
                      <a href="{{ url('/') }}/{{$latest_file_link}}" id="price-sheet" class="btn-grey">
                              <img src="{{ asset('admin/images/download.svg') }}">
                              <p>Latest Report Download</p>
                      </a>     
                      @endif
                    </div>
                        <div class="col-md-2">
                            <a href="{{route('vq-export-approver')}}" id="price-sheet" class="btn-grey">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Export</p>
                            </a>
                        </div>
                    <div class="col-md-12 d-flex">
                        <div class="cancel-btn ml-auto">
                            <button id="approveall" class="orange-btn"  {{$flag}}>
                              APPROVE ALL AND SUBMIT
                            </button>
                        </div>
                      </div>
                     
                        <div class="col">
                            <div class="actions-dashboard table-ct">

                              <table class="table VQ Request Listing vq-request-listing-tb nowrap" id="zero_config">
                                <thead>
                                  <tr>
                                    <!-- <th>ID</th> -->
                                    <th>Institution Name</th>
                                    <th>Institution Code</th>
                                    <th>City</th>
                                    <th>State</th>
                                    <th>Zone</th>
                                    <th>Region</th>
                                    <th>Revision no</th>
                                    <th>CFA Code</th>
                                    <th>SAP Code</th>
                                    <th>Contract Start Date</th>
                                    <th>Contract End Date</th>
                                    <th>VQ Year</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                  </tr>
                                </thead>
                                <tbody>
                                    @if(count($data)>0)
                                    @foreach($data as $item)
                                    <tr>
                                      <!-- <td>{{$item->id}} {{$item->current_level}}</td> -->
                                        <td>{{$item->hospital_name}}</td>
                                        <td>{{$item->institution_id}}</td>
                                        <td>{{$item->city}}</td>
                                        <td>{{$item->state_name}}</td>
                                        <td>{{$item->institution_zone}}</td>
                                        <td>{{$item->institution_region}}</td>
                                        <td>{{$item->revision_count}}</td>
                                        <td>{{$item->cfa_code ? $item->cfa_code : "-"}}</td>
                                        <td>{{$item->sap_code || $item->sap_code != "" ? $item->sap_code : "-"}}</td>
                                        <td>{{date('d M Y',strtotime($item->contract_start_date))}}</td>
                                        <td>{{date('d M Y',strtotime($item->contract_end_date))}}</td>
                                        <td>{{$item->year }}</td>
                                        @if($item->status_vq=='0' && $item->current_level == preg_replace('/[^0-9.]+/', '', Session::get("level")))
                                        <td>
                                            <p class="pending">
                                                Pending
                                            </p>
                                        </td>
                                        @elseif(($item->status_vq=='1' || $item->current_level > preg_replace('/[^0-9.]+/', '', Session::get("level")) && $item->status_vq!='3'))
                                        <td>
                                            <p class="approved">
                                                Approved
                                            </p>
                                        </td>
                                        @else
                                        <td>
                                            <p class="cancelled">
                                                Cancelled 
                                                @if(preg_replace('/[^0-9.]+/', '', Session::get("level")) != $item->deleted_by)
                                                  @if($item->deleted_by == 1)
                                                    By RSM
                                                  @elseif($item->deleted_by == 2)
                                                    By ZSM
                                                  @elseif($item->deleted_by == 3)
                                                    By NSM
                                                  @elseif($item->deleted_by == 4)
                                                    By SBU
                                                  @elseif($item->deleted_by == 5)
                                                    By Semi Cluster
                                                  @elseif($item->deleted_by == 6)
                                                    Cluster
                                                  @endif
                                                @endif
                                            </p>
                                        </td>
                                        @endif

                                        <td><!-- <a href="{{url('initiator',$item->id)}}">view details</a> -->
                                            <a href="{{url('approver/listing',$item->id)}}" data-title="View Details">
                                                <img src="../admin/images/down.svg" alt="">
                                            </a>
                                            <a href="{{url('approver/activity',$item->id)}}" data-title="View Activity Tracker">
                                                <img src="../admin/images/clock.png" alt="">
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @else
                                    <td valign="top" class="dataTables_empty">No data available</td>
                                    @endif
                                </tbody>
                                @if(count($data)>0)
                                <tfoot>
                                    <tr>
                                      <th>Institution Name</th>
                                      <th>Code</th>
                                      <th>City</th>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                      <th></th>
                                    </tr>
                                </tfoot>
                                @endif
                              </table>


                            </div>
                        </div><!-- col close -->


<!-- submited popup -->
<div class="modal show" id="submited">
    <div class="modal-dialog modal-dialog-centered model-pop-ct">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header border-0">
            <div class="tich-logo">
            <img src="{{ asset('admin/images/check-double.svg') }}" alt="">
            </div>
          <button type="button" class="close" data-dismiss="modal">
              <img src="{{ asset('admin/images/close.svg') }}" alt="">
          </button>
        </div>
        <!-- Modal body -->
        <div class="modal-body">
          <p class="border-0">All pending VQ Requests are approved and submitted successfully</p>

          <a class="btn orange-btn big-btn close-button">OK</a>
        </div>
      </div>
    </div>
</div>



<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script>
let flag = 'no';
$( "#approveall" ).on( "click", function() {
	let text = "Are you sure, you want to approve all VQ's?";
	if (confirm(text) == true) {	
    	flag = 'yes';
		var settings = {
          "url": "{{ route('bulk_approve')}}",
          "method": "POST",
          "timeout": 0,
          "headers": {
            "Accept-Language": "application/json",
          },
          "data": {
            "_token": "{{ csrf_token() }}",
          }
        };
        $.ajax(settings).done(function (response) {
          // alert('hello');
          console.log(response);
          // $('#approveall').hide();
          $('#submited').modal('show');
          flag = 'no';
        });
  	}
	
});


	$('.close-button').click(function(){
		$('#submited').modal('hide');
		location.reload(true);
	});

      /****************************************
       *       Basic Table                   *
       ****************************************/
      $("#zero_config").DataTable(

        {  
          language: {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },
          scrollX: true,
          initComplete: function () {
            this.api()
                .columns()
                .every(function () {
                    
                    let column = this;
                    let title = column.footer().textContent;
                    
                    if(title != ""){
                        // Create input element
                        let input = document.createElement('input');
                        input.placeholder = title;
                        column.footer().replaceChildren(input);
        
                        // Event listener for user input
                        input.addEventListener('keyup', () => {
                            if (column.search() !== this.value) {
                                column.search(input.value).draw();
                            }
                        });
                    }
                });
            }
        }
      );
    </script>
    <style type="text/css">
        .copy-center{
            position:inherit !important;margin: 20px 0;
        }
        div.dataTables_wrapper {
            max-width: 1357px;
            width: 100%;
            margin: 0 auto;
        }
    </style>
@endsection