@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
        <div class="card">
                <div class="card-body">
                  <h5 class="card-title"><a href="{{url('/admin/duration')}}">Add Duration</a></h5>
                  <div class="table-responsive">
                    <table
                      id="zero_config"
                      class="table table-striped table-bordered"
                    >
                      <thead>
                        <tr>
                          <th><strong>No.</strong></th>
                          <th><strong>Type</strong></th>
                          <th><strong>Level</strong></th>
                          <th><strong>Days</strong></th>
                          <th><strong>Actions</strong></th>
                        </tr>
                      </thead>
                      <tbody>
                          @foreach($data as $item)
                        <tr>
                          <td>{{$item->id}}</td>
                          <td>{{$item->type}}</td>
                          <td>{{$item->level}}</td>
                          <td>{{$item->days}}</td>
                          <td>
                            <a href="{{url('admin/duration-edit',['id'=>$item->id])}}" class="btn btn-primary btn-sm">Edit</a><a onclick="deleteDurationHandler(<?= $item->id?>)" class="btn btn-danger btn-sm text-white">Delete</a>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                      <!-- <tfoot>
                      <tr>
                          <th><strong>No.</strong></th>
                          <th><strong>Type</strong></th>
                          <th><strong>Level</strong></th>
                          <th><strong>Days</strong></th>
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
  function deleteDurationHandler(id){
    let del = confirm('Do you really want to delete?');
    if(del){
      document.location.href = '/admin/duration-delete/'+id;
    }
  }
</script>
@endsection