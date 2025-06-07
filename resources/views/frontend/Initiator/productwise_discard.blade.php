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
                                <li class="nav-item active"><a class="nav-link" href="{{route('initiator_listing')}}"> <img src="{{ asset('admin/images/back.svg')}}">Product wise Discard functionality</a></li>
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
                        Product wise Discard functionality
                      </a>
                    </li>
                </ul>
                <!-- Page content-->
                <div class="container-fluid">
                    <!-- <h1 class="mt-4">Simple Sidebar</h1> -->
                    <div class="row">
                      <div class="col-md-3 mr-20">
                        <label>Brand name: </label>
                        <select id="item_code" class="js-example-basic-single itemCode" name="itemCode">
                          <option value=''> Select </option>
                          @foreach($brand_Names as $item)
                          <option value="{{$item->item_code}}">{{$item->item_code}} - {{$item->brand_name}}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-3 d-flex" style="align-items: center;margin-top: 31px;">
                          <button class="view-data orange-btn-bor">
                              Fetch VQ
                          </button>
                          <button class="orange-btn" id="discard_btn" disabled>Discard</button>
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
                                        <th class="" style="">Institution Name</th>
                                        <th class="" style="">Institution Code</th>
                                        <th class="" style="">Division</th>
                                        <th class="digitcenter">Disc. PTR (%)</th>
                                        <th class="digitcenter">RTH (Excl. GST)</th>
                                        <th class="digitcenter">Mother Brand Name</th>
                                        <th class="digitcenter">Item Code</th>
                                        <th class="" style="">Brand</th>
                                        <th class="digitcenter">Revision Count</th>
                                        <th>SAP Code</th>
                                        <th class="digitcenter">Pending With</th>
                                        <th class="d-none">vq_id</th>
                                        <th class="d-none">sku_id</th>
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
          <a class="btn orange-btn d-none" href="javascript:void(0)" id="untickStockistButton">Untick Items with Missing Stockist Details</a>
          <a href="javascript:void(0)" class="btn orange-btn d-none" id="download_missing_stockist" title="Download Missing Stockist">Download Missing Stockist in Excel</a>
          <a class="btn orange-btn d-none" href="javascript:void(0)" id="untickpaymodeButton">Untick Items with Missing Payment mode</a>
          <a href="javascript:void(0)" class="btn orange-btn d-none" id="download_missing_paymode" title="Download Missing Payment mode">Download Missing Payment mode in Excel</a>
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
        <p>Product Discarded Successfully</p>
        <a class="btn orange-btn" href="{{route('initiator_listing')}}" id="">VQ Request Detail</a>
      </div>
      
      
    </div>
  </div>
</div>

<!-- <script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script> -->
<script src="{{asset('frontend/js/select2.js')}}"></script>
<script>
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
        alert('Please select any one brand name')
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
    $('#discard_btn').click(function(e){
      $('#loader1').show(); 
      e.preventDefault()
      selected = [];
      var rowcollection = table.$(".dt-checkboxes:checked", {"page": "all"});
        rowcollection.each(function(index,elem){
          var row = $(elem).closest("tr");
          var sku_id = row.find('.sku_id').text();
          var vq_id = row.find('.vq_id').text();
          var item_code = row.find('.item-code').text();
          var hospital_name = row.find('.hospital_name').text()
          var rev_no = row.find('.rev_no').text()
          var institution_id = row.find('.institution_id').text()
          selected.push({
              sku_id: sku_id,
              item_code: item_code,
              vq_id: vq_id,
              rev_no: rev_no,
              institution_id: institution_id,
            });
        });
        console.log(selected.length);
        console.log('Selected items:', selected);
        var settings = {
          "url": "/initiator/productwise_discard_selection",
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
        $.ajax(settings)
        .done(function (response) {
          $('#loader1').hide();
          if(response.success === true) {
            $('#successModal').modal({backdrop: 'static', keyboard: false},'show');
          } else {
            var response_message = response.result ?? 'Error Occurred! Try again.';
            alert(response_message);
          }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
          $('#loader1').hide();
          /*console.error("Error:", errorThrown);
          console.error("Response text:", jqXHR.responseText);*/
          alert('Server Error: ' + (jqXHR.responseJSON?.message || 'Please try again later.'));
        });
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
            url: '/initiator/productwise_discard_getdata',
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
          { data: 'sku_id' },//0
          { data: 'hospital_name' },//1
          { data: 'institution_id' },//2
          { data: 'div_name' },//3
          { data: 'discount_percent' },//4
          { data: 'discount_rate' },//5
          { data: 'mother_brand_name' },//6
          { data: 'item_code' },//7
          { data: 'brand_name' },//8
          { data: 'rev_no' },//9
          { data: 'sap_itemcode' },//10
          { 
            data: function(row) {
               const levelMap = {
                '1': 'Pending with RSM',
                '2': 'Pending with ZSM',
                '3': 'Pending with NSM',
                '4': 'Pending with SBU',
                '5': 'Pending with Semi Cluster',
                '6': 'Pending with Cluster',
                '7': 'Pending with Initiator',
                '8': 'Pending with CEO',

              };

              return levelMap[row.current_level] || 'Unknown Level';
            }
          },//11
          { data: 'vq_id' },//12
          { data: 'sku_id' },//13
          { data: 'institution_id' },//14
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
            { "targets": [9], 
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('text-center rev_no');
              }
            }, 
            { "targets": [7], 
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('text-center item-code');
              }
            }, 
            { "targets": [2,4,5,6,11], 
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).css({
                  'text-align': 'center'
                });
              }
            },  
            { "targets": [12], 
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('d-none vq_id');
              }
            },  
            { "targets": [13],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('d-none sku_id');
              }
            },
            { "targets": [14],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('d-none institution_id');
              }
            },  
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
  function enableBtn() {
    var anyRowChecked = $('#zero_config tbody').find("tr.selected input:checked").length > 0;
    var headerCheckbox = $('.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]');
    var isHeaderChecked = headerCheckbox.prop("checked");
    


    $('#discard_btn').prop("disabled", !anyRowChecked && !isHeaderChecked);
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

