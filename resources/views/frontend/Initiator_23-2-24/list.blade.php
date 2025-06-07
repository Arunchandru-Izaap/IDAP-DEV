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
                                <li class="nav-item active"><a class="nav-link" href="{{route('initiator_dashboard')}}"> <img src="../admin/images/back.svg">VQ Request Listing</a></li>
                            </ul>

                            <ul class="d-flex ml-auto user-name">
                                <li>
                                    <h3>{{Session::get('emp_name')}}</h3>
                                    <p>Initiator</p>
                                </li>

                                <li>
                                    <img src="../admin/images/Sun_Pharma_logo.png">
                                </li>
                            </ul>
                        </div>
                        
                    </div>

                </nav>
                <ul class="bradecram-menu">
                    <li><a href="{{route('initiator_dashboard')}}">
                        Home
                    </a></li>
                    <li class="active">
                      <a href="">
                        VQ Request Listing
                      </a>
                    </li>
                </ul>
                <!-- Page content-->
                <div class="container-fluid">
                @if(Session::has('status'))
                <div class="alert alert-success mt-3" role="alert" >
                    {{Session::get('message')}}
                </div>
                @elseif(Session::has('errors'))
                <div class="alert alert-danger mt-3" role="alert" >
                    {{$errors->first('message')}}
                </div>
                @endif
                
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
                            <a href="{{route('vq-export')}}" id="price-sheet" class="btn-grey">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Export</p>
                            </a>
                        </div>
                        <div class="col">
                            <div class="actions-dashboard table-ct">
                              <table class="table VQ Request Listing vq-request-listing-tb nowrap" id="zero_config">
                                <thead>
                                  <tr>
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
                                    <th>VQ Status</th>
                                    <th>Quotation Status</th>
                                    <th>Actions</th>
                                  </tr>
                                </thead>
                                <tbody>
                                    @if(count($data)>0)
                                    @foreach($data as $item)
                                    <tr>
                                        <!-- <td>{{$item->id}}</td> -->
                                        <td>{{$item->hospital_name}}</td>
                                        <td>{{$item->institution_id}}</td>
                                        <td>{{$item->city}}</td>
                                        <td>{{$item->state_name}}</td>
                                        <td>{{$item->institution_zone}}</td>
                                        <td>{{$item->institution_region}}</td>
                                        <td>{{$item->revision_count}}</td>
                                        <td>{{$item->cfa_code ? $item->cfa_code : "-"}}</td>
                                        <td>{{$item->sap_code || $item->sap_code != "" ? $item->sap_code : "-"}}</td>
                                        <td data-sort="YYYYMMDD"><span style="display: none">{{date('Ymd',strtotime($item->contract_start_date))}}</span>{{date('d M Y',strtotime($item->contract_start_date))}}</td>
                                        <td>{{date('d M Y',strtotime($item->contract_end_date))}}</td>
                                        <td>{{$item->year}}</td>

                                        <td><!-- {{$item->contract_end_date }} -->
                                            
                                            @if($item->current_level!='7')
                                            	<p class="pending">
                                                Pending with
                                                @if($item->current_level == 1)
                                                  RSM
                                                @elseif($item->current_level == 2)
                                                  ZSM
                                                @elseif($item->current_level == 3)
                                                  NSM
                                                @elseif($item->current_level == 4)
                                                  SBU
                                                @elseif($item->current_level == 5)
                                                  Semi Cluster
                                                @elseif($item->current_level == 6)
                                                  Cluster
                                                @endif
                                            	</p>	
                                            @else 
                                            	<p class="approved">Approved</p>
                                            @endif
                                        </td>
                            
                                        <td><!-- {{$item->vq_status}} -->
                                             
                                             @if($item->vq_status=='0')
                                             	<p class="pending">
                                                	Pending
                                             	</p>	
                                            @else
                                            	<p class="approved">
                                                	Sent
                                            	</p>	
                                            @endif
                                            </p>
                                        </td>

                                        <td><!-- <a href="{{url('initiator',$item->id)}}">view details</a> -->
                                            <a href="{{url('initiator/listing',$item->id)}}" data-title="View Details">
                                                <img src="../admin/images/down.svg" alt="">
                                            </a>
                                            <a href="{{url('initiator/activity',$item->id)}}" data-title="View Activity Tracker">
                                                <img src="../admin/images/clock.png" alt="">
                                            </a>
                                            <a data-title="Delete VQ" onclick="deleteVQ(<?= $item->id?>)"><img src="../admin/images/delete.png" alt=""></a>
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
                                        <th></th>
                                    </tr>
                                </tfoot>
                                @endif
                              </table>


                            </div>
                        </div><!-- col close -->
                    



<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script>
      /****************************************
       *       Basic Table                   *
       ****************************************/
      $("#zero_config").DataTable(

        {  
	        'columnDefs': [
                /*
                    { targets: [0, 1], visible: true},
                    { targets: '_all', visible: false }
                */
		        { "bSortable": false, "aTargets": [ 3,4 ] }, 
                { "bSearchable": false, "aTargets": [3,4 ] }
		    ],
            scrollX: true,
		    
	        'language': {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },

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
            /*
            {"aoColumns": [
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        null,
                        null
                    
                    
                    ]}
            */

        );

        function deleteVQ(id){

            let confirmDelete = confirm("Do you want to delete this counter ?")
            
            if(confirmDelete){
                // var settings = {
                //     "url": "/initiator/deleteVQ/"+id,
                //     "method": "GET",
                //     "timeout": 0,
                //     "headers": {
                //         "Accept-Language": "application/json",
                //     },
                // };

                // $.ajax(settings).done(function(response){

                // })
                location.replace("/initiator/deleteVQ/"+id)
            }
            
        }

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