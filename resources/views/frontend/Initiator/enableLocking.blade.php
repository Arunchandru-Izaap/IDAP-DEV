@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
.copyright_inc
{
  top:62rem !important;
}
 .tich-logo {
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
.modal-custom-position {
    margin-top: 50px !important; /* Adjust as needed */
}
</style>
<!-- <div class="container"><h2>Initiator Dashboard demo</h2></div> -->
<!-- Page content wrapper-->
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
      <div class="collapse navbar-collapse show" id="navbarSupportedContent">
        <ul class="navbar-nav dashboard-nav">
          <li class="nav-item active">
            <a class="nav-link" href="">Enable / Disable Locking Period</a>
          </li>
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
      <a href="">
        Enable / Disable locking period
      </a>
    </li>
  </ul>
  <!-- Page content-->
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12 d-flex send-quotation">
        <div class="cancel-btn ml-auto">
          <button class="orange-btn action_btn  mr-2" id="view_log" btn-fn="view_log">View Log</button>
          <button id="approve" class="orange-btn " disabled>
            SAVE CHANGES
          </button>
        </div>
      </div>
    


<div class="col">
<div class="actions-dashboard table-ct quotation-details">
  <!-- <div class="col"> -->
    <h2> Enable / Disable the Locking Period by clicking the checkbox</h2>
    <ul class="checkobx-ct d-flex">
      
        <li class="checkbox">
          <input type="checkbox" id="alter_checkbox" class="single-checkbox" name="locking_period" value="" @if($data->is_enabled == 'Y') checked @endif  ><label for="alter_checkbox" id="locking_text"> Currently Locking Period is @if($data->is_enabled == 'Y') Enabled @else Disabled @endif </label>
        </li>
      </ul>
  <!-- </div> -->
</div>
</div>

<div class="modal show" id="submited">
    <div class="modal-dialog modal-dialog-centered model-pop-ct">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header border-0">
            <div class="tich-logo">
            <img src="{{ asset('admin/images/check-double.svg') }}" alt="">
            </div>
          <button type="button" class="close cancel_btn" data-dismiss="modal">
              <img src="{{ asset('admin/images/close.svg') }}" alt="">
          </button>
        </div>
        <!-- Modal body -->
        <div class="modal-body">
          <p class="border-0">Locking period successfully <label id="lock_status"></label>.</p>

          <a class="btn orange-btn big-btn cancel_btn">Go to VQ Details</a>
        </div>
      </div>
    </div>
</div>

<div class="modal show" id="view_log_modal">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-custom-position" style="max-width: 900px;">
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
          <div class="actions-dashboard table-ct">
            <table class="table  VQ Request Listing vq-request-listing-tb nowrap" id="zero_config">
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
                            $changedToValue = isset($details['changed_to']) ? ($details['changed_to'] === 'Y' ? ' Enabled' : 'Disabled') : '';
                        @endphp
                        
                        @if($details)
                          Locking Period was {{ $changedToValue }}
                        @else
                            Locking Period was Modified
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
                                      $changedToValue = isset($details['changed_to']) ? ($details['changed_to'] === 'Y' ? ' Enabled' : 'Disabled') : '';
                                      
                                      // Shorten user agent (extracting browser and OS)
                                      $userAgent = isset($details['user_agent']) ? $details['user_agent'] : '';
                                      preg_match('/(Mozilla\/[^ ]+).*(Windows|Linux|Mac).*?(Chrome|Safari|Firefox)\/([^ ]+)/', $userAgent, $matches);
                                      $shortUserAgent = isset($matches[2]) ? "{$matches[2]} ({$matches[3]} {$matches[4]})" : $userAgent;
                                  @endphp
                                  
                                  @if($details)
                                      <div class="mb-2"><strong>Financial Year:</strong> {{ $details['fin_year'] ?? 'N/A' }}</div>
                                      <div class="mb-2"><strong>Ip Address:</strong> {{ $details['ip_address'] ?? 'N/A' }}</div>
                                      <div class="mb-2">
                                          <strong>Changed At:</strong> 
                                          {{ \Carbon\Carbon::parse($details['changed_at'])->format('d M Y H:i:s') }}
                                      </div>
                                      <div class="mb-2"><strong>Changed by</strong> {{ $item->emp_code }}-{{ $item->emp_name }}</div>
                                      <div class="mb-2"><strong>Browser:</strong> {{ $shortUserAgent }}</div>
                                      <div class="mb-2"><strong>Locking Period was </strong> {{ $changedToValue }}</div>
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

          <a class="btn orange-btn big-btn" data-dismiss="modal">Close</a>
        </div>
      </div>
    </div>
</div>

<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script>
  $('#approve').click(function(){
    var locking_period = $("input:checkbox[name=locking_period]").is(":checked");
      // Add ajax call for updating price here
    var userConfirmed = confirm("Are you sure you want to change the locking period?");
    if(userConfirmed) {
      var settings = {
        "url": "/initiator/change_locking_period",
        "method": "POST",
        "timeout": 0,
        "headers": {
          "Accept-Language": "application/json",
        },
        "data": {
          "_token": "{{ csrf_token() }}",
          "locking_period": locking_period,
        }
      };
      $.ajax(settings).done(function (response) {
        console.log(response);
        $('#submited').modal('show');
      });
      } else {
        // User clicked "Cancel"
        window.location.reload();
    }
  });
  $(document).on('click', '.view-details-btn', function () {
    // Close any open collapses
    $('.collapse').collapse('hide');
    
    // Open the clicked collapse
    const target = $(this).attr('data-target');
    $(target).collapse('show');
  });
	$('.cancel_btn').click(function(){
		//window.history.back();
    window.location.href = "{{ route('initiator_listing') }}";
	});
  $("input[type='checkbox']").on('change', function(evt) {
    $('#approve').prop("disabled",false); 
    var checked = $("input[type='checkbox']:checked").length;
    if(checked>0){
      $('#locking_text').text('Enabled Locking Period.')
      $('#lock_status').text('enabled')
    }
    else
    {
      $('#locking_text').text('Disabled Locking Period.')
      $('#lock_status').text('disabled')
    }
  });
  $('#view_log').on('click', function(){
    $('#view_log_modal').modal('show');
  })
$("#zero_config").DataTable({  
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
                'width': '20px',
                // Add other styles here
            });
        } 
    },
  ],
  'language': {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },
});






</script>
@endsection