@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
        <div class="card">
                <div class="card-body">
                  <h5 class="card-title"><a href="{{url('/admin/signature')}}">Add Signature</a></h5>
                  <div class="table-responsive">
                    <table
                      id="zero_config"
                      class="table table-striped table-bordered"
                    >
                      <thead>
                        <tr>
                          <th><strong>Employee Code</strong></th>
                          <th><strong>SPLL Sign</strong></th>
                          <th><strong>SPIL Sign </strong></th>
                          <th><strong>Action</strong></th>
                        </tr>
                      </thead>
                      <tbody>
                          @foreach($data as $item)
                        <tr>
                          <td>{{$item->employee_code}}</td>
                          <td><img src="{{ asset('/images/') }}/{{$item->spll_sign}}" style="width: 350px;"/></td>
                          <td><img src="{{ asset('/images/') }}/{{$item->spil_sign}}" style="width:350px"/></td>
                          <td>
                            <a onclick="deleteSignHandler(<?= $item->id?>)"  class="btn btn-danger btn-sm text-white">Delete</a>
                          </td>
                        </tr>
                        @endforeach
                      </tbody>
                      <tfoot>
                      <tr>
                      <th><strong>Employee Code</strong></th>
                          <th><strong>SPLL Sign</strong></th>
                          <th><strong>SPIL Sign </strong></th>
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
<script type="text/javascript">
    function deleteSignHandler(id){
    let del = confirm('Do you really want to delete?');
    if(del){
      document.location.href = '/admin/signature-delete/'+id;
    }
  }
</script>
@endsection