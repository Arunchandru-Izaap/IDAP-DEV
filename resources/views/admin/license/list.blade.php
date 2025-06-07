@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
        <div class="card">
                <div class="card-body">
                  <h5 class="card-title"><a href="{{url('/admin/license')}}">Add License Info</a></h5>
                  <div class="table-responsive">
                    <table
                      id="zero_config"
                      class="table table-striped table-bordered"
                    >
                      <thead>
                        <tr>
                          <th>No.</th>
                          <th>Institution Code</th>
                          <th>Institution Name</th>
                          <th>GST</th>
                          <th>Form 20</th>
                          <th>Form 20 Expiry Date</th>
                          <th>Form 21</th>
                          <th>Form 21 Expiry Date</th>
                          <th><strong>Action</strong></th>
                        </tr>
                      </thead>
                      <tbody>
                        
                        @foreach($data as $k => $item)
                        <tr>
                          <td>{{$k+1}}</td>
                          <td>{{$item->institution_id}}</td>
                          <td>{{$item->institution_name}}</td>
                          <td><a href="{{url('admin/license-download/gst',['id'=>$item->gst])}}" class="btn btn-success btn-sm">Download</a></td>
                          <td><a href="{{url('admin/license-download/form20',['id'=>$item->form20])}}" class="btn btn-success btn-sm">Download</a></td>
                          <td>{{$item->form20_expiry_date}}</td>
                          <td><a href="{{url('admin/license-download/form21',['id'=>$item->form21])}}" class="btn btn-success btn-sm">Download</a></td>
                          <td>{{$item->form21_expiry_date}}</td>
                          <td>
                            <a href="{{url('admin/license-edit',['id'=>$item->id])}}" class="btn btn-primary btn-sm">Edit</a><a onclick="deleteLicenseHandler(<?= $item->id?>)" class="btn btn-danger btn-sm text-white">Delete</a>
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
  function deleteLicenseHandler(id){
    let del = confirm('Do you really want to delete?');
    if(del){
      document.location.href = '/admin/license-delete/'+id;
    }
  }
</script>
@endsection