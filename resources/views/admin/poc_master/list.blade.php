@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
        <div class="card">
                <div class="card-body">
                  <h5 class="card-title"> <a href="{{url('/admin/poc-master')}}">Add Poc Master</a></h5>
                  <div class="table-responsive">
                    <table
                      id="zero_config"
                      class="table table-striped table-bordered"
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
                          @foreach($data as $item)
                        <tr>
                          <td>{{$item->id}}</td>
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
                            <a href="{{url('admin/poc-master-edit',['id'=>$item->id])}}" class="btn btn-primary btn-sm">Edit</a><a onclick="deletePOCHandler(<?= $item->id?>)" class="btn btn-danger btn-sm text-white">Delete</a>
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
              </div>
        </div>  
    </div>
</div>
<script type="text/javascript">
  function deletePOCHandler(id){
    let del = confirm('Do you really want to delete?');
    if(del){
      document.location.href = '/admin/poc-master-delete/'+id;
    }
  }
</script>
@endsection