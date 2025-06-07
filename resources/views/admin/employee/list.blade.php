@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
        <div class="card">
                <div class="card-body">
                  <h5 class="card-title"><a href="{{url('/admin/employee')}}">Add Employee</a></h5>
                  <div class="table-responsive">
                    <table id="zero_config" class="table table-striped table-bordered">
                      <thead>
                        <tr>
                          <th><strong>Division Name</strong></th>
                          <th><strong>Division Code</strong></th>
                          <th><strong>Employee Code </strong></th>
                          <th><strong>Employee Name</strong></th>
                          <th><strong>Employee Email</strong></th>
                          <th><strong>Manager Name</strong></th>
                          <th><strong>Manager Email</strong></th>
                          <th><strong>Employee Type</strong></th>
                          <th><strong>Division Type</strong></th>
                          <th><strong>Employee Category</strong></th>
                          <th><strong>Employee Level</strong></th>
                          <th><strong>Employee Number</strong></th>
                          <th><strong>Employee HO</strong></th>
                          <th><strong>Action</strong></th>
                        </tr>
                      </thead>
                      <tbody>
                          @foreach($data as $item)
                        <tr>
                          <td>{{$item->div_name}}</td>
                          <td>{{$item->div_code}}</td>
                          <td>{{$item->emp_code}}</td>
                          <td>{{$item->emp_name}}</td>
                          <td>{{$item->emp_email}}</td>
                          <td>{{$item->manager_name}}</td>
                          <td>{{$item->manager_email}}</td>
                          <td>{{$item->emp_type}}</td>
                          <td>{{$item->div_type}}</td>
                          <td>{{$item->emp_category}}</td>
                          <td>{{$item->emp_level}}</td>
                          <td>{{$item->emp_number}}</td>
                          <td>{{$item->emp_ho}}</td>
                          <td>
                            <a href="{{url('admin/employee-edit',['id'=>$item->id])}}" class="btn btn-primary btn-sm">Edit</a><a onclick="deleteEmployeeHandler(<?= $item->id?>)" class="btn btn-danger btn-sm text-white">Delete</a>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                      <!-- <tfoot>
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
                      </tfoot> -->
                    </table>
                  </div>
                </div>
              </div>
        </div>  
    </div>
</div>
<script type="text/javascript">
function deleteEmployeeHandler(id){
    let del = confirm('Do you really want to delete?');
    if(del){
      document.location.href = '/admin/employee-delete/'+id;
    }
  }
</script>
@endsection