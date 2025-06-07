@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
        <div class="card">
                <div class="card-body">
                  <h5 class="card-title"><a href="{{url('/admin/last-year-price')}}">Add Last Year Price</a></h5>
                  <div class="table-responsive">
                    <table
                      id="zero_configee"
                      class="table table-striped table-bordered"
                    >
                      <thead>
                        <tr>
                        <th><strong>Sku name</strong></th>
                          <th><strong>Institution name</strong></th>
                          <th><strong>Division name</strong></th>
                          <th><strong>Discount percent</strong></th>
                          <th><strong>Year</strong></th>
                          <th><strong>Action</strong></th>
                          
                        </tr>
                      </thead>
                      <tbody>
                          
                      </tbody>
                      <tfoot>
                      <tr>
                          <th><strong>Sku name</strong></th>
                          <th><strong>Institution name</strong></th>
                          <th><strong>Division name</strong></th>
                          <th><strong>Discount percent</strong></th>
                          <th><strong>Year</strong></th>
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