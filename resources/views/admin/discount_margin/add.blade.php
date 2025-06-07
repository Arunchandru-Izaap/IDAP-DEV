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
            <form class="form-horizontal" method="post" action="{{route('discount-margin-store')}}">
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
                    <label for="discount_margin" class="col-sm-3 text-end control-label col-form-label">Disc Margin%</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" id="discount_margin" name="discount_margin" placeholder="Discount Margin %" required="">
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
    </div>
</div>

@endsection
@push('scripts')
<script type="text/javascript">
    $('.js-example-basic-multiple').select2({ width: '100%' },{placeholder: 'Select Brand Name'});
    // $('.js-example-basic-multiple').select2({ width: '100%' },{placeholder: 'Select Item Name'});
    function adjustSelect2Height(selectElement) {
        let select2Container = selectElement.next('.select2-container');
        let selectionRendered = select2Container.find('.select2-selection__rendered');

        // Calculate the new height based on the content
        let newHeight = Math.max(selectionRendered.height() + 50, 32); // Minimum height is 32px

        // Apply the new height
        select2Container.find('.select2-selection--multiple').css('height', newHeight + 'px');
    }

    // Adjust height on page load for pre-selected values
    adjustSelect2Height($('#item_code'));

    // Adjust height on change
    $('#item_code').on('change', function () {
        adjustSelect2Height($(this));
    });
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