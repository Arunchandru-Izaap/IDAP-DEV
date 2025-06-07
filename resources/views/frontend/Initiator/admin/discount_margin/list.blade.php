@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
    .copy-center {
        position: inherit !important;
        margin: 20px 0;
    }

    div.dataTables_wrapper {
      max-width: 1357px;
      width: 100%;
      margin: 0 auto;
    }

    #zero_config_view_log_wrapper {
      max-width: 1357px;
      width: 100%;
      margin-top: 26px;
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
    .div_filter
    {
      position: absolute;
      left: 14rem;
      width: 40% !important;
      bottom: 0.4rem;
    }
    .modal-dialog {
      max-width: 90%;
      width: auto;
    }
    .select2-container {
      z-index: 9999 !important;
    }
    .select2-dropdown {
      z-index: 99999 !important;
    }

    .select2-container .select2-selection--multiple {
      max-height: 60px;
      overflow-y: auto;
      overflow-x: hidden;
    }
 
    table#zero_config_view_log {
      width:1074px !important;
    }
    #zero_config_view_log_wrapper .dataTables_scrollHeadInner{
      width:1074px !important;
    }
</style>
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
      <div class="collapse navbar-collapse show" id="navbarSupportedContent">
        <ul class="navbar-nav dashboard-nav">
          <li class="nav-item active"><a class="nav-link" href="{{ route('initiator_dashboard') }}"> 
            <img src="../../admin/images/back.svg">Discount Margin Master</a>
          </li>
        </ul>

        <ul class="d-flex ml-auto user-name">
          <li>
            <h3>{{ Session::get('emp_name') }}</h3>
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
    <li><a href="{{ route('initiator_dashboard') }}">Home</a></li>
      <li class="active"><a href="">Discount Margin Master</a></li>
  </ul>
  <div class="container-fluid">
    @if($errors->any())
      <div class="alert alert-danger">
        <ul>
          @foreach($errors->all() as $error)
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
      <div class="col">
        <div>&nbsp;</div>
        <div class="col-md-12 d-flex ">
          <a class="orange-btn-bor" href="{{ url('/initiator/discount-margin') }}">Add Discount Margin</a>
          <button class="orange-btn action_btn  mr-2" id="view_log" btn-fn="view_log">View Log</button>
        </div>
        <div class="actions-dashboard table-ct">
          <table id="zero_config" class="table VQ Request Listing vq-request-listing-tb nowrap">
            <thead>
              <tr>
                <th>No.</th>
                <th>Brand Name</th>
                <th>Sap Item Code</th>
                <th>Item Code</th>
                <th><strong>Disc Margin %</strong></th>
                <th><strong>Action</strong></th>
              </tr>
            </thead>
            <tbody>
              @foreach($listdata as $k => $item)
                  <tr>
                    <td>{{ $k+1 }}</td>
                    <td>{{ $item->brand_name }}</td>
                    <td><?php echo ($item->sap_itemcode != '')? $item->sap_itemcode : '-'; ?></td>
                    <td>{{ $item->item_code }}</td>
                    <td>{{ $item->discount_margin }}</td>
                    <td>
                        <a href="{{ url('initiator/discount-margin-edit',['id'=>$item->dm_id]) }}"
                            class="btn btn-primary btn-sm">Edit</a><a
                            onclick="deleteInstitutionHandler(<?= $item->dm_id?>)"
                            class="btn btn-danger btn-sm text-white">Delete</a>
                    </td>
                  </tr>
              @endforeach
            </tbody>

          </table>
        </div>
      </div>
    </div>
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
                  <th >Changed by</th>
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
<script src="{{ asset('admin/extra-libs/DataTables/datatables.min.js') }}"></script>
<script>
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
        url: '/initiator/discount_margin_view_logs_ajax',
        type: 'GET',
        data: function (d) {
          // Add custom data to the request
          d.brand_name_filter = $('#brand_name_filter').val();
          return d;
        },
      },
      columns:
      [
        { data: null }, 
        { 
          data: function(row) {
            return ` Discount margin was `+ row.changed_to +` for Item Code `+ row.item_code +` - `+ row.brand_name;
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
            return  row.emp_code +` - `+ row.emp_name ;
          }
        },
        { 
          data: function(row) {
            const shortUserAgent = getShortUserAgent(row.user_agent || '');
            var element = `<button class="btn btn-info btn-sm view-details-btn" type="button" data-toggle="collapse" data-target="#details`+row.id+`" aria-expanded="false" aria-controls="details`+row.id+`">
                  View Details
                </button>
                <div class="collapse mt-2" id="details`+row.id+`">
                  <div class="bg-light p-2 rounded">
                    <div class="mb-2"><strong>Financial Year:</strong>`+ row.fin_year +` </div>
                    <div class="mb-2"><strong>Ip Address:</strong> `+ row.ip_address +` </div>
                    <div class="mb-2"><strong>Browser:</strong> `+ shortUserAgent +` </div>
                    <div class="mb-2"><strong>Item_code:</strong> `+ row.item_code +` </div>
                    <div class="mb-2"><strong>Brand name:</strong> `+ row.brand_name +` </div>
                    <div class="mb-2"><strong>Sap itemcode: </strong> `+ row.sap_itemcode +` </div>
                    <div class="mb-2"><strong>Changed discount margin: </strong> `+ row.changed_discount_margin +` </div>
                    <div class="mb-2"><strong>Previous discount margin: </strong> `+ row.pervious_discount_margin +` </div>
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
                  // 'width': '110px',
                  // Add other styles here
              });
          }
        },
        {
          targets: 2, 
          createdCell: function(td, cellData, rowData, row, col) {
            $(td).css({
              'white-space': 'normal',
              // 'width': '110px',
            });
          }
        },
        {
          targets: 3, 
          createdCell: function(td, cellData, rowData, row, col) {
            $(td).css({
              'white-space': 'normal',
              // 'width': '110px',
            });
          }
        },
        {
          targets: 4, 
          createdCell: function(td, cellData, rowData, row, col) {
            $(td).css({
              // 'white-space': 'normal',
              // 'width': '110px',
            });
          }
        },
        // Define other column definitions here
      ],
      order: [[0, 'desc']],
      'language': {   "emptyTable": "<span class='no-data-message'>Processing...</span>", 'paginate': {      'previous': "<img src='{{ asset('admin/images/left.svg') }}' class='left-block' alt=''> <img src='{{ asset('admin/images/left1.svg') }}' class='disbpr' alt=''>",      'next': "<img src='{{ asset('admin/images/right.svg') }}' class='neblk' alt=''> <img src='{{ asset('admin/images/right1.svg') }}' class='disbn' alt=''>"    }  },//added empty table custom message
      scrollX: true,
      "initComplete": function(settings, json) {
          // Check if there is no data returned from the server
          if (json && json.data && json.data.length === 0) {
              // Reload the page after 5 seconds
              setTimeout(function() {
                  location.reload();
              }, 5000);
          }
        var $customDropdown = $(`<select  class="form-control js-example-basic-multiple" multiple="" id="brand_name_filter"><option value="All">Select Brands</option>@foreach($brand_details as $item)<option value="{{$item->item_code}}">{{$item->brand_name}}</option>@endforeach</select>`);
        $('#zero_config_view_log_wrapper .row .col-md-6 .dataTables_length').append($customDropdown);
        // $('#filter_by_brands').append($customDropdown);
        // $('.js-example-basic-single').select2({ width: '100%' },{placeholder: 'Select Item Name'});
        $('.js-example-basic-multiple').select2({
          width: '100%',
          placeholder: 'Select Brand Name',
          dropdownParent: $('#zero_config_view_log_wrapper')// or a modal container if applicable
        });
        $('.select2-container').addClass('div_filter');
      }
    });
  });
  $(document).on('click', '.view-details-btn', function () {
    // Close any open collapses
    $('.collapse').collapse('hide');

    // Open the clicked collapse
    const target = $(this).attr('data-target');
    $(target).collapse('show');
  });
  
  $(document).ready(function () {
    $("#zero_config").DataTable({
      "pageLength": 50,
      'language': {
        'paginate': {
          'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",
          'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"
        }
      },
      // scrollX: true,
    })
  });
  function getShortUserAgent(userAgent) {
    const match = userAgent.match(/(Windows|Linux|Mac).*?(Chrome|Safari|Firefox)\/([\d.]+)/);
    if (match) {
        return `${match[1]} (${match[2]} ${match[3]})`;
    }
    return userAgent;
  }
  $('body').on('change','#brand_name_filter',function(){
    var table = $('#zero_config_view_log').DataTable();
    table.page('first').draw('page');  // Reset to the first page
    table.ajax.reload(null, false);  // Reload the table data
  });
  
  $(document).on('select2-container--open', function() {
    alter('ff');
    setTimeout(() => {
      document.querySelector('.select2-search__field').focus();
    },2000);
  });
  function deleteInstitutionHandler(id) {
    let del = confirm('Do you really want to delete?');
    if (del) {
      document.location.href = '/initiator/discount-margin-delete/' + id;
    }
  }
</script>
@endsection
