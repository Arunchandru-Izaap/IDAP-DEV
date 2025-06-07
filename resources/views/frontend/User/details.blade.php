@extends('layouts.frontend.app')
@section('content')

<div id="page-content-wrapper">
                <!-- Top navigation-->
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                            <ul class="navbar-nav dashboard-nav">
                                <li class="nav-item active"><a class="nav-link" href="{{route('user_listing')}}"> <img src="{{ asset('admin/images/back.svg')}}">{{$data['vq_data']['hospital_name']}} - VQ Request Details</a></li>
                            </ul>

                            <ul class="d-flex ml-auto user-name">
                                <li>
                                    <h3>{{Session::get('emp_name')}}</h3>
                                    <p>User</p>
                                </li>

                                <li>
                                    <img src="{{ asset('admin/images/Sun_Pharma_logo.png') }}">
                                </li>
                            </ul>
                        </div>
                        
                    </div>

                </nav>
                <ul class="bradecram-menu">
                    <li><a href="{{route('user_dashboard')}}">
                        Home
                    </a></li>
                    <li class="">
                      <a href="{{route('user_listing')}}">
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
                        if($data['vq_data']["current_level"] == 7 && $data['vq_data']["vq_status"] == 1){
                        @endphp
                            <div class="col-md-12 d-flex send-quotation">
                               


                            <div class="cancel-btn ml-auto">
                            <a href="{{url('user/activity',$data['vq_data']['id'])}}" class="btn-grey border-0 m-0 p-0" data-title="View Activity">
                                <img src="{{ asset('admin/images/clock.png') }}" class="view-activity">
                                <!-- <p>View Activity</p> -->
                                </a>
                                <a href="{{route('user-cover-letter-pdf',$data['vq_data']['id'])}}" id="cover-letter" class="btn-grey">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Cover Letter</p>
                                </a>
                                <a href="{{route('user-price-sheet',$data['vq_data']['id'])}}" id="price-sheet" class="btn-grey">
                                <img src="{{ asset('admin/images/download.svg') }}">
                                <p>Price Sheet</p>
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
                                        <th>Division</th>
                                        <th class="d-none">#</th>
                                        <th class="mbrname">Mother Brand</th>
                                        <th>Item Code</th>
                                        <th class="mbrname">Brand</th>
                                        <th>Stockist Code</th>
                                        <th>Stockist Name</th>
                                        <th>Stockist margin</th>
                                        <th>Mode of discount</th>
                                        <th>Pack</th>
                                        
                                        <th>App. GST(%)</th>
                                        <th>L. Y. Rate</th>
                                        <th>L. Y. Disc.(%)</th>
                                        <th>MRP</th>
                                        <th>C. Y. PTR</th>
                                        <th>Disc. PTR (%)</th>
                                        <th>RTH (Excl. GST)</th>
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
                                        @foreach($data['details'] as $item)
                                        <tr>
                                            <td>{{$item->div_name}}</td>
                                            <td class="d-none">{{$item->id}}</td>
                                            <td>{{$item->mother_brand_name}}</td>

                                            <td>{{$item->item_code }}</td>
                                            <td class="brand_name">{{$item->brand_name}}</td>
                                            @php
                                            $stockistCode = "";
                                            $stockistName = "";
                                            $modeOfDisc = "";
                                            @endphp
                                            @foreach($item->getSkuStockist as $skuStock)
                                              @php
                                                $stockistCode = $stockistCode.', '.data_get($skuStock, 'getStockistDetails.stockist_code');
                                                $stockistName = $stockistName.', '.data_get($skuStock, 'getStockistDetails.stockist_name');
                                                $modeOfDisc = $modeOfDisc.', '.data_get($skuStock, 'payment_mode', 'NULL');
                                              @endphp
                                            @endforeach
                                            @php
                                            $stockistCode = ltrim($stockistCode, ',');
                                            $stockistName = ltrim($stockistName, ',');
                                            $modeOfDisc = ltrim($modeOfDisc, ',');
                                            @endphp
                                            
                                            <td>{{$stockistCode}}</td>
                                            <td>{{$stockistName}}</td>
                                            <td>{{$data['stockist_margin']}}</td>
                                            <td>{{$modeOfDisc}}</td>
                                            <td>{{$item->pack}}</td>
                                            
                                            <td>{{$item->applicable_gst}}</td>
                                            <td>{{$item->last_year_rate}}</td>
                                            <td>{{$item->last_year_percent}}</td>
                                            <td>{{$item->mrp}}</td>
                                            <td>{{$item->ptr }}
                                             <!-- <input type="text" class="cBalance" value="">-->
                                            </td>
                                           
                                            <td>{{$item->discount_percent}}</td>
                                            <td>{{$item->discount_rate }}</td>
                                            <td style="text-align: right">{{round($item->mrp_margin,2)}}</td>
                                            <td style="text-align: right">{{round($item->discount_rate - (($item->discount_rate*$data['stockist_margin'])/100),2) }}</td>
                                               <td style="text-align: right">{{round($item->discount_rate,2) }}</td>
                                               <td style="text-align: right">{{ $item->last_year_mrp != NULL && $item->last_year_ptr != NULL ? (($item->last_year_mrp - $item->last_year_ptr)/$item->last_year_mrp) * 100 : "-"}}</td>
                                            <td>
                                                <a href="#" class="view-comments" data-title="View Details">
                                                    <img src="{{ asset('admin/images/down.svg') }}" alt="">
                                                </a>
                                               
                                            </td>
                                            <td>{{$item->type}}</td>
                                            <td>{{$item->hsn_code}}</td>
                                            <td>{{$item->sap_itemcode || $item->sap_itemcode != "" ? $item->sap_itemcode : "-"}}</td>
                                            <td>{{$item->composition }}</td>
                                            
   


                                          
                                            

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

    $('.view-comments').click(function(e){
        e.preventDefault();
        var row = $(this).closest("tr");
        var id = parseFloat(row.find(".d-none").text());
        var brandname = (row.find(".brand_name").text());

        var settings = {
          "url": "/user/sku_comment",
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
                $('#showcmts .data-layer').append('<h6>'+element['level'].replace(/\D/g,'')+'</h6><h5>'+element['comment']+'</h5>');
                console.log( element['comment']);
                
                });
            }else{
                $('#showcmts .data-layer').empty();
                $('#showcmts .data-layer').append('<h5>No comments</h5>');
            }
            $('#showcmts .modal_heading').empty();
            $('#showcmts .modal_heading').text(brandname+' Comments');
            $('#showcmts').modal('show');
        });

    });

    $('#Commentbtn').click(function(e){
        e.preventDefault();


        var settings = {
          "url": "/user/vq_comment",
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
                        $('#showgeneralcmts .data-layer').append('<h6>Level '+element['level'].replace(/\D/g,'')+' of '+k+'</h6><h5>'+element['comment']+'</h5>');
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

        {  language: {    'paginate': {      'previous': "<img src='{{ asset('admin/images/left.svg') }}' class='left-block' alt=''> <img src='{{ asset('admin/images/left1.svg') }}' class='disbpr' alt=''>",      'next': "<img src='{{ asset('admin/images/right.svg') }}' class='neblk' alt=''> <img src='{{ asset('admin/images/right1.svg') }}' class='disbn' alt=''>"    }  
        },
        scrollX: true
        });
$(".table-ct .dataTables_filter label").append("<img src='{{ asset('admin/images/search.svg') }}' class='search-ct' alt=''>");
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
        max-width: 1279px;
        width: 100%;
        margin: 0 auto;
    }
    
@endsection

