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
            <form class="form-horizontal" method="post" action="{{route('poc-master-store')}}">
                @csrf
            <div class="card-body">
                <h4 class="card-title">Poc Master Info</h4>
                <div class="form-group row">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">Institution Name</label>
                    <div class="col-sm-4">
                        <select name="institution_id[]" id="institution_id" class="form-control js-example-basic-multiple" multiple="" data-placeholder="Select institutions" required="">
                            @foreach($data['institution'] as $item)
                            <option value="{{ $item->INST_ID }}" 
                                {{ is_array(old('institution_id')) && in_array($item->INST_ID, old('institution_id')) ? 'selected' : '' }}>
                                {{ $item->INST_ID }} - {{ $item->INST_NAME }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">FSM Name</label>
                    <div class="col-sm-4">
                        <select name="fsm_code" id="fsm_code" class="form-control js-example-basic-single" required="">
                            @foreach($data['poc'] as $item)
                            <option value="{{$item->fsm_code}}" {{ old('fsm_code') == $item->fsm_code ? 'selected' : '' }}>{{$item->fsm_code}}-{{$item->fsm_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">RSM Name</label>
                    <div class="col-sm-4">
                        <select name="rsm_code" id="rsm_code" class="form-control js-example-basic-single" required="">
                            <option value="">Select RSM Name</option>
                            @foreach($data['rsm'] as $item)
                            <option value="{{$item->rsm_code}}" {{ old('rsm_code') == $item->rsm_code ? 'selected' : '' }}>{{$item->rsm_code}}-{{$item->rsm_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="fname" class="col-sm-3 text-end control-label col-form-label">ZSM Name</label>
                    <div class="col-sm-4">
                        <select name="zsm_code" id="zsm_code" class="form-control js-example-basic-single" required="">
                            <option value="">Select ZSM Name</option>
                            @foreach($data['zsm'] as $item)
                            <option value="{{$item->zsm_code}}" {{ old('zsm_code') == $item->zsm_code ? 'selected' : '' }}>{{$item->zsm_code}}-{{$item->zsm_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
              
            </div>
            <div class="border-top">
                <div class="card-body">
                <button type="submit" class="btn btn-primary">
                    Submit
                </button>
                <a href="{{url('/admin/poc-master')}}" class="btn btn-warning">Cancel</a>
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
    $('.js-example-basic-multiple').select2({ width: '100%' },{placeholder: 'Select Item Name'});
    function adjustSelect2Height(selectElement) {
        let select2Container = selectElement.next('.select2-container');
        let selectionRendered = select2Container.find('.select2-selection__rendered');

        // Calculate the new height based on the content
        let newHeight = Math.max(selectionRendered.height() + 50, 32); // Minimum height is 32px

        // Apply the new height
        select2Container.find('.select2-selection--multiple').css('height', newHeight + 'px');
    }

    // Adjust height on page load for pre-selected values
    adjustSelect2Height($('#institution_id'));

    // Adjust height on change
    $('#institution_id').on('change', function () {
        adjustSelect2Height($(this));
    });
</script>
@endpush