@extends('layouts.frontend.app')
@section('content')

<div id="page-content-wrapper">
                <!-- Top navigation-->
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                            <ul class="navbar-nav dashboard-nav">
                                <li class="nav-item active"><a class="nav-link" href="{{route('initiator_listing')}}"> <img src="{{ asset('admin/images/back.svg')}}">{{$data['vq_data']['hospital_name']}} - VQ Request Details</a></li>
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
                        if($data['vq_data']){
                        if($data['vq_data']["current_level"] == 7 && $data['vq_data']["vq_status"] != 1){
                        @endphp
                            <div class="col-md-12 d-flex send-quotation">

                                <button id="bulk_update_btn" class="orange-btn">Bulk Update</button>
                                
                            <div class="cancel-btn ml-auto">
                                
                                <button class="orange-btn" id="save_changes_btn">Save Changes</button>

                                <a href="{{url('initiator/view_poc',$data['vq_data']['id'])}}" class="orange-btn" id="send_quotation" >
                                Send Quotation
                                </a>
                            </div>
                        </div>
                        @php 
                        }elseif($data['vq_data']["current_level"] == 7 || $data['vq_data']["vq_status"] == 1){
                        @endphp
                        <div class="col-md-12 d-flex send-quotation">
                                
                            <button id="bulk_update_btn" class="orange-btn">Bulk Update</button>

                            <div class="cancel-btn ml-auto">
                                <button class="orange-btn" id="save_changes_btn">Save Changes</button>
                            </div>
                        </div>

                        @php
                        }
                        }
                        @endphp 
                        <div class="col pd-20">
                            <div class="actions-dashboard table-ct">

                              <table class="table VQ Request Listing vq-request-listing-tb nowrap" id="zero_config">
                                    <thead>
                                    <tr>
                                        <th class="" style="width: 110px !important">Division</th>
                                        <th class="d-none">#</th>
                                        <th class="w-110" style="width: 110px;">Mother Brand</th>
                                        <th class="digitcenter">Item Code</th>
                                        <th class="" style="width: 160px !important">Brand</th>
                                        <th class="digitcenter">Pack</th>
                                        
                                        <th class="digitcenter">App. GST(%)</th>
                                        <!--<th class="digitcenter">L. Y. Rate</th>-->
                                        <th class="digitcenter">L. Y. Disc.(%)</th>
                                        <th class="digitcenter">L. Y. Disc Rate</th>
                                        <th class="digitcenter">MRP</th>
                                        <th class="digitcenter">L. Y.MRP</th>
                                        <th class="digitcenter">C. Y. PTR</th>
                                        <th class="digitcenter">Disc. PTR (%)</th>
                                        <th class="digitcenter">RTH (Excl. GST)</th>
                                        <th class="digitcenter">MRP Margin (%)</th>
                                        <th>Type</th>
                                        <th>HSN Code</th>
                                        <th>Composition</th>
                                        <th>Stockist Name</th>
                                        <th>Mode of Payment</th>
                                        <th style="display: none;"></th>
                                        <th>Net Discount Percent</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['details'] as $item)
                                        <tr>
                                            <td class="" style="white-space:normal; width: 110px !important">{{$item->div_name}}</td>
                                            <td class="d-none">{{$item->id}}</td>
                                            <td style="width: 110px;white-space:normal;">{{$item->mother_brand_name}}</td>

                                            <td>{{$item->item_code }}</td>
                                            <td class="brand_name" style="width: 160px !important;white-space:normal;">{{$item->brand_name}}</td>
                                            <td>{{$item->pack}}</td>
                                            
                                           
                                            <td style="text-align: right">{{$item->applicable_gst}}</td>
                                            <td style="text-align: right">{{round($item->last_year_percent,2)}}</td>
                                            <td style="text-align: right">{{round($item->last_year_rate, 2)}}</td>
                                            <td style="text-align: right">{{round($item->mrp,2)}}</td>
                                            <td style="text-align: right">{{round($item->last_year_mrp, 2)}}</td>
                                            <td style="text-align: right">{{round($item->ptr,2) }}
                                            </td>
                                           
                                            <td style="text-align: right">{{round($item->discount_percent,2)}} </td>
                                            <td style="text-align: right">{{round($item->discount_rate,2) }} </td>
                                           <td style="text-align: right">{{round($item->mrp_margin,2)}}</td>
                                            <td>{{$item->type}}</td>
                                            <td>{{$item->hsn_code}}</td>
                                            <td>{{ucwords(strtolower($item->composition)) }}</td>
                                            <td>{{ $item->stockist_name }}</td>
                                            <td>
                                                <select name="payment_mode" class="payment_mode" class="form-control form-control-sm" onchange="singlePayModeChangeHandler($(this), <?= $item->ptr ?>, <?= $item->discount_percent ?>)">
                                                    <option value="DM" <?php if($item->payment_mode == 'DM') echo "selected"; ?>>Direct Master</option>
                                                    <option value="CN" <?php if($item->payment_mode == 'CN') echo "selected"; ?>>Credit Note</option>
                                                </select>
                                            </td>
                                            <td style="display: none;">{{ $item->id }}</td>
                                              <td class="net_discount_percent">
                                         	@if($item->net_discount_percent != NULL)
                                                    {{$item->net_discount_percent}}
                                                @else
                                                    @if($item->ptr == 0)
                                                        {{ number_format((float)(($item->ptr-(($item->ptr - (($item->ptr * $item->discount_percent) / 100))-(($item->ptr - (($item->ptr * $item->discount_percent) / 100)) * 10 / 100))) / 100), 2, '.', '') }}
                                                    @else
                                                        {{  number_format((float)(($item->ptr-(($item->ptr - (($item->ptr * $item->discount_percent) / 100))-(($item->ptr - (($item->ptr * $item->discount_percent) / 100)) * 10 / 100))) / $item->ptr*100), 2, '.', '') }}
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                              </table>

                            </div>
                        </div><!-- col close -->

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

<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script>

    function singlePayModeChangeHandler(e, ptr, inputDiscRate){
        let selectedPaymentMode = e.val();
        let netDiscountRateToStockist;
        let inputMargin = 10;
        if(selectedPaymentMode == 'DM'){
            let discountamt = ptr - ((ptr * inputDiscRate) / 100);
            let marginamt = discountamt * inputMargin / 100;
            let nrv = discountamt - marginamt;
            netDiscountRateToStockist = (ptr - nrv) / ptr * 100;
        }else if(selectedPaymentMode == 'CN'){
            netDiscountRateToStockist = inputDiscRate;
        }
        $(e.parent()).siblings('.net_discount_percent').html(netDiscountRateToStockist % 1 ? Number(netDiscountRateToStockist).toFixed(2) : Number(netDiscountRateToStockist));
    }

    $(document).ready(function(){
        let selectedStockist;

        $('#send_quotation').hide()
        $('#bulk_update_btn').click(function(){

            $("#bulk_update_btn").after(`<div class="col-md-6 d-flex mr-20" id="bulk_update_form">
                                    <ul class="rth-cal d-flex">
                                        <li>
                                            <div class="form-group m-2">
                                                <select name="stockist_dropdown" id="stockist_dropdown" class="form-control form-control-sm fit_width">
                                                    <option value="none" selected disabled hidden>Select</option>
                                                    @foreach($data['stockists'] as $stk)
                                                        <option value="{{$stk['id']}}">{{ $stk['stockist_name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="form-group m-2">
                                                <select name="payment_mode_dropdown" id="payment_mode_dropdown" class="form-control form-control-sm fit_width">
                                                    <option value="DM">Direct Master</option>
                                                    <option value="CN">Credit Note</option>
                                                </select>
                                            </div>
                                        </li>
                                        <input type="hidden" value="{{$data['vq_data']['id']}}" name="vq_id"/>
                                        <li>
                                            <button class="apply-val orange-btn-bor" id="apply_bulk_update_btn">
                                            APPLY
                                            </button>
                                        </li>
                                        <li>
                                            <button class="clear-val orange-btn-bor" id="cancel_bulk_update_btn">
                                            CANCEL
                                            </button>
                                        </li>
                                    </ul>
                                </div>`);
            
            $("#bulk_update_btn").hide();
        })

        $(document).on('click', '#cancel_bulk_update_btn',function(){
            $("div").remove("#bulk_update_form");
            $("#bulk_update_btn").show();
        })
        
        $(document).on('change', '#stockist_dropdown', function(){
            selectedStockist = document.getElementById('stockist_dropdown')[document.getElementById('stockist_dropdown').selectedIndex].innerHTML;;
            console.log(selectedStockist);
            
            var table = $('#zero_config').DataTable();
            $(".search-ct").css("opacity", "0");
            table.search( selectedStockist ).draw();

        })

        $(document).on('click', '#apply_bulk_update_btn', function(){

            let selectedPaymentMode = $('#payment_mode_dropdown').val();

            let table = $('#zero_config').DataTable();
            let rowNodes = table.rows({filter: 'applied'}).nodes().toArray();
            let rowData = table.rows({filter: 'applied'}).data().toArray();

            for(i = 0; i < rowNodes.length; i++){
                if(!$(rowNodes[i]).find("select.payment_mode").prop("disabled")){
                    let netDiscountRateToStockist;
                    let inputMargin = 10;
                    if(selectedPaymentMode == 'DM'){
                        let ptr = rowData[i][11];
                        let inputDiscRate = rowData[i][12];
                        let discountamt = ptr - ((ptr * inputDiscRate) / 100);
                        let marginamt = discountamt * inputMargin / 100;
                        let nrv = discountamt - marginamt;
                        netDiscountRateToStockist = (ptr - nrv) / ptr * 100;
                        // console.log(ptr);
                    }else if(selectedPaymentMode == 'CN'){
                        let inputDiscRate = rowData[i][12];
                        netDiscountRateToStockist = inputDiscRate;
                    }
                    $(rowNodes[i]).find("select.payment_mode").val(selectedPaymentMode);
                    $(rowNodes[i]).find(".net_discount_percent").html(netDiscountRateToStockist % 1 ? Number(netDiscountRateToStockist).toFixed(2) : netDiscountRateToStockist);
                }
            }

        })


        $(document).on('click', '#save_changes_btn', function(){
            let confirmSaveChanges = confirm("Do you want to save the changes?")

            if(confirmSaveChanges){
                const dbDataArr = <?= $data['details']?>;

                let table = $('#zero_config').DataTable();
                let rowData = table.rows().data().toArray();
                let rowNodes = table.rows().nodes().toArray(); 

                let selectedModeArr = [];  
                for (i = 0; i < rowData.length; i++) {
                    let selectedMode = $(rowNodes[i]).find("select.payment_mode option:selected").val();
                    let netDiscPercent = $(rowNodes[i]).find(".net_discount_percent").html();

                    for(j = 0; j < dbDataArr.length; j++){

                        if(Number(rowData[i][1]) == dbDataArr[j].id){
                            if(selectedMode != dbDataArr[j].payment_mode){
                                let obj = {id: dbDataArr[j].id, sku_id: dbDataArr[j].sku_id, payMode: selectedMode, netDiscPercent: netDiscPercent};
                                selectedModeArr.push(obj);
                            }
                            break;
                        }
                    }
                }
                let jsonData = JSON.stringify(selectedModeArr);
                skuPaymentModeHandler(jsonData);

            }
        })

    })

    function skuPaymentModeHandler(jsonData){
        let vq_id = <?= $data['vq_data']['id']?>;
        var settings = {
          "url": "/initiator/saveSkuPaymentMode",
          "method": "POST",
          "timeout": 0,
          "headers": {
            "Accept-Language": "application/json",
          },
          "data": {
            "_token": "{{ csrf_token() }}",
            "data": jsonData,
            "vq_id": vq_id
          }
        };
        $.ajax(settings).done(function (response) {
            if(response.success == true){
                // let updatedDiscountData = response.data;

                // let table = $('#zero_config').DataTable();
                // let rowNodes = table.rows({filter: 'applied'}).nodes().toArray();
                // for (i = 0; i < rowNodes.length; i++) {
                //     let selectedMode = $(rowNodes[i]).find("td.net_discount_percent").html(updatedDiscountData[i]);
                // }

                alert(response.message);
                $('#send_quotation').show();
            }

        });

    }

    $('.view-comments').click(function(e){
        e.preventDefault();
        var row = $(this).closest("tr");
        var id = parseFloat(row.find(".d-none").text());
        var brandname = (row.find(".brand_name").text());

        var settings = {
          "url": "/initiator/sku_comment",
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
          console.log(response);
          parsedTest = JSON.parse(response.result['comments']);
          if(parsedTest != null){
            $('#showcmts .data-layer').empty();
            parsedTest.forEach((element) => {
	            var level_no = element['level'].replace(/\D/g,'');
	            var level_name = '';
	            switch(level_no){
		            case '1': level_name = 'NSM';break;
		            case '2': level_name = 'SBU';break;
		            case '3': level_name = 'Semi Cluster';break;
		            case '4': level_name = 'Cluster';break;
	            }
	            
                //$('#showcmts .data-layer').append('<h6>'+element['level'].replace(/\D/g,'')+'</h6><h5>'+element['comment']+'</h5>');
                $('#showcmts .data-layer').append('<h6>'+level_name+'</h6><h5>'+element['comment']+'</h5>');

                console.log( element['comment']);
                
                });
            }else{
                $('#showcmts .data-layer').empty();
                $('#showcmts .data-layer').append('<h5>No comments</h5>');
            }
            $('#showcmts .modal_heading').empty();
            $('#showcmts .modal_heading').html('<span>Brand Name: - </span>' + brandname+' Comments');
            $('#showcmts').modal('show');
        });

    });


    $('#Commentbtn').click(function(e){
        e.preventDefault();


        var settings = {
          "url": "/initiator/vq_comment",
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
				            case '1': level_name = 'NSM';break;
				            case '2': level_name = 'SBU';break;
				            case '3': level_name = 'Semi Cluster';break;
				            case '4': level_name = 'Cluster';break;
			            }  
                        //$('#showgeneralcmts .data-layer').append('<h6>Level '+element['level'].replace(/\D/g,'')+' of '+k+'</h6><h5>'+element['comment']+'</h5>');
                        $('#showgeneralcmts .data-layer').append('<h6>'+level_name+' of '+k+'</h6><h5>'+element['comment']+'</h5>');
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

      /****************************************
       *       Basic Table                   *
       ****************************************/

      $("#zero_config").DataTable(

        {  language: {    'paginate': {      'previous': "<img src='{{ asset('admin/images/left.svg') }}' class='left-block' alt=''> <img src='{{ asset('admin/images/left1.svg') }}' class='disbpr' alt=''>",      'next': "<img src='{{ asset('admin/images/right.svg') }}' class='neblk' alt=''> <img src='{{ asset('admin/images/right1.svg') }}' class='disbn' alt=''>"    }  },
        scrollX: true
        }
        );

/*
$("#zero_config").DataTable(
    {  
	    'columnDefs': [
            { "bSortable": false, "aTargets": [ 2, 3 ] }, 
            { "bSearchable": false, "aTargets": [2, 3 ] },
            { "width": "100px", "targets": 2 }
		],
		    
	        'language': {    
                            'paginate': {      
                                'previous': "<img src='{{ asset('admin/images/left.svg') }}' class='left-block' alt=''> <img src='{{ asset('admin/images/left1.svg') }}' class='disbpr' alt=''>",      'next': "<img src='{{ asset('admin/images/right.svg') }}' class='neblk' alt=''> <img src='{{ asset('admin/images/right1.svg') }}' class='disbn' alt=''>"    
                            }  
                        },
            scrollX: true
	}
);
*/
// $(".table-ct .dataTables_filter label").append("<img src='{{ asset('admin/images/search.svg') }}' class='search-ct' alt=''>");
$('.dataTables_filter label').contents().filter((_, el) => el.nodeType === 3).remove();
$(".dataTables_filter label input").keyup(function(){
        $(".search-ct").css("opacity", "0");
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
    .fit_width{
        width:fit-content
    }
    </style>
@endsection
