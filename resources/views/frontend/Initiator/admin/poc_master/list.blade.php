@extends('layouts.frontend.app')
@section('content')
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
          <div class="collapse navbar-collapse show" id="navbarSupportedContent">
              <ul class="navbar-nav dashboard-nav">
                  <li class="nav-item active"><a class="nav-link" href="{{route('initiator_dashboard')}}"> <img src="../../admin/images/back.svg">Poc Master</a></li>
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
          Poc Master
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
          <a class="orange-btn-bor" href="{{url('/initiator/poc-master')}}">Bulk Add/ Update Poc Master</a>
        </div>
        <div class="col">
          <div class="actions-dashboard table-ct">
            <table
              id="zero_config"
              class="table VQ Request Listing vq-request-listing-tb nowrap"
            >
              <thead>
                <tr>
                  <th><strong>No.</strong></th>
                  <th><strong>Institution ID</strong></th>
                  <th><strong>Institution Name</strong></th>
                  <th><strong>Fsm Code</strong></th>
                  <th><strong>Fsm Name </strong></th>
                  <th><strong>Fsm Mobile</strong></th>
                  <th><strong>Fsm Email</strong></th>
                  <th><strong>Fsm HO</strong></th>

                  <th><strong>Rsm Code</strong></th>
                  <th><strong>Rsm Name </strong></th>
                  <th><strong>Rsm Mobile</strong></th>
                  <th><strong>Rsm Email</strong></th>
                  <th><strong>Rsm HO</strong></th>

                  <th><strong>Zsm Code</strong></th>
                  <th><strong>Zsm Name </strong></th>
                  <th><strong>Zsm Mobile</strong></th>
                  <th><strong>Zsm Email</strong></th>
                  <th><strong>Zsm HO</strong></th>
                  <th><strong>Action</strong></th>
                </tr>
              </thead>
              <tbody>
                  @foreach($data as $k=>$item)
                <tr>
                  <!-- <td>{{$item->id}}</td> -->
                  <td>{{$k+1}}</td>
                  <td>{{$item->institution_id}}</td>
                  <td>{{$item->institution_name}}</td>

                  <td>{{$item->fsm_code}}</td>
                  <td>{{$item->fsm_name}}</td>
                  <td>{{$item->fsm_number}}</td>
                  <td>{{$item->fsm_email}}</td>
                  <td>{{$item->fsm_ho}}</td>

                  <td>{{$item->rsm_code}}</td>
                  <td>{{$item->rsm_name}}</td>
                  <td>{{$item->rsm_number}}</td>
                  <td>{{$item->rsm_email}}</td>
                  <td>{{$item->rsm_ho}}</td>

                  <td>{{$item->zsm_code}}</td>
                  <td>{{$item->zsm_name}}</td>
                  <td>{{$item->zsm_number}}</td>
                  <td>{{$item->zsm_email}}</td>
                  <td>{{$item->zsm_ho}}</td>
                  <td>
                    <a href="{{url('initiator/poc-master-edit',['id'=>$item->id])}}" class="btn btn-primary btn-sm">Edit</a><a onclick="deletePOCHandler(<?= $item->id?>)" class="btn btn-danger btn-sm text-white">Delete</a>
                  </td>
                </tr>
                @endforeach
              </tbody>
              <!-- <tfoot>
              <tr>
              <th><strong>No.</strong></th>
                  <th><strong>Name</strong></th>
                  <th><strong>Email ID</strong></th>
                  <th><strong>Emp Code</strong></th>
                  <th><strong>Designation</strong></th>
                  <th><strong>Region</strong></th>
                  <th><strong>HQ</strong></th>
                  <th><strong>Mobile Number</strong></th>
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
  function deletePOCHandler(id){
    let del = confirm('Do you really want to delete?');
    if(del){
      document.location.href = '/initiator/poc-master-delete/'+id;
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