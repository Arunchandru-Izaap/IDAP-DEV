@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
  #messageContainer
  {
    height: 150px;
    overflow: auto;
  }
  #pendingMessage
  {
    height: 100px;
    overflow: auto;
  }
  #pendingInstitutions
  {
    display: block;
    height: 100px;
    overflow: auto;
  }
  .disabled { pointer-events: none; opacity: 0.6; }
  .div_filter
  {
    position: absolute;
    left: 14rem;
    width: 40% !important;
    bottom: 0.4rem;
  }
  #loader1
    {
      background-image: url(../../images/loader.gif);
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
    #messageContainer span {
      display: block;
      
    }
</style>
<div id="page-content-wrapper">
                <!-- Top navigation-->
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                            <ul class="navbar-nav dashboard-nav">
                                <li class="nav-item active"><a class="nav-link" href="#!"> <img src="{{ asset('admin/images/back.svg')}}">VQ Request Listing</a></li>
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
                            View Request
                        </a>
                    </li>
                    <li class="">
                      <a href="{{route('initiator_listing')}}">
                        VQ Request Listing
                      </a>
                    </li>
                    <li class="active">
                      <a href="#">
                      @isset($vq_listing)
                      {{ $vq_listing['hospital_name'] }}
                      @endisset
                      <?php
                        $flag = '';
                        if (!isset($vq_listing)) {
                          $flag = 'style="display:none;"';
                        }
                      ?>
                      </a>
                    </li>
                </ul>
                <!-- Page content-->
                <div class="container-fluid">
                    <!-- <h1 class="mt-4">Simple Sidebar</h1> -->
                    <div class="row">
                      <div id='loader1' style='display: none;'>
                              
                        </div>
<!--
<div class="cancel-btn ml-auto">

    <a href="" id="selected_reinit" class="orange-btn">
    Send Quotation
    </a>
</div>
-->

                            <div class="col-md-12 d-flex send-quotation">
<!--                                 <h3 class="pl-15">Lilavati Hospital</h3> -->
                            <div class="cancel-btn ml-auto">
                            
                                <button id="selected_reinit" class="orange-btn">
                                RE-INITIATE VQ REQUEST
                                </button>
                            </div>
                        </div>

                        <input type="hidden" id="inputDataInstitutes" value="{{ $inputData['institutes'] }}">

                        <input type="hidden" id="inputDataSkipApproval" value="{{ $inputData['skip_approval'] }}">

                        <input type="hidden" id="inputDataSelectApproval" value="{{ $inputData['select_approval'] }}">
                        
                        <div class="col">
                            <div class="actions-dashboard table-ct">

                              <table class="table VQ Request Listing vq-request-listing-tb nowrap" id="zero_config">
                                    <thead>
                                    <tr>
	                                    <th><input type="checkbox" id="checkAll"></th>
                                        <th class="dvname">Division Name</th>
                                        <th>Mother Brand Name</th>
                                        <th>Item Code</th>
                                        <th class="mbrname">Brand Name</th>
                                        <th>Pack</th>
                                        <th>Type</th>
                                        <th class="digitcenter">GST</th>
                                        <th class="digitcenter" <?php echo $flag;?>>L. Y. Disc.(%)</th>
                                        <th class="digitcenter" <?php echo $flag;?>>L. Y. Disc Rate</th>
                                        <th class="digitcenter">MRP</th>
                                        <th class="digitcenter">L. Y. MRP</th>
                                        <th class="digitcenter" <?php echo $flag;?>>MRP Margin</th>
                                        <th class="digitcenter">C. Y. PTR</th>
                                        <th class="digitcenter" <?php echo $flag;?>>Disc.PTR (%)</th>
                                        <th class="digitcenter" <?php echo $flag;?>>RTH</th>
                                        <th>HSN Code</th>
                                        <th>Composition</th>
                                        <th class="d-none">Product Type</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($final_data as $item)
                                        <tr>
	                                        <!-- <td class="call-checkbox">{{$item->item_code}}</td> -->
                                          <td><input type="checkbox" class="row-checkbox {{ $item->discounted_flag ? 'disabled' : '' }}" ></td>
                                            <td class="div_name">{{$item->div_name}}</td>
                                            <td>{{$item->mother_brand_name}}</td>

                                            <td class="item_code">{{$item->item_code }}</td>
                                            <td class="brand_name">{{$item->brand_name}}</td>
                                            <td>{{$item->pack}}</td>
                                            <td>{{$item->type}}</td>
                                            <td>{{$item->applicable_gst}}</td>
                                            <td <?php echo $flag;?>>{{round($item->last_year_percent,2)}}</td>
                                            <td <?php echo $flag;?>>{{round($item->last_year_rate,2)}}</td>
                                            <td>{{$item->mrp}}</td>
                                            <td>{{round($item->last_year_mrp,2)}}</td>
                                            <td <?php echo $flag;?>>{{round($item->mrp_margin,2)}}</td>
                                            <td>{{$item->ptr }}
                                             <!-- <input type="text" class="cBalance" value="">-->
                                            </td>
                                           
                                            <td <?php echo $flag;?>>{{$item->discount_percent}}</td>
                                            <td <?php echo $flag;?>>{{$item->discount_rate }}</td>
                                           
                                        
                                            <td>{{$item->hsn_code}}</td>
                                            <td>{{$item->composition }}</td>
                                            <td class="product_type d-none">{{$item->product_type }}</td>
                                            
   


                                          
                                            

                                        </tr>
                                    @endforeach
                                </tbody>
                              </table>

                            </div>
                        </div><!-- col close -->


<!-- Pending Item Modal -->
<div class="modal show" id="pendingModal">
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
          <p class="d-none">Total <span id="pendingCount"></span> item(s) are pending with approver</p>
          <p>Following item(s) pending with approver in institution(s) <span style="font-weight: 500;" class="d-none" id="multiInstDisp">Please go back and remove the pending institution(s)</span> <span id="pendingInstitutions"></span></p>
          <a href="javascript:void(0)" class="pending_instiutions1 d-none">
              Download pending institutions
          </a>
          <div id="pendingMessage"></div>
          <a href="javascript:void(0)" class="pending_item">
              Download pending items
          </a>
          <a class="btn orange-btn" href="javascript:void(0)" id="untick_pending">UNTICK PENDING ITEM</a>
          <a class="btn orange-btn" href="javascript:void(0)" data-dismiss="modal" id="">CLOSE</a>
        </div>
        
        
      </div>
    </div>
  </div>
  <div class="modal show" id="NewModal">
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
          <p>Following new item(s) present so only one item selection is allowed</p>
          <div id="messageContainer"></div>
          <a class="btn orange-btn" href="javascript:void(0)" id="untick_pending_new_item">UNTICK NEW ITEM</a>
          <a class="btn orange-btn" href="javascript:void(0)" data-dismiss="modal" id="">CLOSE</a>
        </div>
        
        
      </div>
    </div>
  </div>
  <script src="{{asset('frontend/js/select2.js')}}"></script>
<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>

<script>
      /****************************************
       *       Basic Table                   *
       ****************************************/
/*
      $("#zero_config").DataTable(

        {  language: {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  }}
        );
*/
var table;
var pending_items =[];
var newItemSelected = [];
$(document).ready(function () {


/*
var checked_status = this.checked;
    if (checked_status == true) {
       $("#yourbuttonid").removeAttr("disabled");
       alert('ala')
    } else {
       $("#yourbuttonid").attr("disabled", "disabled");
    }	
*/

  
/*
$('#selected_reinit').prop("disabled", true);
	$('input[type="checkbox"]').click(function() {
		alert('chck click')
	 if ($(this).is(':checked')) {
		 alert('click')
	 $('#selected_reinit').prop("disabled", false);
	 } else {
	 if ($('.dt-checkboxes').filter(':checked').length < 1){
	 $('#selected_reinit').attr('disabled',true);}
	 }
	});
*/
// var rowcollection = table.$(".dt-checkboxes:checked", {"page": "all"});
// alert(rowcollection);
var table01 = $("#zero_config");
var btn = $('#selected_reinit');
var total_item_count = <?php echo count($final_data);?>;
var selectedCount = 0;

function enableBtn() {//changed fn to listen for the common checkbox checked event 30042024
     var visibleRows = table01.find("tr:visible");
    var anyRowChecked = visibleRows.find("input:checked").length > 0;
    var headerCheckbox = $('.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]');
    var isHeaderChecked = headerCheckbox.prop("checked");
    let text = $('.select-item').text();

    // Extracting the value before " rows"
    let selectedRowValue = text.split(" ")[0];

    if (!isNaN(selectedRowValue)) {
      if(selectedRowValue>0)
      {
        anyRowChecked = true;
      }
    }

    btn.prop("disabled", !anyRowChecked && !isHeaderChecked);
}

table01.on("change", "input[type='checkbox']", function(){
  enableBtn()
});
$('body').on("change",'.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]', function(){
  enableBtn();
});//added fn to listen for the common checkbox checked event 30042024
enableBtn();


// setTimeout(function(){
table = $('#zero_config').DataTable({     
      'columnDefs': [
         {
            'targets': 0,
            'orderable': false
            /*'data': 3,//changed from 2 to 3 as the selected number of rows is not coming correctly
            'checkboxes': {
               'selectRow': true
            }*/
         }
      ],
      /*'select': {
         'style': 'multi'
      },*/
      'order': [[1, 'asc']],
      "pageLength": 50,
      'language': { 
              'paginate': {      
                'previous': "<img src='{{ asset('admin/images/left.svg') }}' class='left-block' alt=''> <img src='{{ asset('admin/images/left1.svg') }}' class='disbpr' alt=''>",      'next': "<img src='{{ asset('admin/images/right.svg') }}' class='neblk' alt=''> <img src='{{ asset('admin/images/right1.svg') }}' class='disbn' alt=''>"    
                }  
              },
        scrollX: true,
        "initComplete": function(settings, json) {
          var $customDropdown = $(`<select class="form-control js-example-basic-single" id="division_filter"><option value="">Select Division</option>@foreach($uniqueDivisionNames as $divisionName)<option value="{{ $divisionName }}">{{ $divisionName }}</option>@endforeach`);
          $('#zero_config_wrapper .row .col-md-6 .dataTables_length').append($customDropdown);
          $('.js-example-basic-single').select2({ width: '100%' },{placeholder: 'Select Item Name'});
          $('.select2-container').addClass('div_filter');
        }
   });
$('#zero_config_wrapper > div:eq(2) .col-sm-12:eq(0)').append('<span class="select-info"><span class="select-item"></span></span>');
   // },100);
// var table = $('#zero_config').DataTable();
// console.log("test");       

// setTimeout(function(){
	//var table = $('#zero_config').DataTable();
/*	$('#zero_config tbody').on( 'click', 'tr', function () {
    	vqDataR.push(table.row( this ).data() );
    	console.log(vqDataR)
	});*/
// }, 500);

$('#selected_reinit').click(function(e){
      e.preventDefault();
      $('#selected_reinit').attr('disabled', true);//added on 24042024
      $('#loader1').show(); 
      var rowcollection = table.$(".row-checkbox:checked", {"page": "all"});
      vqDataR = []
      var selectedInstitutions = JSON.parse($('#inputDataInstitutes').val())
      if (rowcollection.length > 0) {
        if (rowcollection.length > 1 /*&& selectedInstitutions.length==1*/) {
            // Check if any unchecked checkbox is disabled
            var checkedCheckboxes = table.$(".row-checkbox:checked", {"page": "all"});
            var isAnyNew = false;
            var newItems = [];
            newItemSelected = []

            checkedCheckboxes.each(function(index, elem) {
                var row = $(elem).closest("tr");
                if(row.find(".product_type").text() == 'new')
                {
                  var itemName = '<span>Item Code '
                  +row.find(".item_code").text().trim()+' - '+row.find(".brand_name").text().trim()+' in Division - '+row.find(".div_name").text().trim()+'</span>';
                  newItems.push(itemName);
                  newItemSelected.push(row.find(".item_code").text().trim())
                  /*isAnyNew = true;
                  return false;*/
                }
            });

            if(newItems.length > 0)
            {
              var newItemsList = newItems.join('\n');
              //alert('For new products, only one item selection is allowed.\nNew Items:\n' + newItemsList);
              $('#selected_reinit').attr('disabled', false);
              $('#loader1').hide(); 
              $('#messageContainer').empty();
              $('#messageContainer').append(newItemsList);
              $('#NewModal').modal({backdrop: 'static', keyboard: false},'show');
              return
            }
        }
        rowcollection.each(function(index,elem){
            //You have access to the current iterating row
            var checkbox_value = $(elem).val();
            var row = $(elem).closest("tr");
            var item_code = (row.find(".item_code").text());
            vqDataR.push(item_code);
            //Do something with 'checkbox_value'
        });
      } else {
        alert('Please select atleast one item')
        $('#selected_reinit').attr('disabled', false);
        $('#loader1').hide(); 
        return;
      }
      
      if(selectedInstitutions.length>1 && rowcollection.length > 1)
      {
        /*var settings = {
          "url": "/initiator/checkMultiInstituteNewItem",
          "method": "POST",
          "timeout": 0,
          "headers": {
            "Accept-Language": "application/json",
            "Content-Type": "application/json"
          },
          "data": JSON.stringify({
            "_token": "{{ csrf_token() }}",
            "item_codes": vqDataR,
            "selectedInstitutions": selectedInstitutions,
          })
        };
        $.ajax(settings).done(function (response) {
          if (!response.status) {
            $('#loader1').hide();
            $('#NewModal').modal({backdrop: 'static', keyboard: false},'show');
            $('#selected_reinit').attr('disabled', false);
            $('#messageContainer').empty();
            let container = document.getElementById('messageContainer');
            var newItems = [];
            newItemSelected = []
            response.data.forEach(function(item) {
                let itemCode = item.item_code;
                let institutions = item.institutions;
                var selectedrowsTable = table.$(".row-checkbox:checked", {"page": "all"});
                let matchingRow = selectedrowsTable.filter(function() {
                    return $(this).closest('tr').find('.item_code').text() === itemCode;
                });
                newItemSelected.push(itemCode.toString())
                if (matchingRow.length > 0) {
                  // Assuming the division name is stored in a data attribute or a specific column
                  let divisionName = matchingRow.closest('tr').find('.brand_name').text()+ ' - in Division '+matchingRow.closest('tr').find('.div_name').text(); // Adjust the selector as needed

                  // Create a new paragraph for each item code, institutions, and division name
                  let paragraph = document.createElement('span');
                  paragraph.textContent = `Item code ${itemCode} - ${divisionName} is new for institutions: ${institutions.join(', ')}`;
                  paragraph.textContent = `Item code ${itemCode} - ${divisionName}`
                  var itemName = `Item code ${itemCode} - ${divisionName}`
                  newItems.push(itemName);
                  container.appendChild(paragraph);
                }
            });

            // if(newItems.length > 0)
            // {
            //   var newItemsList = newItems.join('\n');
            //   alert('For new products, only one item selection is allowed.\nNew Items:\n' + newItemsList);
            // }
          } else {
            reinitiate(vqDataR);
          }
        });*/
        reinitiate(vqDataR);
      }
      else
      {
        reinitiate(vqDataR);
      }
      
    });
    $('#untick_pending').on('click', function(){
      let confirmSaveChanges = confirm("Do you want to untick the pending items?")
      if(confirmSaveChanges){
        var valuesToCheck = Array.isArray(pending_items) ? pending_items : [];
        table.rows().every(function() {
          var rowData = this.data();
          
          var rowValue = rowData[3];

          if (valuesToCheck.indexOf(rowValue) !== -1) {
            $(this.node()).find('.row-checkbox').prop('checked', false);
            //$(this.node()).find('input[type="checkbox"]').trigger('change');
            $(this.node()).removeClass('selected')
          }
        });
        table.draw(false);
        $('.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]').prop('checked', false)
        updateSelectedCount();
        if(table.cells().nodes().to$().find('input[type="checkbox"]:checked').length == 0)
        {
          $('.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]').prop('checked', false);
          enableBtn();
        }
        $('#pendingModal').modal('hide');
      }
    })
    $('#untick_pending_new_item').on('click', function(){
      let confirmSaveChanges = confirm("Do you want to untick the new items?")
      if(confirmSaveChanges){
        var valuesToCheck = Array.isArray(newItemSelected) ? newItemSelected : [];
        table.rows().every(function() {
          var rowData = this.data();
          
          var rowValue = rowData[3];

          if (valuesToCheck.indexOf(rowValue) !== -1) {
            $(this.node()).find('.row-checkbox').prop('checked', false);
            //$(this.node()).find('input[type="checkbox"]').trigger('change');
            $(this.node()).removeClass('selected')
          }
        });
        table.draw(false);
        $('.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]').prop('checked', false)
        updateSelectedCount();
        if(table.cells().nodes().to$().find('input[type="checkbox"]:checked').length == 0)
        {
          $('.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]').prop('checked', false);
          enableBtn();
        }
        $('#NewModal').modal('hide');
      }
    })

$(".table-ct .dataTables_filter label").append("<img src='{{ asset('admin/images/search.svg') }}' class='search-ct' alt=''>");
$('.dataTables_filter label').contents().filter((_, el) => el.nodeType === 3).remove();
$(".dataTables_filter label input").keyup(function(){
        $(".search-ct").css("opacity", "0");
});
$('#checkAll').on('click', function() {
  var checked = this.checked;
  table.rows({ search: 'applied' }).every(function() {
    var node = this.node();
    var checkbox = $(node).find('.row-checkbox');
    if (!checkbox.hasClass('disabled')) {
      checkbox.prop('checked', checked);
    }
    return true;
  });
  updateSelectedCount();
});
// Handle individual checkbox change
$('#zero_config tbody').on('change', '.row-checkbox', function() {
  if (!this.checked) {
    $('#checkAll').prop('checked', false);
  }
  var allChecked = true;
  $('#zero_config tbody .row-checkbox').each(function() {
      if (!this.checked) {
          allChecked = false;
          return false; // Exit the loop early if any checkbox is unchecked
      }
  });

  // Update the checkAll checkbox based on the status of all checkboxes
  $('#checkAll').prop('checked', allChecked);
  updateSelectedCount();
});


updateSelectAllCheckbox();

$('body').on('change','#division_filter',function(){
  var filterValue = $(this).val();
  table
    .column(1)
    .search(filterValue)
    .draw();
})
  $('.pending_item').on('click',function(){

    var url = "{{ route('pending-item-export') }}";

    // Redirect to the constructed URL
    window.location.href = url;
  });
  $('.pending_instiutions').on('click',function(){

    var url = "{{ route('pending-institution-export') }}";

    // Redirect to the constructed URL
    window.location.href = url;
  });
});
// Update selected count for all page entries
function updateSelectedCount() {
  selectedCount = 0;
  table.rows({"page": "all"}).every(function() {
    var node = this.node();
    var checkbox = $(node).find('.row-checkbox');
    if (checkbox.prop('checked')&& !checkbox.hasClass('disabled')) {
      selectedCount++;
    }
    return true;
  });
  if(selectedCount == 0)
  {
    $('.select-item').text('');
  }
  else if(selectedCount == 1)
  {
    $('.select-item').text(selectedCount + ' row selected')
  }
  else
  {
   $('.select-item').text(selectedCount + ' rows selected')
  }
}
function updateSelectAllCheckbox() {
    var allDisabled = true;
    var allChecked = true;
    table.rows({ search: 'applied' }).every(function() {
        var node = this.node();
        var checkbox = $(node).find('.row-checkbox');

        if (!checkbox.hasClass('disabled')) {
            allDisabled = false;
            if (!checkbox.prop('checked')) {
                allChecked = false;
            }
        }
    });

    $('#checkAll').prop('disabled', allDisabled);
}
function reinitiate(vqDataR)
{
  $('#multiInstDisp').addClass('d-none');
  $('.pending_instiutions').addClass('d-none');
  var url_string =window.location.href;
  var url = new URL(url_string);
  var startdate = "{{ data_get($inputData, 'start_date') }}";
  var enddate = "{{ data_get($inputData, 'end_date') }}";
  var institutesCodeArr = $('#inputDataInstitutes').val();
  var inputDataSkipApproval = $('#inputDataSkipApproval').val()
  var inputDataSelectApproval = $('#inputDataSelectApproval').val()
  var settings = {
      "url": "/initiator/reinitiate",
      "method": "POST",
      "timeout": 0,
      "headers": {
        "Accept-Language": "application/json",
      },
      "data": {
        "_token": "{{ csrf_token() }}",
        "institution_code": institutesCodeArr ? institutesCodeArr : "{{ app('request')->route('id') }}",
        "item_codes": vqDataR,
        "skip_approval": inputDataSkipApproval,
        "selected_approval": inputDataSelectApproval,
        "from": startdate,
        "to": enddate,
      }
    };
    $.ajax(settings).done(function (response) {
      console.log(response);
      if(response.state == true)//added on 24042024 for checking the pending item code
      {
        $('#loader1').hide(); 
        $('#startval').empty();//added to empty the date if multiple click happens
        $('#enddateval').empty();//added to empty the date if multiple click happens
        $('#startval').append(startdate);
        $('#enddateval').append(enddate);
        $('#myModal').modal('show');
        // window.location.href ='{{route("initiator_listing")}}';
      }
      else//added on 24042024 for checking the pending item code and display alert
      {
        $('#loader1').hide(); 
        $('#selected_reinit').attr('disabled', false);
        //alert(response.message);
        var selected_inst = JSON.parse($('#inputDataInstitutes').val())
        if(selected_inst.length>1)
        {
          $('#multiInstDisp').removeClass('d-none');
          $('.pending_instiutions').removeClass('d-none');
        }
        $('#pendingModal').modal({backdrop: 'static', keyboard: false},'show');
        $('#pendingCount').text(response.count);
        //$('#pendingMessage').html(response.message);
        $('#pendingMessage').empty(); // Clear existing content if necessary
        const pendingItemInst = response.pendingItem_listInst;
        let messagesHTML = '';
        Object.keys(pendingItemInst).forEach(institution => {
            const itemCodes = pendingItemInst[institution].join(', ');
            messagesHTML += `<div>Institution: ${institution} - Item codes: ${itemCodes}</div>`;
        });
        document.getElementById('pendingMessage').innerHTML = messagesHTML;
        $('#pendingInstitutions').html(response.pendingInstitutions);
        pending_items = Array.isArray(response.pendingItems) ? response.pendingItems : Object.values(response.pendingItems);
        console.log(pending_items)
      }
      
    });
}
    </script>
        <style type="text/css">
        .copy-center{
            position:inherit !important;margin: 20px 0;
        }
            table.dataTable.dt-checkboxes-select tbody tr,
table.dataTable thead th.dt-checkboxes-select-all,
table.dataTable tbody td.dt-checkboxes-cell {
  cursor: pointer;
}

table.dataTable thead th.dt-checkboxes-select-all,
table.dataTable tbody td.dt-checkboxes-cell {
  text-align: center;
}

div.dataTables_wrapper span.select-info,
div.dataTables_wrapper span.select-item {
  margin-left: 0.5em;
}

   div.dataTables_wrapper {
        max-width: 1280px;
        width: 100%;
        margin: 0 auto;
    }
    div.dataTables_wrapper span.select-info, div.dataTables_wrapper span.select-item{
      margin-left: 1.5em !important;
      top: 1.1em;
      position: absolute;
      font-size: 14px;
      width: 100%;
    }
            </style>
@endsection

