@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
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
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
          <div class="collapse navbar-collapse show" id="navbarSupportedContent">
              <ul class="navbar-nav dashboard-nav">
                  <li class="nav-item active"><a class="nav-link" href="{{route('initiator_dashboard')}}"> <img src="../../admin/images/back.svg">Duration Master</a></li>
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
          Duration Master
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
      <div class="col">
        <div>&nbsp;</div>
        <div class="col-md-12 d-flex ">
          <a class="orange-btn-bor" href="{{url('/initiator/duration')}}">Add Duration</a>
          <button class="orange-btn action_btn  mr-2" id="view_log" btn-fn="view_log">View Log</button>
        </div>
        <div class="actions-dashboard table-ct">
          <table
            id="zero_config"
            class="table VQ Request Listing vq-request-listing-tb nowrap"
          >
            <thead>
              <tr>
                <th><strong>No.</strong></th>
                <th><strong>Type</strong></th>
                <th><strong>Level</strong></th>
                <th><strong>Start Date</strong></th>
                <th><strong>End Date</strong></th>
                <th><strong>Days</strong></th>
                <th><strong>Actions</strong></th>
              </tr>
            </thead>
            <tbody>
                @foreach($data as $k => $item)
              <tr>
                <td>{{$k+1}}</td>
                <!-- <td>{{$item->id}}</td> -->
                <td>{{$item->type}}</td>
                <td>{{$item->level}}</td>
                <td>{{ $item->start_date ? \Carbon\Carbon::parse($item->start_date)->format('d M Y') : '-' }}</td>
                <td> {{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('d M Y') : '-' }}</td>
                <td>{{$item->days}}</td>
                <td>
                  <a href="{{url('initiator/duration-edit',['id'=>$item->id])}}" class="btn btn-primary btn-sm">Edit</a><a onclick="deleteDurationHandler(<?= $item->id?>)" class="btn btn-danger btn-sm text-white d-none">Delete</a>
                </td>
              </tr>
              @endforeach
            </tbody>
            <!-- <tfoot>
            <tr>
                <th><strong>No.</strong></th>
                <th><strong>Type</strong></th>
                <th><strong>Level</strong></th>
                <th><strong>Days</strong></th>
                <th><strong>Action</strong></th>
              </tr>
            </tfoot> -->
          </table>
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
            <table class="table  VQ Request Listing vq-request-listing-tb nowrap" id="zero_config1">
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
                          $details = json_decode($item->activity, true);
                          $changedToArray = $details['changed_to'] ?? [];
                          $hasCreated = false;
                          $hasDeleted = false;
                          // Check if any 'changed_to' item has action 'created'
                          foreach ($changedToArray as $change) {
                              if (isset($change['action']) && $change['action'] === 'created') {
                                  $hasCreated = true;
                                  break;
                              }
                              if (isset($change['action']) && $change['action'] === 'deleted') {
                                  $hasDeleted = true;
                                  break;
                              }
                          }
                      @endphp

                      @if($hasCreated)
                          Duration added
                      @elseif($hasDeleted)
                          Duration Deleted
                      @else 
                          Duration updated
                      @endif</td>
                      <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y H:i:s') }}</td>
                      <td style="text-align: center">{{ $item->emp_code }}-{{ $item->emp_name }}</td>
                      <td>
                          <button class="btn btn-info btn-sm view-details-btn" type="button" data-toggle="collapse" data-target="#details{{ $loop->iteration }}" aria-expanded="false" aria-controls="details{{ $loop->iteration }}">
                            View Details
                          </button>

                          <!-- Collapsible section for JSON details -->
                          <div class="collapse mt-2" id="details{{ $loop->iteration }}" style="max-width: 400px; max-height: 300px; overflow-y: auto;">
                              <div class="bg-light p-2 rounded">
                                  @php
                                      // Decode the JSON activity field
                                      $details = json_decode($item->activity, true);
                                      
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
                                    
                                    <hr>
                                    <h6>Change Details:</h6>
                                    @foreach($details['changed_to'] as $change)
                                        <div class="border p-2 mb-2 bg-white rounded">
                                            <div><strong>Action:</strong> {{ ucfirst($change['action']) }}</div>
                                            <div><strong>Level:</strong> {{ $change['level'] }}</div>
                                            <div><strong>Type:</strong> {{ $change['type'] }}</div>
                                            @if(isset($change['old_values']) && is_array($change['old_values']))
                                            <div class="mt-2">
                                                <strong>Old Values:</strong>
                                                <ul>
                                                    @foreach($change['old_values'] as $key => $value)
                                                        <li>
                                                            {{ ucfirst($key) }}:
                                                            @if(str_contains($key, 'date') && $value)
                                                                {{ \Carbon\Carbon::parse($value)->format('d M Y') }}
                                                            @else
                                                                {{ $value }}
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            @endif
                                            @if(isset($change['new_values']) && is_array($change['new_values']))
                                            <div>
                                                <strong>New Values:</strong>
                                                <ul>
                                                    @foreach($change['new_values'] as $key => $value)
                                                         <li>
                                                            {{ ucfirst($key) }}:
                                                            @if(str_contains($key, 'date') && $value)
                                                                {{ \Carbon\Carbon::parse($value)->format('d M Y') }}
                                                            @else
                                                                {{ $value }}
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            @endif
                                        </div>
                                    @endforeach
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
<script type="text/javascript">
  $(document).ready(function(){
    $("#zero_config").DataTable(
    {  
      "pageLength": 50,
      
      'language': {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },
      // scrollX: true,
    })
    /*$('.copyright').addClass('copyright_inc');
    $('#admin_main_menu').on('click', function(){
      $('.copyright').toggleClass('copyright_inc');
    })*/
    $('#view_log').on('click', function(){
      $('#view_log_modal').modal('show');
    })
    $(document).on('click', '.view-details-btn', function () {
      // Close any open collapses
      $('.collapse').collapse('hide');
      
      // Open the clicked collapse
      const target = $(this).attr('data-target');
      $(target).collapse('show');
    });
    $("#zero_config1").DataTable({  
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
  })
  function deleteDurationHandler(id){
    let del = confirm('Do you really want to delete?');
    if(del){
      document.location.href = '/initiator/duration-delete/'+id;
    }
  }
</script>
<style type="text/css">
  /*.copyright_inc
  {
    top:62rem !important;
  }*/
    .copy-center{
        position:inherit !important;
        margin: 20px  0;
    }
    div.dataTables_wrapper {
        max-width: 1357px;
        width: 100%;
        margin: 0 auto;
    }
</style>
@endsection