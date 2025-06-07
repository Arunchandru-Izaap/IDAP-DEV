@extends('layouts.frontend.app')
@section('content')

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
                                    <h3>Jitu Patil</h3>
                                    <p>Front end developer</p>
                                </li>

                                <li>
                                    <img src="{{ asset('admin/images/Sun_Pharma_logo.png') }}">
                                </li>
                            </ul>
                        </div>
                        
                    </div>

                </nav>
                <ul class="bradecram-menu">
                    <li><a href="">
                        Home
                    </a></li>
                    <li class="">
                        <a href="">
                            View Request
                        </a>
                    </li>
                    <li class="">
                      <a href="">
                        VQ Request Listing
                      </a>
                    </li>
                    <li class="active">
                      <a href="">
                        Lilavati Hospital
                      </a>
                    </li>
                </ul>
                <!-- Page content-->
                <div class="container-fluid">
                    <!-- <h1 class="mt-4">Simple Sidebar</h1> -->
                    <div class="row">

<div class="cancel-btn ml-auto">

    <a href="" id="selected_reinit" class="orange-btn">
    Send Quotation
    </a>
</div>
                        <div class="col">
                            <div class="actions-dashboard table-ct">

                              <table class="table VQ Request Listing vq-request-listing-tb" id="zero_config">
                                    <thead>
                                    <tr>
	                                    <th></th>
                                        <th>Division Name</th>
                                        <th>Mother Brand Name</th>
                                        <th>Item Code</th>
                                        <th>Brand Name</th>
                                        <th>Pack</th>
                                        <th>Type</th>
                                        <th>GST</th>
                                        <th>L.Y.Rate</th>
                                        <th>L.Y.Disc.</th>
                                        <th>MRP</th>
                                        <th>C.Y.PTR</th>
                                        <th>Disc.PTR (%)</th>
                                        <th>RTH</th>
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
                                            <td>{{$item->last_year_rate}}</td>
                                            <td>{{$item->last_year_percent}}</td>
                                            <td>{{$item->mrp}}</td>
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
	

// setTimeout(function(){
var table = $('#zero_config').DataTable({     
      'columnDefs': [
         {
            'targets': 0,
            'data': 2,
            'checkboxes': {
               'selectRow': true
            }
         }
      ],
      'select': {
         'style': 'multi'
      },
      'order': [[1, 'asc']],
      'language': { 
              'paginate': {      
                'previous': "<img src='{{ asset('admin/images/left.svg') }}' class='left-block' alt=''> <img src='{{ asset('admin/images/left1.svg') }}' class='disbpr' alt=''>",      'next': "<img src='{{ asset('admin/images/right.svg') }}' class='neblk' alt=''> <img src='{{ asset('admin/images/right1.svg') }}' class='disbn' alt=''>"    
                }  
              }
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
      var startdate = url.searchParams.get("startdate");
      var enddate = url.searchParams.get("enddate");

      var settings = {
          "url": "/initiator/reinitiate",
          "method": "POST",
          "timeout": 0,
          "headers": {
            "Accept-Language": "application/json",
          },
          "data": {
            "_token": "{{ csrf_token() }}",
            "institution_code": "{{ app('request')->route('id') }}",
            "item_codes": vqDataR,
            "from": startdate,
            "to": enddate,
          }
        };
        $.ajax(settings).done(function (response) {
          console.log(response);
          $('#startval').append(startdate);
          $('#enddateval').append(enddate);
          $('#myModal').modal('show');
        });
    });

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
        </style>
@endsection

