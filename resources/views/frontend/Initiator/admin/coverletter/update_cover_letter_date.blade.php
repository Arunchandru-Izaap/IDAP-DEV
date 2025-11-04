@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
    table#zero_config_view_log {
      width:1074px !important;
    }
    #zero_config_view_log_wrapper {
      max-width: 1357px;
      width: 100%;
      margin-top: 26px;
    }
    #zero_config_view_log_wrapper .dataTables_scrollHeadInner{
      width:1074px !important;
    }
    
    .copy-center {
        position: fixed !important;
        margin: 20px 0;
    }
    div.dataTables_wrapper {
        max-width: 1357px;
        width: 100%;
        margin: 0 auto;
    }
    .modal-dialog {
      max-width: 90%;
      width: auto;
    }
    .tich-logo {
      margin: 0 auto;
      position: absolute;
      left: 0;
      right: 0;
      text-align: center;
      top: -32px;
      width: 92px;
      height: 92px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      box-shadow: 0 4px 25px 0 rgba(0, 0, 0, 0.05);
      border: solid 1px #e8e8e8;
      background-color: #fff;
    }
</style>
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
          <div class="collapse navbar-collapse show" id="navbarSupportedContent">
              <ul class="navbar-nav dashboard-nav">
                  <li class="nav-item active"><a class="nav-link" href="{{route('view_update_date')}}"> <img src="../../admin/images/back.svg">Update Cover Letter Date</a></li>
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
      <li class="active">
        <a href="{{route('view_update_date')}}">
          Update Cover Letter Date
        </a>
      </li>
  </ul>
  <div class="container-fluid">
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
        </ul>
      </div><br />
      @endif
      @if(session()->has('message'))
        <div class="alert alert-success">
            {{ session()->get('message') }}
        </div>
      @endif
    <div class="row">
        <div class="col-md-12 d-flex justify-content-end" style="margin-bottom: 12px;margin-top: 10px;">
          <button class="orange-btn action_btn  mr-2" id="view_log" btn-fn="view_log">View Log</button>
        </div>
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('initiator-update-coverletter-date')}}">
                @csrf
            <div class="card-body">
                <h4 class="card-title">Update Cover Letter Date Info</h4>
                <div class="form-group row">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">Institution Name</label>
                    <div class="col-sm-4">
                        <select name="institution_id[]" id="institution_id" class="form-control js-example-basic-single" multiple="" data-placeholder="Select institutions" required="">
                            <option value="">Select Insititution</option>
                            @foreach($data['institutions'] as $item)
                                <option value="{{ $item['institution_id'] }}">
                                    {{ $item['institution_id'] }} - {{ $item['hospital_name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="rev_no" class="col-sm-3 text-end control-label col-form-label">Revision No (Optional)</label>
                    <div class="col-sm-4">
                        <select name="rev_no" id="rev_no" class="form-control js-example-basic-single" >
                            <option value="empty">Select Revision No</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="coverletter_date" class="col-sm-3 text-end control-label col-form-label">Date</label>
                    <div class="col-sm-4">
                        <input type="date" id="coverletter_date" name="coverletter_date" class="form-control">
                    </div>
                </div>
            </div>
            <div class="border-top">
                <div class="card-body">
                <button type="submit" id="update_date" class="btn btn-primary">
                    Submit
                </button>
                </div>
            </div>
            </form>
        </div>
    </div>

<div class="modal show" id="view_log_modal">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-custom-position" style="max-width: 1110px;">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header border-0">
            <div class="tich-logo">
            <img src="{{ asset('admin/images/check-double.svg') }}" alt="">
            </div>
          <button type="button" class="close" data-dismiss="modal">
              <img src="{{ asset('admin/images/close.svg') }}" alt="" style="width: 40px;">
          </button>
        </div>
        <!-- Modal body -->
        <div class="modal-body">
        <div id='filter_by_brands'></div>
          <div class="actions-dashboard table-ct">
            <table class="table  VQ Request Listing vq-request-listing-tb nowrap" id="zero_config_view_log">
              <thead>
                <tr>
                  <th >S.no</th>
                  <th >Action</th>
                  <th >Changed at</th>
                  <th >Institution</th>
                  <th >Revision No</th>
                  <th >Details</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>

          <div class="text-center mt-3">
            <a class="btn orange-btn big-btn" data-dismiss="modal">Close</a>
          </div>
          
        </div>
      </div>
    </div>
</div>
<script src="{{asset('frontend/js/select2.js')}}"></script>
<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script src="{{ asset('admin/extra-libs/DataTables/datatables.min.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $('.js-example-basic-single').select2({ width: '100%' }/*,{placeholder: 'Select Item Name'}*/);
  });
  $("#institution_id").on('change', function(){
    var inst_ids = $("#institution_id").val();
    var settings = {
        "url": "/initiator/GetInstutitionRevisions",
        "method": "POST",
        "timeout": 0,
        "headers": {
          "Accept-Language": "application/json",
        },
        "data": {
          "_token": "{{ csrf_token() }}",
          "data": inst_ids,
        }
    };
    $.ajax(settings).done(function (response) {
        if(response.success == true){
            var option_html = '<option value="empty">Select Revision No</option>';
            for(i = 0; i < response.data.maxValue; i++){
                option_html += '<option value="'+ i +'">'+ i +'</option>';
            }
            $("#rev_no").html(option_html);
            console.log(response.data.maxValue);
        }
    });
  });

  /*
  // initi Data table 
  $("#zero_config_view_log").DataTable({
    "pageLength": 50,
    columnDefs: [
      {
        targets: 0, 
        createdCell: function(td, cellData, rowData, row, col) {
          $(td).css({
                'text-align': 'center',
                'width': '10px',
                // Add other styles here
            });
        }
      },
      {
        targets: 1, 
        createdCell: function(td, cellData, rowData, row, col) {
            $(td).css({
              'text-align': 'center',
              'width': '20px',
              // Add other styles here
            });
        }
      },
      {
        targets: 2, 
        createdCell: function(td, cellData, rowData, row, col) {
          $(td).css({
            'text-align': 'center',
            'width': '10px',
            // Add other styles here
          });
        }
      },
      {
        targets: 3, 
        createdCell: function(td, cellData, rowData, row, col) {
          $(td).css({
            'text-align': 'left',
            'width': '10px',
            // Add other styles here
          });
        }
      },
      {
        targets: 4, 
        createdCell: function(td, cellData, rowData, row, col) {
          $(td).css({
            'text-align': 'center',
            'width': '10px',
            // Add other styles here
          });
        }
      },
      {
        targets: 5, 
        createdCell: function(td, cellData, rowData, row, col) {
          $(td).css({
            // 'white-space': 'normal',
            // 'width': '110px',
          });
        }
      },
      // Define other column definitions here
    ],
    'language': {   "emptyTable": "<span class='no-data-message'>Processing...</span>", 'paginate': {      'previous': "<img src='{{ asset('admin/images/left.svg') }}' class='left-block' alt=''> <img src='{{ asset('admin/images/left1.svg') }}' class='disbpr' alt=''>",      'next': "<img src='{{ asset('admin/images/right.svg') }}' class='neblk' alt=''> <img src='{{ asset('admin/images/right1.svg') }}' class='disbn' alt=''>"    }  },//added empty table custom message
  });

  // click on the View log Button
  $('#view_log').on('click', function(){
    $('#view_log_modal').modal({backdrop: 'static', keyboard: false}, 'show');
  });
  */

  $(document).on('click', '.view-details-btn', function () {
    // Close any open collapses
    $('.collapse').collapse('hide');

    // Open the clicked collapse
    const target = $(this).attr('data-target');
    $(target).collapse('show');
    // Fix header misalignment after expand/collapse
    
    // Recalculate column widths safely
    setTimeout(function () {
        $('#zero_config_view_log').DataTable().columns.adjust();
    }, 100); // small delay to let collapse animation finish
  });
  $(document).on('hidden.bs.collapse', '.collapse', function () {
    // When a collapse closes, fix widths again
    $('#zero_config_view_log').DataTable().columns.adjust();
  });
  function getShortUserAgent(userAgent) {
    const match = userAgent.match(/(Windows|Linux|Mac).*?(Chrome|Safari|Firefox)\/([\d.]+)/);
    if (match) {
        return `${match[1]} (${match[2]} ${match[3]})`;
    }
    return userAgent;
  }

  $('#view_log').on('click', function(){
    if ($.fn.dataTable.isDataTable('#zero_config_view_log')) {
      $('#zero_config_view_log').DataTable().destroy();
      $('#zero_config_view_log tbody').empty();
    } 
    $('#view_log_modal').modal({backdrop: 'static', keyboard: false}, 'show');
    $("#zero_config_view_log").DataTable({
      serverSide: true,
      processing: true,
      responsive: true,
      "pageLength": 50,
      ajax: {
        url: '/initiator/cover_letter_date_updated_view_logs_ajax',
        type: 'GET',
        data: function (d) {
         
        },
      },
      columns:
      [
        { data: null }, 
        { 
          data: function(row) {
            return `Cover Letter Date was Updated for Institution ID  `+ row.institution_id +` and Revision no `+ row.revision_no;
          }
        },
        { 
          data: function(row) {
            const date = new Date(row.changed_at.replace(" ", "T")); // Ensure it's ISO format
            const options = {
              day: "2-digit",
              month: "short",
              year: "numeric",
              hour: "2-digit",
              minute: "2-digit",
              second: "2-digit",
              hour12: false,
            };

            const formatted = new Intl.DateTimeFormat("en-GB", options).format(date);
            return formatted;
          }
        },
        { 
          data: function(row) {
            return  row.institution_id + ' - ' + row.hospital_name;
          }
        },
        { 
          data: function(row) {
            return  row.revision_no;
          }
        },
        { 
          data: function(row) {
            const shortUserAgent = getShortUserAgent(row.user_agent || '');
            var changed_cover_letter_date = 'NULL';
            if(row.changed_cover_letter_date == null || row.changed_cover_letter_date === "null" || row.changed_cover_letter_date === "0000-00-00 00:00:00"){
              changed_cover_letter_date = 'NULL';
            }else{
              changed_cover_letter_date = new Date(row.changed_cover_letter_date).toLocaleDateString('en-GB'); // dd/mm/yyyy;
            }
            var pervious_cover_letter_date = 'NULL';
            if(row.pervious_cover_letter_date == null || row.pervious_cover_letter_date === "null" || row.pervious_cover_letter_date === "0000-00-00 00:00:00"){
              pervious_cover_letter_date = 'NULL';
            }else{
              pervious_cover_letter_date = new Date(row.pervious_cover_letter_date).toLocaleDateString('en-GB'); // dd/mm/yyyy;
            }
            var element = `<button class="btn btn-info btn-sm view-details-btn" type="button" data-toggle="collapse" data-target="#details`+row.id+`" aria-expanded="false" aria-controls="details`+row.id+`">
                  View Details
                </button>
                <div class="collapse mt-2" id="details`+row.id+`">
                  <div class="bg-light p-2 rounded">
                    <div class="mb-2"><strong>Financial Year:</strong>`+ row.fin_year +` </div>
                    <div class="mb-2"><strong>Ip Address:</strong> `+ row.ip_address +` </div>
                    <div class="mb-2"><strong>Browser:</strong> `+ shortUserAgent +` </div>
                    <div class="mb-2"><strong>institution_id:</strong> `+ row.institution_id +` </div>
                    <div class="mb-2"><strong>revision_no:</strong> `+ row.revision_no +` </div>
                    <div class="mb-2"><strong>Changed Date: </strong> `+ changed_cover_letter_date +` </div>
                    <div class="mb-2"><strong>Previous Date: </strong> `+ pervious_cover_letter_date +` </div>
                  </div>
                </div>`
            return element;
          }
        },
      ],
      columnDefs: [
        {
          targets: [0], // First column for S.No
          orderable: false,
          render: function (data, type, row, meta) {
            return meta.row + 1 + meta.settings._iDisplayStart;
          },
        },
        {
          targets: 1, 
          createdCell: function(td, cellData, rowData, row, col) {
              $(td).css({
                'white-space': 'normal',
                // 'text-align': 'center',
                // 'width': '10px',
                // // Add other styles here
              });
          }
        },
        {
          targets: 2, 
          createdCell: function(td, cellData, rowData, row, col) {
            $(td).css({
              'white-space': 'normal',
              // 'text-align': 'center',
              // 'width': '10px',
              // // Add other styles here
            });
          }
        },
        {
          targets: 3, 
          createdCell: function(td, cellData, rowData, row, col) {
            $(td).css({
              'white-space': 'normal',
              // 'text-align': 'center',
              // 'width': '10px',
              // // Add other styles here
            });
          }
        },
        {
          targets: 4, 
          createdCell: function(td, cellData, rowData, row, col) {
            $(td).css({
              'white-space': 'normal',
              // 'text-align': 'center',
              // 'width': '10px',
              // // Add other styles here
            });
          }
        },
        {
          targets: 5, 
          createdCell: function(td, cellData, rowData, row, col) {
            $(td).css({
              'white-space': 'normal',
              // 'white-space': 'normal',
              // 'width': '110px',
            });
          }
        },
        // Define other column definitions here
      ],
      autoWidth: false ,
      order: [[0, 'desc']],
      'language': {   "emptyTable": "<span class='no-data-message'>Processing...</span>", 'paginate': {      'previous': "<img src='{{ asset('admin/images/left.svg') }}' class='left-block' alt=''> <img src='{{ asset('admin/images/left1.svg') }}' class='disbpr' alt=''>",      'next': "<img src='{{ asset('admin/images/right.svg') }}' class='neblk' alt=''> <img src='{{ asset('admin/images/right1.svg') }}' class='disbn' alt=''>"    }  },//added empty table custom message
      scrollX: true,
    });
    // Fix header misalignment after expand/collapse
    $('#zero_config_view_log').DataTable().columns.adjust();
  });
  
</script>

@endsection