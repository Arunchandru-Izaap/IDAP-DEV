@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('license-store')}}" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <h4 class="card-title">License Info</h4>

                    <div class="form-group row">
                    <label for="institutionId" class="col-sm-3 text-end control-label col-form-label">Institution Code</label>
                    <div class="col-sm-9">
                        <input type="hidden" name="institution_id" id="institution_id" value="{{old('institution_id')}}">
                        <select class="form-select" aria-label="Default select example" id="select_institution" onchange="institutionNameHandler(this.value)">
                            <option >Select Institution Code</option>
                            @foreach($vq as $v)
                            <option value="{{$v->hospital_name}}" <?php echo old('institution_id') == $v->institution_id ? "selected" : "";?> >{{$v->institution_id}}</option> 
                            @endforeach
                            
                        </select>
                    </div>
                    </div>

                    <div class="form-group row">
                    <label for="institutionName" class="col-sm-3 text-end control-label col-form-label">Institution Name</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="selected_institution_name" disabled placeholder="Institution Name" value="{{ old('institution_name') }}">
                        <input type="hidden" name="institution_name" id="institution_name" value="{{ old('institution_name') }}">
                    </div>
                    </div>
                    
                    <div class="form-group row">
                    <label for="gst" class="col-sm-3 text-end control-label col-form-label">GST</label>
                    <div class="col-sm-9">
                    <input type="file" class="form-control" id="gst" name="gst" value="{{old('gst')}}">
                    </div>
                    </div>

                    <div class="form-group row">
                    <label for="form20" class="col-sm-3 text-end control-label col-form-label">Form 20</label>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" id="form20" name="form20">
                    </div>
                    </div>

                    <div class="form-group row">
                    <label for="form20_expiry_date" class="col-sm-3 text-end control-label col-form-label">Form 20 expiry date</label>
                    <div class="col-sm-9">
                        <input type="date" id="form20_expiry_date" name="form20_expiry_date" value="{{old('form20_expiry_date')}}">
                    </div>
                    </div>

                    <div class="form-group row">
                    <label for="form21" class="col-sm-3 text-end control-label col-form-label">Form 21</label>
                    <div class="col-sm-9">
                        <input type="file" class="form-control" id="form21" name="form21">
                    </div>
                    </div>
                   
                    <div class="form-group row">
                    <label for="form21_expiry_date" class="col-sm-3 text-end control-label col-form-label">Form 21 expiry date</label>
                    <div class="col-sm-9">
                        <input type="date" id="form21_expiry_date" name="form21_expiry_date"  value="{{old('form21_expiry_date')}}">
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
<script>
    function institutionNameHandler(institution_name){
        
        var selectElement = document.getElementById("select_institution");
        var selectedOption = selectElement.options[selectElement.selectedIndex];
        var placeholderText = selectedOption.textContent;
        $('#institution_name').val(institution_name);
        $('#institution_id').val(placeholderText);
        $('#selected_institution_name').val(institution_name)
    }
    
</script>
@endsection