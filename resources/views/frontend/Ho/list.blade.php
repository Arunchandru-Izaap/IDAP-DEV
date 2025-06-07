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
                                    <p>Ho</p>
                                </li>

                                <li>
                                    <img src="../admin/images/Sun_Pharma_logo.png">
                                </li>
                            </ul>
                        </div>
                        
                    </div>

                </nav>
                <ul class="bradecram-menu">
                    <li><a href="{{route('ho_dashboard')}}">
                        Home
                    </a></li>
                    <li class="active">
                      <a href="">
                        VQ Request Listing
                      </a>
                    </li>
                </ul>
                @if (session('status'))
                <div class="alert alert-success mt-3" role="alert">
                    {{ session('message') }}
                </div>
                @endif
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
                        <a href="{{route('latest-report')}}" id="price-sheet" class="btn-grey">
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
                                    
                                </tbody>
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
                                        <th></th>
                                    </tr>
                                </tfoot>
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

            serverSide: true,
            processing:true,
            ajax: {
                url: '/ho/vq_listing_ho_ajax',
                type: 'GET',
            },
            columns:
            [
                { data: 'hospital_name' },
                { data: 'institution_id' },
                { data: 'city' },
                { data: 'state_name' },
                { data: 'institution_zone' },
                { data: 'institution_region' },
                { data: 'revision_count' },
                { 
                    data: function(row) {
                        return row.cfa_code ? row.cfa_code : "-";
                    },
                },
                { 
                    data: function(row) {
                        return row.sap_code ? row.sap_code : "-";
                    },
                },
                { 
                    data: function(row) {
                        var formattedDate = format_date(row.contract_start_date);
                        return formattedDate;
                    }
                },
                { 
                    data: function(row) {
                        var formattedDate = format_date(row.contract_end_date);
                        return formattedDate;
                    }
                },
                { data: 'year' },
                { data: function(row) {
                    if(row.current_level != '7')
                    {
                        var status = 'Pending with';
                        if(row.current_level == 1)
                          status += 'RSM';
                        else if(row.current_level == 2)
                          status += 'ZSM';
                        else if(row.current_level == 3)
                          status += 'NSM';
                        else if(row.current_level == 4)
                          status += 'SBU';
                        else if(row.current_level == 5)
                          status += 'Semi Cluster';
                        else if(row.current_level == 6)
                          status += 'Cluster';
                      return '<p class="pending">'+status+'</p>';
                    }
                    else
                    {
                      return '<p class="approved">Approved</p>';
                    }
                  }
                },
                { data: function(row) {
                    if(row.vq_status == '0')
                    {
                      return '<p class="pending">Pending</p>';
                    }
                    else
                    {
                      return '<p class="approved">Sent</p>';
                    }
                  }
                },
                { 
                    data: function(row) {
                        var element = `<a href="/ho/listing/${row.id}" data-title="View Details">
                            <img src="../admin/images/down.svg" alt="">
                        </a>
                        <a href="/ho/activity/${row.id}" data-title="View Activity Tracker">
                            <img src="../admin/images/clock.png" alt="">
                        </a>`
                        if(row.vq_status == 1) element += `<a href="/ho/newPriceSheet/${row.id}" data-title="Price Sheet">
                            <img src="../admin/images/Price_list.svg" alt="">
                        </a>`
                        return element;
                    }
                },
            ],
            "order": [],
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

	    });
        function format_date(dateString)
        {
            var date = new Date(dateString);

           // Define an array to map month numbers to short month names
            var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            // Format the date manually
            var day = date.getDate();
            var monthIndex = date.getMonth();
            var year = date.getFullYear();

            var formattedDate = day + ' ' + months[monthIndex] + ' ' + year;
            return formattedDate;
        }
    </script>
    <style type="text/css">
        .copy-center{
            position:inherit !important;margin: 20px 0;
        }
    </style>
@endsection