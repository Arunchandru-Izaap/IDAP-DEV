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
            <img src="../../admin/images/back.svg">Approval Email Schedule Master</a>
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
      <li class="active"><a href="">Approval Email Schedule Master</a></li>
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
          <a class="orange-btn-bor" href="{{ url('/initiator/approval-email-schedule') }}">Add Approval Email Schedule</a>
          <button class="orange-btn action_btn  mr-2" id="view_log" btn-fn="view_log">View Log</button>
        </div>
        <div class="actions-dashboard table-ct">
          <table id="zero_config" class="table VQ Request Listing vq-request-listing-tb nowrap">
            <thead>
              <tr>
                <th>No.</th>
                <th>Type</th>
                <th>Level</th>
                <th>Start days</th>
                <th><strong>Frequency days</strong></th>
                <th><strong>Action</strong></th>
              </tr>
            </thead>
            <tbody>
              @foreach($listdata as $k => $item)
                  <tr>
                    <td>{{$k+1}}</td>
                    <td>{{$item->type}}</td>
                    <td>{{$item->level}}</td>
                    <td>{{$item->start_days}}</td>
                    <td>{{$item->frequency_days}}</td>
                    <td>
                      <a href="{{ url('initiator/approval-email-schedule-edit',['id'=>$item->id]) }}" class="btn btn-primary btn-sm">Edit</a>
                      <!-- <a onclick="deleteInstitutionHandler(<?= $item->id?>)" class="btn btn-danger btn-sm text-white">Delete</a> -->
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
                      <td>
                        @php
                          $details1 = json_decode($item->activity, true);
                          
                            $action = (isset($details1['action'])) ? $details1['action'] : '-';
                            
                          
                        @endphp
                        @if($details1)
                        {{ $action }}
                        @endif
                      </td>
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
                                      <div class="mb-2"><strong>Level:</strong> {{ $details['level'] ?? 'N/A' }}</div>
                                      <div class="mb-2"><strong>Type:</strong> {{ $details['type'] ?? 'N/A' }}</div>
                                      <div class="mb-2"><strong>Changed Start Days:</strong> {{ $details['changed_start_days'] ?? '-' }}</div>
                                      <div class="mb-2"><strong>Pervious Start Days:</strong> {{ $details['pervious_start_days'] ?? '-' }}</div>
                                      <div class="mb-2"><strong>Changed Frequency Days:</strong> {{ $details['changed_frequency_days'] ?? '-' }}</div>
                                      <div class="mb-2"><strong>Pervious Frequency Days:</strong> {{ $details['pervious_frequency_days'] ?? '-' }}</div>
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
            <a class="btn orange-btn big-btn" data-dismiss="modal">Close</a>
          </div>
          
        </div>
      </div>
    </div>
</div>
<script src="{{asset('frontend/js/select2.js')}}"></script>
<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script>
  $("#zero_config").DataTable(
    {  
      "pageLength": 50,
      'language': {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },
      // scrollX: true,
    }
  );

  $("#zero_config_view_log").DataTable(
    {  
      "pageLength": 50,
      'language': {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },
      // scrollX: true,
    }
  );

  $('#view_log').on('click', function(){
    $('#view_log_modal').modal({backdrop: 'static', keyboard: false}, 'show');
  });
  $(document).on('click', '.view-details-btn', function () {
    // Close any open collapses
    $('.collapse').collapse('hide');

    // Open the clicked collapse
    const target = $(this).attr('data-target');
    $(target).collapse('show');
  });

  function deleteInstitutionHandler(id) {
    let del = confirm('Do you really want to delete?');
    if (del) {
      document.location.href = '/initiator/approval-email-schedule-delete/' + id;
    }
  }
</script>
@endsection
