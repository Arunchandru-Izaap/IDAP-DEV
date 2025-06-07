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
                                    <p>Distribution</p>
                                </li>

                                <li>
                                    <img src="../admin/images/Sun_Pharma_logo.png">
                                </li>
                            </ul>
                        </div>
                        
                    </div>

                </nav>
                <ul class="bradecram-menu">
                    <li><a href="{{route('distribution_dashboard')}}">
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
                            <a href="{{route('vq-export')}}" id="price-sheet" class="btn-grey" style="margin-right: 4rem;">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Export</p>
                            </a>
                        </div>
                        <div class="col" id="grid_wrapper">
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

            serverSide: true,
            processing:true,
            "pageLength": 50,
            ajax: {
                url: '/distribution/vq_listing_distribution_ajax',
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
                        var element = `<a href="/distribution/listing/${row.id}" data-title="View Details">
                            <img src="../admin/images/down.svg" alt="">
                        </a>
                        <a href="/distribution/activity/${row.id}" data-title="View Activity Tracker">
                            <img src="../admin/images/clock.png" alt="">
                        </a>`
                        if(row.vq_status == 1) element += `<a href="/distribution/newPriceSheet/${row.id}" data-title="Price Sheet">
                            <img src="../admin/images/Price_list.svg" alt="">
                        </a>`
                        return element;
                    }
                },
            ],
            "order": [],
            'language': {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },

            scrollX: true,
            autoWidth: false,   
            initComplete: function () {
            this.api()
                .columns()
                .every(function (index) {
                    
                    let column = this;
                    let title = column.footer().textContent;
                    
                    if(title != ""){
                        // Create input element
                        let input = document.createElement('input');
                        input.placeholder = title;
                        if (index === 2 || index === 1) {
                            input.style.width = '100px';
                        }
                        column.footer().replaceChildren(input);
                        
                        // Event listener for user input
                        input.addEventListener('keyup', () => {
                            if (column.search() !== this.value) {
                                column.search(input.value).draw();
                            }
                        });
                        $('.dataTables_filter input').addClass('common_filter');
                    }
                });
            }

        });
        var storedValue = localStorage.getItem('distributionListSearch');
        if (storedValue !== null && storedValue !='') {
            $('.dataTables_filter input').val(storedValue);

            $('.dataTables_filter input').trigger('input')
        }
        $('body').on('input', '.common_filter', function(){
            if($(this).val()!='')
            {
                localStorage.setItem('distributionListSearch', $(this).val());
            }
            else
            {
                localStorage.removeItem('distributionListSearch');
            }
        })
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
        "url": "{{ route('filter-latest-report-distribution') }}",
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