@extends('layouts.admin.app')
@section('content')
  <!-- <link rel='stylesheet' href="https://cdn.datatables.net/r/dt/dt-1.10.9/datatables.min.css"> -->
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
                  <button class="btn btn-primary view-data orange-btn-bor">
                    Fetch VQ
                  </button>
                </li>
              </ul>
            </div>
            <div class="col-md-6 d-flex">
              <div class="cancel-btn ml-auto">
                <!-- <button class="orange-btn" id="save_changes_btn" disabled>Save Changes</button> -->
                <button class="btn btn-danger action_btn mr-2" id="move_up" btn-fn="move_up" disabled>Move Up</button>
                <button class="btn btn-danger action_btn mr-2" id="send_back" btn-fn="send_back" disabled>Send Back</button>
                <button class="btn btn-danger action_btn" id="move_initiator" btn-fn="mv_initiator" disabled>Move to Initiator</button>
              </div>
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
        </div>
      </div>
    </div>
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
      <div class="modal-body text-center">
        <p>Following item(s) already in RSM Level</p>
        <div class="mb-3" id="pendingMessage"></div>
         <a class="btn btn-warning" href="javascript:void(0)" id="untickFirstLevelButton">Untick</a>
        <a class="btn btn-warning" href="javascript:void(0)" data-bs-dismiss="modal" id="">Close</a>
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
      <div class="modal-body text-center">
        <p>Following item(s) already in Initiator Level</p>
        <div class="mb-3" id="pendingMessage1"></div>
         <a class="btn btn-warning" href="javascript:void(0)" id="untickLastLevelButton">Untick</a>
        <a class="btn btn-warning" href="javascript:void(0)" data-bs-dismiss="modal" id="">Close</a>
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
      <div class="modal-body text-center">
        <p>Updation successfully done</p>
        <!-- <a class="btn btn-warning" href="{{route('initiator_listing')}}" id="">VQ Request Detail</a> -->
        <a class="btn btn-warning" href="{{route('adjust_workflow_admin')}}" id="">Close</a>
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
      <div class="modal-body text-center">
        <p>The Following item(s) at the CEO</p>
        <div class="mb-3" id="pendingMessage2"></div>
         <a class="btn btn-warning" href="javascript:void(0)" id="untickceoLevelButton">Untick</a>
        <a class="btn btn-warning" href="javascript:void(0)" data-bs-dismiss="modal" id="">Close</a>
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
      <div class="modal-body text-center">
        <p>Invalid selection</p>
        <div class="mb-3" id="pendingMessage3"></div>
         <a class="btn btn-warning" href="javascript:void(0)" id="untickinvalidButton">Untick</a>
        <a class="btn btn-warning" href="javascript:void(0)" data-bs-dismiss="modal" id="">Close</a>
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
    $('#institution_id').on('change', function() {
        let select2Container = $(this).next('.select2-container');
        let selectionRendered = select2Container.find('.select2-selection__rendered');
        
        // Calculate the new height based on the content
        let newHeight = Math.max(selectionRendered.height() + 50, 32); // Minimum height is 66px

        // Apply the new height
        select2Container.find('.select2-selection--multiple').css('height', newHeight + 'px');
    });
    $('.view-data').on('click',function(){
      selected = [];
      $('#selectAll').prop('checked',false)
      $('.action_btn').attr('disabled', true)
      $('#send_quotation').addClass('d-none')
      if ($.fn.DataTable.isDataTable('#zero_config_admin')) {
          // If it is, destroy it first
          $('#zero_config_admin tbody').empty();
          $('#zero_config_admin').DataTable().destroy();
          $('#zero_config_admin tbody').empty();
      }
      if($('#institution_id').val() == '')
      {
        alert('Please select any one Hospital')
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
            var rsmLevelItems = hospital_name + ' in ' + rev_no + ' revision ' + ' already in RSM Level';
            //rsmLevel.push(rsmLevelItems);
            rsmLevel.push({vq_id: vq_id, message: rsmLevelItems});
          }
          if ((btn_type === 'move_up' || btn_type === 'mv_initiator')  && current_level == 7) {
            var initatorLevelItems = hospital_name + ' in ' + rev_no + ' revision ' + ' already in Initiator Level';
            //initatorLevel.push(initatorLevelItems);
            initatorLevel.push({vq_id: vq_id, message: initatorLevelItems});
          }
          if ((btn_type === 'move_up' || btn_type === 'mv_initiator')  && current_level == 8) {
            var ceoLevelItems = hospital_name+'-'+institution_id + ' in revision no:' +rev_no + '  at CEO Level';
            //ceoLevel.push(ceoLevelItems);
            ceoLevel.push({vq_id: vq_id, message: ceoLevelItems});
          }
          if (rsmLevel.length == 0 && initatorLevel.length == 0) {
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
        } 
        if(rsmLevel.length == 0 && initatorLevel.length == 0 && ceoLevel.length == 0) {
          //console.log('Selected items:', selected);
          var settings = {
            "url": "/admin/workflow_adjust",
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
             //$('#successModal').modal('show');
              var myModal = new bootstrap.Modal(document.getElementById('successModal'), {
                backdrop: 'static',
                keyboard: false
              });

              myModal.show();
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
                  //$('#invaliderror').modal('show');
                  var myModal = new bootstrap.Modal(document.getElementById('invaliderror'), {
                    backdrop: 'static',
                    keyboard: false
                  });

                  myModal.show();
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
      table = $("#zero_config_admin").DataTable({
        "pageLength": 10,
        "aaSorting": [[ 2, "asc" ]],
        responsive: true,
        ajax: {
            url: '/admin/get_pending_vq_data_workflow',
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
        /*'language': {
            'paginate': {
                'previous': "<img src='{{ asset('admin/images/left.svg') }}' class='left-block' alt=''> <img src='{{ asset('admin/images/left1.svg') }}' class='disbpr' alt=''>",
                'next': "<img src='{{ asset('admin/images/right.svg') }}' class='neblk' alt=''> <img src='{{ asset('admin/images/right1.svg') }}' class='disbn' alt=''>"
            }
        },*/
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
    var anyRowChecked = $('#zero_config_admin tbody').find("tr.selected input:checked").length > 0;
    var headerCheckbox = $('.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]');
    var isHeaderChecked = headerCheckbox.prop("checked");
    


    $('.action_btn').prop("disabled", !anyRowChecked && !isHeaderChecked);
  }
</script>
@endpush



