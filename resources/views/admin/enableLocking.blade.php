@extends('layouts.admin.app')
@section('content')
<!-- <div class="container"><h2>Initiator Dashboard demo</h2></div> -->
<!-- Page content wrapper-->
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
</style>
  <div class="container-fluid">
    <div class="row">
    <div class="card">
    <div class="card-body actions-dashboard table-ct quotation-details">
      <div class="col-md-12 d-flex send-quotation justify-content-end">
        <button class="btn btn-primary   mr-2" id="view_log" btn-fn="view_log" style="margin-right: 1rem;">View Log</button>
        <button id="approve" class="btn btn-primary ml-auto" disabled>
          SAVE CHANGES
        </button>
      </div>
      <!-- <div class="col"> -->
        <h3> Enable / Disable the Locking Period by clicking the checkbox</h3>
        <ul class="checkobx-ct d-flex">
          
            <li class="checkbox">
              <input type="checkbox" id="alter_checkbox" class="single-checkbox" name="locking_period" value="" @if($data->is_enabled == 'Y') checked @endif  ><label for="alter_checkbox" id="locking_text" style="cursor: pointer"> Currently Locking Period is @if($data->is_enabled == 'Y') Enabled @else Disabled @endif </label>
            </li>
          </ul>
      <!-- </div> -->
    </div>
    </div>
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
            <a class="close cancel_btn modal_close" data-bs-dismiss="modal">
                <img src="{{ asset('admin/images/close.svg') }}" alt="">
            </a>
          </div>
          <!-- Modal body -->
          <div class="modal-body text-center">
            <p class="border-0">Locking period successfully <label id="lock_status"></label>.</p>

            <a class="btn btn-primary orange-btn big-btn cancel_btn">Go to Dashboard</a>
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
          <button type="button" class="close" data-bs-dismiss="modal">
              <img src="{{ asset('admin/images/close.svg') }}" alt="" style="width:40px;">
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
  $('#approve').click(function(){
    var locking_period = $("input:checkbox[name=locking_period]").is(":checked");
      // Add ajax call for updating price here
    var userConfirmed = confirm("Are you sure you want to change the locking period?");
    if(userConfirmed) {
      var settings = {
        "url": "/admin/change_locking_period",
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
        var myModal = new bootstrap.Modal(document.getElementById('submited'), {
          backdrop: 'static',
          keyboard: false
        });

        myModal.show();
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
    window.location.href = "{{ route('home') }}";
	});
  $('#view_log').on('click', function(){
    $('#view_log_modal').modal('show');
  })
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
</script>
@endpush
