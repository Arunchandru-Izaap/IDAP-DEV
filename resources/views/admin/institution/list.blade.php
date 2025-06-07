@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
        <div class="card">
                <div class="card-body">
                  <h5 class="card-title"><a href="{{url('/admin/institution')}}">Add Institution</a></h5>
                  <div class="table-responsive">
                    <table
                      id="zero_config"
                      class="table table-striped table-bordered"
                    >
                      <thead>
                        <tr>
                          <th><strong>No.</strong></th>
                          <th><strong>Institution name</strong></th>
                          <th><strong>Institution code</strong></th>
                          <th><strong>Key account name</strong></th>
                          <th><strong>City</strong></th>
                          <th><strong>Region</strong></th>
                          <th><strong>HQ</strong></th>
                          <th><strong>Zone</strong></th>
                          <th><strong>Retailer name 1</strong></th>
                          <th><strong>Retailer name 2</strong></th>
                          <th><strong>Retailer name 3</strong></th>
                          <th><strong>Address</strong></th>
                          <th><strong>Action</strong></th>
                        </tr>
                      </thead>
                      <tbody>
                          @foreach($data as $item)
                        <tr>
                          <td>{{$item->id}}</td>
                          <td>{{$item->institution_name}}</td>
                          <td>{{$item->institution_code}}</td>
                          <td>{{$item->key_account_name}}</td>
                          <td>{{$item->city}}</td>
                          <td>{{$item->region}}</td>
                          <td>{{$item->hq}}</td>
                          <td>{{$item->zone}}</td>
                          <td>{{$item->retailer_name_1}}</td>
                          <td>{{$item->retailer_name_2}}</td>
                          <td>{{$item->retailer_name_3}}</td>
                          <td>{{$item->address}}</td>
                          <td>
                            <a href="{{url('admin/edit',['id'=>$item->id])}}" class="btn btn-primary btn-sm">Edit</a><a href="{{url('admin/delete',['id'=>$item->id])}}" class="btn btn-danger btn-sm text-white">Delete</a>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                      <tfoot>
                      <tr>
                          <th><strong>Institution name</strong></th>
                          <th><strong>Institution code</strong></th>
                          <th><strong>Key account name</strong></th>
                          <th><strong>City</strong></th>
                          <th><strong>Region</strong></th>
                          <th><strong>HQ</strong></th>
                          <th><strong>Zone</strong></th>
                          <th><strong>Retailer name 1</strong></th>
                          <th><strong>Retailer name 2</strong></th>
                          <th><strong>Retailer name 3</strong></th>
                          <th><strong>Address</strong></th>
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