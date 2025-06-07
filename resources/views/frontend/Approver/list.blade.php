@extends('layouts.frontend.app')
@section('content')
<style>
  tfoot input {
      width: 100%;
      padding: 3px;
      box-sizing: border-box;
  }
  .table-ct .table tbody tr.odd {
    background: #fff8f2 !important;
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
/*.table-ct .dataTables_wrapper .table tbody tr td:last-child a
{
    margin-right: 2px;
}
.table-ct .table tbody tr td:last-child a {
    margin-right: 2px;
}*/
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
                    /*
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
                      */ // hide by arun at21042025
                      $flag = $pendingVqExists ? '' : 'disabled';
                    ?>

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
                    <div class="col-md-2 ">
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
                      <?php 
                      $div_ids = explode(',',Session::get("division_id"));
                      $division_names = explode(',', Session::get("division_name"));
                      if(count($div_ids) == 1) {?>
                      <a href="{{route('latest-report')}}" id="price-sheet" class="btn-grey">
                              <img src="{{ asset('admin/images/download.svg') }}">
                              <p>Generate Latest Report</p>
                      </a>
                      <?php } else if(count($div_ids) > 1) { ?> 
                      <a href="#" id="price-sheet-latest" class="btn-grey">
                              <img src="{{ asset('admin/images/download.svg') }}">
                              <p>Generate Latest Report</p>
                      </a> 
                      <?php } else { ?> 
                      <a href="{{route('latest-report')}}" id="price-sheet" class="btn-grey">
                              <img src="{{ asset('admin/images/download.svg') }}">
                              <p>Generate Latest Report</p>
                      </a> <?php } ?>
                      @if ($latest_file_link)
                      <a href="{{ url('/') }}/{{$latest_file_link}}" id="price-sheet" class="btn-grey">
                              <img src="{{ asset('admin/images/download.svg') }}">
                              <!-- <p>Latest Report Download</p> -->
                              <p>Download Latest Report as on ({{$latest_file_creation_date}})</p>
                      </a>     
                      @endif
                    </div>
                        <div class="col-md-2">
                            <a href="{{route('vq-export-approver')}}" id="price-sheet" class="btn-grey" style="margin-right: 4rem; ">
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
                     
                        <div class="col" id="grid_wrapper">
                            <div class="table-responsive actions-dashboard table-ct">

                              <table class="table VQ Request Listing vq-request-listing-tb nowrap" id="zero_config" style="width: 100%;">
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
                                    </tr>
                                </tfoot>
                                
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
<!-- filter for report popup -->
<div class="modal show" id="filter_modal">
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
          <select id="division_name" class="js-example-basic-multiple" name="" multiple="multiple">
             <option value="all">All</option>
             <?php 
              foreach($div_ids as $key => $div_id) {
                  $div_name = $division_names[$key] ?? '';
              ?>
              <option value="<?php echo $div_id; ?>"><?php echo $div_name; ?></option>
              <?php } ?>
          </select>
          <a class="btn orange-btn big-btn close-button-filter" data-dismiss="modal">Generate latest report</a>
        </div>
      </div>
    </div>
</div>
<div class="modal show" id="bulk_update_modal">
    <!-- added by govind on 170425 start -->
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
                <p class="border-0">Select division to approve</p>
                <div id="division_checkbox_list"
                    style="max-height: 200px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
                    @foreach($pendingDivisions as $division)
                        <label class="d-block">
                            <input type="checkbox" class="division-checkbox" name="division_ids[]"
                                value="{{ $division->div_name }}-{{ $division->div_id }}" checked>
                            {{ $division->div_name }}-{{ $division->div_id }}
                        </label>
                    @endforeach
                </div>
                <button class="btn orange-btn big-btn" id="division_selection_approve">Approve All and Submit</button>
            </div>
        </div>
    </div>
</div><!-- added by govind on 170425 end -->


<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script src="{{asset('frontend/js/select2.js')}}"></script>
<script>
$('.js-example-basic-multiple').select2({ width: '100%' });
$('#division_name').val('all').change();
let flag = 'no';
$( "#approveall" ).on( "click", function() {
  if(session_current_level == 5 || session_current_level == 6)
  {
    $('#bulk_update_modal').modal('show');
  }
  else
  {
    bulk_approve();
  }
});

$('#division_selection_approve').on('click', function(){
  bulk_approve();
})
function bulk_approve()
{
  let selectedDivisions = [];
  if (session_current_level == 5 || session_current_level == 6) {
    $('.division-checkbox:checked').each(function () {
      selectedDivisions.push($(this).val());
    });
    if (selectedDivisions.length === 0) {
      alert("Please select at least one division.");
      $("#approveall").attr('disabled', false);
      $("#division_selection_approve").attr('disabled', false);
      return;
    }
  }
  let text = "Are you sure, you want to approve all VQ's?";
  if (confirm(text) == true) {
    flag = 'yes';
    $( "#approveall" ).attr('disabled',true);
    $( "#division_selection_approve" ).attr('disabled',true);
  
    var settings = {
      "url": "{{ route('bulk_approve') }}",
      "method": "POST",
      "timeout": 0,
      "headers": {
        "Accept-Language": "application/json",
      },
      "data": {
        "_token": "{{ csrf_token() }}",
        "div_id": (session_current_level == 5 || session_current_level == 6) ? selectedDivisions : []
      }
    };
    $.ajax(settings).done(function (response) {
      // alert('hello');
      console.log(response);
      // $('#approveall').hide();
      $('#bulk_update_modal').modal('hide');
      $('#submited').modal('show');
      flag = 'no';
    });
  }
}//added by govind on 170425 end


	$('.close-button').click(function(){
		$('#submited').modal('hide');
		location.reload(true);
	});

      /****************************************
       *       Basic Table                   *
       ****************************************/
       const session_current_level = `{{preg_replace('/[^0-9.]+/', '', Session::get("level"))}}`;
      $("#zero_config").DataTable(

        {  
          serverSide: true,
          processing:true,
          "pageLength": 50,
          ajax: {
            url: '/approver/vq_listing_approver_ajax',
            type: 'GET',
            data: function (d) {
            // Add custom data to the request
                d.total_row = "{{count($data)}}";
                return d;
            },
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
                if(row.status_vq =='0' && row.current_level == session_current_level)
                {
                  return '<p class="pending">Pending</p>';
                }
                else if(row.status_vq =='1'|| row.current_level > session_current_level  && row.status_vq!='3')
                {
                  return '<p class="approved">Approved</p>';
                }
                else
                {
                  var status = 'Cancelled';
                  if(session_current_level !=row.deleted_by )
                  {
                    if(row.deleted_by == 1)
                    {
                      status += 'By RSM';
                    }
                    else if(row.deleted_by == 2)
                    {
                      status += 'By ZSM';
                    }
                    else if(row.deleted_by == 3)
                    {
                      status += 'By NSM';
                    }
                    else if(row.deleted_by == 4)
                    {
                      status += 'By SBU';
                    }
                    else if(row.deleted_by == 5)
                    {
                      status += 'By Semi Cluster';
                    }
                    else if(row.deleted_by == 6)
                    {
                      status += 'Cluster';
                    }
                  }
                  return '<p class="cancelled">'+status+'</p>';
                }
              }
            },
            { 
                data: function(row) {
                    var element = `<a href="/approver/listing/${row.id}" data-title="View Details">
                        <img src="../admin/images/down.svg" alt="">
                    </a>
                    <a href="/approver/activity/${row.id}" data-title="View Activity Tracker">
                        <img src="../admin/images/clock.png" alt="">
                    </a>`
                    if(row.vq_status == 1) element += `<a href="/approver/newPriceSheet/${row.id}" data-title="Price Sheet">
                        <img src="../admin/images/Price_list.svg" alt="">
                    </a>`
                    return element;
                }
            },
          ],
          language: {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },
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
          }
        );
    var storedValue = localStorage.getItem('approverListSearch');
    if (storedValue !== null && storedValue !='') {
        $('.dataTables_filter input').val(storedValue);

        $('.dataTables_filter input').trigger('input')
    }
    $('body').on('input', '.common_filter', function(){
        if($(this).val()!='')
        {
            localStorage.setItem('approverListSearch', $(this).val());
        }
        else
        {
            localStorage.removeItem('approverListSearch');
        }
    })
    $('#price-sheet-latest').on('click', function(){
      $('#filter_modal').modal({backdrop: 'static', keyboard: false},'show');
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
    $('.close-button-filter').on('click', function(){
      var selectedDivision = $('#division_name').val()
      var settings = {
        "url": "{{ route('filter-latest-report') }}",
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
        div.dataTables_wrapper {
            max-width: 1357px;
            width: 100%;
            margin: 0 auto;
        }
    </style>
@endsection