@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
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
                      </a>
                    </li>
                </ul>
                <!-- Page content-->
                <div class="container-fluid">
                    <!-- <h1 class="mt-4">Simple Sidebar</h1> -->
                    <div class="row">

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
	                                    <th></th>
                                        <th class="dvname">Division Name</th>
                                        <th>Mother Brand Name</th>
                                        <th>Item Code</th>
                                        <th class="mbrname">Brand Name</th>
                                        <th>Pack</th>
                                        <th>Type</th>
                                        <th class="digitcenter">GST</th>
                                        <th class="digitcenter">L. Y. Disc.(%)</th>
                                        <th class="digitcenter">L. Y. Disc Rate</th>
                                        <th class="digitcenter">MRP</th>
                                        <th class="digitcenter">L. Y. MRP</th>
                                        <th class="digitcenter">MRP Margin</th>
                                        <th class="digitcenter">C. Y. PTR</th>
                                        <th class="digitcenter">Disc.PTR (%)</th>
                                        <th class="digitcenter">RTH</th>
                                        <th>HSN Code</th>
                                        <th>Composition</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($final_data as $item)
                                        <tr>
	                                        <td class="call-checkbox">{{$item->item_code}}</td>
                                            <td>{{$item->div_name}}</td>
                                            <td>{{$item->mother_brand_name}}</td>

                                            <td class="item_code">{{$item->item_code }}</td>
                                            <td class="brand_name">{{$item->brand_name}}</td>
                                            <td>{{$item->pack}}</td>
                                            <td>{{$item->type}}</td>
                                            <td>{{$item->applicable_gst}}</td>
                                            <td>{{round($item->last_year_percent,2)}}</td>
                                            <td>{{round($item->last_year_rate,2)}}</td>
                                            <td>{{$item->mrp}}</td>
                                            <td>{{round($item->last_year_mrp,2)}}</td>
                                            <td>{{round($item->mrp_margin,2)}}</td>
                                            <td>{{$item->ptr }}
                                             <!-- <input type="text" class="cBalance" value="">-->
                                            </td>
                                           
                                            <td>{{$item->discount_percent}}</td>
                                            <td>{{$item->discount_rate }}</td>
                                           
                                        
                                            <td>{{$item->hsn_code}}</td>
                                            <td>{{$item->composition }}</td>
                                            
   


                                          
                                            

                                        </tr>
                                    @endforeach
                                </tbody>
                              </table>

                            </div>
                        </div><!-- col close -->


<!-- Pending Item Modal -->
<div class="modal show" id="pendingModal">
    <div class="modal-dialog modal-dialog-centered model-pop-ct">
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
          <p>Following item(s) pending with approver</p>
          <div id="pendingMessage"></div>

          <!-- <a class="btn orange-btn" href="javascript:void(0)" id="untick_pending">UNTICK PENDING ITEM</a> -->
          <a class="btn orange-btn" href="javascript:void(0)" id="untick_pending">Close</a>
        </div>
        
        
      </div>
    </div>
  </div>
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
var pending_items;
var total_item_count = <?php echo count($final_data);?>

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
var table = $('#zero_config').DataTable({     
      'columnDefs': [
         {
            'targets': 0,
            'data': 3,//changed from 2 to 3 as the selected number of rows is not coming correctly
            'checkboxes': {
               'selectRow': true
            }
         }
      ],
      'select': {
         'style': 'multi'
      },
      'order': [[1, 'asc']],
      /*'pageLength': 10,
      'lengthMenu': [5, 10, 20, 50, 100, 200, 500,1000,1500,1600,1700],*/
      'language': { 
              'paginate': {      
                'previous': "<img src='{{ asset('admin/images/left.svg') }}' class='left-block' alt=''> <img src='{{ asset('admin/images/left1.svg') }}' class='disbpr' alt=''>",      'next': "<img src='{{ asset('admin/images/right.svg') }}' class='neblk' alt=''> <img src='{{ asset('admin/images/right1.svg') }}' class='disbn' alt=''>"    
                }  
              },
        scrollX: true
   });
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
      var rowcollection = table.$(".dt-checkboxes:checked", {"page": "all"});
      vqDataR = []
      rowcollection.each(function(index,elem){
          //You have access to the current iterating row
          var checkbox_value = $(elem).val();
          var row = $(elem).closest("tr");
          var item_code = (row.find(".item_code").text());
          vqDataR.push(item_code);
          //Do something with 'checkbox_value'
      });
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
            $('#startval').empty();//added to empty the date if multiple click happens
            $('#enddateval').empty();//added to empty the date if multiple click happens
            $('#startval').append(startdate);
            $('#enddateval').append(enddate);
            $('#myModal').modal('show');
            // window.location.href ='{{route("initiator_listing")}}';
          }
          else//added on 24042024 for checking the pending item code and display alert
          {
            $('#selected_reinit').attr('disabled', false);
            //alert(response.message);
            $('#pendingModal').modal({backdrop: 'static', keyboard: false},'show');
            $('#pendingCount').text(response.count);
            $('#pendingMessage').html(response.message);
            pending_items = response.pendingItems;
          }
          
        });
    });
    $('#untick_pending').on('click', function(){
      // Define the array of values to check against
      var valuesToCheck = pending_items
      table.rows().every(function() {
        var rowData = this.data();
        
        var rowValue = rowData[3];

        if (valuesToCheck.indexOf(rowValue) !== -1) {
          $(this.node()).find('input[type="checkbox"]').trigger('click');
          //$(this.node()).find('input[type="checkbox"]').trigger('change');
          $(this.node()).removeClass('selected')
        }
      });
      table.draw(false);
      /*var rowcollection = table.$(".dt-checkboxes:checked", {"page": "all"});
      rowcollection.each(function(index,elem){
          //You have access to the current iterating row
          var checkbox_value = $(elem).val();
          var row = $(elem).closest("tr");
          var item_code = (row.find(".item_code").text());
          if (valuesToCheck.indexOf(item_code) !== -1) {
            $(elem).trigger('click')
            //$(elem).trigger('change');
          }
      });*/
      //table.rows().invalidate().draw(false);
      /*var selected_checkbox = table.cells().nodes().to$().find('input[type="checkbox"]:checked').length;
      $('.select-item').text(selected_checkbox+' rows selected')*/
      if($('.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]').prop('checked') == true)
      {
        //$('.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]').attr('checked',false).trigger('click');
      }
      if($('#pendingCount').text() == total_item_count)
      {
        /*$('.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]').attr('checked',false).trigger('click');
        enableBtn();*/
      }
      if(table.cells().nodes().to$().find('input[type="checkbox"]:checked').length == 0)
      {
        /*$('.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]').trigger('click');
        enableBtn();*/
      }
      $('#pendingModal').modal('hide');
    })

$(".table-ct .dataTables_filter label").append("<img src='{{ asset('admin/images/search.svg') }}' class='search-ct' alt=''>");
$('.dataTables_filter label').contents().filter((_, el) => el.nodeType === 3).remove();
$(".dataTables_filter label input").keyup(function(){
        $(".search-ct").css("opacity", "0");
});





});
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
            </style>
@endsection

