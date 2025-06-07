@extends('layouts.frontend.app')
@section('content')

<div id="page-content-wrapper">
                <!-- Top navigation-->
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                            <ul class="navbar-nav dashboard-nav">
                                <li class="nav-item active"><a class="nav-link" href="{{route('ho_listing')}}"> <img src="{{ asset('admin/images/back.svg')}}">{{$data['vq_data']['hospital_name']}} - VQ Request Details</a></li>
                            </ul>

                            <ul class="d-flex ml-auto user-name">
                                <li>
                                    <h3>{{Session::get('emp_name')}}</h3>
                                    <p>Ho</p>
                                </li>

                                <li>
                                    <img src="{{ asset('admin/images/Sun_Pharma_logo.png') }}">
                                </li>
                            </ul>
                        </div>
                        
                    </div>

                </nav>
                <ul class="bradecram-menu">
                    <li><a href="{{route('ho_dashboard')}}">
                        Home
                    </a></li>
                    <li class="">
                      <a href="{{route('ho_listing')}}">
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
                                <a href="{{url('ho/modify_stockist',$data['vq_data']['institution_id'])}}" id="approve" class="orange-btn-bor">
                                MODIFY STOCKIST
                                </a>


                            <div class="cancel-btn ml-auto">
                            <a href="{{url('ho/activity',$data['vq_data']['id'])}}" class="btn-grey border-0 m-0 p-0" data-title="View Activity">
                                <img src="{{ asset('admin/images/clock.png') }}" class="view-activity">
                                <!-- <p>View Activity</p> -->
                                </a>
                                <a id="Commentbtn" href="#" class="btn-grey">
                                  <img src="{{ asset('admin/images/blueeye.svg') }}">
                                  <p>Comments</p>
                                </a>
                                <a href="{{route('cover-letter-pdf-ho',$data['vq_data']['id'])}}" id="cover-letter" class="btn-grey">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Cover Letter</p>
                                </a>
                                <a href="{{route('price-sheet-ho',$data['vq_data']['id'])}}" id="price-sheet" class="btn-grey">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Price Sheet</p>
                                </a>
                                <a href="{{url('ho/view_ho',$data['vq_data']['id'])}}" class="orange-btn">
                                Send Quotation
                                </a>
                            </div>
                        </div>
                        @php 
                        }elseif($data['vq_data']["current_level"] == 7 ){
                          @endphp
                          <div class="col-md-12 d-flex send-quotation">
                                


                            <div class="cancel-btn ml-auto">
                            <a href="{{url('ho/activity',$data['vq_data']['id'])}}" class="btn-grey border-0 m-0 p-0" data-title="View Activity">
                                <img src="{{ asset('admin/images/clock.png') }}" class="view-activity">
                                <!-- <p>View Activity</p> -->
                                </a>
                                <a id="Commentbtn" href="#" class="btn-grey">
                                  <img src="{{ asset('admin/images/blueeye.svg') }}">
                                  <p>Comments</p>
                                </a>
                                <a href="{{route('cover-letter-pdf',$data['vq_data']['id'])}}" id="cover-letter" class="btn-grey">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Cover Letter</p>
                                </a>
                                <a href="{{route('price-sheet',$data['vq_data']['id'])}}" id="price-sheet" class="btn-grey">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Price Sheet</p>
                                </a>
                            </div>
                        </div>

                          @php
                        }else{
                      
                          @endphp
                          <div class="col-md-12 d-flex send-quotation">
                                


                            <div class="cancel-btn ml-auto">
                            <a href="{{url('initiator/activity',$data['vq_data']['id'])}}" class="btn-grey border-0 m-0 p-0" data-title="View Activity">
                                <img src="{{ asset('admin/images/clock.png') }}" class="view-activity">
                                <!-- <p>View Activity</p> -->
                                </a>
                                <a id="Commentbtn" href="#" class="btn-grey">
                                  <img src="{{ asset('admin/images/blueeye.svg') }}">
                                  <p>Comments</p>
                                </a>
                               
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
                                        <th>Stockist Code</th>
                                        <th>Stockist Name</th>
                                        <th>Stockist margin</th>
                                        <th>Mode of discount</th>
                                        <th class="digitcenter">Pack</th>
                                        
                                        <th class="digitcenter">App. GST(%)</th>
                                        
                                        <th class="digitcenter">L. Y. Disc.(%)</th>
					<th class="digitcenter">L. Y. Disc Rate</th>
                                        <th class="digitcenter">MRP</th>
					<th class="digitcenter">L. Y. MRP</th>
                                        <th class="digitcenter">C. Y. PTR</th>
                                        <th class="digitcenter">Disc. PTR (%)</th>
                                        <th class="digitcenter">RTH (Excl. GST)</th>
                                        <th class="digitcenter">MRP Margin (%)</th>
                                        <th class="digitcenter">Billing price(Debit Master)</th>
                                        <th class="digitcenter">Billing price(Credit Note)</th>
                                        <th class="digitcenter">Last year % Margin on MRP</th>
                                        <th>Comments</th>
                                        <th>Type</th>
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

    $('body').on('click','.view-comments',function(e){
        e.preventDefault();
        var row = $(this).closest("tr");
        var id = parseFloat(row.find(".d-none").text());
        var brandname = (row.find(".brand_name").text());

        var settings = {
          "url": "/ho/sku_comment",
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
		            case '1': level_name = 'RSM';break;
                case '2': level_name = 'ZSM';break;
                case '3': level_name = 'NSM';break;
		            case '4': level_name = 'SBU';break;
		            case '5': level_name = 'Semi Cluster';break;
		            case '6': level_name = 'Cluster';break;
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
          "url": "/ho/vq_comment",
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

      $("#zero_config").DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: '/ho/vq_detail_listing_ho_ajax',
            type: 'GET',
            data: function (d) {
            // Add custom data to the request
                d.vq_id = "{{$data['vq_data']['id']}}";
                return d;
            },
            "dataSrc": function(response) {
                // Extract stockist_margin value from the response and store it in a global variable
                window.stockistMargin = response.stockist_margin;
                // Return the data array for DataTable to process
                return response.data;
            }
        },
        //order:[],
        columns:
        [
            { data: 'div_name' },
            { data: 'id' },
            { data: 'mother_brand_name' },
            { data: 'item_code' },
            { data: 'brand_name' },
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
                data: 'discount_margin',
                render: function(data, type, row, meta) {
                if (data !== null && data !== undefined && data !== '') {
                    return parseFloat(data).toFixed(2);
                } else {
                    return 10;
                }
                }
            },
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
                        return parseFloat(data).toFixed(2);
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
                        return parseFloat(data).toFixed(2);
                    } else {
                        return 0;
                    }
                }
            },
            { data: 'discount_percent',
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
                        return parseFloat(data).toFixed(2);
                    } else {
                        return 0;
                    }
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
                        return parseFloat(calculated_data).toFixed(2);
                    } else {
                        return 0;
                    }
                }
            },
            { data: 'discount_rate',
                "render": function(data, type, row, meta) {
                    if (data !== null && data !== undefined && data !== '') {
                        return parseFloat(data).toFixed(2);
                    } else {
                        return 0;
                    }
                }
            },
            { 
                data: function(row) {
                    var lastYearMrp = row.last_year_mrp;
                    var lastYearPtr = row.last_year_ptr;
                    var result = lastYearMrp != null && lastYearPtr != null ? ((lastYearMrp - lastYearPtr) / lastYearMrp) * 100 : "-";
                    return result;
                }
            },
            { 
                data: function(row) {
                    var element = `<a href="#" class="view-comments add-comments" data-title="Comments">
                        <img src="{{ asset('admin/images/comment-medical.svg') }}" alt="">
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
                targets: 0, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'white-space': 'normal',
                        'width': '110px',
                        // Add other styles here
                    });
                }
            },
            {
                targets: 1, 
                className: 'd-none', 
            },
            {
                targets: 2, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'white-space': 'normal',
                        'width': '110px',
                    });
                }
            },
            {
                targets: 4, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'white-space': 'normal',
                        'width': '160px',
                    });
                }
            },
            {
                targets: 10, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 11, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 12, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'center'
                    });
                }
            },
            {
                targets: 13, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 14, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'center'
                    });
                }
            },
            {
                targets: 15, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 16, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 17, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 18, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 19, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 20, 
                createdCell: function(td, cellData, rowData, row, col) {
                   $(td).css({
                        'text-align': 'right'
                    });
                }
            },
            {
                targets: 21, 
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
                        'text-align': 'center'
                    });
                }
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

