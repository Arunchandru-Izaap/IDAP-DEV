@extends('layouts.frontend.app')
@section('content')
<style>
    tfoot input {
        width: 100%;
        padding: 3px;
        box-sizing: border-box;
    }
    .search-ct
    {
        display: none !important;
    }
    .dataTables_scrollBody
    {
        height: 20rem !important;
    }
    .table-responsive {
    overflow-x: auto;
    white-space: nowrap;
}

.dataTables_wrapper .dataTables_scroll {
    width: 100%; /* Ensures the table uses the full width */
}

td {
    white-space: nowrap; /* Prevent wrapping inside table cells */
}
#grid_wrapper .col-sm-12.col-md-5 {
    width: 37% !important;
    max-width: 37%;
}
body {
    overflow-x: hidden; /* Prevent the entire page from scrolling */
}
#grid_wrapper .table-responsive {
    overflow-x: hidden !important;
    white-space: nowrap;
}
th.sorting:last-child {
    width: 190px !important;
}
td:last-child {
    width: 190px !important;
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
                                    <p>Poc</p>
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
                    <!-- <h1 class="mt-4">Simple Sidebar</h1> -->
                    @if(Session::has('status'))
                    <div class="alert alert-success mt-3" role="alert" >
                        {{Session::get('message')}}
                    </div>
                    @elseif(Session::has('errors'))
                    <div class="alert alert-danger mt-3" role="alert" >
                        {{$errors->first('message')}}
                    </div>
                    @endif
                    <div id="reportMessage"></div>
                    <div class="row">
                    <div class="col-md-6"></div>
                    <div class="col-md-2">
                        <a href="{{route('history-report')}}" id="price-sheet" class="btn-grey d-none">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Generate Historical Report</p>
                        </a>
                        @if ($historical_file_link)
                        <a href="{{ url('/') }}/{{$historical_file_link}}" id="price-sheet" class="btn-grey d-none">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Historical Report Download</p>
                        </a>     
                        @endif 
                    </div>
                    <div class="col-md-2">
                        <!-- <a href="{{route('latest-report')}}" id="price-sheet" class="btn-grey">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Generate Latest Report</p>
                        </a> --> 
                        <a href="#" id="price-sheet-latest" class="btn-grey">
                              <img src="{{ asset('admin/images/download.svg') }}">
                              <p>Generate Latest Report</p>
                        </a> 
                        @if ($latest_file_link)
                        <a href="{{ url('/') }}/{{$latest_file_link}}" id="price-sheet" class="btn-grey">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <!-- <p>Latest Report Download</p> -->
                                <p>Download Latest Report as on ({{$latest_file_creation_date}})</p>
                        </a>     
                        @endif
                    </div>
                    
                        <div class="col-md-2">
                            <a href="{{route('vq-export')}}" id="price-sheet" class="btn-grey" style="margin-right: 4rem; ">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Export</p>
                            </a>
                        </div>
                        <div class="col"  id="grid_wrapper">
                            <div class="table-responsive actions-dashboard table-ct">
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
                                            <a href="{{url('poc/listing',$item->id)}}" data-title="View Details">
                                                <img src="../admin/images/down.svg" alt="">
                                            </a>
                                            <a href="{{url('poc/activity',$item->id)}}" data-title="View Activity Tracker">
                                                <img src="../admin/images/clock.png" alt="">
                                            </a>
                                            @if($item->vq_status=='1')
                                            <a href="{{url('poc/newPriceSheet',$item->id)}}" data-title="View Activity Tracker">
                                                <img src="../admin/images/Price_list.svg" alt="">
                                            </a>
                                            @endif
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


<div class="modal show" id="filter_latest">
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
          <p class="border-0">Select division to filter</p>
          <select id="division_name_latest" class="js-example-basic-multiple" name="" multiple="multiple">
            <option value="all">All</option>
            @foreach($division_name as $div_id => $div_name)
                <option value="{{ $div_id }}">{{ $div_name }}</option>
            @endforeach
          </select>
          <a class="btn orange-btn big-btn close-button-filter_latest" data-dismiss="modal">Generate latest report</a>
        </div>
      </div>
    </div>
</div>
<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script src="{{asset('frontend/js/select2.js')}}"></script>
<script>
    $('.js-example-basic-multiple').select2({ width: '100%' });
    $('#division_name_latest').val('all').change();
      /****************************************
       *       Basic Table                   *
       ****************************************/
      $("#zero_config").DataTable(

        {  
            "pageLength": 50,
	        'columnDefs': [
                /*
                    { targets: [0, 1], visible: true},
                    { targets: '_all', visible: false }
                */
		        { "bSortable": false, "aTargets": [ 3,4 ] }, 
                { "bSearchable": false, "aTargets": [3,4 ] }
		    ],
		    
	        'language': {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },
            scrollX: true,
            autoWidth: false, 
            initComplete: function () {
                this.api()
                    .columns()
                    .every(function () {
                        
                        let column = this;
                        let footer = column.footer();
                        if (footer && footer.textContent != "") {
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
                                $('.dataTables_filter input').addClass('common_filter');
                            }
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
        var storedValue = localStorage.getItem('pocListSearch');
        if (storedValue !== null && storedValue !='') {
            $('.dataTables_filter input').val(storedValue);

            $('.dataTables_filter input').trigger('input')
        }
        $('body').on('input', '.common_filter', function(){
            if($(this).val()!='')
            {
                localStorage.setItem('pocListSearch', $(this).val());
            }
            else
            {
                localStorage.removeItem('pocListSearch');
            }
        })
        $('#price-sheet-latest').on('click', function(){
          $('#filter_latest').modal({backdrop: 'static', keyboard: false},'show');
        })
        $('.js-example-basic-multiple').on('select2:select', function(e) {
          var selected = $(this).val();
          if (selected.includes('all')) {
            $(this).val('all').trigger('change'); // Select only 'All' option
          }
        });
        $('.js-example-basic-multiple').on('select2:unselect', function(e) {
          var selected = $(this).val();
          if (!selected) {
            $(this).val(null).trigger('change'); // Clear selection if all options are unselected
          }
        });
        $('.close-button-filter_latest').on('click', function(){
          var selectedDivision = $('#division_name_latest').val()
          var settings = {
            "url": "{{ route('filter-latest-report-poc') }}",
            "method": "POST",
            "timeout": 0,
            "headers": {
                "Accept-Language": "application/json",
            },
            "data": {
                "_token": "{{ csrf_token() }}",
                "div_id": selectedDivision
            }
            };

            $.ajax(settings).done(function (response) {
                $('#reportMessage').html(`<div class="alert alert-success mt-3" role="alert" >
                    ${response.message}
                </div>`)
                //console.log(response);
            }).fail(function (jqXHR, textStatus) {
                $('#reportMessage').html(`<div class="alert alert-danger mt-3" role="alert" >
                    Please try again later
                </div>`)
            });
        })
    </script>
    <style type="text/css">
        .copy-center{
            position:inherit !important;margin: 20px 0;
        }
    </style>
@endsection