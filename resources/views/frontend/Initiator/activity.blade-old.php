@extends('layouts.frontend.app')
@section('content')
<!-- <div class="container"><h2>Initiator Dashboard demo</h2></div> -->
<!-- Page content wrapper-->
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
            <h3>Jitu Patil</h3>
            <p>Front end developer</p>
          </li>
          <li>
            <img src="../admin/images/Sun_Pharma_logo.png">
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
              <a href="{{route('approver_listing')}}">
                <div class="ic-bg">
                  <img src="../admin/images/check-double.svg">
                </div>
                <h2>Manage <Br>Request </h2>
              </a>
            </li>
            <li>
              <a href="">
                <div class="ic-bg">
                  <img src="../admin/images/exclamation-circle.svg">
                </div>
                <h2>Raise a <Br>Ticket </h2>
              </a>
            </li>
          </ul>
        </div>
      </div>
@endsection