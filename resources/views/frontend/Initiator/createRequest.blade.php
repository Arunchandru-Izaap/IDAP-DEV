@extends('layouts.frontend.app')
@section('content')
<style type="text/css">
#current_cycle_institutes_counter .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 28px;
    /*color: #ced4da;*/
}
#current_cycle_institutes_counter .select2-selection__arrow {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.6rem center;
    background-size: 16px 12px;
}
#current_cycle_institutes_counter .select2-selection__arrow  b
{
  display: none;
}
#current_cycle_institutes_counter span.select2
{
  display: inline-block;
  padding: 2px 5px 0px 5px;
  border-radius: 4px;
  border: solid 1px #ced4da;
  background-color: white;
  position: relative;
  background-repeat: no-repeat;
  background-position: right .75rem center;
  background-size: 16px 12px;
}
#current_cycle_institutes_counter .select2-container--default .select2-selection--single .select2-selection__arrow {
    width: 29px;
}
#current_cycle_institutes_counter .select2-container--default .select2-selection--single {
    background-color: #fff;
    border: 0;
    border-radius: 4px;
}
#current_cycle_institutes_counter .select2-selection__rendered[title="Select Institution"] {
    color: #ced4da;
}
#frmcopycounter .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 28px;
    /*color: #ced4da;*/
}
#frmcopycounter .select2-selection__rendered[title="Select Institution"] {
    color: #ced4da;
}
#frmcopycounter .select2-selection__arrow {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.2rem center;
    background-size: 16px 12px;
}
#frmcopycounter .select2-selection__arrow b
{
  display: none;
}
#frmcopycounter span.select2
{
  display: inline-block;
  padding: 2px 5px 0px 5px;
  border-radius: 4px;
  border: solid 1px #ced4da;
  background-color: white;
  position: relative;
  background-repeat: no-repeat;
  background-position: right .75rem center;
  background-size: 16px 12px;
}
#frmcopycounter .select2-container--default .select2-selection--single .select2-selection__arrow {
    width: 29px;
}
#frmcopycounter .select2-container--default .select2-selection--single {
    background-color: #fff;
    border: 0;
    border-radius: 4px;
}
</style>

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
		@if(Session::has('errors'))
			<div class="alert alert-danger mt-3" role="alert" >
			{{$errors->first('message')}}
			</div>
		@endif

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
                                            @if($data['intiator_button_status'] !='yes')
	                                        <div class="form-check-inline radio-btn">
                                            <input type="radio" class="form-check-input" name="optradio" checked="checked" id="invet">	
	                                          <label class="form-check-label radio-label-ct" target="invet" for="invet">
	                                            Initiate VQ Request
	                                          </label>
	
	                                        </div>
                                            @endif
                                           
	                                    @if($data['is_reinitiate'])
	                                        <div class="form-check-inline radio-btn">
	                                            <input type="radio" class="form-check-input" name="optradio" id="re">
	                                          <label class="form-check-label radio-label-ct" target="radioopt" for="re">
	                                            Re-Initiate VQ Request
	                                          </label>
	                                        </div>
                                        @endif

                                        <form id="frmradioopt" class="fromdate m-3">
                                          <h5>Select an option</h5>
                                          <div class="form-check-inline radio-btn">
	                                          <input type="radio" checked="checked" class="form-check-input radio-add" name="optradio" id="add_product" target="reinit">
	                                          <label class="form-check-label radio-label-ct" target="radioopt" for="add_product">
	                                            Add products
	                                          </label>
	                                        </div>

                                          <div class="form-check-inline radio-btn">
	                                          <input type="radio" class="form-check-input radio-add" name="optradio" id="add_new_pack" target="addpack">
	                                          <label class="form-check-label radio-label-ct" target="radioopt" for="add_new_pack">
                                              Add new pack
	                                          </label>
	                                        </div>

                                          <div class="form-check-inline radio-btn">
	                                          <input type="radio" class="form-check-input radio-add" name="optradio" id="add_counter" target="addcounter">
	                                          <label class="form-check-label radio-label-ct" target="radioopt" for="add_counter">
                                              Add counter
	                                          </label>
	                                        </div>

                                          @if($data['lockingEnabled']==false)
                                          <div class="form-check-inline radio-btn">
                                            <input type="radio" class="form-check-input radio-add" name="optradio" id="copy_counter" target="copycounter">
                                            <label class="form-check-label radio-label-ct" target="radioopt" for="copy_counter">
                                              Copy Counter
                                            </label>
                                          </div>
                                          @endif
                                        </form>
                                       
                                        <form id="frminvet" class="fromdate">
                                          <!-- <input id="datepicker"> -->
                                          <h5>Select Start Date and End Date</h5>
                                          <div class="start-end-date">
                                              <div class="input-group">
                                                  <div class="inputbox">
                                                      <input type="text" class="form-control required" id="startdate" value="{{$start_date}}" placeholder="Select Start Date" onkeydown="return false">
                                                      <label class="input-group-prepend m-0" for='startdate'>
                                                      <img src="../admin/images/cal.svg">
                                                      </label>
                                                  </div>
                                              </div>

                                              <div class="input-group">
                                                  <div class="inputbox">
                                                      <input type="text" class="form-control required" id="enddate" value="{{$end_date}}" placeholder="Select End Date" onkeydown="return false">
                                                      <label class="input-group-prepend m-0" for='enddate'>
                                                      <img src="../admin/images/cal.svg">
                                                      </label>
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

                                        <form id="frmreinit" class="fromdate formadd mr-3" method="POST" action="{{url('initiator/reinitiate','all')}}" style="border:orange; border-width:1px; border-style:solid; border-radius: 5px; padding: 20px">
                                          @csrf()

                                          <fieldset>
                                            <legend>Add Products Form</legend>
                                            <h5>Select a Workflow</h5>
                                            <div class="form-check-inline radio-btn">
                                              <input type="radio" class="form-check-input radio-workflow" name="skip_approval" id="annual_workflow" checked="checked"  checked="checked" value="0">
                                              <label class="form-check-label radio-label-ct disable-click" for="annual_workflow">
                                                Annual workflow
                                              </label>
                                            </div>

                                            <div class="form-check-inline radio-btn">
                                              <input type="radio" class="form-check-input radio-workflow" name="skip_approval" id="choose_approval" value="1">
                                              <label class="form-check-label radio-label-ct disable-click" for="choose_approval">
                                                Choose approvals
                                              </label>
                                            </div>

                                            <div class="p-3" id="select_approval_form">
                                              <div class="form-check-inline" style="border:orange; border-width:1px; border-style:solid; border-radius: 5px; padding: 20px">
                                                <div class="px-3">
                                                  <div>
                                                    <input type="checkbox" name="select_approval[]" value="1" id="level_1" class="mr-1"><label for="level_1">RSM</label>
                                                  </div>
                                                  <div>
                                                    <input type="checkbox" name="select_approval[]" value="2" id="level_2" class="mr-1"><label for="level_2">ZSM</label>
                                                  </div>
                                                  
                                                </div>

                                                <div class="px-3">
                                                  <div>
                                                    <input type="checkbox" name="select_approval[]" value="3" id="level_3" class="mr-1"><label for="level_3">NSM</label>
                                                  </div>
                                                  <div>
                                                    <input type="checkbox" name="select_approval[]" value="4" id="level_4" class="mr-1"><label for="level_4">SBU</label>
                                                  </div>
                                                </div>

                                                <div class="px-3">
                                                  
                                                  <div>
                                                    <input type="checkbox" name="select_approval[]" value="5" id="level_5" class="mr-1"><label for="level_5">Semi Cluster</label>
                                                  </div>
                                                  <div>
                                                    <input type="checkbox" name="select_approval[]" value="6" id="level_6" class="mr-1"><label for="level_6">Cluster</label>
                                                  </div>
                                                </div>
                                              </div>
                                            </div>

                                            <h5>Select Institution</h5>
                                            <div class="start-end-date">
                                                <div class="input-group">
                                                    <select name="institutes[]" id="institute_drop" multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="100" onchange="console.log(this.selectedOptions)"><!-- changed the multiselect-max-items="100" -->
                                                        @foreach($data['current_cycle_institutes'] as $item)
                                                        <option value="{{$item->institution_id}}" code="{{url('initiator/reinitiate',$item->institution_id)}}">{{$item->hospital_name}} - {{$item->institution_id}} - {{$item->city}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- <input id="datepicker"> -->
                                            <h5>Select Start Date and End Date</h5>
                                            <div class="start-end-date">
                                                <div class="input-group">
                                                    <div class="inputbox">
                                                        <input name="start_date" type="text" class="form-control required" id="startdate_re" placeholder="Select Start Date">
                                                        <label class="input-group-prepend m-0" for='startdate_re'>
                                                        <img src="../admin/images/cal.svg">
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="input-group">
                                                    <div class="inputbox">
                                                        <input type="text" name="end_date" class="form-control required" id="enddate_re" placeholder="Select End Date">
                                                        <label class="input-group-prepend" for='enddate_re'>
                                                        <img src="../admin/images/cal.svg">
                                                        </label>
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control required" id="flag" value="0" style="display:none">

                                            </div>
                                            <div class="w-100">
                                                <div id="error_message" class="error ajax_response" ></div>
                                                <div id="success_message" class="ajax_response"></div>
                                            </div>
                                            <div class="w-100">
                                              <button class="orange-btn d-none" id="re-initiate" type="submit">Re - INITIATE VQ REQUEST</button>
                                            </div>
                                          </fieldset>
                                        </form>

                                        <form id="frmaddpack" class="fromdate formadd mr-3" style="border:orange; border-width:1px; border-style:solid; border-radius: 5px; padding: 20px">

                                          <legend>Add Pack Form</legend>
                                          <h5>Select Old Pack</h5>
                                          <div class="start-end-date old-pack-group">
                                            <div class="input-group">
                                              <select name="field2" id="old_pack_drop">
                                                <option value="-1">Select Old Pack</option>
                                                @foreach($data['old_product_data'] as $item)
                                                <!-- <option value="{{$item['item_code']}}" data-item="{{json_encode($item)}}">{{$item['brand_name']}} [{{$item['type']}}]</option> -->
                                                <option value="{{$item['item_code']}}" data-item="{{json_encode($item)}}">{{$item['sap_itemcode']}} - [{{$item['brand_name']}}]</option>
                                                @endforeach
                                              </select>
                                            </div>
                                          </div>
                                          <h5>Select New Pack</h5>
                                          <div class="start-end-date old-pack-group">
                                            <div class="input-group">
                                              <select name="field2" id="new_pack_drop">
                                                <option value="-1">Select New Pack</option>
                                                @foreach($data['new_product_data'] as $item)
                                                <!-- <option value="{{$item->ITEM_CODE}}" data-item="{{json_encode($item)}}">{{$item->BRAND_NAME}} [{{$item->ITEM_TYPE}}]</option> -->
                                               <option value="{{$item->ITEM_CODE}}" data-item="{{json_encode($item)}}">{{$item->SAP_ITEMCODE}} - [{{$item->BRAND_NAME}}]</option>
                                               
                                                @endforeach
                                              </select>
                                            </div>
                                          </div>
                                          <div class="w-100">
                                            <button class="orange-btn d-none" id="add-new-pack">SUBMIT</button>
                                          </div>
                                        </form>
                                        <form id="frmcopycounter" class="fromdate formadd mr-3" style="border:orange; border-width:1px; border-style:solid; border-radius: 5px; padding: 20px">

                                          <legend>Copy Counter Form</legend>
                                          <h5>Select From Counter</h5>
                                          <div class="start-end-date old-pack-group">
                                            <div class="input-group">
                                              <select name="field2" id="from_counter_drop">
                                                <option value="-1">Select Institution</option>
                                                @foreach($data['currentCycleInstitutesNewCounterRt'] as $item)
                                                <option value="{{$item->institution_id}}" code="{{url('initiator/reinitiate',$item->institution_id)}}">{{$item->hospital_name}} - {{$item->institution_id}} - {{$item->city}}</option>
                                                @endforeach
                                              </select>
                                            </div>
                                          </div>
                                          <h5>Select To Counter</h5>
                                          <div class="start-end-date old-pack-group">
                                            <div class="input-group">
                                              <select name="field2" id="to_counter_drop" multiple multiselect-max-items="100" multiselect-search="true">
                                                <<!-- option value="-1">Select To Counter</option> -->
                                                @foreach($data['current_cycle_institutes'] as $item)
                                                <option value="{{$item->institution_id}}" code="{{url('initiator/reinitiate',$item->institution_id)}}">{{$item->hospital_name}} - {{$item->institution_id}} - {{$item->city}}</option>
                                                @endforeach
                                              </select>
                                            </div>
                                          </div>
                                          <div class="w-100">
                                            <button class="orange-btn d-none" id="add-copy-counter">SUBMIT</button>
                                          </div>
                                        </form>
                                        <form id="frmaddcounter" class="fromdate formadd mr-3" style="border:orange; border-width:1px; border-style:solid; border-radius: 5px; padding: 20px">
                                          <legend>Add counter</legend>
                                            <h5>Select Institution</h5>
                                            <div class="start-end-date">
                                                <div class="input-group">
                                                    <select name="newInstitutes[]" id="new_institutes_drop" multiple multiselect-search="true" multiselect-select-all="true" multiselect-max-items="3" onchange="console.log(this.selectedOptions)">
                                                        @foreach($data['new_institutes'] as $item)
                                                          <option value="{{$item->INST_ID}}" code="{{url('initiator/reinitiate',$item->INST_ID)}}">{{$item->INST_NAME}} - {{$item->INST_ID}} - {{$item->CITY}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            @if(count($data['new_institutes'])> 0)
                                            <div class="form-check px-4 mt-2">
                                              <input type="checkbox" class="form-check-input" id="addCounterTransferCheck"onclick="toggleCurrentCycleInstitutesCounter()">
                                              <label class="form-check-label" for="addCounterTransferCheck">Rate Transfer</label>
                                            </div>
                                            <div class="d-none" id="current_cycle_institutes_counter">
                                              <h5>Copy rate from institution</h5>
                                              <div class="start-end-date">
                                                <div class="input-group">
                                                      <select name="newInstitutesRateTransfer" id="new_institutes_rt_drop" onchange="console.log(this.selectedOptions)">
                                                          <option value="-1">Select Institution</option>
                                                          @foreach($data['currentCycleInstitutesNewCounterRt'] as $item)
                                                           <option value="{{$item->institution_id}}" code="{{url('initiator/reinitiate',$item->institution_id)}}">{{$item->hospital_name}} - {{$item->institution_id}} - {{$item->city}}</option>
                                                          @endforeach
                                                      </select>
                                                  </div>
                                              </div>
                                            </div>
                                            @endif
                                            <!-- <input id="datepicker"> -->
                                            <h5>Select Start Date and End Date</h5>
                                            <div class="start-end-date">
                                                <div class="input-group">
                                                    <div class="inputbox">
                                                        <input name="start_date" type="text" class="form-control required" id="new_counter_startdate" placeholder="Select Start Date">
                                                        <label class="input-group-prepend m-0" for='new_counter_startdate'>
                                                        <img src="../admin/images/cal.svg">
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="input-group">
                                                    <div class="inputbox">
                                                        <input type="text" name="end_date" class="form-control required" id="new_counter_enddate" placeholder="Select End Date">
                                                        <label class="input-group-prepend" for='new_counter_enddate'>
                                                        <img src="../admin/images/cal.svg">
                                                        </label>
                                                    </div>
                                                </div>
                                                <input type="text" class="form-control required" id="flag" value="0" style="display:none">

                                            </div>
                                            <div class="w-100">
                                              <button class="orange-btn" id="add_new_counter_btn">Submit</button>
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
                        <style type="text/css" media="screen">
                        	.copy-center{
	                        	position:inherit;
	                        	padding: 15px 0;
                        	}
                        </style>
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>  
                        <script src="{{asset('frontend/js/select2.js')}}"></script>
        <script>
            $('#add_new_counter_btn').hide();
        $('#old_pack_drop').select2({placeholder: 'Select Old Pack'});
        $('#new_pack_drop').select2({placeholder: 'Select New Pack'});
        $('#from_counter_drop').select2({placeholder: 'Select From Counter'});
        //$('#to_counter_drop').select2({placeholder: 'Select To Counter'});
        $('#new_institutes_rt_drop').select2({placeholder: 'Select Rate Transfer Institution'});
        
        $('#select_approval_form').hide();
        $('input[name="skip_approval"]').click(function(){

          let skip_approval = $('input[name="skip_approval"]:checked').val();
          
          if(skip_approval == 1){
            $('#select_approval_form').show();
          }else{
            $('#select_approval_form').hide();
          }
        })

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
                if(response['result'] == true){
                    //var start = moment(response["dates"]['contract_start_date']).format('DD/MM/YYYY');  //commented on 02/05/2024 to get current date
                    /*get current year starts*/
                    var currentDate = new Date();
                    var year = currentDate.getFullYear(); 
                    var month = currentDate.getMonth() + 1; 
                    var day = currentDate.getDate(); 
                    var formattedDate = year + '-' + (month < 10 ? '0' : '') + month + '-' + (day < 10 ? '0' : '') + day;
                    /*get current year ends*/
                    var start = moment(formattedDate).format('DD/MM/YYYY'); 
                    var end = moment(response["dates"]['contract_end_date']).format('DD/MM/YYYY');  
                    $('#startdate_re').val(start);
                    $('#enddate_re').val(end);
                    $('#flag').val('0');
                    $('#re-initiate').removeClass( "d-none" )
                }else{
                    $('#flag').val('1');
                }
            });
        });

        $('#new_institutes_drop, #new_counter_startdate, #new_counter_enddate').on('change', function(){
          let new_institutes_drop = $("#new_institutes_drop").val();
          let new_counter_startdate = $("#new_counter_startdate").val();
          let new_counter_enddate = $("#new_counter_enddate").val();
          
          if(new_institutes_drop.length > 0 && $("#new_counter_startdate").val() != '' && $("#new_counter_enddate").val() != ''){
            $('#add_new_counter_btn').show();
          }else{
            $('#add_new_counter_btn').hide();
          }
        })
        
        $('#add_new_counter_btn').click(function(e){
          e.preventDefault();
          if ($('#addCounterTransferCheck').is(':checked') && $('#new_institutes_rt_drop').val() == -1) {
            alert('Please select rate transfer institution');
            return;
          }
          var startdate = moment($("#new_counter_startdate").val(),'DD/MM/YYYY').format('MM/DD/YYYY');
          var enddate = moment($("#new_counter_enddate").val(),'DD/MM/YYYY').format('MM/DD/YYYY');
          var settings = {
            "url": "/initiator/add_new_counter",
            "method": "POST",
            "timeout": 0,
            "headers": {
            "Accept-Language": "application/json",
            },
            "data": {
            "_token": "{{ csrf_token() }}",
            "institution_id_arr": $("#new_institutes_drop").val(),
            "from": startdate,
            "to": enddate,
            'rateTransfer':$('#addCounterTransferCheck').is(':checked'),
            'rateTransferInstitution': $('#new_institutes_rt_drop').val()
            }
          };
          $.ajax(settings).done(function (response) {
            if(response.success){
              $('#startval').empty();//added to empty the date if multiple click happens
              $('#enddateval').empty();//added to empty the date if multiple click happens
              $('#startval').append($("#new_counter_startdate").val());
              $('#enddateval').append($("#new_counter_enddate").val());
              $('#myModal').modal('show');
              $('.dymdate').show();
            }else{
              alert("Something went wrong!");
            }
          });
          
        })

        // Add New Pack Script
        $('#old_pack_drop').on('change', function(e) {
          e.preventDefault();
          if(($(this).val() != "" && $('#new_pack_drop').val() != "") && ($(this).val() != "-1" && $('#new_pack_drop').val() != "-1")){
            $("#add-new-pack").removeClass("d-none");
          }
          else{
            $("#add-new-pack").addClass("d-none");
          }
        });
        $('#new_pack_drop').on('change', function(e) {
          e.preventDefault();
          if(($(this).val() != "" && $('#old_pack_drop').val() != "") && ($(this).val() != "-1" && $('#old_pack_drop').val() != "-1")){
            $("#add-new-pack").removeClass("d-none");
          }
          else{
            $("#add-new-pack").addClass("d-none");
          }
        });
        $('#add-new-pack').click(function(e) {
          e.preventDefault();
          if($('#old_pack_drop option:selected').val() == "" || $('#new_pack_drop option:selected').val() == "" || $('#old_pack_drop option:selected').val() == "-1" || $('#new_pack_drop option:selected').val() == "-1") {
            $("#error_message").show().html("Old Pack and New Pack values are Required");
          } else {
            $('#add-new-pack').attr('disabled',true);
            let selectedNewPackData = $('#new_pack_drop option:selected').data('item');
            let composition = selectedNewPackData.COMPOSITION;
            let hsnCode = selectedNewPackData.HSN_CODE;
            // console.log(hsnCode);
            if (!composition || !hsnCode) {
              if (confirm("Please check with SAP Master team to update the HSN CODE and COMPOSITION of new item")) {
                $('#add-new-pack').attr('disabled', false);
                return false;
              } else {
                $('#add-new-pack').attr('disabled', false);
                return false;
              }
            }
            // console.log('sss');
            // return false;
            var settings = {
              "url": "/initiator/reinitiateVQWithNewPack",
              "method": "POST",
              "timeout": 0,
              "headers": {
                "Accept-Language": "application/json",
              },
              "data": {
                "_token": "{{ csrf_token() }}",
                "oldpack": $('#old_pack_drop option:selected').data('item'),
                "newpack": $('#new_pack_drop option:selected').data('item'),
              }
            };
            $.ajax(settings).done(function (response) {
              if(response.state == true)//added on 29042024 for checking the pending item code
              {
                $('.dymdate').hide();
                $('#myModal').modal('show');
              }
              else//added on 24042024 for checking the pending item code and display alert
              {
                $('#add-new-pack').attr('disabled', false);
                alert(response.message);
              }
            });
          }
        });
        //copy counter script
        $('#from_counter_drop').on('change', function(e) {
          e.preventDefault();
          if(($(this).val() != "" && $('#to_counter_drop').val() != "") && ($(this).val() != "-1" && $('#to_counter_drop').val() != "-1")){
            $("#add-copy-counter").removeClass("d-none");
          }
          else{
            $("#add-copy-counter").addClass("d-none");
          }
        });
        $('#to_counter_drop').on('change', function(e) {
          e.preventDefault();
          if(($(this).val() != "" && $('#from_counter_drop').val() != "") && ($(this).val() != "-1" && $('#from_counter_drop').val() != "-1")){
            $("#add-copy-counter").removeClass("d-none");
          }
          else{
            $("#add-copy-counter").addClass("d-none");
          }
        });
        $('#add-copy-counter').click(function(e) {
          e.preventDefault();
          if($('#from_counter_drop option:selected').val() == "" || $('#to_counter_drop option:selected').val() == "" || $('#from_counter_drop option:selected').val() == "-1" || $('#to_counter_drop option:selected').val() == "-1") {
            $("#error_message").show().html("From counter and To counter values are Required");
          } /*else if($('#from_counter_drop option:selected').val() == $('#to_counter_drop option:selected').val()){
            alert('From Counter and To Counter cannot be same')
          }*/ else {
            $('#add-copy-counter').attr('disabled',true)
            var settings = {
            "url": "/initiator/reinitiateVQCopyCounter",
            "method": "POST",
            "timeout": 0,
            "headers": {
            "Accept-Language": "application/json",
            },
            "data": {
            "_token": "{{ csrf_token() }}",
            "fromcounter": $('#from_counter_drop option:selected').val(),
            "tocounter": $('#to_counter_drop').val(),
            }
            };
            $.ajax(settings).done(function (response) {
              if(response.state == true)//added on 29042024 for checking the pending item code
              {
                $('.dymdate').hide();
                $('#myModal').modal('show');
              }
              else//added on 24042024 for checking the pending item code and display alert
              {
                $('#add-copy-counter').attr('disabled', false);
                alert(response.message);
              }
            });
          }
        });
    </script>
<script>
var style = document.createElement('style');
style.setAttribute("id","multiselect_dropdown_styles");
style.innerHTML = `
.select2 {
  width: 100% !important;
}
.multiselect-dropdown{
  display: inline-block;
  padding: 2px 5px 0px 5px;
  border-radius: 4px;
  border: solid 1px #ced4da;
  background-color: white;
  position: relative;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right .75rem center;
  background-size: 16px 12px;
}
.multiselect-dropdown span.optext, .multiselect-dropdown span.placeholder{
  margin-right:0.5em; 
  margin-bottom:2px;
  padding:1px 0; 
  border-radius: 4px; 
  display:inline-block;
}
.multiselect-dropdown span.optext{
  background-color:lightgray;
  padding:1px 0.75em; 
}
.multiselect-dropdown span.optext .optdel {
  float: right;
  margin: 0 -6px 1px 5px;
  font-size: 0.7em;
  margin-top: 2px;
  cursor: pointer;
  color: #666;
}
.multiselect-dropdown span.optext .optdel:hover { color: #c66;}
.multiselect-dropdown span.placeholder{
  color:#ced4da;
}
.multiselect-dropdown-list-wrapper{
  box-shadow: gray 0 3px 8px;
  z-index: 100;
  padding:2px;
  border-radius: 4px;
  border: solid 1px #ced4da;
  display: none;
  margin: -1px;
  position: absolute;
  top:0;
  left: 0;
  right: 0;
  background: white;
}
.multiselect-dropdown-list-wrapper .multiselect-dropdown-search{
  margin-bottom:5px;
}
.multiselect-dropdown-list{
  padding:2px;
  height: 15rem;
  overflow-y:auto;
  overflow-x: hidden;
}
.multiselect-dropdown-list::-webkit-scrollbar {
  width: 6px;
}
.multiselect-dropdown-list::-webkit-scrollbar-thumb {
  background-color: #bec4ca;
  border-radius:3px;
}

.multiselect-dropdown-list div{
  padding: 5px;
}
.multiselect-dropdown-list input{
  height: 1.15em;
  width: 1.15em;
  margin-right: 0.35em;  
}
.multiselect-dropdown-list div.checked{
}
.multiselect-dropdown-list div:hover{
  background-color: #ced4da;
}
.multiselect-dropdown span.maxselected {width:100%;}
.multiselect-dropdown-all-selector {border-bottom:solid 1px #999;}
`;
document.head.appendChild(style);

function MultiselectDropdown(options){
  var config={
    search:true,
    height:'15rem',
    placeholder:'Select Institution',
    txtSelected:'selected',
    txtAll:'All',
    txtRemove: 'Remove',
    txtSearch:'search',
    ...options
  };
  function newEl(tag,attrs){
    var e=document.createElement(tag);
    if(attrs!==undefined) Object.keys(attrs).forEach(k=>{
      if(k==='class') { Array.isArray(attrs[k]) ? attrs[k].forEach(o=>o!==''?e.classList.add(o):0) : (attrs[k]!==''?e.classList.add(attrs[k]):0)}
      else if(k==='style'){  
        Object.keys(attrs[k]).forEach(ks=>{
          e.style[ks]=attrs[k][ks];
        });
       }
      else if(k==='text'){attrs[k]===''?e.innerHTML='&nbsp;':e.innerText=attrs[k]}
      else e[k]=attrs[k];
    });
    return e;
  }

  
  document.querySelectorAll("select[multiple]").forEach((el,k)=>{
    
    var div=newEl('div',{class:'multiselect-dropdown',style:{width:"100%"}});
    el.style.display='none';
    el.parentNode.insertBefore(div,el.nextSibling);
    var listWrap=newEl('div',{class:'multiselect-dropdown-list-wrapper'});
    var list=newEl('div',{class:'multiselect-dropdown-list',style:{height:config.height}});
    var search=newEl('input',{class:['multiselect-dropdown-search'].concat([config.searchInput?.class??'form-control']),style:{width:'100%',display:el.attributes['multiselect-search']?.value==='true'?'block':'none'},placeholder:config.txtSearch});
    listWrap.appendChild(search);
    div.appendChild(listWrap);
    listWrap.appendChild(list);

    el.loadOptions=()=>{
      list.innerHTML='';
      
      if(el.attributes['multiselect-select-all']?.value=='true'){
        var op=newEl('div',{class:'multiselect-dropdown-all-selector'})
        var ic=newEl('input',{type:'checkbox'});
        op.appendChild(ic);
        op.appendChild(newEl('label',{text:config.txtAll}));
  
        op.addEventListener('click',()=>{
          op.classList.toggle('checked');
          op.querySelector("input").checked=!op.querySelector("input").checked;
          
          var ch=op.querySelector("input").checked;
          list.querySelectorAll(":scope > div:not(.multiselect-dropdown-all-selector)")
            .forEach(i=>{if(i.style.display!=='none'){i.querySelector("input").checked=ch; i.optEl.selected=ch}});
  
          el.dispatchEvent(new Event('change'));
        });
        ic.addEventListener('click',(ev)=>{
          ic.checked=!ic.checked;
        });
        el.addEventListener('change', (ev)=>{
          let itms=Array.from(list.querySelectorAll(":scope > div:not(.multiselect-dropdown-all-selector)")).filter(e=>e.style.display!=='none')
          let existsNotSelected=itms.find(i=>!i.querySelector("input").checked);
          if(ic.checked && existsNotSelected) ic.checked=false;
          else if(ic.checked==false && existsNotSelected===undefined) ic.checked=true;
        });
  
        list.appendChild(op);
      }

      Array.from(el.options).map(o=>{
        var op=newEl('div',{class:o.selected?'checked':'',optEl:o})
        var ic=newEl('input',{type:'checkbox',checked:o.selected});
        op.appendChild(ic);
        op.appendChild(newEl('label',{text:o.text}));

        op.addEventListener('click',()=>{
          op.classList.toggle('checked');
          op.querySelector("input").checked=!op.querySelector("input").checked;
          op.optEl.selected=!!!op.optEl.selected;
          el.dispatchEvent(new Event('change'));
        });
        ic.addEventListener('click',(ev)=>{
          ic.checked=!ic.checked;
        });
        o.listitemEl=op;
        list.appendChild(op);
      });
      div.listEl=listWrap;

      div.refresh=()=>{
        div.querySelectorAll('span.optext, span.placeholder').forEach(t=>div.removeChild(t));
        var sels=Array.from(el.selectedOptions);
        if(sels.length>(el.attributes['multiselect-max-items']?.value??5)){
          div.appendChild(newEl('span',{class:['optext','maxselected'],text:sels.length+' '+config.txtSelected}));          
        }
        else{
          sels.map(x=>{
            var c=newEl('span',{class:'optext',text:x.text, srcOption: x});
            if((el.attributes['multiselect-hide-x']?.value !== 'true'))
              c.appendChild(newEl('span',{class:'optdel',text:'🗙',title:config.txtRemove, onclick:(ev)=>{c.srcOption.listitemEl.dispatchEvent(new Event('click'));div.refresh();ev.stopPropagation();}}));

            div.appendChild(c);
          });
        }
        if(0==el.selectedOptions.length) div.appendChild(newEl('span',{class:'placeholder',text:el.attributes['placeholder']?.value??config.placeholder}));
      };
      div.refresh();
    }
    el.loadOptions();
    
    search.addEventListener('input',()=>{
      list.querySelectorAll(":scope div:not(.multiselect-dropdown-all-selector)").forEach(d=>{
        var txt=d.querySelector("label").innerText.toUpperCase();
        d.style.display=txt.includes(search.value.toUpperCase())?'block':'none';
      });
    });

    div.addEventListener('click',()=>{
      div.listEl.style.display='block';
      search.focus();
      search.select();
    });
    
    document.addEventListener('click', function(event) {
      if (!div.contains(event.target)) {
        listWrap.style.display='none';
        div.refresh();
      }
    });    
  });
}

window.addEventListener('load',()=>{
  MultiselectDropdown(window.MultiselectDropdownOptions);
});
$('#frmreinit').on('submit', function(e) {//added for validation when choose approval is selected with selecting approval level
    if ($('#choose_approval').is(':checked') && $('input[name="select_approval[]"]:checked').length === 0) {
        alert('Please select at least one approval level.');
        e.preventDefault();
    }
});
function toggleCurrentCycleInstitutesCounter() {
  var checkBox = document.getElementById("addCounterTransferCheck");
  var counterDiv = document.getElementById("current_cycle_institutes_counter");

  if (checkBox.checked) {
      counterDiv.classList.remove("d-none");
  } else {
      counterDiv.classList.add("d-none");
  }
  $('#new_institutes_rt_drop').val('-1').change()
}
</script>
@endsection

