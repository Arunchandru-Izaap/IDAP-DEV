@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
  tfoot tr th {
    font-size: 12px;
  }
</style>
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
          <div class="collapse navbar-collapse show" id="navbarSupportedContent">
              <ul class="navbar-nav dashboard-nav">
                  <li class="nav-item active"><a class="nav-link" href="{{route('initiator_dashboard')}}"> <img src="../../admin/images/back.svg">Signature Master</a></li>
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
          Signature Master
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
          <a class="orange-btn-bor" href="{{url('/initiator/signature')}}">Add Signature</a>
        </div>
        <div class="col">
          <div class="actions-dashboard table-ct">
            <table
              id="zero_config"
              class="table VQ Request Listing vq-request-listing-tb nowrap"
            >
              <thead>
                <tr>
                  <th><strong>Employee Code</strong></th>
                  <th><strong>SPLL Sign</strong></th>
                  <th><strong>SPIL Sign </strong></th>
                  <th><strong>Action</strong></th>
                </tr>
              </thead>
              <tbody>
                  @foreach($data as $item)
                <tr>
                  <td>{{$item->employee_code}}</td>
                  <td><img src="{{ asset('/images/') }}/{{$item->spll_sign}}" style="width: 350px;"/></td>
                  <td><img src="{{ asset('/images/') }}/{{$item->spil_sign}}" style="width:350px"/></td>
                  <td>
                    <a onclick="deleteSignHandler(<?= $item->id?>)" class="btn btn-danger btn-sm text-white">Delete</a>
                  </td>
                </tr>
                @endforeach
              </tbody>
              <tfoot>
              <tr>
              <th><strong>Employee Code</strong></th>
                  <th><strong>SPLL Sign</strong></th>
                  <th><strong>SPIL Sign </strong></th>
                  <th><strong>Action</strong></th>
                </tr>
              </tfoot>
            </table>
          </div> 
        </div> 
<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $("#zero_config").DataTable(
    {  
      "pageLength": 50,
      
      'language': {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },
      //scrollX: true,
    })
    /*$('.copyright').addClass('copyright_inc');
    $('#admin_main_menu').on('click', function(){
      $('.copyright').toggleClass('copyright_inc');
    })*/
  })
  function deleteSignHandler(id){
    let del = confirm('Do you really want to delete?');
    if(del){
      document.location.href = '/initiator/signature-delete/'+id;
    }
  }
</script>
<style type="text/css">
  /*.copyright_inc
  {
    top:62rem !important;
  }*/
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