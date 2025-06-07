@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
        <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Config Data</h5>
                  <div class="table-responsive">
                    <table
                      id="zero_config"
                      class="table table-striped table-bordered"
                    >
                      <thead>
                        <tr>
                          <th>No.</th>
                          <th>Meta Name</th>
                          <th><strong>Meta Value</strong></th>
                          <th><strong>Action</strong></th>
                        </tr>
                      </thead>
                      <tbody>
                        
                        @foreach($data as $k => $item)
                        <tr>
                          <td>{{$k+1}}</td>
                          <td>{{$item->meta_name}}</td>
                          <td>{{$item->meta_value}}</td>
                          <td>
                            <a href="{{url('admin/config-data-edit',['id'=>$item->id])}}" class="btn btn-primary btn-sm">Edit</a>
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
@endsection