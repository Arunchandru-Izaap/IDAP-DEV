@extends('layouts.admin.app')
@section('content')
  <!-- <link rel='stylesheet' href="https://cdn.datatables.net/r/dt/dt-1.10.9/datatables.min.css"> -->
<link rel='stylesheet' href="https://cdn.datatables.net/select/1.3.4/css/select.dataTables.min.css">
<style type="text/css">
  table.dataTable thead .sorting 
{
        background:none;
}
  .no-data-message {/*empty table custom  message*/
      font-weight: bold; 
      text-align: center; 
  }
  .yellow-bg
  {
    background: #f9f938 !important;
  }
  .disabled { pointer-events: none; opacity: 0.6; }
  .select2-container--classic .select2-selection--multiple .select2-selection__choice, .select2-container--default .select2-selection--multiple .select2-selection__choice, .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    /*background-color: #2255a4;
    border-color: #2255a4;*/
    color: #000;
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
    #pendingMessage
    {
      height: 150px;
      overflow: auto;
    }
    .orange-btn:disabled {
        background: #faf9f7;
        color: #b4b3b1;
    }
    .orange-btn:hover {
        /*color: #000;*/
        text-decoration: none;
    }
    .orange-btn {
      border-radius: 24px;
      box-shadow: 0 4px 12px 0 rgba(208, 210, 214, 0.99);
      background: #f7941e;
      font-size: 14px;
      font-weight: 800;
      font-stretch: normal;
      font-style: normal;
      line-height: 1.43;
      letter-spacing: 0.2px;
      text-align: center;
      color: #fff;
      border: none;
      padding: 7px 20px;
      text-transform: uppercase;
      cursor: pointer;
  }
  table.dataTable tbody>tr.selected, table.dataTable tbody>tr>.selected {
    background: none !important;
  }
  .dataTables_paginate {
    margin-top: 10px;
    text-align: right;
  }

  /* Style for all pagination buttons */
  .dataTables_paginate .paginate_button {
      background-color: #fff;
      color: #7460ee;
      border: 1px solid #dee2e6;
      padding: 6px 12px;
      /*margin: 2px;*/
      text-decoration: none;
      cursor: pointer;
      transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
  }

  /* Hover effect for pagination buttons */
  .dataTables_paginate .paginate_button:hover {
      color: #5d4dbe;
    background-color: #e9ecef;
    border-color: #dee2e6;
  }

  /* Active page button style (blue background) */
  .dataTables_paginate .paginate_button.current {
      background-color: #007bff !important;
      color: white !important;
      border-color: #007bff !important;
  }

  /* Disabled previous/next button style */
  .dataTables_paginate .paginate_button.disabled {
      color: #6c757d;
    pointer-events: none;
    background-color: #fff;
    border-color: #dee2e6;
  }
  .btn-danger:disabled {
     color: #fff;
  }
  .tich-logo
  {
    margin: 0 auto;
    position: absolute;
    left: 0;
    right: 0;
    text-align: center;
    top: -50px;
    width: 92px;
    height: 92px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    box-shadow: 0 4px 25px 0 rgba(0, 0, 0, 0.05);
    border: solid 1px #e8e8e8;
    background-color: #fff;
  }
  .btn-warning:focus, .btn-danger:focus {
    color: #fff;
  }
  ul {
    list-style: none;
    margin: 0;
    padding: 0;
  }
</style>
<!-- Page content-->
<div class="container-fluid">
    <!-- <h1 class="mt-4">Simple Sidebar</h1> -->
    <div class="row">
      <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-3 mr-20">
                        <label>Division: </label>
                        <select class="js-example-basic-multiple"  id="divisionName_filter" name="" multiple="multiple">
                            <option value=''> Select </option>
                            @foreach($division_Names as $divisionName)
                                <option value="{{ $divisionName->div_id }}">{{ $divisionName->div_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mr-20">
                        <label>Brand name: </label>
                        <select class="js-example-basic-multiple" id="brandName_filter" name="" multiple="multiple">
                        <!-- <option value='all'> All </option> -->
                        <!-- @foreach($brand_Names as $brandName)
                            <option value="{{ $brandName->brand_name }}">{{ $brandName->brand_name }}</option>
                        @endforeach -->
                        </select>
                    </div>
                    <div class="col-md-3 d-flex" style="align-items: center;margin-top: 31px;">
                        <ul class="d-flex list-unstyled">
                            <li>
                                <button class="view-data orange-btn-bor btn btn-primary"> View </button>
                            </li>
                            <li style="padding: 0px 2px 1px 8px;">
                                <button class="orange-btn-bor btn btn-primary export-excel"> <img src="../admin/images/download.svg" style=""> Export to Excel </button>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6 d-flex">
                        <div class="cancel-btn ml-auto">
                            
                            
                        </div>
                    </div>
                </div>
                <div class="row">
                <div id='loader1' style='display: none;'></div>
                    <div class="col pd-20">
                        <div class="actions-dashboard table-ct">
                            <table class="table table-striped table-bordered" id="zero_config_admin">
                                <thead>
                                    <tr>
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
                    </div><!-- col close -->
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<!-- <script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script> -->
<script src="{{asset('frontend/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('frontend/js/dataTables.select.min.js')}}"></script>
<script type="text/javascript" src="{{asset('frontend/js/dataTables.checkboxes.min.js')}}"></script>
<!-- 
<script src="{{ asset('admin/extra-libs/DataTables/datatables.min.js') }}"></script>
<script src="{{ asset('frontend/js/select2.js') }}"></script> -->
<script type="text/javascript">
    $('.js-example-basic-multiple').select2({ width: '100%' });
    $('.js-example-basic-single').select2({ width: '100%' });
    // $('.js-example-basic-multiple').on('select2:select', function(e) {
    //   var selected = $(this).val();
    //   if (selected.includes('all')) {
    //     $(this).val('all').trigger('change'); // Select only 'All' option
    //   }
    // });
    // $('.js-example-basic-multiple').on('select2:unselect', function(e) {
    //   var selected = $(this).val();
    //   if (!selected) {
    //     $(this).val(null).trigger('change'); // Clear selection if all options are unselected
    //   }
    // });
   
    function init_table() {
        // $('#loader1').show(); 
        table = $("#zero_config_admin").DataTable({
            "pageLength": 50,
            "aaSorting": [
                [1, "asc"]
            ],
            
            processing: true,
            responsive: true,
            serverSide: true,
            
            ajax: {
                url: '/{{$emp_category}}/cumulative_report_new',
                type: 'GET',
                data: function (d) {
                    // Add custom data to the request
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
            columns: [{
                    data: 'hospital_name'
                }, //0
                {
                    data: 'institution_id'
                }, //1
                {
                    data: 'city'
                }, //2
                {
                    data: 'state_name'
                }, //3
                {
                    data: 'rev_no'
                }, //4
                {
                    data: 'div_name'
                }, //5
                {
                    data: 'mother_brand_name'
                }, //6
                {
                    data: 'brand_name'
                }, //7
                {
                    data: 'item_code'
                }, //8
                {
                    data: 'sap_code'
                }, //9
                {
                    data: 'discount_percent',
                    "render": function (data, type, row, meta) {
                        if (data !== null && data !== undefined && data !== '') {
                            return parseFloat(data).toFixed(2);
                        } else {
                            return 0;
                        }
                    }
                }, //10
                {
                    data: 'discount_rate',
                    "render": function (data, type, row, meta) {
                        if (data !== null && data !== undefined && data !== '') {
                            return parseFloat(data).toFixed(2);
                        } else {
                            return 0;
                        }
                    }
                }, //11
                {
                    data: 'applicable_gst'
                }, //12
                {
                    data: 'pack'
                }, //13
                {
                    data: 'cfa_code'
                }, //14
                {
                    data: 'mrp',
                    "render": function (data, type, row, meta) {
                        if (data !== null && data !== undefined && data !== '') {
                            return parseFloat(data).toFixed(2);
                        } else {
                            return 0;
                        }
                    }
                }, //15
                {
                    data: 'ptr',
                    "render": function (data, type, row, meta) {
                        if (data !== null && data !== undefined && data !== '') {
                            return parseFloat(data).toFixed(2);
                        } else {
                            return 0;
                        }
                    }
                }, //16
                {
                    data: 'mrp_margin',
                    "render": function (data, type, row, meta) {
                        if (data !== null && data !== undefined && data !== '') {
                            return parseFloat(data).toFixed(2);
                        } else {
                            return 0;
                        }
                    }
                }, //17
                {
                    data: 'product_type'
                }, //18
                {
                    data: 'hsn_code'
                }, //19
                {
                    data: function (row) {
                        var composition = row.composition;
                        return composition.toUpperCase().toLowerCase();
                    }
                }, //20
                {
                    data: 'contract_start_date'
                }, //21
                {
                    data: 'contract_end_date'
                }, //22
                {
                    data: 'year'
                }, //23
                
            ],
            columnDefs: [{
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
                        //    'text-align': 'center'
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
                            // 'text-align': 'center'
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
                            'text-align': 'center'
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
                            // 'text-align': 'center'
                        });
                    }
                },
                {
                    targets: 21,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css({
                            'text-align': 'center'
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
                            'text-align': 'center',
                            // 'padding-top': '4px'
                        });
                    }
                },
                // Define other column definitions here
            ],
            'select': {
                'style': 'multi'
            },
            scrollX: true,
            
            initComplete: function () {
                this.api()
                    .columns()
                    .every(function (index) {
                        let column = this;
                        let title = column.footer() ? column.footer().textContent : '';
                        // let title = column.footer().textContent;
                        if (title != "") {
                            // Create input element
                            let input = document.createElement('input');
                            input.placeholder = title;
                            input.type = 'search';
                            //input.style.width = '100%'; // Set input width to 100%
                            // Add specific class to the third input
                            if (index === 2 || index === 1) {
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
        if ($.fn.DataTable.isDataTable('#zero_config_admin')) {
         alert('cc');
          // If it is, destroy it first
          $('#zero_config_admin tbody').empty();
          $('#zero_config_admin').DataTable().destroy();
          $('#zero_config_admin tbody').empty();
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
        $('#zero_config_admin_length select').addClass('form-control form-control-sm')
        $('#zero_config_admin_filter input').addClass('form-control form-control-sm')
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
        var divisionName = $('#divisionName_filter').val();
        var brandName = $('#brandName_filter').val();
        var CumulativeReport = "ProductWise";

        var url = "{{ route($emp_category.'-export-cumulative-report') }}";
        url += '?reporttype=' + encodeURIComponent(CumulativeReport);
        url += '&divisionName=' + encodeURIComponent(JSON.stringify(divisionName));
        url += '&brandName=' + encodeURIComponent(JSON.stringify(brandName));
        // Redirect to the constructed URL
        window.location.href = url;

        // $('.export-excel').ajaxStart(function () {
        //     $("#loadingMessage").show();  // Show loading message
        //     alert("#loadingMessageshow");
        // }).ajaxStop(function () {
        //     alert("#loadingMessagehide");
        //     $("#loadingMessage").hide();  // Hide loading message when all AJAX calls are complete
        // });

        // var settings = {
        //     "url": "/{{$emp_category}}/CumulativeReportExport",
        //     "method": "GET",
        //     "timeout": 0,
        //     "headers": {
        //       "Accept-Language": "application/json",
        //     },
        //     "data": {
        //       "_token": "{{ csrf_token() }}",
        //       "reporttype": CumulativeReport,
        //       "divisionName" : JSON.stringify(divisionName),
        //       "brandName" : JSON.stringify(brandName)
        //     }
        // };
        // $.ajax(settings).done(function (response) {
        // // alert(response);
        // });


    });

    // Filter division by brand name
    $('#divisionName_filter').on('change', function(e){
        e.preventDefault()
        var div_name = $('#divisionName_filter').val();
        if(div_name != ""){
          // Add ajax call for filter division by brand here
          var settings = {
            "url": "/{{$emp_category}}/filter_division_by_brand",
            "method": "POST",
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
            let $dropdown = '';
            $.each(response, function (index, item) {
                // $dropdown.append(``);
                $dropdown +=`<option value="${item.brand_name}">${item.brand_name}</option>`;
            });
            $("#brandName_filter").append($dropdown);
          });
        }else{
            alert('Please select Division Name');
        }
    });
</script>
@endpush



