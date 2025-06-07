@extends('layouts.frontend.app')
@section('content')
<style>
    tfoot input {
        width: 100%;
        padding: 3px;
        box-sizing: border-box;
    }
    tfoot tr input:nth-child(3) {
       width: 500px !important;
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
.table-ct .dataTables_wrapper .table tbody tr td:last-child a
{
    margin-right: 2px;
}
.table-ct .table tbody tr td:last-child a {
    margin-right: 2px;
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
                @elseif(Session::has('ignored_institution'))
                <div class="modal show" id="parent_institution_selection">
                    <div class="modal-dialog modal-dialog-centered model-pop-ct">
                      <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header border-0">
                            <div class="tich-logo">
                            <img src="{{ asset('admin/images/check-double.svg') }}" alt="">
                            </div>
                          <button type="button" class="close" data-dismiss="modal" id="child_modal_close">
                              <img src="{{ asset('admin/images/close.svg') }}" alt="">
                          </button>
                        </div>
                        <!-- Modal body -->
                        <div class="modal-body">
                          <p class="border-0">Select the Child instiution to make as Parent Institution for {{Session::get('parent')}}</p>
                          <select id="parent_insititution_chain" class="js-example-basic-single" name="">
                            <option value="">Select Institution</option>
                            @foreach(Session::get('child_institutions') as $single_child_institution)
                                <option value="{{ $single_child_institution['institution_id'] }}">
                                    {{ $single_child_institution['institution_name'] }}-{{ $single_child_institution['institution_id'] }}
                                </option>
                            @endforeach
                          </select>
                          <input type="hidden" name="" value="{{Session::get('selected_vq_delete')}}" id="selected_vq_delete">
                          <a class="btn orange-btn big-btn" id="make_parent_btn">Submit</a>
                        </div>
                      </div>
                    </div>
                </div>
                <script type="text/javascript">$('#parent_institution_selection').modal({backdrop: 'static', keyboard: false},'show');</script>
                @elseif($errors->any())
                    <div class="alert alert-danger mt-3" role="alert" >
                        {{$errors->first('message')}}
                    </div>
                @endif
                <div id="reportMessage"></div>
                    <!-- <h1 class="mt-4">Simple Sidebar</h1> -->
                    <div class="row">
                    <div class="col-md-3">
                        <select id="financial-year-dropdown-report" class="form-control">
                            <option value="">Select Financial Year for Report</option>
                            @foreach($financialYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <a href="#" id="generate_financial_history_report" class="btn-grey d-none">
                            <img src="{{ asset('admin/images/download.svg') }}">
                            <p>Generate Financial Year Historical Report</p>
                        </a>
                        <a href="#" id="download_financial_history_report" class="btn-grey d-none">
                            <img src="{{ asset('admin/images/download.svg') }}">
                            <p>Download Financial Year Historical Report as on <span id="fin_report_date"></span></p>
                        </a>
                    </div>
                    <div class="col-md-2">
                        <!-- <a href="{{route('history-report')}}" id="price-sheet" class="btn-grey">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Generate Historical Report</p>
                        </a> -->
                        <a href="#" id="historical-report" class="btn-grey">
                              <img src="{{ asset('admin/images/download.svg') }}">
                              <p>Generate Historical Report</p>
                        </a> 
                        @if ($historical_file_link)
                        <a href="{{ url('/') }}/{{$historical_file_link}}" id="price-sheet" class="btn-grey">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Historical Report Download as on ({{$historical_file_creation_date}})</p>
                        </a>     
                        @endif
                    </div>
                    <div class="col-md-2">
                        <!-- <a href="{{route('latest-report')}}" id="price-sheet" class="btn-grey">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Generate Latest Report</p>
                        </a>  -->
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
                            <a href="{{route('vq-export')}}" id="price-sheet" class="btn-grey">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Export</p>
                            </a>
                        </div>
                        <div id='loader1' style='display: none;'>
                              
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
                                    <th style="width: 110px !important;">Actions</th>
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
                    

<!-- filter for report popup -->
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
<div class="modal show" id="filter_historical">
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
          <select id="division_name_historical" class="js-example-basic-multiple" name="" multiple="multiple">
            <option value="all">All</option>
            @foreach($division_name as $div_id => $div_name)
                <option value="{{ $div_id }}">{{ $div_name }}</option>
            @endforeach
          </select>
          <a class="btn orange-btn big-btn close-button-filter_historical" data-dismiss="modal">Generate historical report</a>
        </div>
      </div>
    </div>
</div>
<div class="modal show" id="filter_financial">
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
          <select id="division_name_financial" class="js-example-basic-multiple" name="" multiple="multiple">
            <option value="all">All</option>
            @foreach($division_name as $div_id => $div_name)
                <option value="{{ $div_id }}">{{ $div_name }}</option>
            @endforeach
          </select>
          <a class="btn orange-btn big-btn close-button-filter_financial" data-dismiss="modal">Generate financial report</a>
        </div>
      </div>
    </div>
</div>

<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script src="{{asset('frontend/js/select2.js')}}"></script>
<!-- <script  src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="//cdn.datatables.net/2.0.1/js/dataTables.min.js"></script> -->
<script type="text/javascript">
    $('.js-example-basic-multiple').select2({ width: '100%' });
    $('.js-example-basic-single').select2({ width: '100%' });
    $('#division_name_latest').val('all').change();
    $('#division_name_financial').val('all').change();
    $('#division_name_historical').val('all').change();
    $('#zero_config').DataTable({
        processing: true,
        "pageLength": 50,
        order:[],
        columns: [
            { data: 'hospital_name' },
            { data: 'institution_id' },
            { data: 'city' },
            { data: 'state_name' },
            { data: 'institution_zone' },
            { data: 'institution_region' },
            { data: 'revision_count' },
            { data: 'cfa_code'},
            { data: 'sap_code'},
            { data: 'contract_start_date'},
            { data: 'contract_end_date'},
            { data: 'year' },
            { 
                data: function(row) {
                    var current_level = row.current_level;
                    if(current_level == 'Approved') var current_level_display = '<p class="approved">'+current_level+'</p>'
                    else var current_level_display = '<p class="pending">'+current_level+'</p>' 
                    return current_level_display;
                }
            },
            { 
                data: function(row) {
                    var vq_status = row.vq_status;
                    if(vq_status == 'Pending') var vq_status_display = '<p class="pending">'+vq_status+'</p>'
                    else var vq_status_display = '<p class="approved">'+vq_status+'</p>' 
                    return vq_status_display;
                }
            },
            { 
                data: function(row) {
                    var element = `<a href="/initiator/listing/${row.id}" data-title="View Details">
                            <img src="../admin/images/down.svg" alt="">
                        </a>
                        <a href="/initiator/activity/${row.id}" data-title="View Activity Tracker">
                            <img src="../admin/images/clock.png" alt="">
                        </a>
                        <a data-title="Delete VQ" onclick="deleteVQ(${row.id})"><img src="../admin/images/delete.png" alt=""></a>`
                    if(row.vq_status == 'Sent') element += `<a href="/initiator/newPriceSheet/${row.id}" data-title="Price Sheet">
                            <img src="../admin/images/Price_list.svg" alt="">
                        </a>`
                    return element;
                }
            },
            // Add more columns as needed...
        ],
        columnDefs: [
            { targets: 2, width: '50px' },
             // Set width for the City column
        ],
        serverSide: true,
        ajax: {
            url: '/initiator/vq_listing_ajax',
            type: 'GET',
        },
        // Other DataTables configurations...
        scrollX: true,
        autoWidth: false,    
            'language': {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },

            //scrollX: true,

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
                            input.type = 'search';
                            //input.style.width = '100%'; // Set input width to 100%
                            // Add specific class to the third input
                            if (index === 2 || index === 1) {
                                input.style.width = '100px';
                            }
                            column.footer().replaceChildren(input);
            
                            // Event listener for user input
                            input.addEventListener('input', () => {
                                if (column.search() !== this.value) {
                                    column.search(input.value).draw();
                                }
                            });
                            $('.dataTables_filter input').addClass('common_filter');
                        }
                    });
                }
    });
    var storedValue = localStorage.getItem('initiatorListSearch');
    if (storedValue !== null && storedValue !='') {
        $('.dataTables_filter input').val(storedValue);

        $('.dataTables_filter input').trigger('input')
    }
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
    $('body').on('input', '.common_filter', function(){
        if($(this).val()!='')
        {
            localStorage.setItem('initiatorListSearch', $(this).val());
        }
        else
        {
            localStorage.removeItem('initiatorListSearch');
        }
    })
    $('#financial-year-dropdown-report').on('change', function(){
        if($(this).val() != '')
        {
            var settings = {
              "url": "/initiator/check_financial_year_report",
              "method": "POST",
              "timeout": 0,
              "headers": {
                "Accept-Language": "application/json",
              },
              "data": {
                "_token": "{{ csrf_token() }}",
                "financialYear": $(this).val(),
              }
            };
            $.ajax(settings).done(function (response) {
              //console.log(response);
              if(response.result == null)
              {
                $('#download_financial_history_report').addClass('d-none');
                $('#generate_financial_history_report').removeClass('d-none');
                $('#download_financial_history_report').attr('href','');
              }
              else
              {
                $('#generate_financial_history_report').removeClass('d-none');
                $('#download_financial_history_report').removeClass('d-none');
                $('#download_financial_history_report').attr('href', "{{ url('/') }}/" + response.result)
                $('#fin_report_date').text('('+response.created_date+')');
              }
            });
        }
        else
        {
            $('#download_financial_history_report').addClass('d-none');
            $('#generate_financial_history_report').addClass('d-none');
            $('#download_financial_history_report').attr('href', "")
        }
    })
    $('#generate_financial_history_report').on('click', function(event) {
        event.preventDefault(); // Prevent the default anchor behavior

        var selectedYear = $('#financial-year-dropdown-report').val(); // Get the selected financial year

        if(selectedYear != '') {
            $('#filter_financial').modal({backdrop: 'static', keyboard: false},'show');
        } else {
            alert('Please select a financial year.');
        }
    });
    $('#price-sheet-latest').on('click', function(){
      $('#filter_latest').modal({backdrop: 'static', keyboard: false},'show');
    })
    $('#historical-report').on('click', function(){
      $('#filter_historical').modal({backdrop: 'static', keyboard: false},'show');
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
        "url": "{{ route('filter-latest-report-initiator') }}",
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
    $('.close-button-filter_historical').on('click', function(){
      var selectedDivision = $('#division_name_historical').val()
      var settings = {
        "url": "{{ route('filter-historical-report-initiator') }}",
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
    $('.close-button-filter_financial').on('click', function(){
      var selectedDivision = $('#division_name_financial').val()
      var selectedYear = $('#financial-year-dropdown-report').val(); 
      if(selectedYear != '') {
          var settings = {
                "url": "{{ route('financial-history-report') }}",
                "method": "POST",
                "timeout": 0,
                "headers": {
                    "Accept-Language": "application/json",
                },
                "data": {
                    "_token": "{{ csrf_token() }}",
                    "financialYear": selectedYear,
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
        } else {
            alert('Please select a financial year.');
        }
    })
    $('#make_parent_btn').on('click', function(){
        var selected_vq_delete = $('#selected_vq_delete').val()
        var parent_institution_selection = $('#parent_insititution_chain').val()
        if(parent_institution_selection == '')
        {
            alert('Please select parent institution')
        }
        else
        {
            $('#child_modal_close').trigger('click');
            $('#loader1').show(); 
            setTimeout(function() {
                 var settings = {
                    "url": "{{ route('initator-make-parent') }}",
                    "method": "POST",
                    "timeout": 0,
                    "headers": {
                        "Accept-Language": "application/json",
                    },
                    "data": {
                        "_token": "{{ csrf_token() }}",
                        "selected_vq_delete_id": selected_vq_delete,
                        "parent_institution_selection": parent_institution_selection
                    }
                };

                $.ajax(settings).done(function (response) {
                    $('#loader1').hide(); 
                    // $('#reportMessage').html(`<div class="alert alert-success mt-3" role="alert" >
                    //     ${response.message}
                    // </div>`) // hide by arunchadnru 15012025
                    if(response.type == 'success'){
                        $('#reportMessage').html(`<div class="alert alert-success mt-3" role="alert" >
                            ${response.message}
                        </div>`);
                    }else if(response.type == 'error'){
                        alert(response.message);
                    }

                    $('.dataTables_filter input').val(' ')
                    var table = $('#zero_config').DataTable();
                    table.ajax.reload(null, false);
                    //console.log(response);
                }).fail(function (jqXHR, textStatus) {
                    $('#loader1').hide(); 
                    $('#reportMessage').html(`<div class="alert alert-danger mt-3" role="alert" >
                        Please try again later
                    </div>`)
                    $('.dataTables_filter input').val(' ')
                    var table = $('#zero_config').DataTable();
                    table.ajax.reload(null, false);
                });

            }, 1000);
        }
    })
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