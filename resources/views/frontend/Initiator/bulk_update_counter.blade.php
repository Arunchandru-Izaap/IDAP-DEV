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
    #bulk-update-product-quotation{
      display:none !important;
    }

    .rth-cal {
      padding: 0;
      margin: 0;
      list-style: none;
      display: flex;
      align-items: center;
      gap: 10px; /* Adds spacing between buttons */
    }

    .rth-cal li {
      display: flex;
      align-items: center;
    }

    button.orange-btn-bor {
      white-space: nowrap; /* Prevents multi-line */
    }
</style>
<div id="page-content-wrapper">
    <!-- Top navigation-->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                <ul class="navbar-nav dashboard-nav">
                    <li class="nav-item active"><a class="nav-link" href="{{route('initiator_listing')}}"> <img src="{{ asset('admin/images/back.svg')}}">Bulk Update Counter Products</a></li>
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
        <li>
            <a href="{{route('initiator_dashboard')}}">Home</a>
        </li>
        <li class="">
            <a href="{{route('initiator_listing')}}">VQ Request Listing</a>
        </li>
        <li class="">
            <a href="#"> Bulk Update Counter Products</a>
        </li>
    </ul>
    <!-- Page content-->
    <div class="container-fluid">
        <!-- <h1 class="mt-4">Simple Sidebar</h1> -->
        <div class="row">
            <div class="col-md-4 mr-20">
              <label>Brand name: </label>
              <select class="js-example-basic-multiple" id="item_code" name="" multiple="multiple">
                  <!-- <option value='all'> All </option> -->
                  @foreach($brandNameswithItemcode as $brandnameitemcode)
                      <option value="{{ $brandnameitemcode->item_code }}">{{ $brandnameitemcode->item_code }} - {{ $brandnameitemcode->brand_name }}</option>
                  @endforeach
              </select>
            </div>
            <div class="col-md-4 mr-20">
              <label>Institution : </label>
              <select class="js-example-basic-multiple"  id="institution_id" name="" multiple="multiple">
                <option value='all'> All </option>
                @foreach($vqInstitutions as $vq)
                  <option value="{{ $vq->institution_id }}">{{ $vq->institution_id }} - {{ $vq->hospital_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4 d-flex" style="align-items: center;margin-top: 31px;">
              <ul class="d-flex list-unstyled">
                  <li>
                  <button class="view-data orange-btn-bor">
                      Fetch VQ
                  </button>
                  </li>
              </ul>
            </div>

            <div class="col-md-12 d-flex bulk-update-product-quotation" id="bulk-update-product-quotation">
              <div class="col-md-8 d-flex">
                  <div class="col-md-8 d-flex mr-20" id="bulk_update_form">
                    <label style="padding: 10px 18px;">Bulk Update : </label>
                    <ul class="rth-cal d-flex">
                      <li>
                        <div class="form-group m-2">
                          <select name="payment_mode_dropdown" id="payment_mode_dropdown" class="form-control form-control-sm fit_width" style='width: 14rem;'>
                            <option value="DM">Direct Master</option>
                            <option value="CN">Credit Note</option>
                          </select>
                        </div>
                      </li>
                      <li>
                          <button class="apply-val orange-btn-bor" id="apply_bulk_update_btn">
                          SAVE CHANGES
                          </button>
                      </li>
                      <!-- <li>
                          <button class="clear-val orange-btn-bor" id="cancel_bulk_update_btn">
                          CANCEL
                          </button>
                      </li> -->
                    </ul>
                  </div>
              </div>
              <div class="col-md-4 d-flex">
                  <div class="cancel-btn ml-auto">
                      <button class="orange-btn" id="send_quotation_btn" disabled>Send Quotation</button>
                  </div>
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
                            <th class="d-none">vqsls_id</th>
                        </tr>
                        </thead>
                        <tbody>
                            
                    </tbody>
                    </table>
                </div>
            </div><!-- col close -->
        </div>
    </div>
  </div>
<textarea id="filter_data" style="display: none;"></textarea>

<!-- bulk update popup -->
<div class="modal show" id="showbulkpopup">
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
          <p class="border-0 modal_heading">Bulk update applied successfully</p>
        </div>
      </div>
    </div>
</div>
<!-- Send Quotation popup -->
<div class="modal show" id="showsendquotationpopup">
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
          <p class="border-0 modal_heading send_quoation_success_msg"></p>
        </div>
      </div>
    </div>
</div>
<style type="text/css">
    p.copy-center.copyright {
        position: inherit;
        padding: 10px 0;
    }
</style>
<script src="{{ asset('admin/extra-libs/DataTables/datatables.min.js') }}"></script>
<script src="{{ asset('frontend/js/select2.js') }}"></script>
<script type="text/javascript">
    const Disc_margin_item_code = @json($data['DiscountMargin_datas']);
    $('.js-example-basic-multiple').select2({ width: '100%' });

    $('.close').on('click',  function(){
      $('#showsendquotationpopup').css('display', 'none');
      // window.location.href = '';
    });
    $('.view-data').on('click',function(){
      selected = [];
      $('#selectAll').prop('checked',false)
      $('#send_quotation_btn').attr('disabled', true)
      $('#bulk-update-product-quotation').removeAttr("id");
      if ($.fn.DataTable.isDataTable('#zero_config')) {
          // If it is, destroy it first
          $('#zero_config tbody').empty();
          $('#zero_config').DataTable().destroy();
          $('#zero_config tbody').empty();
      }
      if($('#institution_id').val() == '')
      {
        alert('Please select any one institution')
        return;
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
    });

    function init_table()
    {
      // $('#loader1').show(); 
      table = $("#zero_config").DataTable({
        "pageLength": 50,
        "aaSorting": [[ 1, "asc" ]],
        processing: true,
        responsive: true,
        // serverSide: true,
        ajax: {
            url: '/initiator/bulk_counter_update_vq_data',
            type: 'GET',
            data: function (d) {
                // Add custom data to the request
                d.institution_id = $('#institution_id').val();
                d.item_code = $('#item_code').val();
                return d;
            },
            "dataSrc": function(response) {
                // Return the data array for DataTable to process
                // console.log(response.data);
                $("#filter_data").val(JSON.stringify(response.data));
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
          { data: 'vqsls_id' },//29
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
            { "targets": [29],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('d-none vqsls_id');
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


    function enableBtn() {
      var anyRowChecked = $('#zero_config tbody').find("tr.selected input:checked").length > 0;
      var headerCheckbox = $('.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]');
      var isHeaderChecked = headerCheckbox.prop("checked");

      $('#send_quotation_btn').prop("disabled", !anyRowChecked && !isHeaderChecked);
    }
    function singlePayModeChangeHandler(e, ptr, inputDiscRate){
      let row = e.closest('tr');
      let itemCode = row.find('.item-code').text();
      let selectedPaymentMode = e.val();
      let netDiscountRateToStockist;
      let inputMargin = (Disc_margin_item_code[itemCode])? Disc_margin_item_code[itemCode] : 10;
      console.log(inputMargin,'inputMargin');
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

    $(document).on('click', '#apply_bulk_update_btn', function(){
      let selectedPaymentMode = $('#payment_mode_dropdown').val();
      let table = $('#zero_config').DataTable();
      let rowNodes = table.rows({filter: 'applied'}).nodes().toArray();
      let rowData = table.rows({filter: 'applied'}).data().toArray();
      console.log(rowNodes);
      if(rowNodes.length > 0){
        let text = "Are you sure you want to apply mode of Payment for All SKUs and Institutions?";
        if (confirm(text) == true) {
          for(i = 0; i < rowNodes.length; i++){
            if(!$(rowNodes[i]).find("select.payment_mode").prop("disabled")){
              let netDiscountRateToStockist;
              // let inputMargin = 10;
              let disc_mrg_item_code = $(rowNodes[i]).find(".item-code").text();
              let inputMargin = (Disc_margin_item_code[disc_mrg_item_code])?Disc_margin_item_code[disc_mrg_item_code] : 10;
              if(selectedPaymentMode == 'DM'){
                  /*let ptr = rowData[i][12];
                  let inputDiscRate = rowData[i][13];*/
                  let ptr = $(rowNodes[i]).find(".cBalance").text()
                  let inputDiscRate = $(rowNodes[i]).find(".discount_percent").val()
                  // console.log(disc_mrg_item_code);
                  // console.log(ptr);
                  // console.log(inputDiscRate);
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
                  // console.log(ptr);
              }else if(selectedPaymentMode == 'CN'){
                //let inputDiscRate = rowData[i][13];
                let inputDiscRate = $(rowNodes[i]).find(".discount_percent").val()
                netDiscountRateToStockist = inputDiscRate;
              }
              $(rowNodes[i]).find("select.payment_mode").val(selectedPaymentMode);
              $(rowNodes[i]).find(".net_discount_percent").html(netDiscountRateToStockist % 1 ? Number(netDiscountRateToStockist).toFixed(2) : Number(netDiscountRateToStockist).toFixed(2));
            }
          }
          $('#showbulkpopup').modal('show');
          $('#cancel_bulk_update_btn').trigger('click')
        }
      
      }else{
        alert('Please select at least one row.');
      }
    });

    $(document).on('click', '#send_quotation_btn', function(){
      let confirmSaveChanges = confirm("Do you want to Send Quotation?")
      if(confirmSaveChanges){
        
        const dbDataArr = JSON.parse($('#filter_data').val());
        // console.log(dbDataArr);

        let table = $('#zero_config').DataTable();
        let rowData = table.rows().data().toArray();

        // let rowNodes = table.rows().nodes().toArray(); 
        // // console.log(rowData);
        // let selectedModeArr = [];  
        // for (i = 0; i < rowData.length; i++) {
        //   let selectedMode = $(rowNodes[i]).find("select.payment_mode option:selected").val();
        //   let netDiscPercent = $(rowNodes[i]).find(".net_discount_percent").html();
        //   let item_code = $(rowNodes[i]).find(".item_code").val();
        //   let vqsl_sku_id = $(rowNodes[i]).find(".sku_id").val();
        //   let vqsls_id = $(rowNodes[i]).find(".vqsls_id").val();
        //   if (netDiscPercent == "" || netDiscPercent == "NaN") {
        //       //console.log("Field does not contain a number.");
        //       alert("The Item Code: "+item_code+" Net Discount Rate is Empty")
        //       return;
        //   } else {
        //       //console.log("Field contains a number");
        //   }
        //   console.log(selectedMode);
        //   for(j = 0; j < dbDataArr.length; j++){
        //     if(Number(rowData[i]['vqsls_id']) == dbDataArr[j].vqsls_id){
        //       if(selectedMode != dbDataArr[j].payment_mode){
        //         // console.log('ff');
        //         let obj = {id: dbDataArr[j].vqsls_id, sku_id: dbDataArr[j].sku_id, payMode: selectedMode, netDiscPercent: netDiscPercent};
        //         selectedModeArr.push(obj);
        //       }
        //       else{
               
        //       }
        //       break;
        //     }
        //   }      
        // }
       
        let selectedModeArr = [];
        var rowcollection = table.$(".dt-checkboxes:checked", {"page": "all"});
        rowcollection.each(function(index,elem){
          var row = $(elem).closest("tr");
          let institutionName = row.find('.institution_id').text(); // adjust index based on column
          let selectedMode = row.find("select.payment_mode option:selected").val();
          let netDiscPercent = row.find(".net_discount_percent").text();
          let item_code = row.find(".item-code").text();
          let vqsl_sku_id = row.find(".sku_id").text();
          let vqsls_id = row.find(".vqsls_id").text();
          if (netDiscPercent == "" || netDiscPercent == "NaN") {
            alert("The Item Code: "+item_code+" Net Discount Rate is Empty")
            return;
          }
          // console.log("Institution:", institutionName, "Item Code:", item_code, "Paymode:", selectedMode, "SKU ID:", vqsl_sku_id, "VQSLS ID:", vqsls_id);

          for(j = 0; j < dbDataArr.length; j++){
            if(Number(vqsls_id) == dbDataArr[j].vqsls_id){
              if(selectedMode != dbDataArr[j].payment_mode){
                let obj = {id: dbDataArr[j].vqsls_id, sku_id: dbDataArr[j].sku_id, payMode: selectedMode, netDiscPercent: netDiscPercent};
                selectedModeArr.push(obj);
              }
              else{
              
              }
              break;
            }
          }
        });
        // alert(selectedModeArr.length);
        if(selectedModeArr.length > 0 && rowData.length == dbDataArr.length){
            let jsonData = JSON.stringify(selectedModeArr);
            BulkCounterSendQuotation(jsonData);
        }else{
            alert('You have not change mode of discount for any item so cannot proceed for send quotation');
        }
        // console.log(selectedModeArr);
      }
    });
    function BulkCounterSendQuotation(jsonData){
      $('#send_quotation_btn').attr('disabled', true);
      var settings = {
        "url": "/initiator/BulkUpdateCounterSendQuotation",
        "method": "POST",
        "timeout": 0,
        "headers": {
          "Accept-Language": "application/json",
        },
        "data": {
          "_token": "{{ csrf_token() }}",
          "data": jsonData,
        }
      };
      $.ajax(settings).done(function (response) {
        if(response.success == true){
          console.log(response.data);
          $('.send_quoation_success_msg').text(response.message);
          $('#showsendquotationpopup').show();
          // Redirect after small delay
          setTimeout(function() {
              window.location.href = '';
          }, 1000); // 300ms delay
        }
      });
    }
</script>
@endsection
