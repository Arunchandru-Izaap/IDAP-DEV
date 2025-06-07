<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta
      name="keywords"
      content="wrappixel, admin dashboard, html css dashboard, web dashboard, bootstrap 5 admin, bootstrap 5, css3 dashboard, bootstrap 5 dashboard, Matrix lite admin bootstrap 5 dashboard, frontend, responsive bootstrap 5 admin template, Matrix admin lite design, Matrix admin lite dashboard bootstrap 5 dashboard template"
    />
    <meta
      name="description"
      content="Matrix Admin Lite Free Version is powerful and clean admin dashboard template, inpired from Bootstrap Framework"
    />
    <meta name="robots" content="noindex,nofollow" />
    <title>IDAP</title>
    <!-- Favicon icon -->
    <link
      rel="icon"
      type="image/png"
      sizes="16x16"
      href="{{asset('admin/images/favicon.png')}}"
    />
    <link href="{{asset('admin/libs/flot/css/float-chart.css')}}" rel="stylesheet" />
    <!-- Custom CSS -->
    <link href="{{asset('admin/css/style.min.css')}}" rel="stylesheet" />
    <link
      rel="stylesheet"
      type="text/css"
      href="{{asset('admin/extra-libs/multicheck/multicheck.css')}}"
    />
    <link
      href="{{asset('admin/libs/datatables.net-bs4/css/dataTables.bootstrap4.css')}}"
      rel="stylesheet"
    />
    <!-- <link
      href="{{asset('admin/libs/datatables.net-bs4/css/dataTables.bootstrap4.css')}}"
      rel="stylesheet"
    /> -->
      <link rel='stylesheet' href="{{asset('frontend/css/select2.css')}}">


    
  </head>

<body>
    <div id="app">
      <div
        id="main-wrapper"
        data-layout="vertical"
        data-navbarbg="skin5"
        data-sidebartype="full"
        data-sidebar-position="absolute"
        data-header-position="absolute"
        data-boxed-layout="full"
      >
      @include('layouts.admin.header')
      
        <div class="page-wrapper">
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
            </ul>
          </div><br />
        @endif
        @if(session()->has('message'))
          <div class="alert alert-success">
              {{ session()->get('message') }}
          </div>
      @endif
            <main class="py-4">
                @yield('content')
            </main>
        </div>
        <!-- @include('layouts.admin.footer') -->
  </div>
</div>
      
    <script src="{{asset('admin/libs/jquery/dist/jquery.min.js')}}"></script>
    <script src="{{asset('admin/libs/bootstrap/dist/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('admin/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js')}}"></script>
    <script src="{{asset('admin/extra-libs/sparkline/sparkline.js')}}"></script>
    <!--Wave Effects -->
    <script src="{{asset('admin/js/waves.js')}}"></script>
    <!--Menu sidebar -->
    <script src="{{asset('admin/js/sidebarmenu.js')}}"></script>
    <!--Custom JavaScript -->
    <script src="{{asset('admin/js/custom.min.js')}}"></script>
    <!--This page JavaScript -->
    <script src="{{asset('admin/js/pages/dashboards/dashboard1.js')}}"></script>
    <!-- Charts js Files -->
    <script src="{{asset('admin/libs/flot/excanvas.js')}}"></script>
    <script src="{{asset('admin/libs/flot/jquery.flot.js')}}"></script>
    <script src="{{asset('admin/libs/flot/jquery.flot.pie.js')}}"></script>
    <script src="{{asset('admin/libs/flot/jquery.flot.time.js')}}"></script>
    <script src="{{asset('admin/libs/flot/jquery.flot.stack.js')}}"></script>
    <script src="{{asset('admin/libs/flot/jquery.flot.crosshair.js')}}"></script>
    <script src="{{asset('admin/libs/flot.tooltip/js/jquery.flot.tooltip.min.js')}}"></script>
    <script src="{{asset('admin/js/pages/chart/chart-page-init.js')}}"></script>
    <script src="{{asset('admin/extra-libs/multicheck/datatable-checkbox-init.js')}}"></script>
    <script src="{{asset('admin/extra-libs/multicheck/jquery.multicheck.js')}}"></script>
    <script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
    <script src="{{asset('admin/js/common.js')}}"></script>
    <script src="{{asset('frontend/js/select2.js')}}"></script>
    <script>
      /****************************************
       *       Basic Table                   *
       ****************************************/
      $("#zero_config").DataTable();
     
      $('#zero_configee').DataTable({
        "serverSide": true,
        "processing": true,
        "order": [[ 1, "asc" ]],
        "ajax":{
        "url": "{{ url('admin/get_last_year') }}",
        "dataType": "json",
        "type": "GET",
        "data":{ _token: "{{csrf_token()}}"}
        },
        "columns": [
        { "data": "sku_id" },
        { "data": "institution_id" },
        { "data": "division_id" },
        { "data": "discount_percent" },
        { "data": "year" },
        { "data": "actions" },
        ],
        "columnDefs":[
            {
                "targets": [5],
                "orderable": false,

            }
        ]
        
        });
        $(document).ready(function(){
          if($('#emp_category').val() == 'initiator' || $('#emp_category').val() == 'admin'|| $('#emp_category').val() == 'ceo'){
            $('.hidden-cap').css('display','none');
          }
          if($('#emp_category').val() == 'poc'){
            $('.poc-hidden').css('display','none');
            $('.poc_addl').css('display','flex');
          }
          if($('#emp_category').val() == 'distribution'){
            $('.distribution-hidden').css('display','none');
            $('label[for="div_code"]').text('CFA Code');
            $('#div_code').attr('placeholder','CFA Code');
          }
          // jQuery methods go here...
          $('#emp_category').on('change', function() {
            if(this.value == 'approver' || this.value == 'distribution'){
                $('.hidden-cap').css('display','flex');
                $('.poc_addl').css('display','none');
                $('.poc-hidden').css('display','flex');
            }else{
              $('.hidden-cap').css('display','none');
            }
            if(this.value == 'poc'){
              $('.hidden-cap').css('display','flex');
              $('.poc-hidden').css('display','none');
            }
            $('label[for="div_code"]').text('Division Code');
            $('#div_code').attr('placeholder','Division Code');
            if(this.value == 'distribution'){
              $('.hidden-cap').css('display','flex');
              $('.distribution-hidden').css('display','none');
              $('label[for="div_code"]').text('CFA Code');
              $('#div_code').attr('placeholder','CFA Code');
            }
          });
          $('.js-example-basic-single').select2({ width: '100%' }/*,{placeholder: 'Select Item Name'}*/);});
      </script>
      @stack('scripts')
</body>
</html>
