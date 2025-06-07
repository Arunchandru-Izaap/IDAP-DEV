@extends('layouts.admin.app')
@section('content')
<link rel='stylesheet' href="https://cdn.datatables.net/select/1.3.4/css/select.dataTables.min.css">
<style type="text/css">
   table.dataTable thead .sorting 
{
        background:none;
}
  .no-data-message {/*empty table custom  message*/
      font-weight: bold; 
      text-align: center; 
  }
  .yellow-bg
  {
    background: #f9f938 !important;
  }
  .disabled { pointer-events: none; opacity: 0.6; }
  .select2-container--classic .select2-selection--multiple .select2-selection__choice, .select2-container--default .select2-selection--multiple .select2-selection__choice, .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    /*background-color: #2255a4;
    border-color: #2255a4;*/
    color: #000;
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
    .orange-btn:disabled {
        background: #faf9f7;
        color: #b4b3b1;
    }
    .orange-btn:hover {
        /*color: #000;*/
        text-decoration: none;
    }
    .orange-btn {
      border-radius: 24px;
      box-shadow: 0 4px 12px 0 rgba(208, 210, 214, 0.99);
      background: #f7941e;
      font-size: 14px;
      font-weight: 800;
      font-stretch: normal;
      font-style: normal;
      line-height: 1.43;
      letter-spacing: 0.2px;
      text-align: center;
      color: #fff;
      border: none;
      padding: 7px 20px;
      text-transform: uppercase;
      cursor: pointer;
  }
  table.dataTable tbody>tr.selected, table.dataTable tbody>tr>.selected {
    background: none !important;
  }
  .dataTables_paginate {
    margin-top: 10px;
    text-align: right;
  }

  /* Style for all pagination buttons */
  .dataTables_paginate .paginate_button {
      background-color: #fff;
      color: #7460ee;
      border: 1px solid #dee2e6;
      padding: 6px 12px;
      /*margin: 2px;*/
      text-decoration: none;
      cursor: pointer;
      transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  }

  /* Hover effect for pagination buttons */
  .dataTables_paginate .paginate_button:hover {
      color: #5d4dbe;
    background-color: #e9ecef;
    border-color: #dee2e6;
  }

  /* Active page button style (blue background) */
  .dataTables_paginate .paginate_button.current {
      background-color: #007bff !important;
      color: white !important;
      border-color: #007bff !important;
  }

  /* Disabled previous/next button style */
  .dataTables_paginate .paginate_button.disabled {
      color: #6c757d;
    pointer-events: none;
    background-color: #fff;
    border-color: #dee2e6;
  }
  .btn-danger:disabled {
     color: #fff;
  }
  .tich-logo
  {
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
  .btn-warning:focus, .btn-danger:focus {
    color: #fff;
  }
  ul {
    list-style: none;
    margin: 0;
    padding: 0;
  }
</style>
  <!-- Page content-->
  <div class="container-fluid">
      <!-- <h1 class="mt-4">Simple Sidebar</h1> -->
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-body">
              <div class="row mb-2">
                <div class="col-md-3 mr-20">
                  <label>Brand name: </label>
                  <select id="item_code" class="js-example-basic-single itemCode" name="itemCode">
                    <option value=''> Select </option>
                    @foreach($brand_Names as $item)
                    <option value="{{$item->item_code}}">{{$item->item_code}} - {{$item->brand_name}}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3 d-flex" style="align-items: center;margin-top: 24px;">
                    <button class="btn btn-primary view-data orange-btn-bor" style="margin-right: 1rem;">
                        Fetch VQ
                    </button>
                    <button class="btn btn-warning" id="discard_btn" disabled>Discard</button>
                </div>
              </div>
              <div class="row">
                  <div id='loader1' style='display: none;'>
                        
                  </div>
                  <div class="col pd-20">
                      <div class="actions-dashboard table-ct">

                        <table class="table table-striped table-bordered" id="zero_config_admin">
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
            </div>
          </div>
        </div>
      </div>
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
  <div class="modal-dialog modal-dialog-centered model-pop-ct" style="max-width: 700px;">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header border-0">
          <div class="tich-logo">
          <img style="width: 50px;" src="{{ asset('admin/images/ticket.svg') }}">
          </div>
      </div>
      <!-- Modal body -->
      <div class="modal-body text-center">
        <p>Product Discarded Successfully</p>
        <a class="btn orange-btn" href="{{route('productwise_discard_data_admin')}}" id="">Close</a>
      </div>
      
      
    </div>
  </div>
</div>
@endsection
@push('scripts')
<!-- <script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script> -->
<script src="{{asset('frontend/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('frontend/js/dataTables.select.min.js')}}"></script>
<script type="text/javascript" src="{{asset('frontend/js/dataTables.checkboxes.min.js')}}"></script>
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
      if ($.fn.DataTable.isDataTable('#zero_config_admin')) {
          // If it is, destroy it first
          $('#zero_config_admin tbody').empty();
          $('#zero_config_admin').DataTable().destroy();
          $('#zero_config_admin tbody').empty();
      }
      if($('#item_code').val() == '')
      {
        alert('Please select any one brand name')
        return;
      }
      init_table()
      $('#zero_config_admin_length select').addClass('form-control form-control-sm')
      $('#zero_config_admin_filter input').addClass('form-control form-control-sm')
    });
    $('#zero_config_admin tbody').on("change", "input[type='checkbox']", function() {
      var checkbox = this;
      //updateSelectedRows(checkbox);
      enableBtn();
    });
    $('body').on("change", '.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]', function() {
      var isHeaderChecked = this.checked;
      $('#zero_config_admin tbody').find("input[type='checkbox']").prop('checked', isHeaderChecked).trigger('change');
      enableBtn();
    });
    $('body').on('click','#zero_config_admin tbody tr',function(){
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
          "url": "/admin/productwise_discard_selection",
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
            //$('#successModal').modal({backdrop: 'static', keyboard: false},'show');
            var myModal = new bootstrap.Modal(document.getElementById('successModal'), {
              backdrop: 'static',
              keyboard: false
            });

            myModal.show();
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
      table = $("#zero_config_admin").DataTable({
        "pageLength": 50,
        "aaSorting": [[ 1, "asc" ]],
        responsive: true,
        ajax: {
            url: '/admin/productwise_discard_getdata',
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
        scrollX: true
      });
  }
  function enableBtn() {
    var anyRowChecked = $('#zero_config_admin tbody').find("tr.selected input:checked").length > 0;
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
@endpush

