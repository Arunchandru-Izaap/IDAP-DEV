@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
  .no-data-message {/*empty table custom  message*/
      font-weight: bold; 
      text-align: center; 
  }
  .yellow-bg
  {
    background: #f9f938 !important;
  }
  .disabled { pointer-events: none; opacity: 0.6; }
    table.dataTable.dt-checkboxes-select tbody tr,
    table.dataTable thead th.dt-checkboxes-select-all,
    table.dataTable tbody td.dt-checkboxes-cell {
      cursor: pointer;
    }
    .table-ct .dataTables_length label select {
        border: solid 1px #e77925;
        max-width: 81px;
        height: 31px;
        margin: 0 10px;
        -webkit-appearance: none;
        -moz-appearance: none;
        background: transparent;
        background-image: url(../../admin/images/downar.svg);
        background-repeat: no-repeat;
        background-position-x: 90%;
        background-position-y: 50%;
        padding-right: 2rem;
    }
    .table-ct .dataTables_wrapper .dataTables_paginate {
      align-items: center;
    }
    .table-ct .dataTables_wrapper .dataTables_paginate a {
      border: none;
      padding: 0;
      margin: 10px 5px;
    }
    .table-ct .dataTables_wrapper .dataTables_paginate .paginate_button a:active {
      background: no-repeat;
      border: none;
      outline: none;
      box-shadow: none;
    }
    .table-ct .dataTables_wrapper .dataTables_paginate  span .paginate_button {
      border: solid 1px #e77925;
      font-size: 14px;
      font-weight: 600;
      font-stretch: normal;
      font-style: normal;
      letter-spacing: normal;
      text-align: center;
      color: #080406;
      border-radius: 50%;
      width: 35px;
      height: 35px;
      /*display: flex;
      align-items: center;*/
      padding: 5px;
     /* margin: 0;*/
      /*justify-content: center;*/
    }
    .table-ct .dataTables_paginate span .paginate_button:not(.current) {
      border: solid 1px #e77925;
      font-size: 14px;
      font-weight: 600;
      font-stretch: normal;
      font-style: normal;
      letter-spacing: normal;
      text-align: center;
      color: #080406;
      border-radius: 50%;
      margin: 0 5px;
    }
    .table-ct .dataTables_wrapper .dataTables_paginate a.previous:hover,
    .table-ct .dataTables_wrapper .dataTables_paginate a.next:hover {
        background: transparent;
        border: none;
        color: #000 !important;
    }
    .table-ct .dataTables_wrapper .dataTables_paginate span a:not(.current):hover {
        background: transparent;
        color: #000 !important;
    }
    .table-ct .dataTables_wrapper .dataTables_paginate a.current,.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
      background: #e77925;
      color: #fff !important;
      border: solid 1px #e77925;
    }
    .table-ct .dataTables_wrapper .dataTables_paginate .previous a, .table-ct .dataTables_wrapper .dataTables_paginate .next a {
      border: none;
      padding: 0;
    }
    .table-ct .dataTables_wrapper .dataTables_paginate .previous .disbpr, .table-ct .dataTables_wrapper .dataTables_paginate .previous .disbn, .table-ct .dataTables_wrapper .dataTables_paginate .next .disbpr, .table-ct .dataTables_wrapper .dataTables_paginate .next .disbn {
      display: none;
    }
    .table-ct .dataTables_wrapper .dataTables_paginate .previous .left-block, .table-ct .dataTables_wrapper .dataTables_paginate .previous .neblk, .table-ct .dataTables_wrapper .dataTables_paginate .next .left-block, .table-ct .dataTables_wrapper .dataTables_paginate .next .neblk {
      display: block;
    }
    .table-ct .dataTables_wrapper .dataTables_paginate .previous.disabled .disbpr, .table-ct .dataTables_wrapper .dataTables_paginate .previous.disabled .disbn, .table-ct .dataTables_wrapper .dataTables_paginate .next.disabled .disbpr, .table-ct .dataTables_wrapper .dataTables_paginate .next.disabled .disbn {
      display: block;
    }
    .table-ct .dataTables_wrapper .dataTables_paginate .previous.disabled .left-block, .table-ct .dataTables_wrapper .dataTables_paginate .previous.disabled .neblk, .table-ct .dataTables_wrapper .dataTables_paginate .next.disabled .left-block, .table-ct .dataTables_wrapper .dataTables_paginate .next.disabled .neblk {
      display: none;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:active {
      box-shadow:none !important;
    }
    #loader1
    {
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
    #pendingMessage
    {
      height: 150px;
      overflow: auto;
    }

    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      pointer-events: none; /* Prevent clicks from interacting with the overlay */
    }
    #ValidateMessage
    {
      padding: 17px;
    }
</style>
<div id="page-content-wrapper">
                <!-- Top navigation-->
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                            <ul class="navbar-nav dashboard-nav">
                                <li class="nav-item active"><a class="nav-link" href="{{route('initiator_listing')}}"> <img src="{{ asset('admin/images/back.svg')}}">Generate VQ for existing Product</a></li>
                            </ul>

                            <ul class="d-flex ml-auto user-name">
                                <li>
                                    <h3>{{Session::get('emp_name')}}</h3>
                                    <p>Initiator</p>
                                </li>

                                <li>
                                    <img src="{{ asset('admin/images/Sun_Pharma_logo.png') }}">
                                </li>
                            </ul>
                        </div>
                        
                    </div>

                </nav>
                <ul class="bradecram-menu">
                    <li><a href="{{route('initiator_dashboard')}}">
                        Home
                    </a></li>
                    <li class="">
                      <a href="{{route('initiator_listing')}}">
                        VQ Request Listing
                      </a>
                    </li>
                    <li class="">
                      <a href="#">
                        Generate VQ for existing Product
                      </a>
                    </li>
                </ul>
                <!-- Page content-->
                <div class="container-fluid">
                    <!-- <h1 class="mt-4">Simple Sidebar</h1> -->
                    <div class="row">
                      <div class="col-md-3 mr-20">
                        <label>Item name: </label>
                        <select id="item_code" class="js-example-basic-single itemCode" name="itemCode">
                          <option value=''> Select </option>
                           @foreach($data['old_product_data'] as $item)
                              <option value="{{ $item->item_code }}">{{ $item->sap_itemcode }} - {{ $item->brand_name }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-3 d-flex" style="align-items: center;margin-top: 31px;">
                        <ul class="d-flex list-unstyled">
                          <li>
                            <button class="view-data orange-btn-bor">
                              Fetch VQ
                            </button>
                          </li>
                        </ul>
                      </div>
                      <div class="col-md-6 d-flex">
                        <div class="cancel-btn ml-auto">
                          <button class="orange-btn" id="save_changes_btn" disabled>Save Changes</button>
                          <a href="#" class="orange-btn d-none" id="send_quotation" > Send Quotation </a>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                        <div id='loader1' style='display: none;'>
                              
                        </div>
                        <div class="col pd-20">
                            <div class="actions-dashboard table-ct">

                              <table class="table VQ Request Listing vq-request-listing-tb nowrap" id="zero_config">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th class="" style="width: 110px !important">Institution Name</th>
                                        <th class="w-110" style="width: 110px;">SAP Code</th>
                                        <th class="" style="width: 160px !important">Mother Brand</th>
                                        <th class="digitcenter">Item Code</th>
                                        <th class="digitcenter">Revision Count</th>
                                        <th class="" style="width: 160px !important">Brand</th>
                                        <th class="digitcenter">Disc. PTR (%)</th>
                                        <th class="digitcenter">RTH (Excl. GST)</th>
                                        <th>Stockist Code</th>
                                        <th>Stockist Name</th>
                                        <th>Mode of payment</th>
                                        <th>Net Discount Percent</th>
                                        <th class="digitcenter">App. GST(%)</th>
                                        <th class="digitcenter">L. Y. Disc.(%)</th>
                                        <th class="digitcenter">L. Y. Disc Rate</th>
                                        <th class="digitcenter">L. Y.MRP</th>
                                        <th class="digitcenter">MRP</th>
                                        <th class="digitcenter">C. Y. PTR</th>
                                        <th class="digitcenter">MRP Margin (%)</th>
                                        <th class="digitcenter">Type</th>
                                        <th>HSN Code</th>
                                        <th>Composition</th>
                                        <th class="d-none">vq_id</th>
                                        <th class="d-none">sku_id</th>
                                        <th class="d-none">stockist_id</th>
                                        <th class="d-none">update_flag</th>
                                        <th class="d-none">parent_vq_id</th>
                                        <th class="d-none">institution_id</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        
                                </tbody>
                              </table>
                            </div>
                        </div><!-- col close -->
                    </div>

<!-- view comments popup -->
<div class="modal show" id="showcmts">
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
          <p class="border-0 modal_heading"></p>

          <div class="data-layer comment-text">
            
          </div>
        </div>
      </div>
    </div>
</div>

<!-- view general comments popup -->
<div class="modal show" id="showgeneralcmts">
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
          <p class="border-0 modal_heading"></p>

          <div class="data-layer comment-text">
            
          </div>
        </div>
      </div>
    </div>
</div>
<div class="modal show" id="errorModal">
  <div class="modal-dialog modal-dialog-centered model-pop-ct" style="max-width: 1000px;">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header border-0">
          <div class="tich-logo">
          <img style="width: 50px;" src="{{ asset('admin/images/ticket.svg') }}">
          </div>
        <button type="button" class="close" data-dismiss="modal">
            <img src="{{ asset('admin/images/close.svg') }}">
        </button>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
        <p>Following item(s) have missing information:</p>
        <div id="pendingMessage"></div>
        <div id="" style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
          <a class="btn orange-btn d-none" href="javascript:void(0)" id="untickStockistButton">Untick Items with Missing Stockist</a>
          <a href="javascript:void(0)" class="btn orange-btn d-none" id="download_missing_stockist" title="Download Missing Stockist">Download Missing Stockist in Excel</a>
          <a class="btn orange-btn d-none" href="javascript:void(0)" id="untickpaymodeButton">Untick Items with Missing Payment mode</a>
          <a href="javascript:void(0)" class="btn orange-btn d-none" id="download_missing_paymode" title="Download Missing Payment mode">Download Missing Payment mode in Excel</a>
          <a class="btn orange-btn d-none" href="javascript:void(0)" data-dismiss="modal" id="">CLOSE</a>
        </div>
      </div>
      
      
    </div>
  </div>
</div>
<div class="modal show" id="errorModal1">
  <div class="modal-dialog modal-dialog-centered model-pop-ct" style="max-width: 700px;">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header border-0">
          <div class="tich-logo">
          <img style="width: 50px;" src="{{ asset('admin/images/ticket.svg') }}">
          </div>
          <button type="button" class="close" data-dismiss="modal">
            <img src="{{ asset('admin/images/close.svg') }}">
        </button>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
        <p>Following institution(s) have missing POC information:</p>
        <div id="pendingMessage1"></div>
        <div id="" style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
          <a class="btn orange-btn" href="javascript:void(0)" id="untickInstitutionButton">Untick Institution with Missing POC Details</a>
          <a href="javascript:void(0)" class="btn orange-btn" id="download_missing_poc" title="Download Missing POC">Download Missing POC in Excel</a>
          <a class="btn orange-btn d-none" href="javascript:void(0)" data-dismiss="modal" id="">CLOSE</a>
        </div>
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
        <p>Quotation Sent Successfully</p>
        <a class="btn orange-btn" href="{{route('initiator_listing')}}" id="">VQ Request Detail</a>
      </div>
      
      
    </div>
  </div>
</div>
<div class="modal show modal-overlay" id="idap_disc_tran_exist_modal" style="display:none">
    <div class="modal-dialog modal-dialog-centered model-pop-ct" style="max-width: 1000px;">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header border-0">
            <div class="tich-logo">
            <img style="width: 50px;" src="{{ asset('admin/images/ticket.svg') }}" alt="">
            </div>
            <button type="button" class="close cancel_btn1" data-dismiss="modal">
              <img src="{{ asset('admin/images/close.svg') }}" alt="">
          </button>
        </div>
        <!-- Modal body -->
        <div class="modal-body" id="idap_disc_tran_exist_html">
          
        </div>
        <p id="ValidateMessage">Your send to Quotation failed. Please connect with admin</p>
      </div>
    </div>
</div>


<!-- <script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script> -->
<script src="{{asset('frontend/js/select2.js')}}"></script>
<script>
  const Disc_margin_item_code = @json($data['DiscountMargin_datas']);
  console.log(Disc_margin_item_code);
  var table;
  var selected = [];
  var missingPaymentModeItems = [];
  var missingStockistDetailsItems = [];
  var missingPocItems = [];
  $(document).ready(function(){
    $('.js-example-basic-single').select2({ width: '100%' },{placeholder: 'Select Item Name'});
    $('.dataTables_filter label').contents().filter((_, el) => el.nodeType === 3).remove();
    $(".dataTables_filter label input").keyup(function(){
      $(".search-ct").css("opacity", "0");
    });
    $('.view-data').on('click',function(){
      selected = [];
      $('#selectAll').prop('checked',false)
      $('#save_changes_btn').attr('disabled', true)
      $('#send_quotation').addClass('d-none')
      if ($.fn.DataTable.isDataTable('#zero_config')) {
          // If it is, destroy it first
          $('#zero_config tbody').empty();
          $('#zero_config').DataTable().destroy();
          $('#zero_config tbody').empty();
      }
      if($('#item_code').val() == '')
      {
        alert('Please select any one item name')
        return;
      }
      init_table()
      $('#zero_config_length select').addClass('form-control form-control-sm')
    });
    $('#zero_config tbody').on("change", "input[type='checkbox']", function() {
      var checkbox = this;
      //updateSelectedRows(checkbox);
      enableBtn();
    });
    $('body').on("change", '.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]', function() {
      var isHeaderChecked = this.checked;
      $('#zero_config tbody').find("input[type='checkbox']").prop('checked', isHeaderChecked).trigger('change');
      enableBtn();
    });
    $('body').on('click','#zero_config tbody tr',function(){
      enableBtn();
    })
    $('#save_changes_btn').click(function(e){
      $('#loader1').show(); 
      e.preventDefault()
      selected = [];
      missingPaymentModeItems = [];
      missingStockistDetailsItems = [];
      missingPocItems = [];
      $('#pendingMessage').empty();
      $('#untickStockistButton').addClass('d-none')
      $('#untickpaymodeButton').addClass('d-none')
      $('#download_missing_stockist').addClass('d-none')
      $('#download_missing_paymode').addClass('d-none')
      var rowcollection = table.$(".dt-checkboxes:checked", {"page": "all"});
      rowcollection.each(function(index,elem){
          var row = $(elem).closest("tr");
          var sku_id = row.find('.sku_id').text();
          var vq_id = row.find('.vq_id').text();
          var item_code = row.find('.item-code').text();
          var ptr_percent = row.find('.disc_percent').text();
          var ptr_rate = row.find('.disc_rate').text();
          var cBalance = row.find('.cBalance').text();
          var mrp_margin = row.find('.mrp_margin').text();
          var mrp_value = row.find('.mrp').text();
          var update_flag = row.find('.update_flag').val();
          var payment_mode = row.find('.payment_mode').val()
          var stockist_code = row.find('.stockist_code').text()
          var stockist_name = row.find('.stockist_name').text()
          var hospital_name = row.find('.hospital_name').text()
          var net_discount_percent = row.find('.net_discount_percent').text()
          var parent_vq_id = row.find('.parent_vq_id').text()
          var stockist_id = row.find('.stockist_id').text()
          var rev_no = row.find('.rev_no').text()
          var institution_id = row.find('.institution_id').text()
          if (payment_mode === '') {
            var missingInfo = item_code + ' in ' + hospital_name+'-'+institution_id+ ' ' + stockist_name + ' (Payment Mode not selected)';
            missingPaymentModeItems.push({sku_id: sku_id, message: missingInfo, institution_id: institution_id, stockist_id: stockist_id});
          }
          if (stockist_id === '') {
            var missingInfo = item_code + ' in ' + hospital_name+'-'+institution_id + ' ' + stockist_name + ' (Missing Stockist Details)';
            missingStockistDetailsItems.push({sku_id: sku_id, message: missingInfo, institution_id: institution_id, stockist_id: stockist_id});
          }
          if (payment_mode !== '' && stockist_id !== '') {
            selected.push({
              sku_id: sku_id,
              item_code: item_code,
              ptr_percent: ptr_percent,
              ptr_rate: ptr_rate,
              cBalance: cBalance,
              mrp_margin: mrp_margin,
              mrp_value: mrp_value,
              vq_id: vq_id,
              update_flag: update_flag,
              stockist_id: stockist_id,
              stockist_code: stockist_code,
              stockist_name: stockist_name,
              payment_mode: payment_mode,
              net_discount_percent: net_discount_percent,
              parent_vq_id: parent_vq_id,
              rev_no: rev_no,
              institution_id: institution_id,
              hospital_name: hospital_name,
            });
          }
        });
        if(missingStockistDetailsItems.length > 0) {
          $('#loader1').hide();
          var missingStockistDetailsHtml = '<ul>';
          missingStockistDetailsItems.forEach(function(item) {
            missingStockistDetailsHtml += '<li>' + item.message + '</li>';
          });
          missingStockistDetailsHtml += '</ul>';
          $('#pendingMessage').append(missingStockistDetailsHtml);
          
          var settings = {
            "url": "/initiator/generate_vq_save_selected_stockist",
            "method": "POST",
            "timeout": 0,
            "headers": {
              "Accept-Language": "application/json",
              "Content-Type": "application/json"
            },
            "data": JSON.stringify({
              "_token": "{{ csrf_token() }}",
              "selected_items": missingStockistDetailsItems,
            })
          };
          $.ajax(settings).done(function (response) {
            $('#loader1').hide(); 
            if(response.success == true)
            {
              $('#untickStockistButton').removeClass('d-none')
              $('#download_missing_stockist').removeClass('d-none')
              $('#errorModal').modal('show');
             $('#download_missing_stockist').attr('href', `{{ url()->current() }}?download=missingStockist&item_code=${$('#item_code').val()}`);
            }
          });
          
        } 
        if (missingPaymentModeItems.length > 0) {
          $('#loader1').hide();
          var missingPaymentModeHtml = '<ul>';
          missingPaymentModeItems.forEach(function(item) {
            missingPaymentModeHtml += '<li>' + item.message + '</li>';
          });
          missingPaymentModeHtml += '</ul>';
          $('#pendingMessage').append(missingPaymentModeHtml);
          
          var settings = {
            "url": "/initiator/generate_vq_save_selected_paymode",
            "method": "POST",
            "timeout": 0,
            "headers": {
              "Accept-Language": "application/json",
              "Content-Type": "application/json"
            },
            "data": JSON.stringify({
              "_token": "{{ csrf_token() }}",
              "selected_items": missingPaymentModeItems,
              //"selected_missingStockistDetailsItems": missingStockistDetailsItems
            })
          };
          $.ajax(settings).done(function (response) {
            $('#loader1').hide(); 
            if(response.success == true)
            {
              $('#untickpaymodeButton').removeClass('d-none')
              $('#download_missing_paymode').removeClass('d-none')
              $('#errorModal').modal('show');
             $('#download_missing_paymode').attr('href', `{{ url()->current() }}?download=missingPaymode&item_code=${$('#item_code').val()}`);
            }
          });
          
        }
        if(missingPaymentModeItems.length == 0 && missingStockistDetailsItems.length == 0) {
          //console.log('Selected items:', selected);
          var settings = {
            "url": "/initiator/generate_vq_save_change",
            "method": "POST",
            "timeout": 0,
            "headers": {
              "Accept-Language": "application/json",
              "Content-Type": "application/json"
            },
            "data": JSON.stringify({
              "_token": "{{ csrf_token() }}",
              "selected_rows": selected
            })
          };
          $.ajax(settings).done(function (response) {
            $('#loader1').hide(); 
            if(response.success == true)
            {
             alert('Payment Mode updated successfully')
             $('#send_quotation').removeClass('d-none');
            }
            else
            {
               alert("Error Occured. Please try again");
            }           
          });
        }
    });
    $('#untickStockistButton').click(function() {
      missingStockistDetailsItems.forEach(function(item) {
        // Uncheck the corresponding row in the DataTable
        var row = table.$('tr').filter(function() {
          return $(this).find('.sku_id').text() == item.sku_id && $(this).find('.stockist_id').text() == item.stockist_id && $(this).find('.stockist_id').text() === '';
        });
        row.find('.dt-checkboxes').prop('checked', false);
        table.row(row).deselect();
      });
      $('#errorModal').modal('hide');
      enableBtn();
    });
    $('#untickpaymodeButton').click(function() {
      missingPaymentModeItems.forEach(function(item) {
        // Uncheck the corresponding row in the DataTable
        var row = table.$('tr').filter(function() {
          return $(this).find('.sku_id').text() == item.sku_id && $(this).find('.stockist_id').text() == item.stockist_id && $(this).find('.payment_mode').val() === '';
        });
        row.find('.dt-checkboxes').prop('checked', false);
        table.row(row).deselect();
      });
      $('#errorModal').modal('hide');
      enableBtn();
    });
    $('#download_missing_paymode').click(function() {
        
    });
    $('#send_quotation').click(function(e){
      // $('#loader1').show(); 
      $('#send_quotation').addClass('d-none');
      e.preventDefault()
      var settings = {
        "url": "/initiator/generate_vq_send_quotation",
        "method": "POST",
        "timeout": 0,
        "headers": {
          "Accept-Language": "application/json",
          "Content-Type": "application/json"
        },
        "data": JSON.stringify({
          "_token": "{{ csrf_token() }}",
          "selected_rows": selected
        })
      };
      $.ajax(settings).done(function (response) {
        $('#loader1').hide(); 
        if(response.success == true)
        {
         $('#successModal').modal({backdrop: 'static', keyboard: false},'show');
        }
        else if(response.success == 'poc_missing')
        {
          $('#errorModal1').modal('show');
          $('#pendingMessage1').text(response.result);
          missingPocItems.push(...response.missing_institution_ids)
          $('#download_missing_poc').attr('href', `{{ url()->current() }}?download=missingPoc&item_code=${$('#item_code').val()}`);
        }  
        else if(response.success == 'send_quotation_failed')
        {
          $('#idap_disc_tran_exist_modal').modal('show');
          $('#idap_disc_tran_exist_html').html(response.result);
        } 
        else
        {
          $('#send_quotation').removeClass('d-none');
          alert("Error Occured. Please try again");
        }    
      });
    });
    $('#untickInstitutionButton').click(function() {
      missingPocItems.forEach(function(institution_id) {
        // Uncheck the corresponding row in the DataTable
        table.$('tr').filter(function() {
            return $(this).find('.institution_id').text() == institution_id;
        }).each(function() {
            var row = $(this);
            // Uncheck the checkbox if needed
            row.find('.dt-checkboxes').prop('checked', false);
            table.row(row).deselect();
        });
      });
      $('#errorModal1').modal('hide');
      enableBtn();
      selected = selected.filter(function(item) {
        return !missingPocItems.includes(item.institution_id);
      });
      if (selected.length === 0) {
        $('#send_quotation').addClass('d-none');
      } else {
        $('#send_quotation').removeClass('d-none');
      }
    });
  });
  function init_table()
  {
      $('#loader1').show(); 
      table = $("#zero_config").DataTable({
        "pageLength": 50,
        "aaSorting": [[ 1, "asc" ]],
        responsive: true,
        ajax: {
            url: '/initiator/generate_vq_data_existing',
            type: 'GET',
            data: function (d) {
            // Add custom data to the request
                d.item_code = $('#item_code').val();
                return d;
            },
            "dataSrc": function(response) {
                // Return the data array for DataTable to process
                $('#loader1').hide(); 
                return response.data;
            }
        },
        "rowCallback": function(row, data) {
          $('#loader1').hide(); 
        },
        columns:
        [
          { data: 'unique_id' },//0
          { data: 'hospital_name' },//1
          { data: 'sap_code' },//2
          { data: 'mother_brand_name' },//3
          { data: 'item_code' },//4
          { data: 'rev_no' },//5
          { data: 'brand_name' },//6
          { 
            data: function(row) {//7
              if (row.discount_percent !== null && row.discount_percent !== undefined && row.discount_percent !== '') {
                  return parseFloat(row.discount_percent).toFixed(2)+`<input type="hidden" class="discount_percent" value="${row.discount_percent}">`
              } else {
                  return 0+`<input type="hidden" class="discount_percent" value="0">`
              }
            }
          },
          { 
            data: function(row) {//8
              var result = parseFloat(row.discount_rate).toFixed(2)
              return result;
            }
          },
          { data: 'stockist_code' },//9
          { data: 'stockist_name' },//10
          { 
            data: function(row) {//11
              var result = `<select name="payment_mode" class="payment_mode" class="form-control form-control-sm"  onchange="singlePayModeChangeHandler($(this), ${row.ptr}, ${row.discount_percent})">
                <option value="">Select payment Mode</option>
                <option value="DM" ${row.payment_mode == 'DM' ? 'selected' : ''}>Direct Master</option>
                <option value="CN" ${row.payment_mode == 'CN' ? 'selected' : ''}>Credit Note</option>
                </select>`
              return result
            }
          },
          { 
            data: function(row) {//12
              var result = row.net_discount_percent
              if(result != null)
              {
                  return parseFloat(result).toFixed(2);
              }
              else
              {
                let inputMargin = (Disc_margin_item_code[row.item_code])?Disc_margin_item_code[row.item_code] : 10;
                var ptr = row.ptr;
                var discountPercent = row.discount_percent;
                if(row.payment_mode == 'DM' || row.payment_mode ==null)
                {
                  if(row.ptr == 0)
                  {
                    var discountedValue = row.ptr - ((row.ptr - (row.ptr * row.discount_percent / 100)) - ((row.ptr - (row.ptr * row.discount_percent / 100)) * inputMargin / 100));
                    var net_discount_percent = (discountedValue / 100).toFixed(2);
                  } else {
                    var discountedValue = row.ptr - ((row.ptr - (row.ptr * row.discount_percent / 100)) - ((row.ptr - (row.ptr * row.discount_percent / 100)) * inputMargin / 100));
                    var net_discount_percent = ((discountedValue / row.ptr) * 100).toFixed(2);
                  }
                }
                else
                {
                  var net_discount_percent = discountPercent;
                }
                return net_discount_percent;
              }
            }
          },
          { data: 'applicable_gst' },//13
          { data: 'last_year_percent',//14
                    "render": function(data, type, row, meta) {
                        if (data !== null && data !== undefined && data !== '') {
                            return parseFloat(data).toFixed(2);
                        } else {
                            return 0;
                        }
                    }
                },
          { data: 'last_year_rate',//15
              "render": function(data, type, row, meta) {
                  if (data !== null && data !== undefined && data !== '') {
                      return parseFloat(data).toFixed(2);
                  } else {
                      return 0;
                  }
              }
          },
          { data: 'last_year_mrp',//16
              "render": function(data, type, row, meta) {
                  if (data !== null && data !== undefined && data !== '') {
                      return parseFloat(data).toFixed(2);
                  } else {
                      return 0;
                  }
              }
          },
          { data: 'mrp',//17
              "render": function(data, type, row, meta) {
                  if (data !== null && data !== undefined && data !== '') {
                      if(parseFloat(data) < 0.1) return parseFloat(data);//added condition to check value less than 0.1 and remove rounding
                      else return parseFloat(data).toFixed(2);
                  } else {
                      return 0;
                  }
              }
          },
          
          { data: 'ptr',//18
              "render": function(data, type, row, meta) {
                  if (data !== null && data !== undefined && data !== '') {
                      if(parseFloat(data) < 0.1) return parseFloat(data);//added condition to check value less than 0.1 and remove rounding
                      else return parseFloat(data).toFixed(2);
                  } else {
                      return 0;
                  }
              }
          },
          { data: 'mrp_margin',//19
              "render": function(data, type, row, meta) {
                  if (data !== null && data !== undefined && data !== '') {
                      return parseFloat(data).toFixed(2);
                  } else {
                      return 0;
                  }
              }
          },
          { data: 'type' },//20
          { data: 'hsn_code' },//21
          {  data: function(row) {//22
                  var composition = row.composition;
                  return composition.toUpperCase().toLowerCase();
              }
          },
          { data: 'vq_id' },//23
          { data: 'sku_id' },//24
          { data: 'stockist_id' },//25
          { 
              data: function(row) {//26
                  var element = `<input type="hidden" class="update_flag" value="false">`
                  return element;
              }
          },
          { data: 'parent_vq_id' },//27
          { data: 'institution_id' },//28
          ],
          "columnDefs": [
            { "targets": 0, "orderable": false,'data': 0,
              'render': function(data, type, row, meta){
                data = '<input type="checkbox" class="dt-checkboxes">'                 
                return data;
              }, 
              'checkboxes': {
               'selectRow': true
              }
            },
            {
                targets: 1, 
                className: 'hospital_name', 
                
            }, 
            { "targets": [5], 
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('text-center rev_no');
              }
            }, 
            { "targets": [11,13,14,15,16,20,21], 
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).css({
                  'text-align': 'center'
                });
              }
            },  
            { "targets": [7], 
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('text-center disc_percent');
              }
            },  
            { "targets": [8],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('text-center disc_rate');
              }
            }, 
            { "targets": [9], 
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('stockist_code');
              }
            },  
            { "targets": [10],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('stockist_name');
              }
            },  
            { "targets": [23], 
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('d-none vq_id');
              }
            },  
            { "targets": [24],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('d-none sku_id');
              }
            },
            { "targets": [25],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('d-none stockist_id');
              }
            },
            { "targets": [26],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('d-none');
              }
            },
            { "targets": [27],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('d-none parent_vq_id');
              }
            },
            { "targets": [28],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('d-none institution_id');
              }
            },
            { 
              "targets": [4],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('item-code');
                $(td).css({
                  'text-align': 'center'
                });
              } 
            },
            { 
              "targets": [6],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('brand_name');
              } 
            },
            { 
              "targets": [17],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('mrp');
                $(td).css({
                  'text-align': 'center'
                });
              } 
            },
            { 
              "targets": [18],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('cBalance');
                $(td).css({
                  'text-align': 'center'
                });
              } 
            },
            { 
              "targets": [19],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('mrp_margin');
                $(td).css({
                  'text-align': 'center'
                });
              } 
            },
            { 
              "targets": [12],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('net_discount_percent');
                $(td).css({
                  'text-align': 'center'
                });
              } 
            }   
          ],
          'select': {
             'style': 'multi'
          },
        'language': {
            'paginate': {
                'previous': "<img src='{{ asset('admin/images/left.svg') }}' class='left-block' alt=''> <img src='{{ asset('admin/images/left1.svg') }}' class='disbpr' alt=''>",
                'next': "<img src='{{ asset('admin/images/right.svg') }}' class='neblk' alt=''> <img src='{{ asset('admin/images/right1.svg') }}' class='disbn' alt=''>"
            }
        },
        scrollX: true
      });
  }
  function singlePayModeChangeHandler(e, ptr, inputDiscRate){
      let row = e.closest('tr');
      let itemCode = row.find('.item-code').text();
      let selectedPaymentMode = e.val();
      let netDiscountRateToStockist;
      let inputMargin = (Disc_margin_item_code[itemCode])? Disc_margin_item_code[itemCode] : 10;
      if(selectedPaymentMode == 'DM'){
          if(ptr == 0)
          {
              netDiscountRateToStockist = 0;
          }
          else
          {
              let discountamt = ptr - ((ptr * inputDiscRate) / 100);
              let marginamt = discountamt * inputMargin / 100;
              let nrv = discountamt - marginamt;
              netDiscountRateToStockist = (ptr - nrv) / ptr * 100;
          }
          $(e.parent()).siblings('.net_discount_percent').html(netDiscountRateToStockist % 1 ? Number(netDiscountRateToStockist).toFixed(2) : Number(netDiscountRateToStockist).toFixed(2));
      }else if(selectedPaymentMode == 'CN'){
        netDiscountRateToStockist = inputDiscRate;
        $(e.parent()).siblings('.net_discount_percent').html(netDiscountRateToStockist % 1 ? Number(netDiscountRateToStockist).toFixed(2) : Number(netDiscountRateToStockist).toFixed(2));
      }
      else
      {
        $(e.parent()).siblings('.net_discount_percent').html('');
      }
      
  }
  function enableBtn() {
    var anyRowChecked = $('#zero_config tbody').find("tr.selected input:checked").length > 0;
    var headerCheckbox = $('.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]');
    var isHeaderChecked = headerCheckbox.prop("checked");
  
    $('#save_changes_btn').prop("disabled", !anyRowChecked && !isHeaderChecked);
  }

  $('.cancel_btn1').click(function(){
    // if(confirm('are close the popup')){
      $('#idap_disc_tran_exist_modal').css('display', 'none');
    // }
  });
</script>
<style type="text/css">
  .copy-center{
    position:inherit !important;margin: 20px 0;
  }
  div.dataTables_wrapper {
    max-width: 1308px;
    width: 100%;
    margin: 0 auto;
  }
</style>
@endsection

