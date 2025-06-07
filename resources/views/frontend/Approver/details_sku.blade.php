@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
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
.actions-dashboard{
        max-width: calc(100vw - 105px);
        overflow-x: hidden;
    }
</style>
<div id="page-content-wrapper">
                <!-- Top navigation-->
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                            <ul class="navbar-nav dashboard-nav">
                                <li class="nav-item active"><a class="nav-link" href="{{route('approver_dashboard')}}"> <img src="{{ asset('admin/images/back.svg')}}">VQ Request Details</a></li>
                            </ul>

                            <ul class="d-flex ml-auto user-name">
                                <li>
                                  <h3>{{Session::get('emp_name')}}</h3>
                                  <p>{{Session::get('division_name')}}</p>
                                </li>

                                <li>
                                    <img src="{{ asset('admin/images/Sun_Pharma_logo.png') }}">
                                </li>
                            </ul>
                        </div>
                        
                    </div>

                </nav>
                <ul class="bradecram-menu">
                    <li><a href="{{route('approver_dashboard')}}">
                        Home
                    </a></li>
                    <li class="">
                      <a href="#">
                        VQ Request Listing
                      </a>
                    </li>
                </ul>
                <!-- Page content-->
                <div class="container-fluid">
                  <div class="row">
                      <div class="col-md-2 mr-20">
                        <label>Year: </label>
                        <select id="fincancialYear" class="js-example-basic-single fincancialYears" name="fincancialYear">
                           @foreach($financialYears as $years)
                           <option value="{{ $years }}">{{ $years }}</option>
                           @endforeach
                        </select>
                      </div>
                      <div class="col-md-4 mr-20">
                        <label>Cluster: </label>
                        <select id="cluster" class="js-example-basic-multiple cluster" name="cluster" multiple="multiple">
                          <!-- <option value=''> Select </option> -->
                          <option value='all'> All </option>
                           @foreach($cluster as $clusters)
                           <option value="{{ $clusters->cluster }}">{{ $clusters->cluster }}</option>
                           @endforeach
                        </select>
                      </div>
                      <div class="col-md-6 mr-20">
                        <label>Insitution Name: </label>
                        <select id="institutionName" class="js-example-basic-multiple" name="institutionName" multiple="multiple">
                          <!-- <option value=''> Select </option> -->
                          <option value='all'> All </option>
                           @foreach($institute as $institutes)
                           <option value="{{ $institutes->institution_id }}">{{ $institutes->hospital_name }} - {{ $institutes->institution_id }}</option>
                           @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-3 mr-20">
                        <label>Status: </label>
                        <select id="status" class="js-example-basic-single fincancialYears" name="status">
                          <option value='pending'> Pending </option>
                          <option value='approved'> Approved </option>
                          <option value='all'> All </option>
                        </select>
                      </div>
                      <div class="col-md-3 mr-20">
                        <label>Criteria: </label>
                        <select id="criteria" class="js-example-basic-multiple criteria" name="criteria" multiple="multiple">
                           @foreach($criteria as $criterias)
                           <option value="{{ $criterias->filter_condition }}">{{ $criterias->filter_criteria }}</option>
                           @endforeach
                        </select>
                      </div>
                      <div class="col-md-6 d-flex" style="align-items: center;margin-top: 31px;">
                        <ul class="d-flex list-unstyled">
                          <li>
                            <button class="view-data orange-btn-bor">
                              View
                            </button>
                          </li>
                          <li>
                            <button class="export-excel orange-btn-bor">
                              Export to Excel
                            </button>
                          </li>
                        </ul>
                      </div>
                    </div>
                    <div class="row">

                        <div class="col-md-12 d-flex mr-20 update_ptr_bulk_section">
                          <ul class="rth-cal d-flex">
                            <li>
                              <div class="form-group form-inline m-0">
                                <!--<label for="">Update PTR in Bulk:</label>
                                <input type="text" class="form-control" id="entervalue" placeholder="Enter Value (%)" maxlength="2" onkeypress="return isNumberKey(event)" name="" required>-->

                                <label for="">Update PTR in Bulk:</label>
                                <input type="text" class="form-control percentctval" id="entervalue" placeholder="Enter Value (%)" value="0" maxlength="6" name="" required>

                              </div>
                            </li>
                            <li>
                              <button class="apply-val orange-btn-bor">
                                APPLY
                              </button>
                            </li>
                            <li>
                              <button class="clear-val orange-btn-bor">
                                Reset
                              </button>
                            </li>
                          </ul>

                          <div class="cancel-btn ml-auto">
                            <button id="approve" class="orange-btn" disabled>
                              APPROVE ALL AND SUBMIT
                            </button>
                          </div>
                      </div>
                    </div>
                      
                        <div class="row">
                            <div id='loader1' style='display: none;'>
                              
                            </div>
                            <div class="actions-dashboard table-ct table-responsive">
                            
                              <table class="table VQ Request Listing vq-request-listing-tb nowrap" id="zero_config">
                                  <thead>
                                    <tr>
                                        <th><!-- <input type="checkbox" id="selectAll" disabled> --></th>
                                        <th class="">Institution Name</th>
                                        <th class="">SAP Code</th>
                                        <th>Revision No</th>
                                        <th class="">City</th>
                                        <th>Division</th>
                                        <th>Mother Brand</th>
                                        <th>SAP Item Code</th>
                                        <th>Brand</th>
                                        <th>Item Code</th>
                                        <th class="digitcenter">Disc. PTR (%)</th>
                                        <th class="digitcenter">RTH (Excl. GST)</th>
                                        <th class="digitcenter">App. GST(%)</th>
                                        <th class="digitcenter">L. Y. Disc.(%)</th>
                                        <th class="digitcenter">L. Y. Disc Rate</th>
                                        <th class="digitcenter">L. Y.MRP</th>
                                        <th class="digitcenter">MRP</th>
                                        <th class="digitcenter">C. Y. PTR</th>
                                        <th class="digitcenter">MRP Margin (%)</th>
                                        <th class="digitcenter">Billing price(Direct Master)</th>
                                        <th class="digitcenter">Billing price(Credit Note)</th>
                                        <th class="digitcenter">Last year % Margin on MRP</th>
                                        <th>Composition</th>
                                        <th>Action</th>
                                        <th class="d-none">id</th>
                                        <th class="d-none">vq_id</th>
                                        <th class="d-none">update_flag</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    
                                  </tbody>
                              </table>

                            </div>
                        </div><!-- col close -->

<!-- add comments popup -->
  <div class="modal show" id="addcomments">
    <div class="modal-dialog modal-dialog-centered model-pop-ct">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header border-0">
            <div class="tich-logo">
            <img src="{{ asset('admin/images/comments.svg') }}" alt="">
            </div>
          <button type="button" class="close" data-dismiss="modal">
              <img src="{{ asset('admin/images/close.svg') }}" alt="">
          </button>
        </div>
        
        
        
      </div>
    </div>
  </div>

<!-- view sku comments popup -->
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




<!-- submit popup -->
<div class="modal show" id="approveandsubmit">
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
          <p class="border-0">Approve VQ Request?</p>
          <div class="form-group">
            <textarea class="form-control" rows="5" id="approve-comment" placeholder="Add your Comments"></textarea>
            <div class="w-100">
                <div id="error_message" class="error ajax_response" ></div>
            </div>
          </div>
          <a id="approve-new" class="btn orange-btn big-btn" href="{{url('initiator/listing')}}">APPROVE AND SUBMIT</a>
        </div>
      </div>
    </div>
</div>

<!-- submited popup -->
<div class="modal show" id="submited">
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
          <p class="border-0">VQ Request  is approved and submitted successfully</p>

          <a class="btn orange-btn big-btn ok-btn">OK</a>
        </div>
      </div>
    </div>
</div>

<!-- <script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script> -->
<script src="{{asset('frontend/js/select2.js')}}"></script>
<script>
  let levelSession = `{{strtolower(Session::get("level").'_status')}}`;
    levelSession = levelSession.toLowerCase()
    let levelNumber = `{{preg_replace('/[^0-9.]+/', '', Session::get("level"))}}`;
    let exception_items
    let stockist_margin = `{{$stockist_margin['meta_value']}}`;
    var bulk_update_flag;
    var table;
    var selected = {};
       /****************************************
       *       Basic Table                   *
       ****************************************/

      $('.decimal').keypress(function(evt){
        return (/^[0-9]*\.?[0-9]*$/).test($(this).val()+evt.key);
      });

      $("#entervalue").focusout(function(){
        var bulk_input = $(this).val();
        if(bulk_input == ' ' || bulk_input == ''){
        $('#entervalue').val('0');
        }
      });

      $('body').on("change",'.ptr_percent',function(){
        // Convert rate here
        var row = $(this).closest("tr");
        var main = parseFloat(row.find(".cBalance").text());//CYptr
        var disc = parseFloat(row.find(".ptr_percent").val());//Disc PTR
        var pdms_dis = parseFloat(row.find(".pdms_discount").text());//PDMS discount
        var max_dis = parseFloat(row.find(".max_discount").text());//Max discount

        var item_code = row.find(".item-code").text();//PDMS discount
        var mrp = parseFloat(row.find(".mrp").text());
        var db_disc_percent = '0.00';
        var db_disc_rate = main;
        row.find('.ptr_rate').css('border','none');
        if (disc != '' && main != '') {
          var dec = disc/100; //its convert 10 into 0.10
          var mult = main*dec; // gives the value for subtract from main value
          var discont = (main-mult).toFixed(2);
          row.find('.ptr_rate').val(discont);//discount (rth)
          db_disc_percent = disc;
          db_disc_rate = discont;
        }else{
          // row.find('.ptr_rate').val(parseInt(main));
          row.find('.ptr_rate').val('0');
          row.find(".ptr_percent").val(main);
          db_disc_percent = '0.0';
          db_disc_rate = main;
        }
        
        if(disc == 0.0 || isNaN(disc)){
	        row.find('.ptr_rate').val(main);
          row.find(".ptr_percent").val('0');
          db_disc_percent = '0.0';
          db_disc_rate = main;
        }
        
        if(disc > 99){
	        db_disc_percent = '99';
		      mult = main*0.99; // gives the value for subtract from main value
          db_disc_rate = (main-mult).toFixed(2);
          row.find(".ptr_percent").val('99');
          row.find('.ptr_rate').val(db_disc_rate);
        }
        row.find('.update_flag').val(true);
        var mrp_margin = ((mrp - db_disc_rate)/mrp)*100;
        row.find('.mrp_margin').text(mrp_margin.toFixed(2));
        // Add ajax call for updating price here
        console.log("{{ app('request')->route('id') }}");
        var settings = {
          "url": "/approver/update_discount",
          "method": "POST",
          "timeout": 0,
          "headers": {
            "Accept-Language": "application/json",
          },
          "data": {
            "_token": "{{ csrf_token() }}",
            "vq_id": "{{ app('request')->route('id') }}",
            "item_code": item_code,
            "discount_percent": db_disc_percent,
            "discount_rate": db_disc_rate,
            "mrp":mrp,
          }
        };
        /*$.ajax(settings).done(function (response) {
          var style=""
          if(pdms_dis){
            if(pdms_dis < db_disc_percent){
              style="color:red;"
              console.log('tt');
              $('#warningpdms').modal('show');
            }else{
              style="color:#3a3a3a;"
              console.log('ttd')

            }
          }
          if(max_dis){
            if(max_dis < db_disc_percent){
              style+="background:lightsalmon !important;"
              // row.attr( 'style', 'background:lightsalmon !important;' );
              $('#warningmaxcap').modal('show');
              console.log('ttdi',db_disc_percent,max_dis)

            }else{
              style+="background:inherit;"
              // row.css('background','inherit');
              console.log('ttd',db_disc_percent,max_dis)

            }
          }
          row.attr( 'style', style);

          row.find('.mrp_margin').text(response.mrp_margin);
        });*/
      });
      
      $('body').on("change",'.ptr_rate',function(){
        console.log("hel");
        // Convert Percentage here
        var ptr_rate = $(this).val();
        var row = $(this).closest("tr");
        var listPrice = parseFloat(row.find(".cBalance").text());//CYPTR
        var salePrice = parseFloat(row.find(".ptr_rate").val());
        var pdms_dis = parseFloat(row.find(".pdms_discount").text());//PDMS discount
        var max_dis = parseFloat(row.find(".max_discount").text());//Max discount
        var disc_per = '0.00';
        var db_sale_price = listPrice;
        var mrp = parseFloat(row.find(".mrp").text());
        
        if(ptr_rate <= listPrice){
          if (salePrice != '' && listPrice != '') {
            var discont = 100 - (salePrice * 100 / listPrice).toFixed(2);
            row.find('.ptr_percent').val(discont.toFixed(2));
          }else{
            // row.find('.ptr_rate').val(parseInt(main));
          }
          disc_per = discont.toFixed(2);
          db_sale_price = salePrice;
          row.find('.ptr_rate').css('border','none');
        }else{
          row.find('.ptr_percent').val('0');
          row.find('.ptr_rate').val(listPrice);
          row.find('.ptr_rate').css('border-color','red');
        }
	    
        if(ptr_rate == ''){
          row.find('.ptr_percent').val('0');
          row.find('.ptr_rate').val(listPrice);
        }
        row.find('.update_flag').val(true);
        var mrp_margin = ((mrp - db_sale_price)/mrp)*100;
        row.find('.mrp_margin').text(mrp_margin.toFixed(2));
        // Add ajax call for updating price here
        var settings = {
          "url": "/approver/update_discount",
          "method": "POST",
          "timeout": 0,
          "headers": {
            "Accept-Language": "application/json",
          },
          "data": {
            "_token": "{{ csrf_token() }}",
            "vq_id": "{{ app('request')->route('id') }}",
            "item_code": row.find(".item-code").text(),
            "discount_percent": disc_per,
            "discount_rate": db_sale_price,
            "mrp":mrp,
          }
        };
        /*$.ajax(settings).done(function (response) {
          console.log(response);
          row.find('.mrp_margin').text(response.mrp_margin);
          var style=""
          if(pdms_dis){
            if(pdms_dis < disc_per){
              style="color:red;"
              console.log('tt');
              $('#warningpdms').modal('show');
            }else{
              style="color:#3a3a3a;"
              console.log('ttd')

            }
          }
          if(max_dis){
            if(max_dis < disc_per){
              style+="background:lightsalmon !important;"
              // row.attr( 'style', 'background:lightsalmon !important;' );
              $('#warningmaxcap').modal('show');
              console.log('ttdi',disc_per,max_dis)

            }else{
              style+="background:inherit;"
              // row.css('background','inherit');
              console.log('ttd',disc_per,max_dis)

            }
          }
          row.attr( 'style', style);

          row.find('.mrp_margin').text(response.mrp_margin);
        });*/
      });


      $('#approve-new').click(function(e){
        e.preventDefault()
        if($('#cluster').val() == '')
        {
          $('#cluster').val('all').change();
        }
        if($('#institutionName').val() == ''){
          $('#institutionName').val('all').change()
        }
        if($('#criteria').val()==''){
          $('#criteria').val($("#criteria option:last").val()).change();
        }
        selected = {};
        var rowcollection = table.$(".dt-checkboxes:checked", {"page": "all"});
        rowcollection.each(function(index,elem){
          //You have access to the current iterating row
            var row = $(elem).closest("tr");
            var sku_id = row.find('.sku_id').text();
            var vq_id = row.find('.vq_id').text();
            var item_code = row.find('.item-code').text();
            var ptr_percent = row.find('.ptr_percent').val();
            var ptr_rate = row.find('.ptr_rate').val();
            var cBalance = row.find('.cBalance').text();
            var mrp_margin = row.find('.mrp_margin').text();
            var mrp_value = row.find('.mrp').text();
            var update_flag = row.find('.update_flag').val();
            selected[sku_id] = {
              sku_id: sku_id,
              item_code: item_code,
              ptr_percent: ptr_percent,
              ptr_rate: ptr_rate,
              cBalance: cBalance,
              mrp_margin: mrp_margin,
              mrp_value: mrp_value,
              vq_id: vq_id,
              update_flag: update_flag,
            };
            //Do something with 'checkbox_value'
        });
        $comment = $('#approve-comment').val();
        if($comment != ""){
          // Add ajax call for updating price here
          var settings = {
            "url": "/approver/single_approve_criteria",
            "method": "POST",
            "timeout": 0,
            "headers": {
              "Accept-Language": "application/json",
            },
            "data": {
              "_token": "{{ csrf_token() }}",
              "cluster": $('#cluster').val(),
              "institutionName": $('#institutionName').val(),
              "criteria": $('#criteria').val(),
              "bulk_ptr_value": $('#entervalue').val(),
              "bulk_update_flag": bulk_update_flag,
              "year": $('#fincancialYear').val(),
              "comment": $comment,
              // "selected_rows":selected // hide arunchandru 28032025
              "selected_rows":JSON.stringify(selected)
            }
          };
          $.ajax(settings).done(function (response) {
            if(response.success == true)
            {
  	         $('#submited').modal('show');
  	         $('#approveandsubmit').modal('hide');
            }
            else
            {
               $("#error_message").show().html("Error Occured. Please try again");
            }
            //window.location.href ='{{route("approver_listing")}}';           
          });
        }else{
          $("#error_message").show().html("Please enter comment.");
        }
      });

	    $('.msg').css('display', 'none');
      
      $('.ok-btn').click(function(){
        location.reload();	
      });
	
      // APPLY now val js
      $(document).ready(function() {
        $(document).on({
          ajaxStart: function() { $('#loader1').show();    },
          ajaxStop: function() { $('#loader1').hide(); }    
        });   
        $('#cluster').val('all');
        $('#institutionName').val('all');
        $('#criteria').val($("#criteria option:last").val());

        $('.js-example-basic-single').select2({ width: '100%' });
        $('.js-example-basic-multiple').select2({ width: '100%' });
        $('.js-example-basic-multiple').on('select2:select', function(e) {
        var selected = $(this).val();
        if (selected.includes('all')) {
          $(this).val('all').trigger('change'); // Select only 'All' option
        }
      });
      $('.js-example-basic-multiple').on('select2:unselect', function(e) {
        var selected = $(this).val();
        if (!selected) {
          $(this).val(null).trigger('change'); // Clear selection if all options are unselected
        }
      });
      $('.export-excel').on('click',function(){
        if($('#cluster').val() == '')
        {
          $('#cluster').val('all').change();
        }
        if($('#institutionName').val() == ''){
          $('#institutionName').val('all').change()
        }
        if($('#criteria').val()==''){
          $('#criteria').val($("#criteria option:last").val()).change();
        }
        var year = $('#fincancialYear').val();
        var status = $('#status').val();
        var criteria = $('#criteria').val() || [];
        var institutionNames = $('#institutionName').val() || [];
        var clusters = $('#cluster').val() || [];

        var url = "{{ route('vq-export-criteria') }}";
        url += '?status=' + encodeURIComponent(status);
        url += '&criteria=' + encodeURIComponent(JSON.stringify(criteria));
        url += '&institutionNames=' + encodeURIComponent(JSON.stringify(institutionNames));
        url += '&clusters=' + encodeURIComponent(JSON.stringify(clusters));
        url += '&year=' + encodeURIComponent(year);

        // Redirect to the constructed URL
        window.location.href = url;
      });
      $('.view-data').trigger('click');

      $('body').on('click', '.add-comments', function(e){
        e.preventDefault();
        var row = $(this).closest("tr");
        $('#item_code_field').val(row.find(".item-code").text());
        $('#brandname').text(row.find(".brand_name").text());
        $('#motherbrandname').text(row.find(".mother_brand_name").text());
        $('.msg').css('display', 'none');
        $('#addcomments').modal('show');
      });

      $('#cancel').click(function(){
        $('#cancelcomment').modal('show');
        $('.modal-body.success').css('display', 'none');
      });

      $('#approve').click(function(){
        var check = 0; // added by arunchandru at 28032025
        var rowcollection = table.$(".dt-checkboxes:checked", {"page": "all"});
        rowcollection.each(function(index,elem){
          //You have access to the current iterating row
            var row = $(elem).closest("tr");
            var vq_id = row.find('.vq_id').text();
            var item_code = row.find('.item-code').text();
            var ptr_percent = row.find('.ptr_percent').val();
            var ptr_rate = row.find('.ptr_rate').val();
            var mrp_value = row.find('.mrp').text();
            if((ptr_percent == ' ' || typeof ptr_percent === 'undefined') || (ptr_rate == ' ' || typeof ptr_rate === 'undefined') || (mrp_value == ' ' || typeof mrp_value === 'undefined')){
              alert("For the Item code "+ item_code + " in VQ ID "+ vq_id + " dis percent or dis ptr or mrp margin is empty");
              check++;
            }
        }); // added by arunchandru at 28032025
        if(check == 0){
          $('#approveandsubmit').modal('show');
        }
      });

      $('.clear-val').click(function(){
        let text = "Are you sure you want to rest Disc PTR(%) for all SKUs? '";
        if (confirm(text) == true) {
          $("#entervalue").val('');
          /*var disc = $("#entervalue").val();
          var settings = {
                "url": "/approver/bulk_update",
                "method": "POST",
                "timeout": 0,
                "headers": {
                  "Accept-Language": "application/json",
                },
                "data": {
                  "_token": "{{ csrf_token() }}",
                  "vq_id": "{{ app('request')->route('id') }}",
                  "discount_percent": 0,
                }
              };
              $.ajax(settings).done(function (response) {
                console.log(response);
                location.reload();
              });*/
              //location.reload();
          $('.view-data').trigger('click')
        }
      });

      $( ".apply-val" ).on( "click", function() {
        bulk_update_flag = true;
        let text = "Are you sure you want to apply Disc PTR(%) for all SKUs? ";
        if (confirm(text) == true) {
          var disc = $("#entervalue").val();
          if(disc > 99){
            disc = 99;
          }
          if(disc >= 0 ){
            var rowcollection = table.$(".dt-checkboxes", {"page": "all"});
            rowcollection.each(function(index,elem){

              var row = $(elem).closest("tr");
              row.find(".ptr_percent").val(disc).trigger('change')

            });
            /*var settings = {
              "url": "/approver/bulk_update",
              "method": "POST",
              "timeout": 0,
              "headers": {
                "Accept-Language": "application/json",
              },
              "data": {
                "_token": "{{ csrf_token() }}",
                "vq_id": "{{ app('request')->route('id') }}",
                "discount_percent": disc,
              }
            };
            $.ajax(settings).done(function (response) {
              console.log(response);
              $("#entervalue").val('');
              location.reload();
            });*/
          }
        }   
      });
    });
      
function isNumberKey(evt){
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
    return true;
}  
// Function to check if item_code and div_id exist in checkItems array
function itemExists(itemCode, divId) {
    return exception_items.some(function(item) {
        return item.item_code === itemCode && item.div_id === divId;
    });
}

//bulk update PTR
document.querySelector('.percentctval').addEventListener('input', function(e) {
  let int = e.target.value.slice(0, e.target.value.length - 1);
  if (int.includes('%')) {
    e.target.value = '%';
  } else if (int.length >= 3 && int.length <= 4 && !int.includes('.')) {
    e.target.value = int.slice(0, 2) + '.' + int.slice(2, 3);
    // e.target.value = int.slice(0, 2) + ‘.’ + int.slice(2, 3) + ‘%’;
    e.target.setSelectionRange(4, 4);
  } else if (int.length >= 5 & int.length <= 6) {
    let whole = int.slice(0, 2);
    let fraction = int.slice(3, 5);
    e.target.value = whole + '.' + fraction;
    // e.target.value = whole + ‘.’ + fraction + ‘%’;
  } else {
    e.target.value = int + ' ';
    // e.target.value = int + ‘%’;
    e.target.setSelectionRange(e.target.value.length - 1, e.target.value.length - 1);
  }
  //console.log('For robots:' + getInt(e.target.value));
});
/*function getInt(val) {
  let v = parseFloat(val);
  if (v % 1 === 0) {
    return v;
  } else {
    let n = v.toString().split('.').join('');
    return parseInt(n);
  }
}*/


    $.fn.dataTable.ext.order['dom-text-numeric'] = function (settings, col) {
      // alert(col);
      return this.api().column(col, { order: 'index' }).nodes().map(function (td) {
        return parseFloat($('input', td).val()) || 0;
      });
    };
    
    
    function init_table()
    {
      table = $("#zero_config").DataTable({
        "pageLength": 50,
        "aaSorting": [[ 1, "asc" ]],
        serverSide: ($('#status').val() == 'approved') ? true : false,
        responsive: true,
        ajax: {
          url: '/approver/manage_request_sku_criteria',
          type: 'GET',
          data: function (d) {
            // Add custom data to the request
            d.year = $('#fincancialYear').val();
            d.cluster = $('#cluster').val();
            d.institutionName = $('#institutionName').val();
            d.status = $('#status').val();
            d.criteria = $('#criteria').val();
            return d;
          },
          "dataSrc": function(response) {
            // Return the data array for DataTable to process
            return response.data;
          }
        },
        "rowCallback": function(row, data) {
          // Example condition: add CSS class 'highlight' to rows where the third column is 'important'
          if(data.current_level == levelNumber)
          {
            $('.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]').attr('disabled', false)
            /*$('#approve').attr('disabled', false)
            $('.apply-val').attr('disabled', false)
            $('.clear-val').attr('disabled', false)*/
            /*if(selected[data.sku_id]) {
              $('input.row-checkbox', row).prop('checked', true);
            }*/
          }
          /*var tr_class = data.discount_percent != data.ceiling_percent ? 'dark-bg' : '';
          var tr_style_disc_ptr;
          if(levelNumber == 8){
            if(data.discount_percent >= 30)
            {
              if (itemExists(data.item_code, data.div_id)) { 
                tr_style_disc_ptr = '';
              }
              else
              {
                tr_style_disc_ptr = 'background: #f9f938 !important;';//added for ceo approval idap-33
              }
            }
          }
          
          var tr_style1 = data.pdms_discount < data.discount_percent && data.pdms_discount != null ? 'color:red;' : '';
          var tr_style2 = data.max_discount < data.discount_percent && data.max_discount != null ? 'background:lightsalmon !important;' : ''
          //console.log(data);
          $(row).addClass(tr_class);
          $(row).attr('style', tr_style1+tr_style2+tr_style_disc_ptr);*/
        },
        columns:
        [
          { data: 'sku_id' },//0
          { data: 'hospital_name' },//1
          { data: 'sap_code' },//2
          { data: 'rev_no' },//3
          { data: 'city' },//4
          { data: 'div_name' },//5
          { data: 'mother_brand_name' },//6
          { data: 'sap_itemcode',//7
            "render": function(data, type, row, meta) {
              if (data !== "null" && data !== undefined && data !== '') {
                return data;
              } else {
                return "-";
              }
            }
          },
          { data: 'brand_name' },//8
          { data: 'item_code' },//9
          { 
            data: function(row) {//10
              if(row.{{strtolower(Session::get("level").'_status')}} == 0 && row.current_level == levelNumber)
              {
                var result = `<input  class="ptr_percent decimal" value="${parseFloat(row.discount_percent).toFixed(2)}" min="0" max="99" value="0" step="0.01" />`
              }
              else
              {
                var result = parseFloat(row.discount_percent).toFixed(2)
              }
              return result;
            }
          },
          { 
            data: function(row) {//11
              if(parseFloat(row.discount_rate) < 0.1) var db_ptr_rate = parseFloat(row.discount_rate);//added condition to check value less than 0.1 and remove rounding
              else var db_ptr_rate = parseFloat(row.discount_rate).toFixed(2);
              if(row.{{strtolower(Session::get("level").'_status')}} == 0 && row.current_level == levelNumber)
              {
                
                var result = `<input  class="ptr_rate decimal" value="${db_ptr_rate}"/>`
              }
              else
              {
                var result = db_ptr_rate
              }
              return result;
            }
          },
          { data: 'applicable_gst',//12
              "render": function(data, type, row, meta) {
                  if (data !== null && data !== undefined && data !== '') {
                      return parseFloat(data).toFixed(2);
                  } else {
                      return 0;
                  }
              }
          },
          { data: 'last_year_percent',//13
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
          { data: 'last_year_mrp',//15
              "render": function(data, type, row, meta) {
                  if (data !== null && data !== undefined && data !== '') {
                      return parseFloat(data).toFixed(2);
                  } else {
                      return 0;
                  }
              }
          },
          { data: 'mrp',//16
              "render": function(data, type, row, meta) {
                  if (data !== null && data !== undefined && data !== '') {
                      if(parseFloat(data) < 0.1) return parseFloat(data);//added condition to check value less than 0.1 and remove rounding
                      else return parseFloat(data).toFixed(2);
                  } else {
                      return 0;
                  }
              }
          },
          
          { data: 'ptr',//17
              "render": function(data, type, row, meta) {
                  if (data !== null && data !== undefined && data !== '') {
                      if(parseFloat(data) < 0.1) return parseFloat(data);//added condition to check value less than 0.1 and remove rounding
                      else return parseFloat(data).toFixed(2);
                  } else {
                      return 0;
                  }
              }
          },
          { data: 'mrp_margin',//18
              "render": function(data, type, row, meta) {
                  if (data !== null && data !== undefined && data !== '') {
                      return parseFloat(data).toFixed(2);
                  } else {
                      return 0;
                  }
              }
          },
          { data: 'discount_rate',//19
              "render": function(data, type, row, meta) {
                  if (data !== null && data !== undefined && data !== '') {
                      var calculated_data = data - ((data*stockist_margin)/100);
                       if(parseFloat(calculated_data) < 0.1) return parseFloat(calculated_data).toFixed(2);//added condition to check value less than 0.1 and remove rounding
                      else return parseFloat(calculated_data).toFixed(2);
                  } else {
                      return 0;
                  }
              }
          },
          { data: 'discount_rate',//20
              "render": function(data, type, row, meta) {
                  if (data !== null && data !== undefined && data !== '') {
                       if(parseFloat(data) < 0.1) return parseFloat(data);//added condition to check value less than 0.1 and remove rounding
                      else return parseFloat(data).toFixed(2);
                  } else {
                      return 0;
                  }
              }
          },
          { 
              data: function(row) {//21
                  var lastYearMrp = row.last_year_mrp;
                  var lastYearPtr = row.last_year_ptr;
                  var result = lastYearMrp != null && lastYearPtr != null ? parseFloat(((lastYearMrp - lastYearPtr) / lastYearMrp) * 100).toFixed(2) : "-";
                  return result;
              }
          },
          {  data: function(row) {//22
                  var composition = row.composition;
                  return composition.toUpperCase().toLowerCase();
              }
          },
          { 
              data: function(row) {//23
                  var element = `<a href="/approver/activity/${row.id}"  data-title="View Activity">  
                    <img src="../admin/images/clock.png" alt="" style="width:30px;">
                    </a>`
                  return element;
              }
          },
          { data: 'vq_id' },//24
          { data: 'sku_id' },//25
          { 
              data: function(row) {//26
                  var element = `<input type="hidden" class="update_flag" value="false">`
                  return element;
              }
          },
        ],
        "columnDefs": [
            { 
              "targets": 0, "orderable": false,'data': 0,
              'render': function(data, type, row, meta){
                data = '<input type="checkbox" class="dt-checkboxes">'
                //if(row['current_level'] != levelNumber && row[`{{strtolower(Session::get("level").'_status')}}`] == 1){
                if(row['current_level'] != levelNumber || row[`{{strtolower(Session::get("level").'_status')}}`] == 1){
                  data = '';
                }
                
                return data;
              },
              'createdCell':  function (td, cellData, rowData, row, col){
                if(rowData['current_level'] != levelNumber || rowData[`{{strtolower(Session::get("level").'_status')}}`] == 1){
                  this.api().cell(td).checkboxes.disable();
                }
              },  
              'checkboxes': {
                'selectRow': true
              }
            },
            {
              targets: 1, 
              className: '', 
            },
            {
              targets: [10, 11], // column indexes for "Disc. PTR (%)" and "RTH (Excl. GST)"
              orderDataType: 'dom-text-numeric' // custom sorting by textbox value
            },
            { "targets": [3,7,12,13,14,15,19,20,21], 
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).css({
                  'text-align': 'center'
                });
              }
            },  
            { "targets": [24], 
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('d-none vq_id');
              }
            },  
            { "targets": [25],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('d-none sku_id');
              }
            },
            { "targets": [26],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('d-none');
              }
            },
            { 
              "targets": [9],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('item-code');
                $(td).css({
                  'text-align': 'center'
                });
              } 
            },
            { 
              "targets": [8],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('brand_name');
              } 
            },
            { 
              "targets": [16],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('mrp');
                $(td).css({
                  'text-align': 'center'
                });
              } 
            },
            { 
              "targets": [17],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('cBalance');
                $(td).css({
                  'text-align': 'center'
                });
              } 
            },
            { 
              "targets": [18],
              "createdCell": function (td, cellData, rowData, row, col) {
                $(td).addClass('mrp_margin');
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

    </script>
    <script>
      $('.view-data').on('click',function(){
        selected = {};
        bulk_update_flag = false;
        $('#selectAll').prop('checked',false)
        $('.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]').attr('disabled', true)
        $('#approve').attr('disabled', true)
        $('#entervalue').val(0);
        if ($.fn.DataTable.isDataTable('#zero_config')) {
            // If it is, destroy it first
            $('#zero_config').DataTable().destroy();
        }
        if($('#cluster').val() == '')
        {
          $('#cluster').val('all').change();
        }
        if($('#institutionName').val() == ''){
          $('#institutionName').val('all').change()
        }
        if($('#criteria').val()==''){
          $('#criteria').val($("#criteria option:last").val()).change();
        }
        /*if($('#status').val()=='approved' || $('#status').val()=='all')
        {
          $('#approve').attr('disabled', true)
          $('.apply-val').attr('disabled', true)
          $('.clear-val').attr('disabled', true)
        }
        else
        {
          $('#approve').attr('disabled', false)
          $('.apply-val').attr('disabled',false)
          $('.clear-val').attr('disabled', false)
        }*/
        init_table()
        $('#zero_config_length select').addClass('form-control form-control-sm')
      });
      function enableBtn() {//changed fn to listen for the common checkbox checked event 30042024
        var anyRowChecked = $('#zero_config tbody').find("tr.selected input:checked").length > 0;
        var headerCheckbox = $('.dataTables_scrollHeadInner table thead tr th:nth-child(1) input[type="checkbox"]');
        var isHeaderChecked = headerCheckbox.prop("checked");
        $('#approve').prop("disabled", !anyRowChecked && !isHeaderChecked);
      }
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
      /*function updateSelectedRows(checkbox) {
          var row = $(checkbox).closest('tr');
          var sku_id = row.find('.sku_id').text();
          var vq_id = row.find('.vq_id').text();
          var item_code = row.find('.item-code').text();
          var ptr_percent = row.find('.ptr_percent').val();
          var ptr_rate = row.find('.ptr_rate').val();
          var cBalance = row.find('.cBalance').text();
          var mrp_margin = row.find('.mrp_margin').text();
          
          if (checkbox.checked) {
              selected[sku_id] = {
                  sku_id: sku_id,
                  item_code: item_code,
                  ptr_percent: ptr_percent,
                  ptr_rate: ptr_rate,
                  cBalance: cBalance,
                  mrp_margin: mrp_margin,
                  vq_id: vq_id,
              };
          } else {
              delete selected[sku_id];
          }

          console.log(selected);
      }*/


    </script>

        <style type="text/css">
        .copy-center{
            position:inherit !important;margin: 20px 0;
        }
        #wrapper {
            width: 100%;
            overflow: hidden;
        }
        div#app {
            width: calc(100% - 75px) !important;
        }
      </style>
@endsection

