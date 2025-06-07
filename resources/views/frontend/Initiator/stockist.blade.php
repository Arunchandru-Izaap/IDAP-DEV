
@extends('layouts.frontend.app')
@section('content')
<style>
 #loader1 {
    background-image: url(../images/loader.gif);
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

.nav-link.active {
    font-weight: bold;
    color: #007bff; /* Change to your preferred color */
    background-color: rgba(0, 123, 255, 0.1); /* Optional background color */
}
.select2-container--default .select2-selection--single {
    height: calc(1.5em + 0.75rem + 2px);
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: calc(1.5em + 0.75rem + 2px);
}
.select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: #495057;
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
<div id="page-content-wrapper">
    <!-- Top navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                <ul class="navbar-nav dashboard-nav">
                    <li class="nav-item active">
                        <a class="nav-link" href="{{route('initiator_dashboard')}}">
                            <img src="../../admin/images/back.svg">Stockist Update
                        </a>
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
        <li class="{{ request()->routeIs('stockist_initiator') ? 'active' : '' }}">
            <a href="{{ route('stockist_initiator') }}">Stockist Update</a>
        </li>
    </ul>
    <div class="container-fluid">
        @if(Session::has('status'))
            <div class="alert alert-success mt-3" role="alert" >
                {{Session::get('message')}}
            </div>
            @elseif($errors->any())
            <div class="alert alert-danger mt-3" role="alert" >
                {{$errors->first('message')}}
            </div>
        @endif
        <div class="row">
            <div class="col-md-12 d-flex send-quotation">
                <div class="cancel-btn ml-auto">
                  <button class="orange-btn action_btn  mr-2" id="view_log" btn-fn="view_log">View Log</button>
                </div>
            </div>
            <div id='loader1' style='display: none;'>
                  
            </div>
            <div class="col mt-2">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <button class="nav-link active" id="btn-institution-wise"  data-toggle="tab" data-target="#intutionwise" role="tab" aria-controls="intutionwise" aria-selected="true" >Institution Wise</a></button>
                    </li>
                    <li class="nav-item">
                       <button class="nav-link " id="btn-stockist-wise" data-toggle="tab" data-target="#stockistwise" role="tab" aria-controls="stockistwise" aria-selected="false">Stockist Wise</button>
                    </li>  
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class= "tab-pane fade show active" id="intutionwise">
                        <div class="card">
                            <form class="form-horizontal" method="post" id="updateStockistForm" >
                              <meta name="csrf-token" content="{{ csrf_token() }}">
                             <div class="container">
                          
                               </div>
                                <div class="card-body">
                                    <h4 class="card-title">Institution Wise</h4>
                                    <div class="form-group row">
                                        <label for="parent_institution_id" class="col-sm-3 text-end control-label col-form-label">Institution</label>
                                        <div class="col-sm-4">
                                            <select name="institution" id="institution" class="js-example-basic-single form-control" required="">
                                                <option value="">Select Institution</option>
                                                @foreach($data['current_cycle_institutes'] as $item)
                                                    <option value="{{ $item->INST_ID}}">
                                                        {{ $item->INST_NAME }} - {{ $item->INST_ID }}
                                                    </option>
                                                    @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row" id="stockists" style='display:none;'>
                                        <label for="lname" class="col-sm-3 text-end control-label col-form-label">Stockist</label>
                                        <div class="col-sm-4">
                                            <select name="stockist" id="stockist" class="form-control" required="">
                                                <option value="">Select Stockist</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row" id="payments" style='display:none;'>
                                        <label for="lname" class="col-sm-3 text-end control-label col-form-label">Mode of Payment</label>
                                        <div class="col-sm-4">
                                            <select name="payment" id="payment" class="form-control" required="">
                                                <option value="">Select Mode of Payment</option>
                                                <option value="DM" id="dm">DM</option>
                                                <option value="CN" id="cm">CN</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row" id="stockiesttypes" style='display:none;'>
                                        <label for="lname" class="col-sm-3 text-end control-label col-form-label">Stockist Type</label>
                                        <div class="col-sm-4">
                                            <select name="field2" id="stockist_type" class="form-control" required="">
                                                <option value="">Select Stockist Type</option>
                                                <option value="BOTH" id="both">Both</option>
                                                <option value="SPIL" id="spil">SPIL</option>
                                                <option value="SPLL" id="spll">SPLL</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row" id="serviceflags" style='display:none;'>
                                        <label for="stockist_type_flag" class="col-sm-3 text-end control-label col-form-label">Active:</label>
                                        <input type="checkbox" id="stockist_type_flag" class="ml-3">
                                        <span class="checkmark"></span>
                                        
                                    </div>
                                </div>
                                <input type="hidden" name="" id="stockist_master_id">
                                <div class="border-top" style='display:none;' id="saveinitiator">
                                    <div class="card-body">
                                        <button type="submit" id="insitution_save" class="btn btn-primary">Save</button>
                                        <a href="/initiator">
                                            <button type="button" class="btn btn-primary">Cancel</button>

                                        </a>
                                         <a href="javascript:void(0)" id="downlaod">
                                        <button type="button" class="btn btn-primary">Download Cover Letter</button>
                                        </a>
                                    </div>
                                </div>    
                            </form>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="stockistwise" >
                        <div class="card">
                           <form class="form-horizontal" method="post" id="updateStockistForms">
                                <meta name="csrf-token" content="{{ csrf_token() }}">
                                <div class="card-body">
                                    <h4 class="card-title">Stockist Wise</h4>
                                    <div class="form-group row">
                                        <label for="parent_institution_id" class="col-sm-3 text-end control-label col-form-label">All Active Stockist</label>
                                        <div class="col-sm-4">
                                            <select name="stockists_wise" id="stockistss" class="js-example-basic-single form-control"    data-placeholder="Select stockist" required="">
                                               <option value="">Select Stockist</option>
                                                @foreach($test['currentCycleInstitutesNewCounterRts'] as $item)

                                                    <option value="{{ $item->stockist_code}}">
                                                        {{ $item->stockist_name }} - {{ $item->stockist_code }}
                                                   </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>


                                    <div class="form-group row">
                                        <label for="lname" class="col-sm-3 text-end control-label col-form-label">Stockist Type</label>
                                        <div class="col-sm-4">
                                            <select name="field2" id="stockist_types" class="form-control" required="">
                                                <option value="">Select Stockist Type</option>
                                                <option value="BOTH" id="bothh">Both</option>
                                                <option value="SPIL" id="spill">SPIL</option>
                                                <option value="SPLL" id="splll">SPLL</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>



                                <div class="border-top" style='display:none;' id="savestockist">
                                    <div class="card-body">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <a href="/initiator">
                                            <button type="button" class="btn btn-primary">Cancel</button>
                                        </a>
                                        <a href="" id="downlaods">
                                        <button type="button" class="btn btn-primary">Download Cover Letter</button>
                                        </a>
                                        <a href="javascript:void(0)" id="view_insititution_stockist">
                                        <button type="button" class="btn btn-primary">View Institionwise Stockist</button>
                                        </a>
                                    </div>
                                </div>
                           </form>
                        </div>
                    </div>
                </div>
            </div>

    <div class="modal show" id="successModal">
      <div class="modal-dialog modal-dialog-centered model-pop-ct">
        <div class="modal-content">
          <!-- Modal Header -->
          <div class="modal-header border-0">
              <div class="tich-logo">
              <img style="width: 50px;" src="{{ asset('admin/images/ticket.svg') }}">
              </div>
          </div>
          <!-- Modal body -->
          <div class="modal-body">
            <p>Updation successfully done</p>
            <a class="btn orange-btn" href="{{route('initiator_listing')}}" id="">VQ Request Details</a>
            <a class="btn orange-btn" href="javascript:void(0)" id="" data-dismiss="modal">Close</a>
          </div>
          
          
        </div>
      </div>
    </div>
    <div class="modal show" id="successModals">
        <div class="modal-dialog modal-dialog-centered model-pop-ct">
            <div class="modal-content">
              <!-- Modal Header -->
              <div class="modal-header border-0">
                  <div class="tich-logo">
                  <img style="width: 50px;" src="{{ asset('admin/images/ticket.svg') }}">
                  </div>
              </div>
              <!-- Modal body -->
              <div class="modal-body">
                <p>Updation successfully done</p>
                <a class="btn orange-btn" href="{{route('initiator_listing')}}" id="">VQ Request Details</a>
                <a class="btn orange-btn" href="javascript:void(0)" id="" data-dismiss="modal">Close</a>
              </div>
              
              
            </div>
        </div>
    </div>
    <div class="modal show" id="view_log_modal">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-custom-position" style="max-width: 1000px;">
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
                          <td>{{ ucwords(str_replace('_', ' ', $item->type)) }}</td>
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
                                          <div class="mb-2"><strong>Changed by:</strong> {{ $item->emp_code }}-{{ $item->emp_name }}</div>
                                          <div class="mb-2"><strong>Browser:</strong> {{ $shortUserAgent }}</div>
                                          <div class="mb-2"><strong>Institution: </strong> {{ $details['institution'] }}</div>
                                          <div class="mb-2"><strong>Stockist: </strong> {{ $details['stockist'] }}</div>
                                           @if(!empty($details['changes']))
                                                <div class="mt-3">
                                                    <strong>Changes Made:</strong>
                                                    <ul>
                                                        @foreach($details['changes'] as $field => $change)
                                                            <li>
                                                                <strong>{{ ucwords(str_replace('_', ' ', $field)) }}:</strong>
                                                                From <em>
                                                                    @if($change['from'] === 0) 
                                                                        Inactive
                                                                    @elseif($change['from'] === 1) 
                                                                        Active
                                                                    @else 
                                                                        {{ $change['from'] }}
                                                                    @endif
                                                                </em> 
                                                                to <em>
                                                                    @if($change['to'] === 0) 
                                                                        Inactive
                                                                    @elseif($change['to'] === 1) 
                                                                        Active
                                                                    @else 
                                                                        {{ $change['to'] }}
                                                                    @endif
                                                                </em>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
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
    <div class="modal show" id="view_institutionwise_modal">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-custom-position" style="max-width: 1000px;">
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
                      <th style="text-align: center">Stockist</th>
                      <th style="text-align: center">Institution</th>
                      <th style="text-align: center">Stockist Type</th>
                      <th style="text-align: center">Status</th>
                    </tr>
                  </thead>
                  <tbody id="appendData">
                    
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
    $('.js-example-basic-single').select2({ width: '100%' });
    $(document).ready(function() {
        $('#view_log').on('click', function(){
            $('#view_log_modal').modal('show');
        })
        $('#view_insititution_stockist').on('click', function(){
            $('#view_institutionwise_modal').modal('show');
        })
        var dataTable = $('#zero_config1').DataTable({  
          "pageLength": 50,
          columnDefs: [
            {
                targets: 0, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'center',
                        'width': '10px',
                    });
                }
            },
            {
                targets: 1, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'center',
                        'width': '20px',
                    });
                } 
            },
            {
                targets: 2, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'center',
                        'width': '20px',
                    });
                } 
            },
            {
                targets: 3, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'center',
                        'width': '20px',
                    });
                } 
            },
          ],
          'language': {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },
        });
        $(document).on('click', '.view-details-btn', function () {
            // Close any open collapses
            $('.collapse').collapse('hide');
            
            // Open the clicked collapse
            const target = $(this).attr('data-target');
            $(target).collapse('show');
        });
        $('#downlaod').click(function() {
            var institutionId = $('#institution').val();
            var stockistType = $('#stockist_type').val();
            var isChecked = $('#stockist_type_flag').is(':checked');
            var checkboxValue = isChecked ? 1 : 0;
            var payment = $('#payment').val();
            var stockist_master_id = $('#stockist_master_id').val();
            if(stockist_master_id == '')
            {
               return
            }
            else
            {
                
            }
            $('#downlaod').prop('href', '/initiator/download_institution/' + stockist_master_id)
            // Disable the button and show loading indication
            //$(this).prop('disabled', true).text('Loading...');
            

            /*$.ajax({
                url: '/initiator/ajax/stockists/' + institutionId,
                type: 'PUT',
                data: {
                    stockist_type: stockistType,
                    payment_mode: payment,
                    stockist_type_flag: checkboxValue,
                },
                success: function(response) {
                    $('#downlaod').prop('href', './initiator/download/' + institutionId)
                        .text('Download Now') // Change button text back
                        .prop('disabled', false); // Re-enable button
                },
                error: function(xhr) {
                    // Handle the error
                    alert('An error occurred. Please try again.'); // Display error message
                    $('#download').prop('disabled', false).text('Download'); // Reset button state
                }
            });*/
        });


                 
        $('#institution').on('change', function() {
            var institutionId = $(this).val();
            if(institutionId != '')
            {
                var stockiest = $("#stockists").show();
                var payments = $("#payments").show();
                var stockiesttypes = $("#stockiesttypes").show();
                var serviceflags = $("#serviceflags").show();
                $("#dm").removeAttr("selected");
                $("#cm").removeAttr("selected");
                $("#spll").removeAttr("selected");
                $("#spil").removeAttr("selected");
                $('#both').removeAttr("selected");
                $('#stockist_type').val('')
                $('#payment').val('')
                $('#stockist_type_flag').prop('checked', false) 
                $('#saveinitiator').show();
                $('#loader1').show();
                $('#stockist_master_id').val('');
                if (institutionId) {
                    $.ajax({
                        url: '/initiator/ajax/stockists/' + institutionId,
                        type: 'GET',
                        success: function(stockists) {
                            var html = '<option value="">Select Stockist</option>';
                            $.each(stockists, function(key, stockist) {
                                html += '<option value="' + stockist.id + '">' + stockist.stockist_name + '-' + stockist.stockist_code + '</option>';
                            });
                            $('select[name="stockist"]').html(html).prop('disabled', false);
                            $('#loader1').hide();
                        },
                        error: function(xhr) {
                            console.error('Error fetching stockists:', xhr);
                            $('#loader1').hide();
                        }
                    });
                } else {
                    $('select[name="stockist"]').html('<option value="">Select Stockist</option>').prop('disabled', true);
                }
            }
            else
            {
                $("#dm").removeAttr("selected");
                $("#cm").removeAttr("selected");
                $("#spll").removeAttr("selected");
                $("#spil").removeAttr("selected");
                $('#both').removeAttr("selected");
                $('#stockist_type_flag').prop('checked', false) 
                $('#saveinitiator').hide();
                $('#stockist').html('<option value="">Select Stockist</option>')
                $('#stockist_master_id').val('');
                $('#stockist_type').val('')
                $('#payment').val('')
            }
        });

        $('#stockist').on('change', function() {
            var stockistId = $(this).val();
            if(stockistId != '')
            {
                $("#spll").removeAttr("selected");
                $("#spil").removeAttr("selected");
                $('#both').removeAttr("selected");
                $('#saveinitiator').show();
                $('#stockist_type_flag').prop('checked', false) 
                $('#loader1').show();
                $('#stockist_master_id').val('');
                if (stockistId) {
                    $.ajax({
                        url: '/initiator/ajax/payment/' + stockistId,
                        type: 'GET',
                        success: function(payments) {
                            var html = '<option value="">Select Mode of Payment</option>';
                            $.each(payments, function(key, payment) {
                                /*if (payment.payment_mode == "DM") {
                                    $('#dm').attr('selected', 'true');
                                }
                                if (payment.payment_mode == "CM") {
                                    $('#cm').attr('selected', 'true');
                                }
                                if (payment.payment_mode == null) {
                                   $("#dm").removeAttr("selected");
                                   $("#cm").removeAttr("selected");
                                }*/
                                $('#payment').val(payment.payment_mode)
                                /*if (payment.stockist_type == "SPIL") {
                                    $('#spil').attr('selected', 'true');
                                }
                                if (payment.stockist_type == "SPLL") {
                                    $('#spll').attr('selected', 'true');
                                }*/

                                if (payment.stockist_type == '' || payment.stockist_type == null) {
                                   /*$("#spll").removeAttr("selected");
                                   $("#spil").removeAttr("selected");
                                   $('#both').attr('selected', 'true');
                                   $('#both').show();*/
                                   $('#stockist_type').val('BOTH')
                                }
                                else
                                {
                                    $('#stockist_type').val(payment.stockist_type)
                                }
                                if (payment.stockist_type_flag === 1) {
                                    $('#stockist_type_flag').prop('checked', true);
                                }
                                if (payment.stockist_type_flag === 0) {
                                    $('#stockist_type_flag').prop('checked', false);
                                }
                                $('#loader1').hide();
                                $('#stockist_master_id').val(payment.id);
                            });
                        },
                        error: function(xhr) {
                            console.error('Error fetching payment modes:', xhr);
                            $('#loader1').hide();
                        }
                    });
                }
            }
            else
            {
                $("#dm").removeAttr("selected");
                $("#cm").removeAttr("selected");
                $("#spll").removeAttr("selected");
                $("#spil").removeAttr("selected");
                $('#both').removeAttr("selected");
                $('#stockist_type_flag').prop('checked', false) 
                $('#stockist_type').val('')
                $('#payment').val('')
                $('#stockist_master_id').val('');
                $('#saveinitiator').hide();
            }
        });

        $('#stockistss').on('change', function() {
            var stockistId = $(this).val();
            if(stockistId != '')
            {
                $('#savestockist').show();
                $('#downlaods').prop('href', '/initiator/download_stockist/'+stockistId)
                $('#loader1').show();
                if (stockistId) {
                    $.ajax({
                        url: '/initiator/ajax/payment/' + stockistId,
                        type: 'GET',
                        success: function(payments) {
                            //$('#appendData').empty();
                            dataTable.clear().draw();
                            dataTable.search('').draw();
                            if (payments.length > 0) {
                                $.each(payments, function(key, payment) {
                                    /*var row = '<tr>' +
                                        '<td style="text-align: center">' + (key + 1) + '</td>' + 
                                        '<td style="text-align: center">' + payment.stockist_name +'-'+payment.stockist_code + '</td>' + 
                                        '<td style="text-align: center">' +payment.hospital_name +'-'+payment.institution_code +  '</td>' + 
                                        '<td style="text-align: center">' + (payment.stockist_type == null ? 'Both' : payment.stockist_type) + '</td>' + 
                                        '</tr>';
                                    $('#appendData').append(row);*/
                                    dataTable.row.add([
                                        key + 1,
                                        payment.stockist_name +'-'+payment.stockist_code,
                                        payment.hospital_name +'-'+payment.institution_code,
                                        payment.stockist_type == null ? 'Both' : payment.stockist_type,
                                        payment.stockist_type_flag == 1 ? 'Active' : 'Inactive'
                                    ]).draw(false);
                                });
                                
                            } else {
                                /*var emptyRow = '<tr><td colspan="4" style="text-align: center">No data available</td></tr>';
                                $('#appendData').append(emptyRow);*/
                                dataTable.row.add([
                                    '', 'No data available', '', ''
                                ]).draw(false);
                            }
                            $('#loader1').hide();
                        },
                        error: function(xhr) {
                            console.error('Error fetching payment modes:', xhr);
                            $('#loader1').hide();
                        }
                    });
                }
            }
            else
            {
                $('#stockist_types').val('')
            }
        });
        $('#updateStockistForms').on('submit', function(e) {
            e.preventDefault();
            var stockistCode = $('#stockistss').val();
            var stockistTypes = $('#stockist_types').val();
            $('#loader1').show();
            $.ajax({
                url: '/initiator/stockists/' + stockistCode,
                type: 'PUT',
                data: {
                     stockist_type: stockistTypes,
                     
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#loader1').hide();
                    //$('#result').html('<div class="alert alert-success">Institution wise updated successfully</div>');
                    $('#successModals').modal({backdrop: 'static', keyboard: false},'show');
                    //$('#result').show();
                },
                error: function(xhr) {
                    var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred.';
                    $('#result').html('<div class="alert alert-success">Fill all fields</div>');
                    $('#loader1').hide();
                }
            });
        });
        $('#updateStockistForm').on('submit', function(e) {
            e.preventDefault();
            var stockistType = $('#stockist_type').val();
            var payment_mode = $('#payment').val();
            var stockist_master_id = $('#stockist_master_id').val()
            var isActive = $('#stockist_type_flag').is(':checked') ? true : false;
            var selectedInstitution = $('#institution option:selected').text().trim();
            if(stockist_master_id !== '' && stockistType !== '' && payment_mode !== '')
            {
                $('#loader1').show();
                var settings = {
                    "url": "/initiator/stockist_update_institution",
                    "method": "POST",
                    "timeout": 0,
                    "headers": {
                        "Accept-Language": "application/json",
                    },
                    "data": {
                        "_token": "{{ csrf_token() }}",
                        "stockist_master_id": stockist_master_id,
                        "payment_mode": payment_mode,
                        "stockistType": stockistType,
                        'isActive':isActive,
                        'selectedInstitution':selectedInstitution
                    }
                };

                $.ajax(settings).done(function (response) {
                    $('#loader1').hide(); 
                    $('#successModals').modal({backdrop: 'static', keyboard: false},'show');
                    //console.log(response);
                }).fail(function (jqXHR, textStatus) {
                    var errorMessage = jqXHR.responseJSON ? jqXHR.responseJSON.message : 'An error occurred.';
                    alert(errorMessage);
                    $('#loader1').hide();
                });
            }
            else
            {
                alert('Error Fetching all records, Please try again');
            }
        });
        $("#zero_config").DataTable({  
          "pageLength": 50,
          columnDefs: [
            {
                targets: 0, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'center',
                        'width': '10px',
                    });
                }
            },
            {
                targets: 1, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'center',
                        'width': '20px',
                    });
                } 
            },
            {
                targets: 2, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'center',
                        'width': '20px',
                    });
                } 
            },
          ],
          'language': {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },
        });
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var tableId = settings.nTable.getAttribute('id');
            if (tableId !== 'zero_config') {
                var table = $("#zero_config1").DataTable();
                var searchTerm = table.search().trim().toLowerCase();
                if (!searchTerm) {
                    return true;  
                }
                var secondColumnData = data[4].trim().toLowerCase();
                if (secondColumnData === searchTerm) {
                    return true;
                }
                for (var i = 0; i < data.length; i++) {
                    if (i !== 4) { 
                        if (data[i].trim().toLowerCase().includes(searchTerm)) {
                            return true;
                        }
                    }
                }
                return false;
            }
            var table = $("#zero_config").DataTable();
            var searchTerm = table.search().trim().toLowerCase();
            if (!searchTerm) {
                return true;  
            }
            var secondColumnData = data[1].trim().toLowerCase();
            if (secondColumnData === searchTerm) {
                return true;
            }
            for (var i = 0; i < data.length; i++) {
                if (i !== 1) { 
                    if (data[i].trim().toLowerCase().includes(searchTerm)) {
                        return true;
                    }
                }
            }
            return false;  
        });
    });

</script>
@endsection
