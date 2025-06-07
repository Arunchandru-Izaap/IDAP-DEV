@extends('layouts.frontend.app')
@section('content')
<style>
  #instituteOpt .select2{ 
    max-width: 680px !important;
    width: 100% !important; 
  }
 
</style>
<div id="page-content-wrapper">
   <!-- Top navigation-->
   <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
         <div class="collapse navbar-collapse show" id="navbarSupportedContent">
            <ul class="navbar-nav dashboard-nav">
               <li class="nav-item active"><a class="nav-link" href="{{route('approver_listing')}}"> <img src="{{ asset('admin/images/back.svg')}}"> - VQ Request Details</a></li>
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
      <li>
         <a href="{{route('approver_dashboard')}}">
         Home
         </a>
      </li>
   </ul>
   <!-- Page content-->
   <div class="container-fluid">
      <div class="row">
      <div class="col-md-6 mr-20">
    
            <select id="brandname" class="js-example-basic-multiple brandName" name="brandName" multiple="multiple" data-placeholder="Select brand name">
               <option value=""> Select brand name </option>
               @foreach($brand as $brand_name)
               <option value="{{ $brand_name->brand_name }}">{{ $brand_name->brand_name }}</option>
               @endforeach
            </select>
            <div class="baseOnBrands mr-20" id="refreshDiv">
              <div class="form-check-inline">
                <label class="form-check-label" for="radio1">
                  <input type="radio" class="form-check-input" id="radio1" name="optradio" value="All institution" checked>All Institution
                </label>
              </div>
              <div class="form-check-inline">
                <label class="form-check-label" for="radio2">
                  <input type="radio" class="form-check-input" id="radio2" name="optradio" value="Institution Manually">Select Institution Manually
                </label>
              </div>
            </div>
         </div>
         <div class="col-md-6 d-flex mr-20">
            <ul class="rth-cal d-flex">
               <li>
                  <div class="form-group form-inline m-0">
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
         </div>
      </div>
      <div class="row">
         <div class="col-md-12" id="instituteOpt">
            <label>Select Institutions</label>
            <br/>
            <select class="js-example-basic-multiple"  id="instituteName" name="instituteId" multiple="multiple">
              
                <option value=""> Institution name</option>
                @foreach($institute as $institute_name)
                <option value="{{$institute_name['institution_id']}}">{{$institute_name['institution_id']}} - {{$institute_name['hospital_name']}}</option>
                @endforeach
                
            </select>
         </div>
         <div class="col-md-12">
          <button class="orange-btn" id="sbmitBtn" style="margin-top: 10px;">View</button>
          <button id="add-top" class="orange-btn btn-lg" style="width:200px;position: absolute;right:2rem;bottom: 0rem;"> Save </button>
         </div>
      </div>
      <div class="row">
      <div id='loader' style='display: none;'>
                <!-- <img src="{{asset('images/loader.gif')}}" width='32px' height='32px'> -->
      </div>
        <div class="actions-dashboard table-ct table-responsive" id="loaderDis">
            <table class="table VQ Request Listing vq-request-listing-tb nowrap" id="manage_sku_table">
              <thead>
                <tr>
                  <th>Institute Name</th>
                  <th>Brand Name</th>
                  <th class="">L. Y. Disc.(%)</th>
                  <th class="digitcenter">MRP</th>
                  <th class="digitcenter">C. Y. PTR</th>
                  <th class="digitcenter">Disc. PTR (%)</th>
                  <th class="digitcenter">RTH (Excl. GST)</th>
                  <th class="digitcenter">MRP Margin (%)</th>
                </tr>
              </thead>
              
              <tbody id="dataList">
              
              </tbody>
             
              </table>
              <button id="add" class="orange-btn btn-lg" style="width:300px;margin: auto;display: block;"> Save </button>
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
          <button type="button" class="close" id="modal_btn_close" data-dismiss="modal">
              <img src="{{ asset('admin/images/close.svg') }}" alt="">
          </button>
        </div>
        <!-- Modal body -->
        <div class="modal-body">
          <p class="border-0"> Data updated  successfully</p>

          <a class="btn orange-btn big-btn ok-btn close_update_ptr" data-dismiss="modal">OK</a>
        </div>
      </div>
    </div>
</div>
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
                <p class="border-0 modal_heading">Disc PTR value applied successfully. Please click save to complete the
                    process</p>
                <!-- <div class="data-layer comment-text">
</div> -->
            </div>
        </div>
    </div>
</div>
<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script src="{{asset('frontend/js/select2.js')}}"></script>
<script>
  $(document).ready(function() {
    $('.js-example-basic-single').select2({ width: '100%' });
    $('.js-example-basic-multiple').select2({ width: '100%' });
  });
  $('#add-top').click(function(){
    $('#add').trigger('click');
  });
  $('#add').click(function(){
    var ptr_percent = [];
    var ptr_rate = [];
    var id = [];
    var mrp_margin = [];
    var brand_name = $('#brandname').val();
    var item_code =[] ;  
    var vq_id = [];
    $('#dataList tr').each(function (index, tr) {
      if($(this).find(".ptr_percent").val() && $(this).find(".ptr_rate").val()){
        id.push($(this).attr('id'));
        item_code.push($(this).data("item_code"));
        ptr_percent.push($(this).find(".ptr_percent").val());
        ptr_rate.push($(this).find(".ptr_rate").val());
        mrp_margin.push($(this).find('.mrp_margin').text());
        vq_id.push($(this).data("vq_id"));
      }
    });
  
    const dataToSend = {
      "_token":"{{ csrf_token() }}",
      'id':id,
      'prt_percent':ptr_percent,
      'ptr_rate':ptr_rate,
      'mrp_margin':mrp_margin,
      'brand_name':brand_name,
      'item_code':item_code,
      'vq_id':vq_id
    };

    $("#loaderDis").hide();
    $.ajax({
      url:'{{route("update-sku")}}',
      method: 'POST',
      // data:{'id':id,'prt_percent':ptr_percent,'ptr_rate':ptr_rate,'mrp_margin':mrp_margin,'brand_name':brand_name, "_token":"{{ csrf_token() }}",'item_code':item_code,'vq_id':vq_id}, // hide by arunchandru 15012025
      data: JSON.stringify(dataToSend), // Serialize the data
      contentType: 'application/json', // Set content type
      beforeSend: function(){
        $("#loader").show();
      },
    success:function(response)
    {
      $("#loader").hide();
      $("#loaderDis").show();
      $('#submited').modal({backdrop: 'static', keyboard: false},'show');
      $('#add').hide();
      $('#add-top').hide();
      //window.location.reload();
    }
  });
});

$(document).ready(function(){
    $('.baseOnBrands').hide();
    $('#instituteOpt').hide();
    $('#sbmitBtn').hide();
    $('#add').hide();
    $('#add-top').hide();
   });

   $('#instituteName').change(function(){
    $('#sbmitBtn').show();
   });

   $('#brandname').change(function(){
      var selValue = $(this).val();
      if(selValue !=''){
        $('.baseOnBrands').show();
        $('#sbmitBtn').show();
      }else{
        $("#radio1").prop("checked", true);
        $('.baseOnBrands').hide();
        $('#instituteOpt').hide();
        // $('#sbmitBtn').hide();
      }
   });

   $('#sbmitBtn').click(function(){
    var add_flag = 0;
    var instituteId = $('#instituteName').val(); 
    var brandName = $('.brandName').val();
    var selectedVal = "";
    var selected = $(".baseOnBrands input[type='radio']:checked");
    if (selected.length > 0) {
        selectedVal = selected.val();
    }
    if (!brandName || brandName.length === 0) {
      alert('No brand name selected.');
      return;
    }
    if(selectedVal == 'Institution Manually')
    {
      if (!instituteId || instituteId.length === 0) {
        alert('No institute selected.');
        return;
      }
    }
    $("#loaderDis").hide();
      $.ajax({
              url:'{{route("list-base-institute")}}',
              method: 'POST',
              data:{'institute':instituteId,'brandName':brandName,'btnValue':selectedVal,"_token":"{{ csrf_token() }}"},
              beforeSend: function(){
                $("#loader").show();
              },

              success:function(response)
              {
                $("#manage_sku_table").DataTable().destroy();

                var strHtml='';
                var val1='';
                var last_year_discount = '';
                $.each(response, function (key1,val1) {
                  var ly_percent = 0;
                  var dt_mrp_margin = 0;
                  var dt_discount_rate = 0;
                  var dt_discount_percent = 0;
                  var dt_ptr = 0;
                  if(val1.last_year_percent != null ){
                    ly_percent = val1.last_year_percent;
                  } 
                  if(val1.mrp_margin != null)
                  { 
                    dt_mrp_margin = val1.mrp_margin.toFixed(2);
                  }
                  if(val1.discount_rate != null)
                  { 
                    dt_discount_rate = val1.discount_rate.toFixed(2);
                  }
                  if(val1.discount_percent != null)
                  { 
                    dt_discount_percent = val1.discount_percent.toFixed(2);
                  }
                  if(val1.ptr != null)
                  { 
                    dt_ptr = val1.ptr.toFixed(2);
                  }
                  if("{{Session::get('level')}}" == "L"+val1.current_level && val1["{{Session::get('level')}}".toLowerCase() + '_status'] == 0){
		 //if("{{Session::get('level')}}" == "L3" && val1["{{Session::get('level')}}".toLowerCase() + '_status'] == 0){
                    strHtml+='<tr class="manage_sku" data-item_code="'+val1.item_code+'" id="'+val1.id+'" data-vq_id="'+val1.vq_id+'"><td>'+val1.hospital_name+'</td><td>'+val1.brand_name+'</td><td>'+ly_percent+'</td><td class="mrp">'+val1.mrp+'</td>';
                    strHtml+='<td class="cBalance">'+dt_ptr+'</td><td><input type="text" class="ptr_percent" name="discount_ptr" value="'+dt_discount_percent+'"></td><td><input type="text" class="ptr_rate" value="'+dt_discount_rate+'"></td>';
                    add_flag = 1;
                    console.log("inside")
                  }else{
                    strHtml+='<tr data-item_code="'+val1.item_code+'" id="'+val1.id+'" data-vq_id="'+val1.vq_id+'"><td>'+val1.hospital_name+'</td><td>'+val1.brand_name+'</td><td>'+ly_percent+'</td><td class="mrp">'+val1.mrp+'</td>';
                    strHtml+='<td class="cBalance">'+dt_ptr+'</td><td>'+dt_discount_percent+'</td><td>'+dt_discount_rate+'</td>';
                    console.log("outside")
                  }
                 

                  strHtml+='<td class="mrp_margin">'+dt_mrp_margin+'</td>';
            
                  strHtml+='</tr>';
                });
                // console.log(strHtml);
                $('#dataList').html(strHtml);
                
                $("#manage_sku_table").DataTable(
                      {
                        "pageLength": 3000,
                        language: {    'paginate': {      'previous': "<img src='{{ asset('admin/images/left.svg') }}' class='left-block' alt=''> <img src='{{ asset('admin/images/left1.svg') }}' class='disbpr' alt=''>",      'next': "<img src='{{ asset('admin/images/right.svg') }}' class='neblk' alt=''> <img src='{{ asset('admin/images/right1.svg') }}' class='disbn' alt=''>"    }  },
                        scrollX: false,
			"dom": "frti",
                        
                      });
                      
                      $("#loader").hide();
                      $("#loaderDis").show();
                      if(add_flag==1){
                        $('#add').show();
                        $('#add-top').show();
                      }else{
                        $('#add').hide();
                        $('#add-top').hide();
                      }
              },
              error: function(response) {
              }
              
          });
   });

   $('#radio1').click(function() {
    if($('#radio1').is(':checked')) 
    { 
      $('#instituteOpt').hide();
      $('#sbmitBtn').show();
    }                      
  });
  $('#radio2').click(function() {
    if($('#radio2').is(':checked')) 
    {
      $('#instituteOpt').show();
      $('#sbmitBtn').show();
    }                      
  });
  $('.decimal').keypress(function(evt){
    return (/^[0-9]*\.?[0-9]*$/).test($(this).val()+evt.key);
  });
   
   $("#entervalue").focusout(function(){
   	var bulk_input = $(this).val();
   	if(bulk_input == ' ' || bulk_input == ''){
    	$('#entervalue').val('0');
    	}
   });
   
   $(document).on("change", '.ptr_percent' ,function(){
    // Convert rate here
      var row = $(this).closest("tr");
      var main = parseFloat(row.find(".cBalance").text());//CYptr
      var disc = parseFloat(row.find(".ptr_percent").val());//Disc PTR
      var mrp = parseFloat(row.find(".mrp").text());
      var db_disc_percent = '0.00';
      var db_disc_rate = main;
      row.find('.ptr_rate').css('border','none');
      var mrp_margin = row.find(".mrp_margin").text();
      
      
    if (disc != '' && main != '') {
      var dec = disc/100; //its convert 10 into 0.10
      var mult = main*dec; // gives the value for subtract from main value
      var discont = (main-mult).toFixed(2);
      row.find('.ptr_rate').val(discont);//discount (rth)
      db_disc_percent = disc;
      db_disc_rate = discont;
      mrp_margin = ((mrp-discont)/mrp)*100;
      row.find(".mrp_margin").text(mrp_margin.toFixed(2));
    }else{
      
      // row.find('.ptr_rate').val(parseInt(main));
      row.find('.ptr_rate').val('0');
      row.find(".ptr_percent").val(main);
      mrp_margin = ((mrp-main)/mrp)*100;
      row.find(".mrp_margin").text(mrp_margin.toFixed(2));
    }
    
    if(disc == 0.0 || isNaN(disc)){
     
       row.find('.ptr_rate').val(main);
      row.find(".ptr_percent").val('0');
      mrp_margin = ((mrp-main)/mrp)*100;
      row.find(".mrp_margin").text(mrp_margin.toFixed(2));
    }
    
    if(disc > 99){
      db_disc_percent = '99';
      mult = main*0.99; // gives the value for subtract from main value
       db_disc_rate = (main-mult).toFixed(2);
       row.find(".ptr_percent").val('99');
       row.find('.ptr_rate').val(db_disc_rate);
       mrp_margin = ((mrp-db_disc_rate)/mrp)*100;
      row.find(".mrp_margin").text(mrp_margin.toFixed(2));
    }

    console.log(row.find(".item-code").text());
    // Add ajax call for updating price here
    console.log("{{ app('request')->route('id') }}");
    
   });
   
    $(document).on("change", '.ptr_rate' ,function(){
    // Convert Percentage here
    var ptr_rate = $(this).val();
    var row = $(this).closest("tr");
    var listPrice = parseFloat(row.find(".cBalance").text());//CYPTR
    var salePrice = parseFloat(row.find(".ptr_rate").val());
    var disc_per = '0.00';
    var db_sale_price = listPrice;
    var mrp = parseFloat(row.find(".mrp").text());
    var mrp_margin = row.find(".mrp_margin").text();
    
    if(ptr_rate <= listPrice){
      if (salePrice != '' && listPrice != '') {
        var discont = 100 - (salePrice * 100 / listPrice).toFixed(2);
        row.find('.ptr_percent').val(discont.toFixed(2));
        mrp_margin = ((mrp-salePrice)/mrp)*100;
        row.find(".mrp_margin").text(mrp_margin.toFixed(2));
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
      mrp_margin = ((mrp-listPrice)/mrp)*100;
      row.find(".mrp_margin").text(mrp_margin.toFixed(2));
    }
   
   if(ptr_rate == ''){
    row.find('.ptr_percent').val('0');
    row.find('.ptr_rate').val(listPrice);
    mrp_margin = ((mrp-listPrice)/mrp)*100;
    row.find(".mrp_margin").text(mrp_margin.toFixed(2));
   }
    
   });
   
   
   // APPLY now val js
   $(document).ready(function() {   
   $('.add-comments').click(function(e){
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
   
   $( ".apply-val" ).on( "click", function() {
    if ($(".manage_sku").length > 0) {
      let text = "Are you sure you want to apply Disc PTR(%) for selected SKUs and Institutions? \n ' This change cannot be revert'";
      if (confirm(text) == true) {
        var ptrBulkValue = $("#entervalue").val();
        if(ptrBulkValue > 99){
          ptrBulkValue = 99;
        }
        if(ptrBulkValue >= 0){

          $('.manage_sku').each(function(){
            var row = $(this).closest("tr");
            var cy_ptr = row.find('.cBalance').text();
            var mrp = row.find('.mrp').text();
            var rth = cy_ptr-(cy_ptr*(ptrBulkValue/100));
            var mrp_margin = ((mrp - rth.toFixed(2)) / mrp)*100;
            console.log('rth = '+ rth.toFixed(2));
            console.log(mrp_margin);
            row.find('.ptr_percent').val(ptrBulkValue); 
            row.find('.ptr_rate').val(rth.toFixed(2)); 
            row.find('.mrp_margin').text(mrp_margin.toFixed(2));
          });

        }
        $('#showbulkpopup').modal('show');
      }  
    } 
   });
   
   $('.close_update_ptr').on('click', function(){
    window.location.reload();
   })
   $('#modal_btn_close').on('click', function(){
    $('#add').show();
    $('#add-top').show();
   })
   });
   
   function isNumberKey(evt){
   var charCode = (evt.which) ? evt.which : event.keyCode
   if (charCode > 31 && (charCode < 48 || charCode > 57))
    return false;
   return true;
   }  
   
   //bulk update PTR
  //  document.querySelector('.percentctval').addEventListener('input', function(e) {
  //  let int = e.target.value.slice(0, e.target.value.length - 1);
  //  if (int.includes('%')) {
  //  e.target.value = '%';
  //  } else if (int.length >= 3 && int.length <= 4 && !int.includes('.')) {
  //  e.target.value = int.slice(0, 2) + '.' + int.slice(2, 3);
  //  // e.target.value = int.slice(0, 2) + ‘.’ + int.slice(2, 3) + ‘%’;
  //  e.target.setSelectionRange(4, 4);
  //  } else if (int.length >= 5 & int.length <= 6) {
  //  let whole = int.slice(0, 2);
  //  let fraction = int.slice(3, 5);
  //  e.target.value = whole + '.' + fraction;
  //  // e.target.value = whole + ‘.’ + fraction + ‘%’;
  //  } else {
  //  e.target.value = int + ' ';
  //  // e.target.value = int + ‘%’;
  //  e.target.setSelectionRange(e.target.value.length - 1, e.target.value.length - 1);
  //  }
  //  }); // hide by arunchandru 03042025
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