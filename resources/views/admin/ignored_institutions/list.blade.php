@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
        <div class="card">
                <div class="card-body">
                  <h5 class="card-title"><a href="{{url('/admin/ignored-institutions')}}">Add Institution Code</a></h5>
                  <div class="table-responsive">
                    <table
                      id="zero_config"
                      class="table table-striped table-bordered"
                    >
                      <thead>
                        <tr>
                          <th>No.</th>
                          <th>Parent Institution Code</th>
                          <th><strong>Institution Code</strong></th>
                          <th><strong>Action</strong></th>
                        </tr>
                      </thead>
                      <tbody>
                        
                        @foreach($data as $k => $item)
                        <tr>
                          <td>{{$k+1}}</td>
                          <td>{{$item->parent_institution_id}}</td>
                          <td>{{$item->institution_id}}</td>
                          <td>
                            <a href="{{url('admin/ignored-institutions-edit',['id'=>$item->id])}}" class="btn btn-primary btn-sm">Edit</a><a onclick="deleteInstitutionHandler(<?= $item->id?>)" class="btn btn-danger btn-sm text-white">Delete</a>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>

                    </table>
                  </div>
                </div>
              </div>
        </div>  
    </div>
</div>
<script>
  function deleteInstitutionHandler(id){
    let del = confirm('Do you really want to delete?');
    if(del){
      document.location.href = '/admin/ignored-institutions-delete/'+id;
    }
  }
</script>
@endsection