@extends('layouts.frontend.app') @section('content')
<!-- <div class="container"><h2>Initiator Dashboard demo</h2></div> -->
<!-- Page content wrapper-->
<style type="text/css">
  .level-list li:nth-child(2):after {
    content: "";
    border: solid #d1d1d1 1px;
    margin: 0 60px;
    height: 40px;
}.level-list li:nth-child(3):after {
    content: "";
    border: solid #d1d1d1 1px;
    margin: 0 60px;
    height: 40px;
}
.copyright_inc
{
  top:62rem !important;
}
</style>
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
      <div class="collapse navbar-collapse show" id="navbarSupportedContent">
        <ul class="navbar-nav dashboard-nav">
          <li class="nav-item active">
            <a class="nav-link" href="#!">My Dashboard</a>
          </li>
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
    <li>
      <a href=""> Home </a>
    </li>
  </ul>
  <!-- Page content-->
  <div class="container-fluid">
    <div class="row">
      <div class="col">
        <div class="actions-dashboard">
          <h5>Select Action</h5>
          <ul class="d-flex level-list">
            <li>
              <a href="{{route('create_request')}}">
                <div class="ic-bg">
                  <img src="../admin/images/plus-or.svg">
                </div>
                <h2>Initiate <Br>Request </h2>
              </a>
            </li>
            <li>
              <a href="{{route('initiator_listing')}}">
                <div class="ic-bg">
                  <img src="../admin/images/eye-or.svg">
                </div>
                <h2>View <Br>Request </h2>
              </a>
            </li>
            <li>
              <a href="{{route('genereate_request')}}">
                <div class="ic-bg">
                  <img src="../admin/images/graph1.svg">
                </div>
                <h2>Generate <br> VQ for New Product </h2>
              </a>
            </li>
            <li>
              <a href="{{route('genereate_request_existing')}}">
                <div class="ic-bg">
                  <img src="../admin/images/graph1.svg">
                </div>
                <h2>Generate <br> VQ for Existing Product </h2>
              </a>
            </li>
          </ul>
        </div>
      </div>
      <script type="text/javascript">
        localStorage.removeItem('initiatorListSearch');
        $('#admin_main_menu').on('click', function(){
          //$('.copyright').toggleClass('copyright_inc');
        })
      </script>
 @endsection