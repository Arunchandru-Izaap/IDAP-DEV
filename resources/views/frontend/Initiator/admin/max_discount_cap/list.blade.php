@extends('layouts.frontend.app')
@section('content')
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
          <div class="collapse navbar-collapse show" id="navbarSupportedContent">
              <ul class="navbar-nav dashboard-nav">
                  <li class="nav-item active"><a class="nav-link" href="{{route('initiator_dashboard')}}"> <img src="../../admin/images/back.svg">Max Discount Cap</a></li>
              </ul>

              <ul class="d-flex ml-auto user-name">
                  <li>
                      <h3>{{Session::get('emp_name')}}</h3>
                      <p>Initiator</p>
                  </li>

                  <li>
                      <img src="../../admin/images/Sun_Pharma_logo.png">
                  </li>
              </ul>
          </div>
          
      </div>

  </nav>
  <ul class="bradecram-menu">
      <li><a href="{{route('initiator_dashboard')}}">
          Home
      </a></li>
      <li class="active">
        <a href="">
          Max Discount Cap
        </a>
      </li>
  </ul>
  <div class="container-fluid">
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
    <div class="row">
        <div class="col">

          <div>&nbsp;</div>
          <div class="col-md-12 d-flex ">
            <a class="orange-btn-bor" href="{{url('/initiator/max-discount-cap')}}">Add Max Discount For Division</a>
          </div>
          <div class="actions-dashboard table-ct">
            <table
              id="zero_config"
              class="table VQ Request Listing vq-request-listing-tb nowrap"
            >
              <thead>
                <tr>
                  <th>No.</th>
                  <th>Division Code</th>
                  <th>Financial Year</th>
                  <th>Max Discount</th>
                  <th><strong>Action</strong></th>
                </tr>
              </thead>
              <tbody>
                
                @foreach($data as $k => $item)
                <tr>
                  <td>{{$k+1}}</td>
                  <td>{{$item->div_id}}</td>
                  <td>{{$item->year}}</td>
                  <td>{{$item->max_discount}}</td>
                  <td>
                    <a href="{{url('initiator/max-discount-cap-edit',['id'=>$item->id])}}" class="btn btn-primary btn-sm">Edit</a><a onclick="deleteDivisionHandler(<?= $item->id?>)" class="btn btn-danger btn-sm text-white">Delete</a>
                  </td>
                </tr>
                @endforeach
              </tbody>

            </table>
          </div>

        </div>  
<script>
  function deleteDivisionHandler(id){
    let del = confirm('Do you really want to delete?');
    if(del){
      document.location.href = '/initiator/max-discount-cap-delete/'+id;
    }
  }
</script>
<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $("#zero_config").DataTable(
    {  
      "pageLength": 50,
      
      'language': {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },
      // scrollX: true,
    })
    /*$('.copyright').addClass('copyright_inc');
    $('#admin_main_menu').on('click', function(){
      $('.copyright').toggleClass('copyright_inc');
    })*/
  })
</script>
<style type="text/css">
  /*.copyright_inc
  {
    top:62rem !important;
  }*/
    .copy-center{
        position:inherit !important;
        margin: 20px  0;
    }
    div.dataTables_wrapper {
        max-width: 1357px;
        width: 100%;
        margin: 0 auto;
    }
</style> 
@endsection