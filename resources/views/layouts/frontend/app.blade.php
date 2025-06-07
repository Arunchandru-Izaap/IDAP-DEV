<!DOCTYPE html>
<html lang="en">
<head>
  <title>Sun Pharma - iDAP</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="{{asset('frontend/css/bootstrap.min.css')}}">
  <link rel="stylesheet" href="{{asset('frontend/css/all.min.css')}}">
  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@100;300;400;700&display=swap" rel="stylesheet">

  <link rel='stylesheet' href="{{asset('frontend/css/jquery-ui.css')}}">
  <link rel='stylesheet' href="https://cdn.datatables.net/r/dt/dt-1.10.9/datatables.min.css">
  <link rel='stylesheet' href="https://cdn.datatables.net/select/1.3.4/css/select.dataTables.min.css">



  <link href="{{asset('css/style.css')}}" rel="stylesheet" />
  <link rel='stylesheet' href="{{asset('frontend/css/select2.css')}}">

  <script src="{{asset('frontend/js/jquery.min.js')}}" ></script>
  <!-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script> -->
  <script src="{{asset('frontend/js/jquery-ui.min.js')}}"></script>
  <script src="{{asset('frontend/js/popper.min.js')}}"></script>
  <script src="{{asset('frontend/js/bootstrap.bundle.min.js')}}"></script>
  
  <script src="{{asset('frontend/js/jquery.dataTables.min.js')}}"></script>
  <script src="{{asset('frontend/js/dataTables.select.min.js')}}"></script>
  <script type="text/javascript" src="{{asset('frontend/js/dataTables.checkboxes.min.js')}}"></script>
  <script src="{{asset('frontend/js/js.cookie.min.js')}}"></script>
  <script src="{{asset('frontend/js/moment.min.js')}}"></script>  
<!--
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@100;300;400;700&display=swap" rel="stylesheet">

  <link rel='stylesheet' href='https://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css'>
  <link rel='stylesheet' href='https://cdn.datatables.net/r/dt/dt-1.10.9/datatables.min.css'>
  <link rel='stylesheet' href='https://cdn.datatables.net/select/1.3.4/css/select.dataTables.min.css'>



  <link href="{{asset('css/style.css')}}" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  //<script src="https://code.jquery.com/jquery-3.5.1.js"></script> 
  <script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js'></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
  
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/select/1.3.4/js/dataTables.select.min.js"></script>
  <script type="text/javascript" src="https://gyrocode.github.io/jquery-datatables-checkboxes/1.2.10/js/dataTables.checkboxes.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.1/dist/js.cookie.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>  


-->
</head>
<body>
@include('layouts.frontend.header')
    <div id="app" class="w-100">
            <!-- <main class="py-4"> -->
                @yield('content')
            <!-- </main> -->
  </div>
  @include('layouts.frontend.footer')
  <script type="text/javascript">
    $('.list-group ul li:first-child, .list-group ul li:nth-child(2), .list-group ul li:nth-child(3), .list-group ul li:last-child').click(function() {
      const fullUrl = window.location.href;
      const parsedUrl = new URL(fullUrl);
      const pathParts = parsedUrl.pathname.split('/');

      const firstPart = pathParts[1] || "";
      if(firstPart=='initiator')
      {
        localStorage.removeItem('initiatorListSearch');
      }
      else if(firstPart=='approver')
      {
        localStorage.removeItem('approverListSearch');
      }
      else if(firstPart=='distribution')
      {
        localStorage.removeItem('distributionListSearch');
      }
      else if(firstPart=='poc')
      {
        localStorage.removeItem('pocListSearch');
      }
      else
      {
        localStorage.removeItem('initiatorListSearch');
        localStorage.removeItem('approverListSearch');
        localStorage.removeItem('distributionListSearch');
        localStorage.removeItem('pocListSearch');
      }
    });
    $('#sidebar-wrapper').hover(
      function() {
          // Mouse enters the div, do nothing (keep the menu open if already open)
      },
      function() {
          // Mouse leaves the div, collapse the menu
          $('.list-unstyled').collapse('hide');
      }
    );
    $('#admin_main_menu').on('click', function(){
      $('#generateMenu').collapse('hide');
      $('#UserManual').collapse('hide');
      $('#Approver-Manual').collapse('hide');
      $('#productwisereportMenu').collapse('hide');
      $("#UserManual").collapse('hide');
      $("#WorkflowAdjust").collapse('hide');
    })
    $('#generate_main_menu').on('click', function(){
      $('#adminMenu').collapse('hide');
      $('#UserManual').collapse('hide');
      $('#Approver-Manual').collapse('hide');
      $('#productwisereportMenu').collapse('hide');
      $("#UserManual").collapse('hide');
      $("#WorkflowAdjust").collapse('hide');
    })
    $('#usermanual_main_menu').on('click', function(){
      $('#adminMenu').collapse('hide');
      $('#generateMenu').collapse('hide');
      $('#productwisereportMenu').collapse('hide');
      $("#WorkflowAdjust").collapse('hide');
    })
    $('#report_main_menu').on('click', function(){
      $('#adminMenu').collapse('hide');
      $('#generateMenu').collapse('hide');
      $('#Approver-Manual').collapse('hide');
      $("#UserManual").collapse('hide');
      $("#WorkflowAdjust").collapse('hide');
      
    })
    $('#workflow_adjust_main_menu').on('click', function(){
      // $('#adminMenu').collapse('hide');
      $('#UserManual').collapse('hide');
      $('#Approver-Manual').collapse('hide');
      $('#productwisereportMenu').collapse('hide');
      $("#UserManual").collapse('hide');
    })
  </script>
</body>
</html>