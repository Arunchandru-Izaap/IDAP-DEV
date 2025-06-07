@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
        <div class="card">
                <div class="card-body">
                  <h5 class="card-title"><a href="{{url('/admin/max-discount-cap')}}">Add Max Discount For Division</a></h5>
                  <div class="table-responsive">
                    <table
                      id="zero_config"
                      class="table table-striped table-bordered"
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
                            <a href="{{url('admin/max-discount-cap-edit',['id'=>$item->id])}}" class="btn btn-primary btn-sm">Edit</a><a onclick="deleteDivisionHandler(<?= $item->id?>)" class="btn btn-danger btn-sm text-white">Delete</a>
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
  function deleteDivisionHandler(id){
    let del = confirm('Do you really want to delete?');
    if(del){
      document.location.href = '/admin/max-discount-cap-delete/'+id;
    }
  }
</script>
@endsection