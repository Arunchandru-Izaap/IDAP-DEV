@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
  .yellow-bg
  {
    background: #f9f938 !important;
  }
</style>
<div id="page-content-wrapper">
                <!-- Top navigation-->
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                            <ul class="navbar-nav dashboard-nav">
                                <li class="nav-item active"><a class="nav-link" href="{{route('approver_listing')}}"> <img src="{{ asset('admin/images/back.svg')}}">{{$data['vq_data']['hospital_name']}} - VQ Request Details</a></li>
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
                      <a href="{{route('approver_listing')}}">
                        VQ Request Listing
                      </a>
                    </li>
                    <li class="active">
                      <a href="">
                      {{$data['vq_data']['hospital_name']}}
                      </a>
                    </li>
                </ul>
                <!-- Page content-->
                <div class="container-fluid">
                    <!-- <h1 class="mt-4">Simple Sidebar</h1> -->
                    <div class="row">
                    @php 
                      $str = Session::get("level").'_status';
                      $main = strtolower($str);
                      $sessionLevel = preg_replace('/[^0-9.]+/', '', Session::get("level"));
                      if(!$data['details']->isEmpty()){
                      // $recordExistsWithNoDelete = $data['details']->contains('is_deleted', 0);
                      $recordExistsWithNoDelete = true;
                      if($recordExistsWithNoDelete) {
                        $matchFound = $data['details']->contains(function ($item) use ($main, $sessionLevel) {
                        return $item->is_deleted == 0 && $item->$main == 0 && $item->current_level == $sessionLevel;
                      });
                      //if($data['details'][0]->$main == 0 && $data['details'][0]->current_level == preg_replace('/[^0-9.]+/', '', Session::get("level")) ){
                      if($matchFound){
                    @endphp
                        <div class="col-md-12 d-flex mr-20">
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
                                CLEAR
                              </button>
                            </li>
                          </ul>

                          <div class="cancel-btn ml-auto">
						  <a href="{{url('approver/activity',$data['vq_data']['id'])}}" class="btn-grey border-0 m-0 p-0" data-title="View Activity">
							<!--<a href="" class="btn-grey border-0 m-0 p-0">-->
                                <img src="{{ asset('admin/images/clock.png') }}" class="view-activity">
                                <!-- <p>View Activity</p> -->
                                </a>
                            <a id="Commentbtn" href="#" class="btn-grey">
                              <img src="{{ asset('admin/images/blueeye.svg') }}">
                              <p>Comments</p>
                            </a>

                            <a id="cancel" href="#" class="btn-grey">
                              <img src="{{ asset('admin/images/cancel.svg') }}">
                              <p>Cancel VQ</p>
                            </a>
                            <button id="approve" class="orange-btn">
                              APPROVE AND SUBMIT
                            </button>
                          </div>
                      </div>
                      @php 
                      }else{
                        @endphp
                        <div class="col-md-12 d-flex">
                      

                          <div class="cancel-btn ml-auto">
                            
                            <a id="Commentbtn" href="#" class="btn-grey smbtn-cm">
                              <img src="{{ asset('admin/images/blueeye.svg') }}">
                              <p>Comments</p>
                            </a>

                          
                          </div>
                      </div>
                        @php

                      }
                    }else{
                        @endphp
                        <div class="col-md-12 d-flex">
                      

                          <div class="cancel-btn ml-auto">
                            
                            <a id="Commentbtn" href="#" class="btn-grey smbtn-cm">
                              <img src="{{ asset('admin/images/blueeye.svg') }}">
                              <p>Comments</p>
                            </a>

                          
                          </div>
                      </div>
                        @php
                      }
                    }
                      @endphp 
                        <div class="col">
                            <div class="actions-dashboard table-ct table-responsive">
                            
                              <table class="table VQ Request Listing vq-request-listing-tb nowrap" id="zero_config">
                                  <thead>
                                    <tr>
                                        <th class="d-none">#</th>
                                        <th class="" style="width: 110px !important">Division</th>
                                        <th class="w-110" style="width: 110px;">Mother Brand</th>
                                        <th>Item Code</th>
                                        <th class="" style="width: 160px !important">Brand</th>
                                        <th class="digitcenter">Disc. PTR (%)</th>
                                        <th class="digitcenter">RTH (Excl. GST)</th>
                                        <th>Stockist Code</th>
                                        <th>Stockist Name</th>
                                        <th>Stockist margin</th>
                                        <th>Mode of discount</th>
                                        <th style="width: 80px !important; white-space:normal;">Pack</th>
                                        
                                        <th class="digitcenter">App. GST(%)</th>
                                        <!--<th class="digitcenter">L. Y. Rate</th>-->
                                        <th class="digitcenter">L. Y. Disc.(%)</th>
                                        <th class="digitcenter">L. Y. Disc Rate</th>
                                        <th class="digitcenter">MRP</th>
                                        <th class="digitcenter">L. Y.MRP</th>
                                        <th class="digitcenter">C. Y. PTR</th>
                                        <th class="digitcenter d-none">PDMS Discount</th>
                                        <th class="digitcenter d-none">Max Discount</th>
                                        <th class="digitcenter d-none">Division Id</th>
                                        <th class="digitcenter">MRP Margin (%)</th>
                                        <th class="digitcenter">Billing price(Direct Master)</th>
                                        <th class="digitcenter">Billing price(Credit Note)</th>
                                        <th class="digitcenter">Last year % Margin on MRP</th>
                                        <th>Comments</th>
                                        <th style="width: 110px !important; white-space:normal;">Type</th>
                                        <th>HSN Code</th>
                                        <th>SAP Code</th>
                                        <th>Composition</th>
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
        <!-- Modal body -->
        <div class="modal-body">
          <p class="heading">Add Comments</p>
          <p class="msg p-0" style="color:green;">Comment added successfully</p>
          <div class="dymdate">
              <h5>Institution Name</h5>
              <h6 id="institutionname" style="text-align: right;">{{$data['vq_data']['hospital_name']}}</h6>
          </div>
          <div class="dymdate">
              <h5>Division Name</h5>
              <h6 id="divisionname">{{strtoupper(Session::get('division_name'))}}</h6>
          </div>
          <div class="dymdate">
              <h5>Mother Brand Name</h5>
              <h6 id="motherbrandname"></h6>
          </div>
          <div class="dymdate">
              <h5>Brand Name</h5>
              <h6 id="brandname"></h6>
          </div>
          <input type="hidden" id="item_code_field" value=""></textarea>

          <div class="form-group">
            <textarea class="form-control" rows="5" id="comment_field" placeholder="Add your Comments"></textarea>
          </div>

          <a id="save_comments" class="btn orange-btn" href="#">SAVE COMMENT</a>

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

<!-- cancel popup -->
<div class="modal show" id="cancelcomment">
    <div class="modal-dialog modal-dialog-centered model-pop-ct">
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header border-0">
            <div class="tich-logo">
            <img src="{{ asset('admin/images/exclamation.svg') }}" alt="">
            </div>
          <button type="button" class="close ok-btn" data-dismiss="modal">
              <img src="{{ asset('admin/images/close.svg') }}" alt="">
          </button>
        </div>
        <!-- Modal body -->
        <div class="modal-body normal">
          <p class="border-0">Cancel VQ Request for {{$data['vq_data']['hospital_name']}}?</p>
          <div class="division_filter_cancel d-none"><!-- added by govind on 170425 start -->
            <p class="border-0">Select division to Cancel</p>
            <div id="division_checkbox_list_cancel" style="max-height: 100px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
                @foreach ($data['pendingDivisions'] as $division)
                    <label class="d-block">
                      <input type="checkbox" class="division-checkbox_cancel" name="division_ids_cancel[]" value="{{ $division->div_name }}-{{ $division->div_id }}" checked>
                      {{ $division->div_name }}-{{ $division->div_id }}
                    </label>
                @endforeach
            </div>
          </div><!-- added by govind on 170425 end -->
 
          <div class="form-group">
            <textarea class="form-control" rows="5" id="cancel-comment" placeholder="Add your Comments"></textarea>
          </div>

          <a id="cancel-new" class="btn orange-btn" href="{{url('initiator/listing')}}">CANCEL VQ REQUEST</a>

        </div>
        <div class="modal-body success">
	        <p class="border-0">VQ Request for {{$data['vq_data']['hospital_name']}} cancelled successfully with your comment.</p>
	        <a id="cancel-new" class="btn orange-btn ok-btn">OK</a>
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
          <p class="border-0">Approve VQ Request for {{$data['vq_data']['hospital_name']}}?</p>
          <div class="form-group">
            <div class="division_filter d-none"><!-- added by govind on 170425 start -->
              <p class="border-0">Select division to approve</p>
              <div id="division_checkbox_list" style="max-height: 100px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
                @foreach ($data['pendingDivisions'] as $division)
                  <label class="d-block">
                    <input type="checkbox" class="division-checkbox" name="division_ids[]" value="{{ $division->div_name }}-{{ $division->div_id }}" checked>
                    {{ $division->div_name }}-{{ $division->div_id }}
                  </label>
                @endforeach
              </div>
            </div><!-- added by govind on 170425 end -->
            <textarea class="form-control" rows="4" id="approve-comment" placeholder="Add your Comments"></textarea>
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
          <button type="button" class="close" data-dismiss="modal" onclick="location.reload();">
              <img src="{{ asset('admin/images/close.svg') }}" alt="">
          </button>
        </div>
        <!-- Modal body -->
        <div class="modal-body">
          <p class="border-0">VQ Request for {{$data['vq_data']['hospital_name']}} is approved and submitted successfully</p>

          <a class="btn orange-btn big-btn ok-btn">OK</a>
        </div>
      </div>
    </div>
</div>

<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>

<script>
  const Disc_margin_item_code = @json($data['DiscountMargin_datas']);
  let levelSession = `{{strtolower(Session::get("level").'_status')}}`;
    levelSession = levelSession.toLowerCase()
    let levelNumber = `{{preg_replace('/[^0-9.]+/', '', Session::get("level"))}}`;
    let exception_items = @json($data['exception_items']);
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
        $.ajax(settings).done(function (response) {
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
        });
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
        $.ajax(settings).done(function (response) {
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
        });
      });


      $('#approve-new').click(function(e){
        e.preventDefault()
        $comment = $('#approve-comment').val();
        if($comment != ""){
          let selectedDivisions = [];
          if (levelNumber == 5 || levelNumber == 6) {
            $('.division-checkbox:checked').each(function () {
              selectedDivisions.push($(this).val());
            });
            if (selectedDivisions.length === 0) {
              alert("Please select at least one division.");
              return;
            }
          }
          // Add ajax call for updating price here
          var settings = {
            "url": "/approver/single_approve",
            "method": "POST",
            "timeout": 0,
            "headers": {
              "Accept-Language": "application/json",
            },
            "data": {
              "_token": "{{ csrf_token() }}",
              "vq_id": "{{ app('request')->route('id') }}",
              "comment": $comment,
              "div_id": (levelNumber == 5 || levelNumber == 6) ? selectedDivisions : []
            }
          };
          $.ajax(settings).done(function (response) {
	         $('#submited').modal('show');
	         $('#approveandsubmit').modal('hide');
            //window.location.href ='{{route("approver_listing")}}';           
          });
        }else{
          $("#error_message").show().html("Please enter comment.");
        }
      });

	  $('.msg').css('display', 'none');
      $('#save_comments').click(function(e){
	    $('.msg').css('display', 'none');
	    e.preventDefault()
        $comment = $('#comment_field').val();
        $item_code = $('#item_code_field').val();
        if($comment != ""){
          // Add ajax call for updating price here
          var settings = {
            "url": "/approver/add_comment",
            "method": "POST",
            "timeout": 0,
            "headers": {
              "Accept-Language": "application/json",
            },
            "data": {
              "_token": "{{ csrf_token() }}",
              "vq_id": "{{ app('request')->route('id') }}",
              "item_code": $item_code,
              "comment": $comment,
            }
          };
          $.ajax(settings).done(function (response) {
            $('#comment_field').val("");
            $('.msg').css('display', 'block');
            //$('.heading').append('<p class="msg p-0" style="color:green;">Comment added successfully</p>').addClass('p-0');
          });
        }else{
          
        }
      });

      $('#cancel-new').click(function(e){
      e.preventDefault();
      
      $comment = $('#cancel-comment').val();
      if($comment != ""){
        let selectedDivisions = [];
 
        if (levelNumber == 5 || levelNumber == 6) {
          $('.division-checkbox_cancel:checked').each(function () {
            selectedDivisions.push($(this).val());
          });
 
          if (selectedDivisions.length === 0) {
            alert("Please select at least one division.");
            return;
          }
        }
 
        // Add ajax call for updating price here
        var settings = {
          "url": "/approver/delete_vq",
          "method": "POST",
          "timeout": 0,
          "headers": {
            "Accept-Language": "application/json",
          },
          "data": {
            "_token": "{{ csrf_token() }}",
            "vq_id": "{{ app('request')->route('id') }}",
            "comment": $comment,
            "div_id": (levelNumber == 5 || levelNumber == 6) ? selectedDivisions : [],
          }
        };
        $.ajax(settings).done(function (response) {
          window.location.href ='{{route("approver_listing")}}'; 
          $('.modal-body.success').css('display','block');
          $('.modal-body.normal').css('display','none');
        });
      }else{
        $('.modal-body.success').css('display','none');
        $('.modal-body.normal').css('display','block');
      }
    });
	
	$('.ok-btn').click(function(){
		location.reload();	
	});
	
    $('body').on('click','.view-comments', function(e){
        e.preventDefault();
        var row = $(this).closest("tr");
        // var id = parseFloat(row.find(".d-none").text());
        var id = (row.find(".line_id").text());
        var brandname = (row.find(".brand_name").text());

        var settings = {
          "url": "/approver/sku_comment",
          "method": "GET",
          "timeout": 0,
          "headers": {
            "Accept-Language": "application/json",
          },
          "data": {
            "_token": "{{ csrf_token() }}",
            "line_id": id,
          }
        };
        $.ajax(settings).done(function (response) {
          parsedTest = JSON.parse(response.result['comments']);
          if(parsedTest != null){
            $('#showcmts .data-layer').empty();
            parsedTest.forEach((element) => {
	            var level_no = element['level'].replace(/\D/g,'');
	            var level_name = '';
	            switch(level_no){
		            case '1': level_name = 'RSM';break;
                case '2': level_name = 'ZSM';break;
                case '3': level_name = 'NSM';break;
		            case '4': level_name = 'SBU';break;
		            case '5': level_name = 'Semi Cluster';break;
		            case '6': level_name = 'Cluster';break;
                case '8': level_name = 'CEO';break;//added for ceo approval idap-33
	            }
                //$('#showcmts .data-layer').append('<h6>Level '+element['level'].replace(/\D/g,'')+'</h6><h5>'+element['comment']+'</h5>');
                $('#showcmts .data-layer').append('<h6>'+level_name+'</h6><h5>'+element['comment']+'</h5>');
                
                
                });
            }else{
                $('#showcmts .data-layer').empty();
                $('#showcmts .data-layer').append('<h5>No comments</h5>');
            }
            $('#showcmts .modal_heading').empty();
            $('#showcmts .modal_heading').html('<span>Brand Name: - </span>' + brandname);
            $('#showcmts').modal('show');
        });

    });

    $('#Commentbtn').click(function(e){
        e.preventDefault();


        var settings = {
          "url": "/approver/vq_comment",
          "method": "GET",
          "timeout": 0,
          "headers": {
            "Accept-Language": "application/json",
          },
          "data": {
            "_token": "{{ csrf_token() }}",
            "vq_id": "{{ app('request')->route('id') }}",
          }
        };
        $.ajax(settings).done(function (response) {
          console.log(response);
          parsedTest = JSON.parse(response.result['comments']);
          console.log(response.result['comments']);
          if(parsedTest != null){
            $('#showgeneralcmts .data-layer').empty();
              for (var k in parsedTest){
                  if (parsedTest.hasOwnProperty(k)) {
                      //alert("Key is " + k + ", value is " + parsedTest[k]);
                      parsedTest[k].forEach((element) => {
	                     	var level_no = element['level'].replace(/\D/g,'');
				            var level_name = '';
				            switch(level_no){
                      case '1': level_name = 'RSM';break;
                      case '2': level_name = 'ZSM';break;
                      case '3': level_name = 'NSM';break;
                      case '4': level_name = 'SBU';break;
                      case '5': level_name = 'Semi Cluster';break;
                      case '6': level_name = 'Cluster';break;
                      case '8': level_name = 'CEO';break;//added for ceo approval idap-33
				            }
                        //$('#showgeneralcmts .data-layer').append('<h6>Level '+element['level'].replace(/\D/g,'')+' of '+k+'</h6><h5>'+element['comment']+'</h5>');
                        $('#showcmts .data-layer').append('<h6>'+level_name+'</h6><h5>'+element['comment']+'</h5>');
                        });
                  }
              }
            }else{
                $('#showgeneralcmts .data-layer').empty();
                $('#showgeneralcmts .data-layer').append('<h5>No comments</h5>');
            }
            $('#showgeneralcmts .modal_heading').empty();
            $('#showgeneralcmts .modal_heading').text(' Comments');
            $('#showgeneralcmts').modal('show');
        });

    });



// APPLY now val js
$(document).ready(function() {
// $('#zero_config').DataTable({
//     "iDisplayLength": 100,
//     "bFilter": false,
//     "aaSorting": [
//       [2, "desc"]
//     ],
//     "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
//       if (aData[2] == "5") {
//         $('td', nRow).css('background-color', 'Red');
//       } else if (aData[2] == "4") {
//         $('td', nRow).css('background-color', 'Orange');
//       }
//     }
//   });
  // $('#addcomments').modal('show');
  // $('#cancelcomment').modal('show');
  
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
  if(levelNumber == 5 || levelNumber == 6)// added by govind on 170425 start
  {
    $('.division_filter_cancel').removeClass('d-none')
  }// added by govind on 170425 end
  $('.modal-body.success').css('display', 'none');
});

$('#approve').click(function(){
  if(levelNumber == 5 || levelNumber == 6)// added by govind on 170425 start
  {
  $('.division_filter').removeClass('d-none')
  }// added by govind on 170425 end
  $('#approveandsubmit').modal('show');
});



    $('.clear-val').click(function(){
      let text = "Are you sure you want to clear Disc PTR(%) for all SKUs? \n ' THIS CHANGE CAN NOT BE REVERT. '";
      if (confirm(text) == true) {
        $("#entervalue").val('');
        var disc = $("#entervalue").val();
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
            });
          }
    });

 //    $('.apply-val').click(function(){
 //        let entervalue = $("#entervalue").val();
 //        alert(entervalue);
 //        $('.ptr_percent').val(entervalue);
 //    });

 //    $(document).on("change keyup blur", ".ptr_percent", function() {
	//   var amd = $('.cBalance').val();
	//   var disc = $('.ptr_percent').val();
	//   if (disc != '' && amd != '') {
	//     $('.ptr_rate').val((parseInt(amd)) - (parseInt(disc)));
	//   }else{
	//     $('.ptr_rate').val(parseInt(amd));
	//   }
	// });


// $(document).on("change keyup blur", ".ptr_percent", function() {


// });



$( ".apply-val" ).on( "click", function() {
  let text = "Are you sure you want to apply Disc PTR(%) for all SKUs? \n ' THIS CHANGE CAN NOT BE REVERT. '";
  if (confirm(text) == true) {
    var disc = $("#entervalue").val();
    if(disc > 99){
      disc = 99;
    }
    if(disc >= 0 ){
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
            "discount_percent": disc,
          }
        };
        $.ajax(settings).done(function (response) {
          console.log(response);
          $("#entervalue").val('');
          location.reload();
        });
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
// document.querySelector('.percentctval').addEventListener('input', function(e) {
//   let int = e.target.value.slice(0, e.target.value.length - 1);
//   if (int.includes('%')) {
//     e.target.value = '%';
//   } else if (int.length >= 3 && int.length <= 4 && !int.includes('.')) {
//     e.target.value = int.slice(0, 2) + '.' + int.slice(2, 3);
//     // e.target.value = int.slice(0, 2) + ‘.’ + int.slice(2, 3) + ‘%’;
//     e.target.setSelectionRange(4, 4);
//   } else if (int.length >= 5 & int.length <= 6) {
//     let whole = int.slice(0, 2);
//     let fraction = int.slice(3, 5);
//     e.target.value = whole + '.' + fraction;
//     // e.target.value = whole + ‘.’ + fraction + ‘%’;
//   } else {
//     e.target.value = int + ' ';
//     // e.target.value = int + ‘%’;
//     e.target.setSelectionRange(e.target.value.length - 1, e.target.value.length - 1);
//   }
//   //console.log('For robots:' + getInt(e.target.value));
// }); // hide by arunchandru 03042025
//bulk update PTR  added by arunchandru 03042025
document.querySelector('.percentctval').addEventListener('input', function (e) {
    let value = e.target.value.replace(/[^0-9.]/g, ''); // Remove any non-numeric and non-dot characters
    let parts = value.split('.'); // Split by dot

    // Prevent multiple dots
    if (parts.length > 2) {
        e.target.value = parts[0] + '.' + parts[1]; // Keep only one dot
        return;
    }

    // Auto-format when length is 3-4 without a dot
    if (value.length >= 3 && value.length <= 4 && !value.includes('.')) {
        value = value.slice(0, 2) + '.' + value.slice(2);
    } else if (value.length > 5) {
        // Limit to 2 decimal places
        value = value.slice(0, 5);
    }

    e.target.value = value;
    e.target.setSelectionRange(e.target.value.length, e.target.value.length);
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
    
    function init_table()
    {
      $("#zero_config").DataTable({
        "pageLength": 50,
        serverSide: true,
        processing: true,
        ajax: {
            url: '/approver/vq_detail_listing_approver_ajax',
            type: 'GET',
            data: function (d) {
            // Add custom data to the request
                d.vq_id = "{{$data['vq_data']['id']}}";
                d.current_level = "{{$data['vq_data']['current_level']}}";
                d.total_rows = "{{count($data['details'])}}";
                return d;
            },
            "dataSrc": function(response) {
                // Extract stockist_margin value from the response and store it in a global variable
                window.stockistMargin = response.stockist_margin;
                // Return the data array for DataTable to process
                return response.data;
            }
        },
        "rowCallback": function(row, data) {
            // Example condition: add CSS class 'highlight' to rows where the third column is 'important'
            var tr_class = data.discount_percent != data.ceiling_percent ? 'dark-bg' : '';
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
            if (data.is_deleted == 1 && data.is_discarded == 1) {
              tr_style = 'background:#b5b5b5 !important;';
              $(row).attr('style', tr_style);
            }
            else{
              $(row).addClass(tr_class);
              $(row).attr('style', tr_style1+tr_style2+tr_style_disc_ptr);
            }
        },
        columns:
        [
          { data: 'id' },
          { data: 'div_name' },
          { data: 'mother_brand_name' },
          { data: 'item_code' },
          { data: 'brand_name' },
          { 
            data: function(row) {
              if(row.{{strtolower(Session::get("level").'_status')}} == 0 && row.current_level == levelNumber && row.is_deleted == 0)
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
            data: function(row) {
              if(parseFloat(row.discount_rate) < 0.1) var db_ptr_rate = parseFloat(row.discount_rate);//added condition to check value less than 0.1 and remove rounding
              else var db_ptr_rate = parseFloat(row.discount_rate).toFixed(2);
              if(row.{{strtolower(Session::get("level").'_status')}} == 0 && row.current_level == levelNumber && row.is_deleted == 0)
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
          {
            data: 'get_sku_stockist',
            render: function(data, type, row, meta) {
                let stockistCodes = data.map(function(stockist) {
                    return stockist.get_stockist_details.stockist_code;
                });
                return stockistCodes.join(', ');
            }
          },
          {
              data: 'get_sku_stockist',
              render: function(data, type, row, meta) {
                  let stockistName = data.map(function(stockist) {
                      return stockist.get_stockist_details.stockist_name;
                  });
                  return stockistName.join(', ');
              }
          },
          {
            data: 'discount_margin',
            render: function(data, type, row, meta) {
              if (data !== null && data !== undefined && data !== '') {
                  return parseFloat(data).toFixed(2);
              } else {
                  return 10;
              }
            }
          },
          // {
          //     // Specify the column where you want to display the stockist_margin value
          //     "data": null,
          //     "render": function(data, type, row, meta) {
          //         let inputMargin = (Disc_margin_item_code[row.item_code])? Disc_margin_item_code[row.item_code] : 10;
          //         return inputMargin;
          //         // Access the stockist_margin value from the global variable and return it
          //         // return window.stockistMargin;
          //     }
          // },
          {
              data: 'get_sku_stockist',
              render: function(data, type, row, meta) {
                  if (data && data.length > 0) {
                      let paymode = data.map(function(stockist) {
                          return stockist.payment_mode ? stockist.payment_mode : ''; // handle null payment_mode
                      });
                      return paymode.filter(Boolean).join(', '); // remove empty strings from array before joining
                  } else {
                      return ''; // handle case where data is null or empty array
                  }
              }
          },
            { data: 'pack' },
            { data: 'applicable_gst' },
            { data: 'last_year_percent',
                "render": function(data, type, row, meta) {
                    if (data !== null && data !== undefined && data !== '') {
                        return parseFloat(data).toFixed(2);
                    } else {
                        return 0;
                    }
                }
            },
            { data: 'last_year_rate',
                "render": function(data, type, row, meta) {
                    if (data !== null && data !== undefined && data !== '') {
                        return parseFloat(data).toFixed(2);
                    } else {
                        return 0;
                    }
                }
            },
            { data: 'mrp',
                "render": function(data, type, row, meta) {
                    if (data !== null && data !== undefined && data !== '') {
                        if(parseFloat(data) < 0.1) return parseFloat(data);//added condition to check value less than 0.1 and remove rounding
                        else return parseFloat(data).toFixed(2);
                    } else {
                        return 0;
                    }
                }
            },
            { data: 'last_year_mrp',
                "render": function(data, type, row, meta) {
                    if (data !== null && data !== undefined && data !== '') {
                        return parseFloat(data).toFixed(2);
                    } else {
                        return 0;
                    }
                }
            },
            { data: 'ptr',
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
              data: function(row) {
                var result = parseFloat(row.pdms_discount).toFixed(2)
                return result;
              }
            },
            { 
              data: function(row) {
                var result = parseFloat(row.max_discount).toFixed(2)
                return result;
              }
            },
            { 
              data: function(row) {
                var result = row.div_id
                return result;
              }
            },
            { data: 'mrp_margin',
                "render": function(data, type, row, meta) {
                    if (data !== null && data !== undefined && data !== '') {
                        return parseFloat(data).toFixed(2);
                    } else {
                        return 0;
                    }
                }
            },
            { data: 'discount_rate',
                "render": function(data, type, row, meta) {
                    if (data !== null && data !== undefined && data !== '') {
                        var calculated_data = data - ((data*window.stockistMargin)/100);
                         if(parseFloat(calculated_data) < 0.1) return parseFloat(calculated_data).toFixed(6);//added condition to check value less than 0.1 and remove rounding
                        else return parseFloat(calculated_data).toFixed(2);
                    } else {
                        return 0;
                    }
                }
            },
            { data: 'discount_rate',
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
                data: function(row) {
                    var lastYearMrp = row.last_year_mrp;
                    var lastYearPtr = row.last_year_ptr;
                    var result = lastYearMrp != null && lastYearPtr != null ? parseFloat(((lastYearMrp - lastYearPtr) / lastYearMrp) * 100).toFixed(2) : "-";
                    return result;
                }
            },
            { 
                data: function(row) {
                    var element = `<a href="javascript:void(0)" class="popup-comment add-comments" data-title="Add Comments">
                      <img src="{{ asset('admin/images/comment-medical.svg') }}" alt="">
                    </a>
                    <a href="javascript:void(0)" class="popup-comment view-comments" data-title="View Comments">
                      <img src="{{ asset('admin/images/comment-eye.svg') }}" alt="">
                    </a>`
                    return element;
                }
            },
            { data: 'type' },
            { data: 'hsn_code' },
            { data: 'sap_itemcode',
                "render": function(data, type, row, meta) {
                    if (data !== "null" && data !== undefined && data !== '') {
                        return data;
                    } else {
                        return "-";
                    }
                }
            },
            {  data: function(row) {
                    var composition = row.composition;
                    return composition.toUpperCase().toLowerCase();
                }
            },
        ],
        columnDefs: [
            {
                targets: 0, //item_id
                className: 'd-none line_id', 
            },
            {
                targets: 1, //div_name
                className: '', 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'white-space': 'normal',
                        'width': '110px',
                        // Add other styles here
                    });
                }
            },
            {
                targets: 2, //mother_brand_name
                className: 'mother_brand_name', 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'white-space': 'normal',
                        'width': '110px',
                    });
                }
            },
            {
                targets: 3, //item_code
                className: 'item-code', 
            },
            {
                targets: 4, //brand_name
                className: 'brand_name', 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'white-space': 'normal',
                        'width': '160px',
                    });
                }
            },
            {
                targets: 5, //discount_percent
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 6, //discount_rate
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 11, //pack
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'white-space': 'normal',
                        'width': '80px',
                    });
                }
            },
            {
                targets: 12, //applicable_gst
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 13, //last_year_percent
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 14, //last_year_rate
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'center'
                    });
                }
            },
            {
                targets: 15, //mrp
                className: 'mrp', 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 16, //last_year_mrp
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'center'
                    });
                }
            },
            {
                targets: 17,//ptr
                className: 'cBalance',  
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 18, //pdms_discount
                className: 'd-none pdms_discount',  
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 19, //max_discount
                className: 'd-none max_discount',  
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 20, //div_id
                className: 'd-none div_id',  
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 21, //mrp_margin
                className: 'mrp_margin', 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 22, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 23, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 24, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 25, 
                className: 'd-flex comment-box', 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'center'
                    });
                }
            },
            {
                targets: 26,  
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                      'white-space': 'normal',
                      'width': '110px !important'
                    });
                }
            }
            // Define other column definitions here
        ],
        'language': {    'paginate': {      'previous': "<img src='{{ asset('admin/images/left.svg') }}' class='left-block' alt=''> <img src='{{ asset('admin/images/left1.svg') }}' class='disbpr' alt=''>",      'next': "<img src='{{ asset('admin/images/right.svg') }}' class='neblk' alt=''> <img src='{{ asset('admin/images/right1.svg') }}' class='disbn' alt=''>"    }  },
        scrollX: true
      });
    }
    </script>
  @if(Session::get('level') != 'L1')
    <script>
        init_table()
    </script>
    @else
    <script>
        init_table()
    </script>
    @endif
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

