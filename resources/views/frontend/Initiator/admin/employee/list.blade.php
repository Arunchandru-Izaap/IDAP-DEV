@extends('layouts.frontend.app')
@section('content')
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
          <div class="collapse navbar-collapse show" id="navbarSupportedContent">
              <ul class="navbar-nav dashboard-nav">
                  <li class="nav-item active"><a class="nav-link" href="{{route('initiator_dashboard')}}"> <img src="../admin/images/back.svg">Employee Master</a></li>
              </ul>

              <ul class="d-flex ml-auto user-name">
                  <li>
                      <h3>{{Session::get('emp_name')}}</h3>
                      <p>Initiator</p>
                  </li>

                  <li>
                      <img src="../admin/images/Sun_Pharma_logo.png">
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
          Employee master
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
          <div>&nbsp;</div>
          <div class="col-md-12 d-flex ">
            <a class="orange-btn-bor" href="{{url('/initiator/employee')}}">Add Employee</a>
          </div>
          <div class="col">
            <div class="actions-dashboard table-ct">
              <table
                id="zero_config"
                class="table VQ Request Listing vq-request-listing-tb nowrap"
              >
                <thead>
                  <tr>
                    <th><strong>Division Name</strong></th>
                    <th><strong>Division Code</strong></th>
                    <th><strong>Employee Code </strong></th>
                    <th><strong>Employee Name</strong></th>
                    <th><strong>Employee Email</strong></th>
                    <th><strong>Manager Name</strong></th>
                    <th><strong>Manager Email</strong></th>
                    <th><strong>Employee Type</strong></th>
                    <th><strong>Division Type</strong></th>
                    <th><strong>Employee Category</strong></th>
                    <th><strong>Employee Level</strong></th>
                    <th><strong>Employee Number</strong></th>
                    <th><strong>Employee HO</strong></th>
                    <th><strong>Action</strong></th>
                  </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                  <tr>
                    <td>{{$item->div_name}}</td>
                    <td>{{$item->div_code}}</td>
                    <td>{{$item->emp_code}}</td>
                    <td>{{$item->emp_name}}</td>
                    <td>{{$item->emp_email}}</td>
                    <td>{{$item->manager_name}}</td>
                    <td>{{$item->manager_email}}</td>
                    <td>{{$item->emp_type}}</td>
                    <td>{{$item->div_type}}</td>
                    <td>{{$item->emp_category}}</td>
                    <td>{{$item->emp_level}}</td>
                    <td>{{$item->emp_number}}</td>
                    <td>{{$item->emp_ho}}</td>
                    <td>
                      <a href="{{url('initiator/employee-edit',['id'=>$item->id])}}" class="btn btn-primary btn-sm">Edit</a><a onclick="deleteEmployeeHandler(<?= $item->id?>)"  class="btn btn-danger btn-sm text-white">Delete</a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
                <!-- <tfoot>
                <tr>
                    <th><strong>Institution name</strong></th>
                    <th><strong>Institution code</strong></th>
                    <th><strong>Key account name</strong></th>
                    <th><strong>City</strong></th>
                    <th><strong>Region</strong></th>
                    <th><strong>HQ</strong></th>
                    <th><strong>Zone</strong></th>
                    <th><strong>Retailer name 1</strong></th>
                    <th><strong>Retailer name 2</strong></th>
                    <th><strong>Retailer name 3</strong></th>
                    <th><strong>Address</strong></th>
                    <th><strong>Action</strong></th>
                  </tr>
                </tfoot> -->
              </table>
            </div>
          </div>  
<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script type="text/javascript">
  $("#zero_config").DataTable(
  {  
    "pageLength": 50,
    
    'language': {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },
    scrollX: true,
  })
  function deleteEmployeeHandler(id){
    let del = confirm('Do you really want to delete?');
    if(del){
      document.location.href = '/initiator/employee-delete/'+id;
    }
  }
</script>
<style type="text/css">
    .copy-center{
        position:inherit !important;margin: 20px 0;
    }
    div.dataTables_wrapper {
        max-width: 1357px;
        width: 100%;
        margin: 0 auto;
    }
</style> 
@endsection