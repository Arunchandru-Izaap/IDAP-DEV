@extends('layouts.admin.app')
@section('content')
<style type="text/css">
  ul {
    list-style: none;
    margin: 0;
    padding: 0;
  }
  .checkobx-ct li {
    flex: 0 0 25%;
  }
  .quotation-details {
    padding: 15px;
}
.checkbox {
    width: 100%;
    margin: 15px auto;
    position: relative;
    display: block;
}
.checkbox input[type=checkbox] {
    position: absolute;
    left: 0;
    margin-left: -30px;
    bottom: 7px;
    width: 25px;
    height: 25px;
    cursor: pointer;
}
.model-pop-ct .tich-logo {
    margin: 0 auto;
    position: absolute;
    left: 0;
    right: 0;
    text-align: center;
    top: -50px;
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
.tich-logo {
    margin: 0 auto;
    position: absolute;
    left: 0;
    right: 0;
    text-align: center;
    top: -20px;
    width: 62px;
    height: 62px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    box-shadow: 0 4px 25px 0 rgba(0, 0, 0, 0.05);
    border: solid 1px #e8e8e8;
    background-color: #fff;
}
.modal_close
{
    cursor: pointer;
    position: absolute;
    left: 29rem;
    top: 3px;
    bottom: 0;
}
.modal-header .close {
    padding: 1rem 1rem;
    margin: -1rem -1rem -1rem auto;
}
button.close {
    padding: 0;
    background-color: transparent;
    border: 0;
}

.close {
    float: right;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #000;
    text-shadow: 0 1px 0 #fff;
    opacity: .5;
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
      /* bottom: 0.4rem; */
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
    .select2-container--classic .select2-selection--multiple .select2-selection__choice, .select2-container--default .select2-selection--multiple .select2-selection__choice, .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    /*background-color: #2255a4;
    border-color: #2255a4;*/
    color: #000;
}
</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
        <div class="card">
                <div class="card-body">
                <div class="col-md-12 d-flex ">
                  <h5 class="card-title me-3"><a href="{{url('/admin/discount-margin')}}">Add Discount Margin</a></h5>
                  <button class="btn btn-primary   mr-2" id="view_log" btn-fn="view_log" style="margin-right: 1rem;">View Log</button>
                </div>
                  <div class="table-responsive">
                    <table id="zero_config" class="table table-striped table-bordered">
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
                          <td>{{$k+1}}</td>
                          <td>{{$item->brand_name}}</td>
                          <td><?php echo ($item->sap_itemcode != '')? $item->sap_itemcode : '-'; ?></td>
                          <td>{{$item->item_code}}</td>
                          <td>{{$item->discount_margin}}</td>
                          <td>
                            <a href="{{url('admin/discount-margin-edit',['id'=>$item->dm_id])}}" class="btn btn-primary btn-sm">Edit</a><a onclick="deleteInstitutionHandler(<?= $item->dm_id?>)" class="btn btn-danger btn-sm text-white">Delete</a>
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
          <div class="actions-dashboard table-ct">
            <table class="table table-striped table-bordered" id="zero_config_view_log">
              <thead>
                <tr>
                  <th style="text-align: center">S.no</th>
                  <th style="text-align: center">Action</th>
                  <th style="text-align: center">Changed at</th>
                  <th style="text-align: center">Changed by</th>
                  <th>Details</th>
                </tr>
              </thead>
              <tbody>
                @if(count($log)>0)
                  @foreach($log as $item)
                  <tr>
                      <td>{{ $loop->iteration }}</td>
                      <td>@php
                            // Decode the JSON activity field
                            $details = json_decode($item->activity, true);
                            $changedToValue = (isset($details['changed_to']))?$details['changed_to'] : '';
                            $item_code = (isset($details['item_code']))?$details['item_code'] : 'N/A';
                            $brand_name = (isset($details['brand_name']))?$details['brand_name'] : 'N/A';
                        @endphp
                        
                        @if($details)
                          Discount margin was {{ $changedToValue }} for Item Code {{ $item_code }} and {{ $brand_name }}.
                        @endif</td>
                      <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y H:i:s') }}</td>
                      <td style="text-align: center">{{ $item->emp_code }}-{{ $item->emp_name }}</td>
                      <td>
                          <button class="btn btn-info btn-sm view-details-btn" type="button" data-toggle="collapse" data-target="#details{{ $loop->iteration }}" aria-expanded="false" aria-controls="details{{ $loop->iteration }}">
                            View Details
                          </button>

                          <!-- Collapsible section for JSON details -->
                          <div class="collapse mt-2" id="details{{ $loop->iteration }}">
                              <div class="bg-light p-2 rounded">
                                  @php
                                      // Decode the JSON activity field
                                      $details = json_decode($item->activity, true);
                                      $changedToValue = (isset($details['changed_to']))?$details['changed_to'] : '';
                                      
                                      // Shorten user agent (extracting browser and OS)
                                      $userAgent = isset($details['user_agent']) ? $details['user_agent'] : '';
                                      preg_match('/(Mozilla\/[^ ]+).*(Windows|Linux|Mac).*?(Chrome|Safari|Firefox)\/([^ ]+)/', $userAgent, $matches);
                                      $shortUserAgent = isset($matches[2]) ? "{$matches[2]} ({$matches[3]} {$matches[4]})" : $userAgent;
                                  @endphp
                                  
                                  @if($details)
                                      <div class="mb-2"><strong>Financial Year:</strong> {{ $details['fin_year'] ?? 'N/A' }}</div>
                                      <div class="mb-2"><strong>Ip Address:</strong> {{ $details['ip_address'] ?? 'N/A' }}</div>
                                      <div class="mb-2"><strong>Browser:</strong> {{ $shortUserAgent }}</div>
                                      <div class="mb-2"><strong>Item_code:</strong> {{ $details['item_code'] ?? 'N/A' }} </div>
                                      <div class="mb-2"><strong>Brand name:</strong> {{ $details['brand_name'] ?? 'N/A' }}</div>
                                      <div class="mb-2"><strong>Sap itemcode: </strong> {{ $details['sap_itemcode'] ?? 'N/A' }}</div>
                                      <div class="mb-2"><strong>Changed discount margin: </strong> {{ $details['changed_discount_margin'] ?? 'N/A' }}</div>
                                      <div class="mb-2"><strong>pervious discount margin: </strong> {{ $details['pervious_discount_margin'] ?? 'N/A' }}</div>
                                  @else
                                      <p>No details available.</p>
                                  @endif
                              </div>
                          </div>
                      </td>
                  </tr>
                  @endforeach
                 @else
                  <tr>
                      <td colspan="4">No logs available.</td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>

          <div class="text-center mt-3">
            <a class="btn btn-warning" data-bs-dismiss="modal">Close</a>
          </div>
        </div>
      </div>
    </div>
</div>
@endsection
@push('scripts')

<script>
  $('#view_log').on('click', function(){
    if ($.fn.dataTable.isDataTable('#zero_config_view_log')) {
      $('#zero_config_view_log').DataTable().destroy();
      $('#zero_config_view_log tbody').empty();
    }
    $('#view_log_modal').modal('show');
    
    $("#zero_config_view_log").DataTable({
      serverSide: true,
      processing: true,
      responsive: true,
      "pageLength": 50,
      ajax: {
        url: '/admin/discount_margin_view_logs_ajax',
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
      // 'language': {   "emptyTable": "<span class='no-data-message'>Processing...</span>", 'paginate': {      'previous': "<img src='{{ asset('admin/images/left.svg') }}' class='left-block' alt=''> <img src='{{ asset('admin/images/left1.svg') }}' class='disbpr' alt=''>",      'next': "<img src='{{ asset('admin/images/right.svg') }}' class='neblk' alt=''> <img src='{{ asset('admin/images/right1.svg') }}' class='disbn' alt=''>"    }  },//added empty table custom message
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
    // Fix header misalignment after expand/collapse
    $('#zero_config_view_log').DataTable().columns.adjust();
  });
  $(document).on('click', '.view-details-btn', function () {
    // Close any open collapses
    $('.collapse').collapse('hide');
    // Open the clicked collapse
    const target = $(this).attr('data-target');
    $(target).collapse('show');
    // Fix header misalignment after expand/collapse
    $('#zero_config_view_log').DataTable().columns.adjust();
  });
  $('.close').on('click',  function(){
    $('#view_log_modal').modal('hide');
  });
	$('.cancel_btn').click(function(){
		//window.history.back();
    window.location.href = "{{ route('home') }}";
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
  
  function deleteInstitutionHandler(id){
    let del = confirm('Do you really want to delete?');
    if(del){
      document.location.href = '/admin/discount-margin-delete/'+id;
    }
  }
 
</script>
@endpush