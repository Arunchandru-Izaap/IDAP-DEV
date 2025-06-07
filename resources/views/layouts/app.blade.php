<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta
      name="keywords"
      content="Sun Pharma - iDAP"
    />
    <meta
      name="description"
      content="Sun Pharma - iDAP"
    />
    <meta name="robots" content="noindex,nofollow" />
    <title>Sun Pharma - iDAP</title>
    <!-- Favicon icon -->
    <link
      rel="icon"
      type="image/png"
      sizes="16x16"
      href="../assets/images/favicon.png"
    />
    <!-- Custom CSS -->
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
    <link href="{{asset('admin/css/style.min.css')}}" rel="stylesheet" />

    <link href="{{asset('css/style.css')}}" rel="stylesheet" />

       
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
  </head>
<body>
  
    <div id="app">
        <main>
            @yield('content')
        </main>
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
</body>
<!-- <script type="text/javascript" href=></script> -->
<script src="{{asset('/js/common.js')}}"></script>
</html>
