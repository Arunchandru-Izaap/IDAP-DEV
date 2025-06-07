@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
        <div class="card">
                <div class="card-body">
                  <h5 class="card-title"><a href="{{url('/admin/special-price')}}">Add Special price</a></h5>
                  <div class="table-responsive">
                    <table
                      id="zero_config"
                      class="table table-striped table-bordered"
                    >
                      <thead>
                        <tr>
                          <th><strong>No.</strong></th>
                          <th><strong>sku id</strong></th>
                          <th><strong>Discount percent</strong></th>
                          <th><strong>Discount rate</strong></th>
                          <th><strong>Action</strong></th>
                        </tr>
                      </thead>
                      <tbody>
                          @foreach($data as $item)
                        <tr>
                          <td>{{$item->id}}</td>
                          <td>{{$item->sku_id}}</td>
                          <td>{{$item->discount_percent}}</td>
                          <td>{{$item->discount_rate}}</td>
                          <td>
                            <a href="{{url('admin/special-price-edit',['id'=>$item->id])}}" class="btn btn-primary btn-sm">Edit</a><a href="{{url('admin/special-price-delete',['id'=>$item->id])}}" class="btn btn-danger btn-sm text-white">Delete</a>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                      <tfoot>
                      <tr>
                          <th><strong>No.</strong></th>
                          <th><strong>sku id</strong></th>
                          <th><strong>Discount percent</strong></th>
                          <th><strong>Discount rate</strong></th>
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