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
</style>
<div id="page-content-wrapper">
                <!-- Top navigation-->
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                            <ul class="navbar-nav dashboard-nav">
                                <li class="nav-item active"><a class="nav-link" href="{{route('initiator_listing')}}"> <img src="{{ asset('admin/images/back.svg')}}">VQ Request Details</a></li>
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
                    <li class="active">
                      <a href="">
                        Workflow Adjustment
                      </a>
                    </li>
                    <li class="active">
                      <a href="">
                        Institutions Wise
                      </a>
                    </li>
                </ul>
                <!-- Page content-->
                <div class="container-fluid">
                    <!-- <h1 class="mt-4">Simple Sidebar</h1> -->
                    <div class="row">
                      <div class="col-md-3 mr-20">
                        <label>Hospital name: </label>
                        <select id="institution_id" class="js-example-basic-multiple" name="institution_id" multiple data-placeholder="Select institutions">
                          <option value=''> Select </option>
                          @foreach($data['institutions'] as $institution)
                          <option value="{{$institution['institution_id']}}">{{$institution['institution_id']}} - {{$institution['hospital_name']}}</option>
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
                          <!-- <button class="orange-btn" id="save_changes_btn" disabled>Save Changes</button> -->
                          <button class="orange-btn action_btn mr-2" id="move_up" btn-fn="move_up" disabled>Move Up</button>
                          <button class="orange-btn action_btn mr-2" id="send_back" btn-fn="send_back" disabled>Send Back</button>
                          <button class="orange-btn action_btn" id="move_initiator" btn-fn="mv_initiator" disabled>Move to Initiator</button>
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
                                        <th class="digitcenter">Revision No</th>
                                        <th class="" style="width: 110px !important">Hospital</th>
                                        <th class="" style="width: 90px !important">Metis Code</th>
                                        <th class="" style="width: 90px !important">SAP Code</th>
                                        <th class="" style="width: 110px !important">Address</th>
                                        <th class="" style="width: 90px !important">City</th>
                                        <th class="" style="width: 90px !important">State</th>
                                        <th class="" style="width: 90px !important">Pending with</th>
                                        <th class="d-none">vq_id</th>
                                        <th class="d-none">institution_id</th>
                                        <th class="d-none">current_level</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        
                                </tbody>
                              </table>
                            </div>
                        </div><!-- col close -->
                    </div>



<div class="modal show" id="firstlevelerror">
  <div class="modal-dialog modal-dialog-centered model-pop-ct" style="max-width: 700px;">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header border-0">
          <div class="tich-logo">
          <img style="width: 50px;" src="{{ asset('admin/images/ticket.svg') }}">
          </div>
        <!-- <button type="button" class="close" data-dismiss="modal">
            <img src="{{ asset('admin/images/close.svg') }}">
        </button> -->
      </div>
      <!-- Modal body -->
      <div class="modal-body">
        <p>The Following item(s) are already at the RSM</p>
        <div id="pendingMessage"></div>
         <a class="btn orange-btn" href="javascript:void(0)" id="untickFirstLevelButton">Untick</a>
        <a class="btn orange-btn" href="javascript:void(0)" data-dismiss="modal" id="">Close</a>
      </div>
      
      
    </div>
  </div>
</div>
<div class="modal show" id="lastlevelerror">
  <div class="modal-dialog modal-dialog-centered model-pop-ct" style="max-width: 700px;">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header border-0">
          <div class="tich-logo">
          <img style="width: 50px;" src="{{ asset('admin/images/ticket.svg') }}">
          </div>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
        <p>The Following item(s) are already at the Initiator</p>
        <div id="pendingMessage1"></div>
         <a class="btn orange-btn" href="javascript:void(0)" id="untickLastLevelButton">Untick</a>
        <a class="btn orange-btn" href="javascript:void(0)" data-dismiss="modal" id="">Close</a>
      </div>
      
      
    </div>
  </div>
</div>
<div class="modal show" id="ceolevelerror">
  <div class="modal-dialog modal-dialog-centered model-pop-ct" style="max-width: 700px;">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header border-0">
          <div class="tich-logo">
          <img style="width: 50px;" src="{{ asset('admin/images/ticket.svg') }}">
          </div>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
        <p>The Following item(s) at the CEO</p>
        <div id="pendingMessage2"></div>
         <a class="btn orange-btn" href="javascript:void(0)" id="untickceoLevelButton">Untick</a>
        <a class="btn orange-btn" href="javascript:void(0)" data-dismiss="modal" id="">Close</a>
      </div>
      
      
    </div>
  </div>
</div>
<div class="modal show" id="invaliderror">
  <div class="modal-dialog modal-dialog-centered model-pop-ct" style="max-width: 700px;">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header border-0">
          <div class="tich-logo">
          <img style="width: 50px;" src="{{ asset('admin/images/ticket.svg') }}">
          </div>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
        <p>Invalid selection</p>
        <div id="pendingMessage3"></div>
         <a class="btn orange-btn" href="javascript:void(0)" id="untickinvalidButton">Untick</a>
        <a class="btn orange-btn" href="javascript:void(0)" data-dismiss="modal" id="">Close</a>
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
  var rsmLevel = [];
  var initatorLevel = [];
  var ceoLevel = [];
  var invalidLevel = [];
  var missingPocItems = [];
  $(document).ready(function(){
    $('.js-example-basic-multiple').select2({ width: '100%' },{placeholder: 'Select Item Name'});
    $('.dataTables_filter label').contents().filter((_, el) => el.nodeType === 3).remove();
    $(".dataTables_filter label input").keyup(function(){
      $(".search-ct").css("opacity", "0");
    });
    $('.view-data').on('click',function(){
      selected = [];
      $('#selectAll').prop('checked',false)
      $('.action_btn').attr('disabled', true)
      $('#send_quotation').addClass('d-none')
      if ($.fn.DataTable.isDataTable('#zero_config')) {
          // If it is, destroy it first
          $('#zero_config tbody').empty();
          $('#zero_config').DataTable().destroy();
          $('#zero_config tbody').empty();
      }
      if($('#institution_id').val() == '')
      {
        alert('Please select any one Hospital name')
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
    $('.action_btn').click(function(e){
      var userConfirmed = confirm("Are you sure you want to "+$(this).text()+"?");
      if(userConfirmed) {
        $('#loader1').show(); 
        e.preventDefault()
        selected = [];
        initatorLevel = [];
        rsmLevel = [];
        ceoLevel = [];
        invalidLevel = [];
        var btn_type = $(this).attr('btn-fn');
        $('#pendingMessage').empty();
        $('#pendingMessage1').empty();
        $('#pendingMessage2').empty();
        $('#pendingMessage3').empty();
        var rowcollection = table.$(".dt-checkboxes:checked", {"page": "all"});
        rowcollection.each(function(index,elem){
          var row = $(elem).closest("tr");
          var vq_id = row.find('.vq_id').text();
          var institution_id = row.find('.institution_id').text()
          var hospital_name = row.find('.hospital_name').text()
          var rev_no = row.find('.rev_no').text()
          var current_level = row.find('.current_level').text()
          if (btn_type === 'send_back' && current_level == 1) {
            var rsmLevelItems = hospital_name+'-'+institution_id + ' in revision no:' +rev_no + ' already at RSM Level';
            //rsmLevel.push(rsmLevelItems);
            rsmLevel.push({vq_id: vq_id, message: rsmLevelItems});
          }
          if ((btn_type === 'move_up' || btn_type === 'mv_initiator')  && current_level == 7) {
            var initatorLevelItems = hospital_name+'-'+institution_id + ' in revision no:' +rev_no + ' already at Initiator Level';
            //initatorLevel.push(initatorLevelItems);
            initatorLevel.push({vq_id: vq_id, message: initatorLevelItems});
          }
          if ((btn_type === 'move_up' || btn_type === 'mv_initiator')  && current_level == 8) {
            var ceoLevelItems = hospital_name+'-'+institution_id + ' in revision no:' +rev_no + '  at CEO Level';
            //ceoLevel.push(ceoLevelItems);
            ceoLevel.push({vq_id: vq_id, message: ceoLevelItems});
          }
          if (rsmLevel.length == 0 && initatorLevel.length == 0 && ceoLevel.length == 0) {
            selected.push({
              vq_id: vq_id,
              rev_no: rev_no,
              institution_id: institution_id,
              hospital_name: hospital_name,
            });
          }
        });

        if (rsmLevel.length > 0) {
          $('#loader1').hide();
          var errorHtml = '<ul>';
          rsmLevel.forEach(function(item) {
            errorHtml += '<li>' + item.message + '</li>';
          });
          errorHtml += '</ul>';
          $('#pendingMessage').append(errorHtml);
          $('#firstlevelerror').modal('show');
          return;
        }
        if(initatorLevel.length > 0) {
          $('#loader1').hide();
          var errorHtml = '<ul>';
          initatorLevel.forEach(function(item) {
            errorHtml += '<li>' + item.message + '</li>';
          });
          errorHtml += '</ul>';
          $('#pendingMessage1').append(errorHtml);
          $('#lastlevelerror').modal('show');
          return;
        } 
        if(ceoLevel.length > 0) {
          $('#loader1').hide();
          var errorHtml = '<ul>';
          ceoLevel.forEach(function(item) {
            errorHtml += '<li>' + item.message + '</li>';
          });
          errorHtml += '</ul>';
          $('#pendingMessage2').append(errorHtml);
          $('#ceolevelerror').modal('show');
          return;
        } 
        if(rsmLevel.length == 0 && initatorLevel.length == 0&& ceoLevel.length == 0) {
          //console.log('Selected items:', selected);
          var settings = {
            "url": "/initiator/workflow_adjust",
            "method": "POST",
            "timeout": 0,
            "headers": {
              "Accept-Language": "application/json",
              "Content-Type": "application/json"
            },
            "data": JSON.stringify({
              "_token": "{{ csrf_token() }}",
              "selected_rows": selected,
              "action":btn_type
            })
          };
          $.ajax(settings).done(function (response) {
            $('#loader1').hide(); 
            if(response.success == true)
            {
              $('#successModal').modal({backdrop: 'static', keyboard: false},'show');
            }
            else
            {
              if(response.type == 'invalid_selection')
              {
                var invalidSelection = response.result;
                if(invalidSelection.length > 0) {
                  var errorHtml = '<ul>';
                  invalidSelection.forEach(function(item) {
                    errorHtml += '<li> Invalid Condition for ' + item.inst_name + ' - '+ item.inst_id +' in revision no '+ item.rev_no+' </li>';
                    invalidLevel.push({vq_id: item.vq_id, message: errorHtml});
                  });
                  errorHtml += '</ul>';
                  $('#pendingMessage3').append(errorHtml);
                  $('#invaliderror').modal('show');
                } 
              }
              else
              {
                alert("Error Occured. Please try again");
              }
            }           
          });
        }
      } else {
      
      }
    });
    $('#untickFirstLevelButton').click(function() {
      rsmLevel.forEach(function(item) {
        // Uncheck the corresponding row in the DataTable
        var row = table.$('tr').filter(function() {
          return $(this).find('.vq_id').text() == item.vq_id;
        });
        row.find('.dt-checkboxes').prop('checked', false);
        table.row(row).deselect();
      });
      $('#firstlevelerror').modal('hide');
      enableBtn();
    });
    $('#untickLastLevelButton').click(function() {
      initatorLevel.forEach(function(item) {
        // Uncheck the corresponding row in the DataTable
        var row = table.$('tr').filter(function() {
          return $(this).find('.vq_id').text() == item.vq_id;
        });
        row.find('.dt-checkboxes').prop('checked', false);
        table.row(row).deselect();
      });
      $('#lastlevelerror').modal('hide');
      enableBtn();
    });
    $('#untickceoLevelButton').click(function() {
      ceoLevel.forEach(function(item) {
        // Uncheck the corresponding row in the DataTable
        var row = table.$('tr').filter(function() {
          return $(this).find('.vq_id').text() == item.vq_id;
        });
        row.find('.dt-checkboxes').prop('checked', false);
        table.row(row).deselect();
      });
      $('#ceolevelerror').modal('hide');
      enableBtn();
    });
    $('#untickinvalidButton').click(function() {
      invalidLevel.forEach(function(item) {
        // Uncheck the corresponding row in the DataTable
        var row = table.$('tr').filter(function() {
          return $(this).find('.vq_id').text() == item.vq_id;
        });
        row.find('.dt-checkboxes').prop('checked', false);
        table.row(row).deselect();
      });
      $('#invaliderror').modal('hide');
      enableBtn();
    });
  });
  function init_table()
  {
      $('#loader1').show(); 
      table = $("#zero_config").DataTable({
        "pageLength": 50,
        "aaSorting": [[ 2, "asc" ]],
        responsive: true,
        ajax: {
            url: '/initiator/get_pending_vq_data_workflow',
            type: 'GET',
            data: function (d) {
            // Add custom data to the request
                d.institution_id = $('#institution_id').val();
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
          { data: 'id' },//0
          { data: 'rev_no' },//1
          { data: 'hospital_name' },//2
          { data: 'institution_id' },//3
          { data: 'institution_id' },//4
          { data: 'address' },//5
          { data: 'city' },//6
          { data: 'state_name' },//7
          { data: function(row){//8
              if(row.current_level == 1) return 'RSM'
              if(row.current_level == 2) return 'ZSM'
              if(row.current_level == 3) return 'NSM'
              if(row.current_level == 4) return 'SBU'
              if(row.current_level == 5) return 'Semi Cluster'
              if(row.current_level == 6) return 'Cluster'
              if(row.current_level == 7) return 'Initiator'
              if(row.current_level == 8) return 'CEO'
            } 
          },
          { data: 'id' },//9
          { data: 'institution_id' },//10
          { data: 'current_level' },//11
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
                className: 'rev_no text-center', 
                
            }, 
            {
                targets: 2, 
                className: 'hospital_name', 
                
            },
            {
                targets: 9, 
                className: 'd-none vq_id', 
                
            },
            {
                targets: 10, 
                className: 'd-none institution_id', 
                
            },
            {
                targets: 11, 
                className: 'd-none current_level', 
                
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
  function singlePayModeChangeHandler(e, ptr, inputDiscRate){
      let selectedPaymentMode = e.val();
      let netDiscountRateToStockist;
      let inputMargin = 10;
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
    


    $('.action_btn').prop("disabled", !anyRowChecked && !isHeaderChecked);
  }
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

