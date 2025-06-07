@extends('layouts.admin.app')
@section('content')
<div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
        <div class="card">
            <form class="form-horizontal" method="post" action="{{route('max-discount-cap-store')}}">
                @csrf
                <div class="card-body">
                    <h4 class="card-title">Max Discount Cap Info</h4>
                    
                    <div class="form-group row">
                    <label for="divCode" class="col-sm-3 text-end control-label col-form-label">Division Code</label>
                    <div class="col-sm-9">
                        <select name="div_id" id="div_id" class="form-select" required>
                            <option value="">Select Division Code</option>
                            @foreach($divArr as $div)
                                <option value="{{$div->div_id}}">{{$div->div_id}}</option>
                            @endforeach
                        </select>
                    </div>
                    </div>

                    <div class="form-group row">
                    <label for="max_discount" class="col-sm-3 text-end control-label col-form-label">Max Discount</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="max_discount" name="max_discount" placeholder="Max discount" required="">
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