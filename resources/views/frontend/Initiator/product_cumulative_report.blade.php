@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
    .no_activity_list {
        margin-top: 1rem;
    }

    .filter_section {
        margin-top: 1rem;
    }

    #loader1 {
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

    .activity-tracker {
        /*max-height: 400px;
      overflow-y: auto;*/
        overflow-x: hidden;
        word-wrap: break-word;
        white-space: normal;
    }

    ul#myList {
        list-style-type: none;
        padding-left: 0;
    }

    .select2-container .select2-selection--multiple {
        max-height: 60px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    tfoot input {
        width: 100%;
        padding: 3px;
        box-sizing: border-box;
    }

    tfoot tr input:nth-child(3) {
        width: 500px !important;
    }

    .search-ct {
        display: none !important;
    }

    .dataTables_scrollBody {
        height: 20rem !important;
    }

    .table-responsive {
        overflow-x: auto;
        /* white-space: nowrap; */
    }

    .dataTables_wrapper .dataTables_scroll {
        width: 100%;
        /* Ensures the table uses the full width */
    }

    td {
        white-space: nowrap;
        /* Prevent wrapping inside table cells */
    }

    #grid_wrapper .col-sm-12.col-md-5 {
        width: 37% !important;
        max-width: 37%;
    }

    body {
        overflow-x: hidden;
        /* Prevent the entire page from scrolling */
    }

    #grid_wrapper .table-responsive {
        max-width: calc(100vw - 127px);
        overflow-x: hidden !important;
        /* white-space: nowrap; */
    }

    th.sorting:last-child {
        width: 190px !important;
    }

    td:last-child {
        width: 190px !important;
    }

    .table-ct .dataTables_wrapper .table tbody tr td:last-child a {
        margin-right: 2px;
    }

    .table-ct .table tbody tr td:last-child a {
        margin-right: 2px;
    }

    .table-ct .dataTables_wrapper .table tbody tr td {
        border: none;
        vertical-align: middle;
        padding: 10px 0 5px 10px !important;
        font-size: 13px;
    }

</style>
<!-- Page content wrapper-->
<div id="page-content-wrapper">
    <!-- Top navigation-->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                <ul class="navbar-nav dashboard-nav">
                    <li class="nav-item active">
                        <a class="nav-link" href="{{ route('initiator_listing') }}">
                            <img src="{{ asset('admin/images/back.svg') }}"> Product wise
                            cumulative report
                        </a>
                    </li>
                </ul>
                <ul class="d-flex ml-auto user-name">
                    <li>
                        <h3>{{ Session::get('emp_name') }}</h3>
                        <p>{{ Session::get('type') }}</p>
                    </li>
                    <li>
                        <img src="{{ asset('admin/images/Sun_Pharma_logo.png') }}">
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <ul class="bradecram-menu">
        @if ($emp_category == 'initiator')
            <li>
                <a href="{{ url($emp_category . '/dashboard') }}"> Home </a>
            </li>
            <li>
                <a href="{{ url($emp_category . '/listing') }}"> VQ Request Listing </a>
            </li>
        @else
            <li>
                <a href="{{ url($emp_category) }}"> Home </a>
            </li>
        @endif
        <li class="active">
          <a href=""> Product wise cumulative report</a>
        </li>
    </ul>
  <!-- Page content-->
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-2 mr-20">
        <label>Division : </label>
        <select class="js-example-basic-multiple"  id="divisionName_filter" name="" multiple="multiple">
          <!-- <option value='all'> All </option> -->
          @foreach($division_Names as $divisionName)
            <option value="{{ $divisionName->div_id }}">{{ $divisionName->div_name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4 mr-20">
        <label>Brand name: </label>
        <select class="js-example-basic-multiple" id="brandName_filter" name="" multiple="multiple">
            <option value='all'> All </option>
        </select>
      </div>
      <div class="col-md-6 mr-20" style="margin-top: 31px; padding-left: 91px;">
        <ul class="d-flex list-unstyled">
          <li>
            <button class="view-data orange-btn-bor" style="background-color: #f3943d; color: #fff;"> View </button>
          </li>
          <li>
            <button class="export-excel orange-btn-bor" style="background-color: #f3943d; color: #fff;"> Export to Excel </button>
          </li>
        </ul>
      </div>
    </div>
    <!-- <div class="row"> -->
      <div id='loader1' style='display: none;'></div>
      <div class="col" id="grid_wrapper">
        <div class="table-responsive actions-dashboard table-ct">
          <table class="table VQ Request Listing vq-request-listing-tb nowrap" id="zero_config">
            <thead>
              <tr>
                <th>Stockist Details</th>
                <th>Institution Name</th>
                <th>Institution Code</th>
                <th>City</th>
                <th>State</th>
                <th>Revision No</th>
                <th>Division</th>
                <th>Mother Brand</th>
                <th>Brand</th>
                <th>Item Code</th>
                <th>SAP Code</th>
                <th>Disc. PTR (%)</th>
                <th>RTH (Excl. GST)</th>
                <th>App. GST(%)</th>
                <th>Pack</th>
                <th>CFA Code</th>
                <th>MRP</th>
                <th>C. Y. PTR</th>
                <th>MRP Margin (%)</th>
                <th>Product Type</th>
                <th>HSN Code</th>
                <th>Composition</th>
                <th>Contract Start Date</th>
                <th>Contract End Date</th>
                <th>VQ Year</th>
              </tr>
            </thead>
            <tbody>

            </tbody>
            <tfoot>
              <tr>
                <th></th>
                <th>Institution Name</th>
                <th>Code</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    <!-- </div> -->
    <!-- col close -->
  </div>
</div>
<div class="modal show" id="successModal">
  <div class="modal-dialog modal-dialog-centered model-pop-ct" style="max-width: 1000px;">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header border-0">
          <div class="tich-logo">
          <img style="width: 50px;" src="{{ asset('admin/images/ticket.svg') }}">
          </div>
          <button type="button" class="close cancel_btn" data-dismiss="modal">
              <img src="{{ asset('admin/images/close.svg') }}" alt="">
          </button>
      </div>
      <!-- Modal body -->
      <div class="modal-body">
        <div class="table-responsive actions-dashboard table-ct">
          <table class="table VQ Request Listing vq-request-listing-tb nowrap" id="zero_config1">
            <thead>
              <tr>
                <th>Institution Name</th>
                <th>Institution Code</th>
                <th>Revision No</th>
                <th>Brand</th>
                <th>Item Code</th>
                <th>Stockist Code</th>
                <th>Stockist Name</th>
                <th>Mode of Discount</th>
                <th>Net Discount Percent</th>
              </tr>
            </thead>
            <tbody>

            </tbody>
          </table>
        </div>
      </div>
      </div>
    </div>
  </div>
</div>
<style type="text/css">
    p.copy-center.copyright {
        position: inherit;
        padding: 10px 0;
    }

    /* #page-content-wrapper nav {
        background: #fff;
    } */

</style>
<script src="{{ asset('admin/extra-libs/DataTables/datatables.min.js') }}"></script>
<script src="{{ asset('frontend/js/select2.js') }}"></script>
<script type="text/javascript">
    $('.js-example-basic-multiple').select2({ width: '100%' });

    // hide by arunchandru 07042025
    // // if select all disable other option, if select other option all disabled.
    // $('#divisionName_filter').on('select2:select', function(e) {
    //   var selected = $(this).val();
    // //   alert(selected);
    //   if (selected.includes('all')) {
    //     $(this).val('all').trigger('change'); // Select only 'All' option
    //   }
    // });
    // $('#divisionName_filter').on('select2:unselect', function(e) {
    //   var selected = $(this).val();
    //   if (!selected) {
    //     $(this).val(null).trigger('change'); // Clear selection if all options are unselected
    //   }
    // });

    // if select all disable other option, if select other option all disabled.
    $('#brandName_filter').on('select2:select', function(e) {
      var selected = $(this).val();
    //   alert(selected);
      if (selected.includes('all')) {
        $(this).val('all').trigger('change'); // Select only 'All' option
      }
    });
    $('#brandName_filter').on('select2:unselect', function(e) {
      var selected = $(this).val();
      if (!selected) {
        $(this).val(null).trigger('change'); // Clear selection if all options are unselected
      }
    });
   
    function init_table() {
        $('#loader1').show(); 
        table = $("#zero_config").DataTable({
            "pageLength": 50,
            "aaSorting": [
                [1, "asc"]
            ],
            processing: true,
            responsive: true,
            serverSide: true,
            ajax: {
                url: '/{{$emp_category}}/cumulative_report_new',
                type: 'POST',
                data: function (d) {
                    // Add custom data to the request
                    d._token = "{{ csrf_token() }}";
                    d.divisionName = $('#divisionName_filter').val();
                    d.brandName = $('#brandName_filter').val();
                    d.CumulativeReport = "ProductWise";
                    return d;
                },
                "dataSrc": function (response) {
                    $('#loader1').hide();
                    // Return the data array for DataTable to process
                    return response.data;
                }
            },
            columns: [
                {
                    data: function(row) {
                      var result = `<a href="javascript:void(0)" name="getStockist" class="getStockist"   onclick="getStockistDetails($(this), ${row.vq_id}, ${row.vqsl_id}, '${row.institution_id}', '${row.rev_no}', '${row.item_code}')">
                        View
                        </a>`
                      return result
                    }
                },//0
                {
                    data: 'hospital_name'
                }, //1
                {
                    data: 'institution_id'
                }, //2
                {
                    data: 'city'
                }, //3
                {
                    data: 'state_name'
                }, //4
                {
                    data: 'rev_no'
                }, //5
                {
                    data: 'div_name'
                }, //6
                {
                    data: 'mother_brand_name'
                }, //7
                {
                    data: 'brand_name'
                }, //8
                {
                    data: 'item_code'
                }, //9
                {
                    data: 'sap_itemcode'
                }, //10
                {
                    data: 'discount_percent',
                    "render": function (data, type, row, meta) {
                        if (data !== null && data !== undefined && data !== '') {
                            return parseFloat(data).toFixed(2);
                        } else {
                            return 0;
                        }
                    }
                }, //11
                {
                    data: 'discount_rate',
                    "render": function (data, type, row, meta) {
                        if (data !== null && data !== undefined && data !== '') {
                            return parseFloat(data).toFixed(2);
                        } else {
                            return 0;
                        }
                    }
                }, //12
                {
                    data: 'applicable_gst'
                }, //13
                {
                    data: 'pack'
                }, //14
                {
                    data: 'cfa_code'
                }, //15
                {
                    data: 'mrp',
                    "render": function (data, type, row, meta) {
                        if (data !== null && data !== undefined && data !== '') {
                            return parseFloat(data).toFixed(2);
                        } else {
                            return 0;
                        }
                    }
                }, //16
                {
                    data: 'ptr',
                    "render": function (data, type, row, meta) {
                        if (data !== null && data !== undefined && data !== '') {
                            return parseFloat(data).toFixed(2);
                        } else {
                            return 0;
                        }
                    }
                }, //17
                {
                    data: 'mrp_margin',
                    "render": function (data, type, row, meta) {
                        if (data !== null && data !== undefined && data !== '') {
                            return parseFloat(data).toFixed(2);
                        } else {
                            return 0;
                        }
                    }
                }, //18
                {
                    data: 'product_type'
                }, //19
                {
                    data: 'hsn_code'
                }, //20
                {
                    data: function (row) {
                        var composition = row.composition;
                        return composition.toUpperCase().toLowerCase();
                    }
                }, //21
                {
                    data: 'contract_start_date'
                }, //22
                {
                    data: 'contract_end_date'
                }, //23
                {
                    data: 'year'
                }, //24
            ],
            columnDefs: [
                {
                    targets: 0,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            // 'text-align': 'center'
                            // Add other styles here
                        });
                    }
                },
                {
                    targets: 1,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            // 'text-align': 'center'
                            // Add other styles here
                        });
                    }
                },
                {
                    targets: 2,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            // 'text-align': 'center'
                            // Add other styles here
                        });
                    }
                },
                {
                    targets: 3,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                        //    'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 4,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                        //    'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 5,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            // 'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 6,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            // 'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 7,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            // 'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 8,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            // 'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 9,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            // 'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 10,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 11,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 12,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 13,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 14,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 15,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 16,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 17,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 18,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 19,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 20,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 21,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            // 'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 22,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 23,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 24,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            'text-align': 'center',
                            // 'padding-top': '4px'
                        });
                    }
                },
                // Define other column definitions here
            ],
            scrollX: true,
            autoWidth: false,    
            'language': {    
                'paginate': {      
                    'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      
                    'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    
                }  
            },
            initComplete: function () {
                this.api()
                    .columns()
                    .every(function (index) {
                        let column = this;
                        console.log(column.footer());
                        // let title = column.footer() ? column.footer().textContent : '';

                        let footer = column.footer();

                        if (!footer) return;

                        let title = footer.textContent.trim();
                        // let title = column.footer().textContent;
                        if (title != "") {
                            // Create input element
                            let input = document.createElement('input');
                            input.placeholder = title;
                            input.type = 'search';
                            //input.style.width = '100%'; // Set input width to 100%
                            // Add specific class to the third input
                            if (index === 2 || index === 3) {
                                input.style.width = '100px';
                            }
                            column.footer().replaceChildren(input);
                            // Event listener for user input
                            input.addEventListener('input', () => {
                                if (column.search() !== this.value) {
                                    column.search(input.value).draw();
                                }
                            });
                            $('.dataTables_filter input').addClass('common_filter');
                        }
                    });
            }
        });
    }
    // view details based on selected data.
    $('.view-data').on('click', function () {
        if ($.fn.DataTable) {
            let table = $('#zero_config').DataTable();
            // If it is, destroy it first
            if ($.fn.DataTable.isDataTable('#zero_config')) {
                table.clear().destroy();
            }
        }
        $('#zero_config tfoot').remove();
        // Ensure <tfoot> exists before reinitializing
        if ($("#zero_config tfoot").length === 0) {
            // alert($("#zero_config tfoot").length);
            $("#zero_config").append(`
                <tfoot>
                    <tr>
                        <th></th>
                        <th>Institution Name</th>
                        <th>Code</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
            `);
        }
        if ($('#divisionName_filter').val() == '') {
            alert('Please select Division Name');
            return;
        }
        if ($('#brandName_filter').val() == '') {
            alert('Please select Brand Name');
            return;
        }

        init_table()
        $('#zero_config_length select').addClass('form-control form-control-sm')
    });

    // Export to excel sheet based on selected data. 
    $('.export-excel').on('click', function (e) {
        e.preventDefault()
        if ($('#divisionName_filter').val() == '') {
            alert('Please select Division Name');
            return;
        }
        if ($('#brandName_filter').val() == '') {
            alert('Please select Brand Name');
            return;
        }
        $('#loader1').show(); 
        var divisionName = $('#divisionName_filter').val();
        var brandName = $('#brandName_filter').val();
        var CumulativeReport = "ProductWise";
        var settings = {
            "url": "/{{$emp_category}}/set_session_filter_report",
            "method": "POST",
            "timeout": 0,
            "headers": {
            "Accept-Language": "application/json",
            },
            "data": {
            "_token": "{{ csrf_token() }}",
            "brandName": JSON.stringify(brandName),
            "divisionName": JSON.stringify(divisionName),
            }
        };
        $.ajax(settings).done(function (response) {
            if(response.status)
            {
                var url = "{{ route($emp_category.'-export-cumulative-report') }}";
                url += '?reporttype=' + encodeURIComponent(CumulativeReport);
                /*url += '&divisionName=' + encodeURIComponent(JSON.stringify(divisionName));
                url += '&brandName=' + encodeURIComponent(JSON.stringify(brandName));*/
                // Redirect to the constructed URL
                window.location.href = url;
                $('#loader1').hide();
            }
            else{
                $('#loader1').hide();
                alert('Please try again')
            } 
        });
    });

    // Filter division by brand name
    $('#divisionName_filter').on('change', function(e){
        e.preventDefault()
        var div_name = $('#divisionName_filter').val();
        if(div_name != ""){
            $("#brandName_filter").val(null).trigger('change');
            $("#brandName_filter .new_option").remove();
            // Add ajax call for filter division by brand here
            var settings = {
                "url": "/{{$emp_category}}/filter_division_by_brand",
                "method": "GET",
                "timeout": 0,
                "headers": {
                "Accept-Language": "application/json",
                },
                "data": {
                "_token": "{{ csrf_token() }}",
                "div_id": div_name,
                }
            };
            $.ajax(settings).done(function (response) {
                let $dropdown = "";
                $.each(response, function (index, item) {
                    // $dropdown.append(``);
                    $dropdown +=`<option class='new_option' value="${item.brand_name}">${item.brand_name}</option>`;
                });
                $("#brandName_filter").append($dropdown);
            });
        }else{
            $("#brandName_filter").val(null).trigger('change');
            $("#brandName_filter .new_option").remove();
            alert('Please select Division Name');
        }
    });
    function getStockistDetails(e, vq_id, vqsl_id, institution_id,rev_no,item_code){
        var brand_name = e.parent().parent().find('td:nth-child(9)').text();
        var hospital_name = e.parent().parent().find('td:nth-child(2)').text();
        if ($.fn.dataTable.isDataTable('#zero_config1')) {
            $('#zero_config1').DataTable().destroy();
            $('#zero_config1 tbody').empty();
        }
        $('#loader1').show();
        var params = {
            vq_id: vq_id,
            vqsl_id: vqsl_id
        }
        $.ajax({
            url: '/{{$emp_category}}/cumulative_report_get_stockist', 
            method: 'GET', 
            data: params, 
            success: function(response) {
                // console.log(response.data);
                $('#loader1').hide();
                $.each(response.data, function(index, item) {
                    var paymentMode = item.payment_mode ? item.payment_mode : '';
                    var netDiscPercent = (item.net_discount_percent != null) ? item.net_discount_percent : '';
                    $('#zero_config1 tbody').append(
                        '<tr>' +
                            '<td>' + hospital_name + '</td>' + 
                            '<td style="text-align: center;">' + institution_id + '</td>' +
                            '<td style="text-align: center;">' + rev_no + '</td>' +
                            '<td>' + brand_name + '</td>' +
                            '<td style="text-align: center;">' + item.item_code + '</td>' +                                
                            '<td style="text-align: center;">' + item.stockist_code + '</td>' +
                            '<td>' + item.stockist_name + '</td>' +
                            '<td style="text-align: center;">' + paymentMode + '</td>' +
                            '<td style="text-align: center;display:block;">' + netDiscPercent + '</td>' +
                        '</tr>'
                    );
                });
                $('#zero_config1').DataTable({
                    searching: false,  
                    lengthChange: false,
                    "paging": false,
                });
                $('#successModal').modal({backdrop: 'static', keyboard: false},'show');
            },
            error: function(error) {
                console.error("There was an error:", error);
                $('#successModal').modal({backdrop: 'static', keyboard: false},'show');
            }
        })
    }
</script>
@endsection
