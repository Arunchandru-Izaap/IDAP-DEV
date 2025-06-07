@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
        <div class="card">
                <div class="card-body">
                  <h5 class="card-title"><a href="{{url('/admin/stockist')}}">Add Stock</a></h5>
                  <div class="table-responsive">
                    <table
                      id="zero_config"
                      class="table table-striped table-bordered"
                    >
                      <thead>
                        <tr>
                          <th><strong>No.</strong></th>
                          <th><strong>Stockist name</strong></th>
                          <th><strong>Email ID</strong></th>
                          <th><strong>Stockist type flag</strong></th>
                          <th><strong>Action</strong></th>
                        </tr>
                      </thead>
                      <tbody>
                          @foreach($data as $item)
                        <tr>
                          <td>{{$item->id}}</td>
                          <td>{{$item->stockist_name}}</td>
                          <td>{{$item->email_id}}</td>
                          @if($item->stockist_type_flag==1)
                          <td>Active</td>
                          @else
                          <td>De-Active</td>
                          @endif
                          <td>
                            <a href="{{url('admin/stockist-edit',['id'=>$item->id])}}" class="btn btn-primary btn-sm">Edit</a><a href="{{url('admin/stockist-delete',['id'=>$item->id])}}" class="btn btn-danger btn-sm text-white">Delete</a>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                      <tfoot>
                      <tr>
                          <th><strong>No.</strong></th>
                          <th><strong>Stockist name</strong></th>
                          <th><strong>Email ID</strong></th>
                          <th><strong>Stockist type flag</strong></th>
                          <th><strong>Action</strong></th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
        </div>  
    </div>
</div>
@endsection