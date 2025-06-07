@extends('layouts.admin.app')
@section('content')
<style type="text/css">
     .select2-container--classic .select2-selection--multiple .select2-selection__choice, .select2-container--default .select2-selection--multiple .select2-selection__choice, .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    /*background-color: #2255a4;
    border-color: #2255a4;*/
    color: #000;
}
</style>
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">

            <form class="form-horizontal" method="post" action="{{route('discount-margin-update')}}">
                @csrf
                <div class="card-body">
                    <h4 class="card-title">Discount Margin Info</h4>
                    <input type="hidden" class="form-control" name="id"  value={{$data['id']}}>
                    
                    <div class="form-group row">
                    <label for="item_code" class="col-sm-3 text-end control-label col-form-label">Item Code</label>
                    <div class="col-sm-9">
                        <select class="js-example-basic-multiple" id="item_code" name="item_code[]" multiple="" data-placeholder="Select Brand Name & SAP Code" required="" disabled>
                            <option value="">Select Brand Name & SAP Code</option>  
                            @foreach($brand_details as $brand)
                                <option value="{{ $brand->item_code }}" <?php if ($brand->item_code == $data['item_code']) echo ' selected="selected"'; ?>><?php $sap = ($brand->sap_itemcode != '')? ' - '.$brand->sap_itemcode :''; echo $brand->brand_name.$sap; ?>  </option>
                            @endforeach
                        </select>
                    </div>
                    </div>

                    <div class="form-group row">
                    <label for="discount_margin" class="col-sm-3 text-end control-label col-form-label">Disc Margin%</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="discount_margin" name="discount_margin" placeholder="Discount Margin %" value={{$data['discount_margin']}}>
                    </div>
                    </div>
                    
                </div>
                <div class="border-top">
                    <div class="card-body">
                    <button type="submit" class="btn btn-primary">
                        Submit
                    </button>
                    <a href="{{ route('discount-margin-list') }}" class="btn btn-warning">Cancel</a>
                    </div>
                </div>
            </form>
            
        </div>
    </div>
    </div>
</div>
@endsection
@push('scripts')
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
@endpush