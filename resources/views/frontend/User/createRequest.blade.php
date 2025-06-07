@extends('layouts.frontend.app')
@section('content')


<div id="page-content-wrapper">
                <!-- Top navigation-->
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                            <ul class="navbar-nav dashboard-nav">
                                <li class="nav-item active"><a class="nav-link" href="{{route('approver_dashboard')}}"> <img src="../admin/images/back.svg">Create Request</a></li>
                            </ul>

                            <ul class="d-flex ml-auto user-name">
                                <li>
                                    <h3>{{Session::get('emp_name')}}</h3>
                                    <p>Initiator</p>
                                </li>

                                <li>
                                    <img src="{{ asset('admin/images/Sun_Pharma_logo.png') }}">
                                </li>
                            </ul>
                        </div>
                        
                    </div>

                </nav>
                <ul class="bradecram-menu">
                    <li><a href="{{route('approver_dashboard')}}">
                        Home
                    </a></li>
                    <li class="active">
                        <a href="">
                            Create Request
                        </a>
                    </li>
                </ul>
                <!-- Page content-->
                <div class="container-fluid">
                    <!-- <h1 class="mt-4">Simple Sidebar</h1> -->
                    <div class="row">
                        <div class="col">
                            <div class="actions-dashboard">
                                <h5>Select the Request Type you want to create</h5>


                                <div class="create-new-request">
                                    <div class="btn-selct d-flex">
                                        <button class="showsingle active" target="vq">Voluntary Request Quotation (VQ)</button>
                                        <button class="showsingle d-none" target="rfq">Request for Quotation (RFQ)</button>
                                    </div>

                                    <div id="sectionvq" class="vq targetDiv">
                                        <h5>Select Action for Voluntary Request Quotation</h5>
                                        <div class="form-check-inline radio-btn">
                                            <input type="radio" class="form-check-input" name="optradio" checked="checked" id="invet">
                                          <label class="form-check-label radio-label-ct" target="invet" for="invet">
                                            Initiate VQ Request
                                          </label>

                                        </div>
                                        <div class="form-check-inline radio-btn">
                                            <input type="radio" class="form-check-input" name="optradio" id="re">
                                          <label class="form-check-label radio-label-ct" target="reinit" for="re">
                                            Re-Initiate VQ Request
                                          </label>
                                        </div>
                                         <form id="frminvet" class="fromdate">
                                                <!-- <input id="datepicker"> -->
                                                <h5>Select Start Date and End Date</h5>
                                                <div class="start-end-date">
                                                    <div class="input-group">
                                                        <div class="inputbox">
                                                            <input type="text" class="form-control required" id="startdate" placeholder="Select Start Date" onkeydown="return false">
                                                            <div class="input-group-prepend" for='startdate'>
                                                            <img src="../admin/images/cal.svg">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="input-group">
                                                        <div class="inputbox">
                                                            <input type="text" class="form-control required" id="enddate" placeholder="Select End Date" onkeydown="return false">
                                                            <div class="input-group-prepend">
                                                            <img src="../admin/images/cal.svg">
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="w-100">
                                                    <div id="error_message" class="error ajax_response" ></div>
                                                    <div id="success_message" class="ajax_response"></div>
                                                </div>
                                                <div class="w-100">
                                                <button class="orange-btn" id="initiate">INITIATE VQ REQUEST</button>
                                                </div>
                                        </form>
                                        <form id="frmreinit" class="fromdate">

                                                <h5>Select Institution</h5>
                                                <div class="start-end-date">
                                                    <div class="input-group">
                                                        <div class="inputbox">
                                                          <select id="institute_drop">
                                                          <option value="">Select Institution</option>
                                                              @foreach($data['reinit_data'] as $item)
                                                                <option value="{{$item->INST_ID}}" code="{{url('initiator/reinitiate',$item->INST_ID)}}">{{$item->INST_NAME}}</option>
                                                              @endforeach
                                                          </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- <input id="datepicker"> -->
                                                <h5>Select Start Date and End Date</h5>
                                                <div class="start-end-date">
                                                    <div class="input-group">
                                                        <div class="inputbox">
                                                            <input type="text" class="form-control required" id="startdate_re" placeholder="Select Start Date">
                                                            <div class="input-group-prepend" for='startdate'>
                                                            <img src="../admin/images/cal.svg">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="input-group">
                                                        <div class="inputbox">
                                                            <input type="text" class="form-control required" id="enddate_re" placeholder="Select End Date">
                                                            <div class="input-group-prepend">
                                                            <img src="../admin/images/cal.svg">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="text" class="form-control required" id="flag" value="0" style="display:none">

                                                </div>
                                                <div class="w-100">
                                                    <div id="error_message" class="error ajax_response" ></div>
                                                    <div id="success_message" class="ajax_response"></div>
                                                </div>
                                                <div class="w-100">
                                                <button class="orange-btn" id="re-initiate">Re - INITIATE VQ REQUEST</button>
                                                </div>
                                        </form>
                                    </div><!-- vq close -->

                                    <div id="sectionrfq" class="rfq targetDiv">
                                        <h5>Select Action for Voluntary Request Quotation</h5>
                                        <div class="form-check-inline radio-btn">
                                            <input type="radio" class="form-check-input" name="optradio" checked="checked" id="requestct">
                                          <label class="form-check-label radio-label-ct" for="requestct">
                                            Initiate VQ Request
                                          </label>

                                        </div>
                                          
                                        <div class="form-check-inline radio-btn">
                                            <input type="radio" class="form-check-input" name="optradio" id="requestct-re">
                                          <label class="form-check-label radio-label-ct" for="requestct-re">
                                            Re-Initiate VQ Request
                                          </label>
                                        </div>

                                        <h5>Select Start Date and End Date</h5>

                                        <div class="start-end-date">
                                            <form>
                                                <!-- <input id="datepicker"> -->
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="startdaterev" placeholder="Select Start Date">
                                                    <div class="input-group-prepend" for='startdate'>
                                                    <img src="../admin/images/cal.svg">
                                                    </div>
                                                </div>

                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="enddaterev" placeholder="Select End Date">
                                                    <div class="input-group-prepend">
                                                    <img src="../admin/images/cal.svg">
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <button class="orange-btn">Request for Quotation (RFQ)</button>
                                    </div>

                                </div>

                            </div>

                            
                        </div>
    <style type="text/css">
    	.copyright{
	    	position:initial;
	    	padding: 15px 0;
    	}
    </style>
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>  
    <script>
        $('select').on('change', function() {
            var settings = {
                "url": "/initiator/parent_vq_checker",
                "method": "GET",
                "timeout": 0,
                "headers": {
                "Accept-Language": "application/json",
                },
                "data": {
                "_token": "{{ csrf_token() }}",
                "institution_id": this.value
                }
            };
            $.ajax(settings).done(function (response) {
                console.log(response);
                if(response['result'] == true){
                    var start = moment(response["dates"]['contract_start_date']).format('DD/MM/YYYY');  
                    var end = moment(response["dates"]['contract_end_date']).format('DD/MM/YYYY');  
                    $('#startdate_re').val(start);
                    $('#enddate_re').val(end);
                    $('#flag').val('0');
                }else{
                    $('#flag').val('1');
                }
            });
        });
        $('#re-initiate').click(function(e) {
            e.preventDefault();
            var flag = $('#flag').val();
            if(flag == 0){
                var startdate = moment($("#startdate_re").val(),'DD/MM/YYYY').format('MM/DD/YYYY');
                var enddate =moment($("#enddate_re").val(),'DD/MM/YYYY').format('MM/DD/YYYY');

                window.location.replace($('option:selected').attr('code')+'?startdate='+startdate+'&enddate='+enddate);
            }else{
                var startdate = moment($("#startdate_re").val(),'DD/MM/YYYY').format('MM/DD/YYYY');
                var enddate =moment($("#enddate_re").val(),'DD/MM/YYYY').format('MM/DD/YYYY');

                if(startdate == "" || enddate == "" ) {
                    $("#error_message").show().html("Start Date and End Date are Required");
                } else {
                var settings = {
                "url": "/initiator/reinitiateNewVQ",
                "method": "POST",
                "timeout": 0,
                "headers": {
                "Accept-Language": "application/json",
                },
                "data": {
                "_token": "{{ csrf_token() }}",
                "institution_code": $('option:selected').val(),
                "from":startdate,
                "to":enddate
                }
                };
                $.ajax(settings).done(function (response) {
                    console.log(response);
                    $('#startval').append(startdate);
                    $('#enddateval').append(enddate);
                    $('#myModal').modal('show');
                });
            }
            }
        });
    </script>
@endsection

