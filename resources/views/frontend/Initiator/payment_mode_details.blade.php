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
                        $largeDatasetJSON = json_encode($data['details']);
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
                                        <th class="d-none">Stockist Id</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        
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

          <!-- <div class="data-layer comment-text">
            
          </div> -->
        </div>
      </div>
    </div>
</div>
<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script>
    const Disc_margin_item_code = @json($data['DiscountMargin_datas']);
    const largeDataset = <?php echo $largeDatasetJSON; ?>;/*assign to js variable to use in datatable*/
    function singlePayModeChangeHandler(e, ptr, inputDiscRate){
        let Disc_margin_item_code = @json($data['DiscountMargin_datas']);
        let row = e.closest('tr');
        let itemCode = row.find('.item_code').val();
        let selectedPaymentMode = e.val();
        let netDiscountRateToStockist;
        let inputMargin = (Disc_margin_item_code[itemCode])? Disc_margin_item_code[itemCode] : 10;
        console.log(inputMargin);
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
            
        }else if(selectedPaymentMode == 'CN'){
            netDiscountRateToStockist = inputDiscRate;
        }
        $(e.parent()).siblings('.net_discount_percent').html(netDiscountRateToStockist % 1 ? Number(netDiscountRateToStockist).toFixed(2) : Number(netDiscountRateToStockist).toFixed(2));
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
                                                        <option value="{{$stk['id']}}">{{ $stk['stockist_code'] }}-{{ $stk['stockist_name'] }}</option>
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
            var table = $('#zero_config').DataTable();
            table.search('').columns().search('').draw();
        })
        
        $(document).on('change', '#stockist_dropdown', function(){
            var selectedStockist_text = document.getElementById('stockist_dropdown')[document.getElementById('stockist_dropdown').selectedIndex].innerHTML;;
            selectedStockist = document.getElementById('stockist_dropdown').value;
            console.log(selectedStockist);
            
            var table = $('#zero_config').DataTable();
            $(".search-ct").css("opacity", "0");
            //table.search( selectedStockist ).draw();
            var columnIndex = table.columns('.stockist_id').indexes()[0];
            table
            .column(columnIndex)
            .search(selectedStockist)
            .draw();
            $('#zero_config_filter input').val(selectedStockist_text)

        })

        $(document).on('click', '#apply_bulk_update_btn', function(){

            let selectedPaymentMode = $('#payment_mode_dropdown').val();

            let table = $('#zero_config').DataTable();
            let rowNodes = table.rows({filter: 'applied'}).nodes().toArray();
            let rowData = table.rows({filter: 'applied'}).data().toArray();

            for(i = 0; i < rowNodes.length; i++){
                if(!$(rowNodes[i]).find("select.payment_mode").prop("disabled")){
                    let netDiscountRateToStockist;
                    // let inputMargin = 10;
                    let disc_mrg_item_code = $(rowNodes[i]).find(".item_code").val();
                    let inputMargin = (Disc_margin_item_code[disc_mrg_item_code])? Disc_margin_item_code[disc_mrg_item_code] : 10;
                    if(selectedPaymentMode == 'DM'){
                        /*let ptr = rowData[i][11];
                        let inputDiscRate = rowData[i][12];*/
                        let ptr = $(rowNodes[i]).find(".ptr").val()
                        let inputDiscRate = $(rowNodes[i]).find(".discount_percent").val()
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
                        //let inputDiscRate = rowData[i][12];
                        let inputDiscRate = $(rowNodes[i]).find(".discount_percent").val()
                        netDiscountRateToStockist = inputDiscRate;
                    }
                    $(rowNodes[i]).find("select.payment_mode").val(selectedPaymentMode);
                    $(rowNodes[i]).find(".net_discount_percent").html(netDiscountRateToStockist % 1 ? Number(netDiscountRateToStockist).toFixed(2) :  Number(netDiscountRateToStockist).toFixed(2));
                }
            }
            $('#showbulkpopup').modal('show');
            $('#cancel_bulk_update_btn').trigger('click')

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
                    let item_code = $(rowNodes[i]).find(".item_code").val();
                    if (netDiscPercent == "" || netDiscPercent == "NaN") {
                        //console.log("Field does not contain a number.");
                        alert("The Item Code: "+item_code+" Net Discount Rate is Empty")
                        return;
                    } else {
                        //console.log("Field contains a number");
                    }
                    for(j = 0; j < dbDataArr.length; j++){

                        //if(Number(rowData[i][1]) == dbDataArr[j].id){
                        if(Number(rowData[i]['id']) == dbDataArr[j].id){
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

        {  
            data: largeDataset,
            "pageLength": 50,
            columns:
            [
                { data: 'div_name' },//0
                { data: 'id' },//1
                { data: 'mother_brand_name' },//2
                { data: function(row) {//3
                    return row.item_code + `<input type="hidden" class="item_code" value="${row.item_code}">`;
                  }
                },
                { data: 'brand_name' },//4
                { data: 'pack' },//5
                { data: 'applicable_gst' },//6
                { data: 'last_year_percent',//7
                    "render": function(data, type, row, meta) {
                        if (data !== null && data !== undefined && data !== '') {
                            return parseFloat(data).toFixed(2);
                        } else {
                            return 0;
                        }
                    }
                },
                { data: 'last_year_rate',//8
                    "render": function(data, type, row, meta) {
                        if (data !== null && data !== undefined && data !== '') {
                            return parseFloat(data).toFixed(2);
                        } else {
                            return 0;
                        }
                    }
                },
                { data: 'mrp',//9
                    "render": function(data, type, row, meta) {
                        if (data !== null && data !== undefined && data !== '') {
                            return parseFloat(data).toFixed(2);
                        } else {
                            return 0;
                        }
                    }
                },
                { data: 'last_year_mrp',//10
                    "render": function(data, type, row, meta) {
                        if (data !== null && data !== undefined && data !== '') {
                            return parseFloat(data).toFixed(2);
                        } else {
                            return 0;
                        }
                    }
                },
                { data: function(row) {//11
                    if (row.ptr !== null && row.ptr !== undefined && row.ptr !== '') {
                        return parseFloat(row.ptr).toFixed(2)+`<input type="hidden" class="ptr" value="${row.ptr}">`
                    } else {
                        return 0+`<input type="hidden" class="ptr" value="0">`
                    }
                  }
                },
                { 
                  data: function(row) {//12
                    if (row.discount_percent !== null && row.discount_percent !== undefined && row.discount_percent !== '') {
                        return parseFloat(row.discount_percent).toFixed(2)+`<input type="hidden" class="discount_percent" value="${row.discount_percent}">`
                    } else {
                        return 0+`<input type="hidden" class="discount_percent" value="0">`
                    }
                  }
                },
                { 
                  data: function(row) {//13
                    var result = parseFloat(row.discount_rate).toFixed(2)
                    return result;
                  }
                },
                { 
                  data: function(row) {//14
                    var result = parseFloat(row.mrp_margin).toFixed(2)
                    return result;
                  }
                },
                { data: 'type'},//15
                { data: 'hsn_code'},//16
                { data: function(row) {//17
                        var composition = row.composition;
                        return ucwords(composition);;
                    }
                },
                { data: 'stockist_name'},//18
                { data: function(row) {//19
                        var result = `<select name="payment_mode" class="payment_mode" class="form-control form-control-sm"  onchange="singlePayModeChangeHandler($(this), ${row.ptr}, ${row.discount_percent})">
                        <option value="DM" ${row.payment_mode == 'DM' ? 'selected' : ''}>Direct Master</option>
                        <option value="CN" ${row.payment_mode == 'CN' ? 'selected' : ''}>Credit Note</option>
                    </select>`
                        return result
                    }
                },
                { data: 'id' },//20
                { 
                  data: function(row) {//21
                    var result = row.net_discount_percent
                    if(result != null)
                    {
                        return parseFloat(result).toFixed(2);
                    }
                    else
                    {
                        let inputMargin = (Disc_margin_item_code[row.item_code])? Disc_margin_item_code[row.item_code] : 10;
                        //var calc = (row.ptr-((row.ptr * row.discount_percent) / 100))
                        var ptr = row.ptr;
                        var discountPercent = row.discount_percent;
                        if(row.ptr == 0)
                        {
                            var discountedValue = row.ptr - ((row.ptr - (row.ptr * row.discount_percent / 100)) - ((row.ptr - (row.ptr * row.discount_percent / 100)) * inputMargin / 100));
                                var net_discount_percent = (discountedValue / 100).toFixed(2);
                            } else {
                                var discountedValue = row.ptr - ((row.ptr - (row.ptr * row.discount_percent / 100)) - ((row.ptr - (row.ptr * row.discount_percent / 100)) * inputMargin / 100));
                                var net_discount_percent = ((discountedValue / row.ptr) * 100).toFixed(2);
                            }
                        return net_discount_percent;
                    }
                  }
                },
                { data: 'stockist_id' },//22
            ],
            columnDefs: [
                {
                    targets: 0, //div_name
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
                    targets: 1, //item_id
                    className: 'd-none', 
                },
                {
                    targets: 2, //mother_brand_name
                    className: '', 
                    createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'white-space': 'normal',
                            'width': '110px',
                        });
                    }
                },
                {
                    targets: 3, //item_code
                    // className: '', 
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
                    targets: 5, //pack
                    /*createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'white-space': 'normal',
                            'width': '80px',
                        });
                    }*/
                },
                {
                    targets: 6, //applicable_gst
                    createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'right'
                        });
                    }
                },
                {
                    targets: 7, //last_year_percent
                    createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'right'
                        });
                    }
                },
                {
                    targets: 8, //last_year_rate
                    createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 9, //mrp
                    className: '', 
                    createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'right'
                        });
                    }
                },
                {
                    targets: 10, //last_year_mrp
                    createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'right'
                        });
                    }
                },
                {
                    targets: 11,//ptr
                    className: '',  
                    createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'right'
                        });
                    }
                },
                {
                    targets: 12, //discount_percent
                    createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'right'
                        });
                    }
                },
                {
                    targets: 13, //discount_rate
                    createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'right'
                        });
                    }
                },
                {
                    targets: 14, //mrp_margin
                    className: '',  
                    createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'right'
                        });
                    }
                },
                {
                    targets: 15, //type
                    /*className: '',  
                    createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'right'
                        });
                    }*/
                },
                {
                    targets: 16, //hsn_code
                    /*className: 'd-none div_id',  
                    createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'right'
                        });
                    }*/
                },
                {
                    targets: 17, //composition
                    /*className: 'mrp_margin', 
                    createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'right'
                        });
                    }*/
                },
                {
                    targets: 18, //stockist_name
                    /*createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'right'
                        });
                    }*/
                },
                {
                    targets: 19, //payment_mode
                   /* createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'right'
                        });
                    }*/
                },
                {
                    targets: 20,//id
                    className: 'd-none', 
                    /*createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'right'
                        });
                    }*/
                },
                {
                    targets: 21, //net_discount_percent
                    className: 'net_discount_percent', 
                    /*createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'center'
                        });
                    }*/
                },
                {
                    targets: 22,//stockist_id
                    className: 'stockist_id d-none', 
                    /*createdCell: function(td, cellData, rowData, row, col) {
                       $(td).css({
                            'text-align': 'right'
                        });
                    }*/
                },
                // Define other column definitions here
            ],
            'language': {    'paginate': {      'previous': "<img src='{{ asset('admin/images/left.svg') }}' class='left-block' alt=''> <img src='{{ asset('admin/images/left1.svg') }}' class='disbpr' alt=''>",      'next': "<img src='{{ asset('admin/images/right.svg') }}' class='neblk' alt=''> <img src='{{ asset('admin/images/right1.svg') }}' class='disbn' alt=''>"    }  },
            scrollX: true
          });

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
function ucwords(str) {
  return str.toLowerCase().replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
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
    .fit_width{
        width:fit-content
    }
    </style>
@endsection
