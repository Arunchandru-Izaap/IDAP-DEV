@extends('layouts.frontend.app')
@section('content')
<div id="page-content-wrapper">
  <!-- Top navigation-->
  <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container-fluid">
          <div class="collapse navbar-collapse show" id="navbarSupportedContent">
              <ul class="navbar-nav dashboard-nav">
                  <li class="nav-item active"><a class="nav-link" href="{{route('initiator-discount-margin-list')}}"> <img src="../../admin/images/back.svg">Discount Margin Master - Add Discount Margin Master</a></li>
              </ul>

              <ul class="d-flex ml-auto user-name">
                  <li>
                      <h3>{{Session::get('emp_name')}}</h3>
                      <p>Initiator</p>
                  </li>

                  <li>
                      <img src="../../admin/images/Sun_Pharma_logo.png">
                  </li>
              </ul>
          </div>
          
      </div>

  </nav>
  <ul class="bradecram-menu">
      <li><a href="{{route('initiator_dashboard')}}">
          Home
      </a></li>
      <li class="">
        <a href="{{route('initiator-discount-margin-list')}}">
          Discount Margin Master
        </a>
      </li>
      <li class="active">
        <a href="">
          Add Discount Margin Master
        </a>
      </li>
  </ul>
  <div class="container-fluid">
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
        </ul>
      </div><br />
      @endif
      @if(session()->has('message'))
        <div class="alert alert-success">
            {{ session()->get('message') }}
        </div>
      @endif
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('initiator-discount-margin-store')}}">
                @csrf
                <div class="card-body">
                    <h4 class="card-title">Discount Margin Info</h4>
                    
                    <div class="form-group row">
                    <label for="item_code" class="col-sm-3 text-end control-label col-form-label">Brand Name & SAP Code</label>
                    <div class="col-sm-4">
                      <select class="js-example-basic-multiple" id="item_code" name="item_code[]" multiple="" data-placeholder="Select Brand Name & SAP Code" required="">
                      <option value="">Select Brand Name & SAP Code</option>  
                      @foreach($brand_details as $brand)
                          <option value="{{ $brand->item_code }}" {{ is_array(old('item_code')) && in_array($brand->item_code, old('item_code')) ? 'selected' : '' }}><?php $sap = ($brand->sap_itemcode != '')? ' - '.$brand->sap_itemcode :''; echo $brand->brand_name.$sap; ?>  </option>
                        @endforeach
                      </select>
                    </div>
                    </div>

                    <div class="form-group row">
                    <label for="discount_margin" class="col-sm-3 text-end control-label col-form-label">Disc Margin %</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control text_input" id="discount_margin" name="discount_margin" placeholder="Disc Margin %" maxlength="20" required="" value="{{ old('discount_margin') }}">
                    </div>
                    </div>
                    
                </div>
                <div class="border-top">
                    <div class="card-body">
                    <button type="submit" class="btn btn-primary">
                        Submit
                    </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script src="{{ asset('frontend/js/select2.js') }}"></script>
<script type="text/javascript">
  $('.js-example-basic-multiple').select2({ width: '100%' });
  document.querySelector('#discount_margin').addEventListener('input', function (e) {
    let value = e.target.value.replace(/[^0-9.]/g, ''); // Remove any non-numeric and non-dot characters
    let parts = value.split('.'); // Split by dot

    // Prevent multiple dots
    if (parts.length > 2) {
        e.target.value = parts[0] + '.' + parts[1]; // Keep only one dot
        return;
    }

    // Auto-format when length is 3-4 without a dot
    if (value.length >= 3 && value.length <= 4 && !value.includes('.')) {
        value = value.slice(0, 2) + '.' + value.slice(2);
    } else if (value.length > 5) {
        // Limit to 2 decimal places
        value = value.slice(0, 5);
    }

    e.target.value = value;
    e.target.setSelectionRange(e.target.value.length, e.target.value.length);
  });
</script>
<style type="text/css">
  /*.copyright_inc
  {
    top:62rem !important;
  }*/
    .copy-center{
        position:inherit !important;
        margin: 20px  0;
    }
    div.dataTables_wrapper {
        max-width: 1357px;
        width: 100%;
        margin: 0 auto;
    }
</style> 
@endsection