<section class="">
  <div class="d-flex" id="wrapper">
    <!-- Sidebar-->
    <div class="" id="sidebar-wrapper">
        <div class="sidebar-heading">
<!--           <img src="{{ asset('admin/images/in-logo.svg') }}" alt="logo"> -->
          <img src="{{ asset('admin/images/logopm.png') }}" alt="logo">
          
        </div>
        
        <div class="list-group list-group-flush">
              <ul>
                @if(Session::get('type')=='initiator') 
                 <li class="{{ (Route::currentRouteNamed('initiator_dashboard') ? 'active' : '')}}">
                  <a class="" href="{{route('initiator_dashboard')}}"><img src="{{ asset('admin/images/home.svg') }}" alt="">Home</a>
                </li>
                <li class="{{ (Route::currentRouteNamed('create_request') ? 'active' : '')}}">
                  <a class="" href="{{route('create_request')}}"><img src="{{ asset('admin/images/plus.svg') }}" alt="">Initiate Request</a>
                </li>
                <li class="{{ (Route::currentRouteNamed('initiator_listing') ? 'active' : '')}} {{(Route::currentRouteNamed('initiatorDetails') ? 'active' : '')}} {{(Route::currentRouteNamed('activityDetailsInitiator') ? 'active' : '')}} {{(Route::currentRouteNamed('modify_stockist') ? 'active' : '')}}{{(Route::currentRouteNamed('edit_stockist') ? 'active' : '')}} {{(Route::currentRouteNamed('view_poc') ? 'active' : '')}}">
                  <a class="" href="{{route('initiator_listing')}}"><img src="{{ asset('admin/images/eye.svg') }}" alt="">View Request</a>
                </li>
                <!-- <li class="{{ (Route::currentRouteNamed('genereate_request') ? 'active' : '')}}">
                  <a class="" href="{{route('genereate_request')}}"><img src="{{ asset('admin/images/graph.svg') }}" alt="">Generate VQ for New Product</a>
                </li> -->
                <li id="initiator_generate">
                  <a href="#generateMenu" data-toggle="collapse" aria-expanded="{{ Route::is('genereate_request','genereate_request_existing') ? 'true' : 'false' }}" class="menu-item dropdown-toggle {{ Route::is('genereate_request','genereate_request_existing') ? 'admin_active' : '' }}" id="generate_main_menu">
                      <img src="{{ asset('admin/images/graph.svg') }}" alt=""> Generate <br> VQ
                  </a>
                  <ul class="collapse list-unstyled {{ Route::is('genereate_request','genereate_request_existing') ? 'show1' : '' }}" id="generateMenu">
                      <li class="{{ Route::is('genereate_request') ? 'active' : '' }}">
                          <a href="{{ route('genereate_request') }}">
                              Generate VQ for New Product
                          </a>
                      </li>
                      <li class="{{ Route::is('genereate_request_existing') ? 'active' : '' }}">
                          <a href="{{ route('genereate_request_existing') }}"> Generate VQ for Existing Product</a>
                      </li>
                  </ul>
                </li>
                <li id="initiator_report">
                  <a href="#productwisereportMenu" data-toggle="collapse" aria-expanded="{{ Route::is('product_cumulative_report') ? 'true' : 'false' }}" class="menu-item dropdown-toggle {{ Route::is('product_cumulative_report') ? 'admin_active' : '' }}" id="report_main_menu">
                      <img src="{{ asset('admin/images/graph.svg') }}" alt=""> Report
                  </a>
                  <ul class="collapse list-unstyled {{ Route::is('product_cumulative_report') ? 'show1' : '' }}" id="productwisereportMenu">
                      <li class="{{ Route::is('product_cumulative_report') ? 'active' : '' }}">
                          <a href="{{ route('product_cumulative_report') }}"> Product wise cumulative report</a>
                      </li>
                  </ul>
                </li>
                <!-- Admin Menu -->
                <li id="initiator_admin">
                  <a href="#adminMenu" data-toggle="collapse" aria-expanded="{{ Route::is('initiator-employee-list', 'initiator-employee-edit','initiator-employee','initiator-signature-list','initiator-signature','initiator-edit','initiator-poc-master-list','initiator-poc-master','initiator-poc-master-edit','initiator-max-discount-cap-list','initiator-max-discount-cap','initiator-max-discount-cap-edit','initiator-duration-list','initiator-duration','initiator-duration-edit','initiator-ignored-institutions-list','initiator-ignored-institutions','initiator-ignored-institutions-edit', 'initiate_date', 'initiator-discount-margin-list', 'initiator-discount-margin-edit', 'initiator-discount-margin', 'initiator-approval-email-schedule-list', 'initiator-approval-email-schedule-edit', 'initiator-approval-email-schedule', 'initiator_bulk_counter_update') ? 'true' : 'false' }}" class="menu-item dropdown-toggle {{ Route::is('initiator-employee-list', 'initiator-employee-edit','initiator-employee','initiator-signature-list','initiator-signature','initiator-edit','initiator-poc-master-list','initiator-poc-master','initiator-poc-master-edit','initiator-max-discount-cap-list','initiator-max-discount-cap','initiator-max-discount-cap-edit','initiator-duration-list','initiator-duration','initiator-duration-edit','initiator-ignored-institutions-list','initiator-ignored-institutions','initiator-ignored-institutions-edit','adjust_workflow_initiator','enable_locking_initiator','stockist_initiator','activity_tracker_new_initiator', 'initiate_date', 'initiator-discount-margin-list', 'initiator-discount-margin-edit', 'initiator-discount-margin', 'initiator-approval-email-schedule-list', 'initiator-approval-email-schedule-edit', 'initiator-approval-email-schedule', 'initiator_bulk_counter_update', 'workflow_adjust_product_wise', 'productwise_discard_data') ? 'admin_active' : '' }}" id="admin_main_menu">
                    <img src="{{ asset('admin/images/AdminMenu.svg') }}" alt=""> Admin
                  </a>
                  <ul class="collapse list-unstyled {{ Route::is('initiator-employee-list', 'initiator-employee-edit','initiator-employee','initiator-signature-list','initiator-signature','initiator-edit','initiator-poc-master-list','initiator-poc-master','initiator-poc-master-edit','initiator-max-discount-cap-list','initiator-max-discount-cap','initiator-max-discount-cap-edit','initiator-duration-list','initiator-duration','initiator-duration-edit','initiator-ignored-institutions-list','initiator-ignored-institutions','initiator-ignored-institutions-edit','adjust_workflow_initiator','enable_locking_initiator','stockist_initiator','activity_tracker_new_initiator', 'initiate_date', 'initiator-discount-margin-list', 'initiator-discount-margin-edit', 'initiator-discount-margin', 'initiator-approval-email-schedule-list', 'initiator-approval-email-schedule-edit', 'initiator-approval-email-schedule', 'initiator_bulk_counter_update', 'workflow_adjust_product_wise', 'productwise_discard_data') ? 'show1' : '' }}" id="adminMenu">
                    <li class="{{ Route::is('initiator-employee-list', 'initiator-employee-edit','initiator-employee') ? 'active' : '' }}">
                      <a href="{{ route('initiator-employee-list') }}">
                        <!-- <img src="{{ asset('admin/images/EmployeeMaster.svg') }}" alt=""> --> Employee Master
                      </a>
                    </li>
                    <li class="{{ Route::is('initiator-signature-list', 'initiator-signature-edit','initiator-signature') ? 'active' : '' }}">
                      <a href="{{ route('initiator-signature-list') }}"><!-- <img src="{{ asset('admin/images/SignatureMaster.svg') }}" alt=""> --> Signature Master</a>
                    </li>
                    <li class="{{ Route::is('initiator-poc-master-list','initiator-poc-master','initiator-poc-master-edit') ? 'active' : '' }}">
                      <a href="{{ route('initiator-poc-master-list') }}"><!-- <img src="{{ asset('admin/images/POCMaster.svg') }}" alt=""> --> Poc Master</a>
                    </li>
                    <li class="{{ Route::is('initiator-max-discount-cap-list','initiator-max-discount-cap','initiator-max-discount-cap-edit') ? 'active' : '' }}">
                      <a href="{{ route('initiator-max-discount-cap-list') }}"><!-- <img src="{{ asset('admin/images/MaxDiscountCap.svg') }}" alt=""> --> Max Discount Cap</a>
                    </li>
                    <li class="{{ Route::is('initiator-duration-list','initiator-duration','initiator-duration-edit') ? 'active' : '' }}">
                      <a href="{{ route('initiator-duration-list') }}"><!-- <img src="{{ asset('admin/images/DurationMaster.svg') }}" alt=""> --> Duration Master</a>
                    </li>
                    <li class="{{ Route::is('initiator-ignored-institutions-list','initiator-ignored-institutions','initiator-ignored-institutions-edit') ? 'active' : '' }}">
                      <a href="{{ route('initiator-ignored-institutions-list') }}"><!-- <img src="{{ asset('admin/images/IgnoredInstitutionsMaster.svg') }}" alt=""> --> Ignored Institions Master</a>
                    </li>
                    <!-- <li class="{{ Route::is('adjust_workflow_initiator') ? 'active' : '' }}">
                      <a href="{{ route('adjust_workflow_initiator') }}"> Workflow Adjust</a>
                    </li> -->
                    <li class="{{ Route::is('enable_locking_initiator') ? 'active' : '' }}">
                      <a href="{{ route('enable_locking_initiator') }}"> Locking Period Control</a>
                    </li>
                    <li class="{{ Route::is('stockist_initiator') ? 'active' : '' }}">
                      <a href="{{ route('stockist_initiator') }}"> Stockist Update</a>
                    </li>
                    <li class="{{ Route::is('activity_tracker_new_initiator') ? 'active' : '' }}">
                      <a href="{{ route('activity_tracker_new_initiator') }}"> Activity Tracker</a>
                    </li>
                    <li class="{{ Route::is('initiate_date') ? 'active' : '' }}">
                      <a href="{{ route('initiate_date') }}">Initiate Date</a>
                    </li>
                    <li class="{{ Route::is('initiator-discount-margin-list','initiator-discount-margin','initiator-discount-margin-edit') ? 'active' : '' }}">
                      <a href="{{ route('initiator-discount-margin-list') }}"> Discount Margin</a>
                    </li>
                    <li class="{{ Route::is('initiator-approval-email-schedule-list','initiator-approval-email-schedule','initiator-approval-email-schedule-edit') ? 'active' : '' }}">
                      <a href="{{ route('initiator-approval-email-schedule-list') }}"> Email Schedule</a>
                    </li>
                    <li class="{{ Route::is('initiator_bulk_counter_update') ? 'active' : '' }}">
                      <a href="{{ route('initiator_bulk_counter_update') }}"> Bulk Counter Update</a>
                    </li>
                    <li id="Workflow_Adjust">
                      <a href="#WorkflowAdjust" id="workflow_adjust_main_menu" data-toggle="collapse" aria-expanded="{{ Route::is('adjust_workflow_initiator','workflow_adjust_product_wise') ? 'ture' : 'false' }}" class="menu-item dropdown-toggle {{ Route::is('adjust_workflow_initiator','workflow_adjust_product_wise') ? 'admin_active' : '' }}">
                        Workflow <br> Adjust
                      </a>
                      <ul class="collapse list-unstyled {{ Route::is('adjust_workflow_initiator','workflow_adjust_product_wise') ? 'show1' : '' }}" id="WorkflowAdjust">
                        <li class="{{ Route::is('adjust_workflow_initiator') ? 'active' : '' }}">
                          <a href="{{ route('adjust_workflow_initiator') }}"> Institution Wise </a>
                        </li>  
                        <li class="product_wise {{ Route::is('workflow_adjust_product_wise') ? 'active' : '' }}">
                          <a href="{{ route('workflow_adjust_product_wise') }}"> Product Wise</a>
                        </li>
                      </ul>
                    </li>
                    <li class="{{ Route::is('productwise_discard_data') ? 'active' : '' }}">
                      <a href="{{ route('productwise_discard_data') }}"> Product wise Discard</a>
                    </li>
                  </ul>
              </li>

                @elseif(Session::get('type')=='poc') 
                 <li class="{{ (Route::currentRouteNamed('poc_dashboard') ? 'active' : '')}}">
                  <a class="" href="{{route('poc_dashboard')}}"><img src="{{ asset('admin/images/home.svg') }}" alt="">Home</a>
                </li>
                <li class="{{ (Route::currentRouteNamed('poc_feedback') ? 'active' : '')}}">
                  <a class="" href="{{route('poc_feedback')}}"><img src="{{ asset('admin/images/plus.svg') }}" alt="">Fill Feedback</a>
                </li>
                <li class="{{ (Route::currentRouteNamed('poc_listing') ? 'active' : '')}} {{(Route::currentRouteNamed('initiatorDetails') ? 'active' : '')}} {{(Route::currentRouteNamed('activityDetailsInitiator') ? 'active' : '')}} {{(Route::currentRouteNamed('modify_stockist') ? 'active' : '')}} {{(Route::currentRouteNamed('view_poc') ? 'active' : '')}}">
                  <a class="" href="{{route('poc_listing')}}"><img src="{{ asset('admin/images/eye.svg') }}" alt="">View Request</a>
                </li>
                <li id="initiator_report">
                  <a href="#productwisereportMenu" data-toggle="collapse" aria-expanded="{{ Route::is('poc_product_cumulative_report') ? 'true' : 'false' }}" class="menu-item dropdown-toggle {{ Route::is('poc_product_cumulative_report') ? 'admin_active' : '' }}" id="report_main_menu">
                      <img src="{{ asset('admin/images/graph.svg') }}" alt=""> Report
                  </a>
                  <ul class="collapse list-unstyled {{ Route::is('poc_product_cumulative_report') ? 'show1' : '' }}" id="productwisereportMenu">
                      <li class="{{ Route::is('poc_product_cumulative_report') ? 'active' : '' }}">
                          <a href="{{ route('poc_product_cumulative_report') }}"> Product wise cumulative report</a>
                      </li>
                  </ul>
                </li>
                @elseif(Session::get('type')=='distribution') 
                 <li class="{{ (Route::currentRouteNamed('distribution_dashboard') ? 'active' : '')}}">
                  <a class="" href="{{route('distribution_dashboard')}}"><img src="{{ asset('admin/images/home.svg') }}" alt="">Home</a>
                </li>
                <li class="{{ (Route::currentRouteNamed('distribution_listing') ? 'active' : '')}} {{(Route::currentRouteNamed('initiatorDetails') ? 'active' : '')}} {{(Route::currentRouteNamed('activityDetailsInitiator') ? 'active' : '')}} {{(Route::currentRouteNamed('modify_stockist') ? 'active' : '')}} {{(Route::currentRouteNamed('view_distribution') ? 'active' : '')}}">
                  <a class="" href="{{route('distribution_listing')}}"><img src="{{ asset('admin/images/eye.svg') }}" alt="">View Request</a>
                </li>
                <li id="initiator_report">
                  <a href="#productwisereportMenu" data-toggle="collapse" aria-expanded="{{ Route::is('distribution_product_cumulative_report') ? 'true' : 'false' }}" class="menu-item dropdown-toggle {{ Route::is('distribution_product_cumulative_report') ? 'admin_active' : '' }}" id="report_main_menu">
                      <img src="{{ asset('admin/images/graph.svg') }}" alt=""> Report
                  </a>
                  <ul class="collapse list-unstyled {{ Route::is('distribution_product_cumulative_report') ? 'show1' : '' }}" id="productwisereportMenu">
                      <li class="{{ Route::is('distribution_product_cumulative_report') ? 'active' : '' }}">
                          <a href="{{ route('distribution_product_cumulative_report') }}"> Product wise cumulative report</a>
                      </li>
                  </ul>
                </li>
                @elseif(Session::get('type')=='ho') 
                 <li class="{{ (Route::currentRouteNamed('ho_dashboard') ? 'active' : '')}}">
                  <a class="" href="{{route('ho_dashboard')}}"><img src="{{ asset('admin/images/home.svg') }}" alt="">Home</a>
                </li>
                <li class="{{ (Route::currentRouteNamed('ho_listing') ? 'active' : '')}} {{(Route::currentRouteNamed('initiatorDetails') ? 'active' : '')}} {{(Route::currentRouteNamed('activityDetailsInitiator') ? 'active' : '')}} {{(Route::currentRouteNamed('modify_stockist') ? 'active' : '')}} {{(Route::currentRouteNamed('view_ho') ? 'active' : '')}}">
                  <a class="" href="{{route('ho_listing')}}"><img src="{{ asset('admin/images/eye.svg') }}" alt="">View Request</a>
                </li>
                @elseif(Session::get('type')=='approver')
                <li class="{{ (Route::currentRouteNamed('approver_dashboard') ? 'active' : '')}}">
                  <a class="" href="{{route('approver_dashboard')}}"><img src="{{ asset('admin/images/home.svg') }}" alt="">Home</a>
                </li>
                <li class="{{ (Route::currentRouteNamed('approver_listing') ? 'active' : '')}} {{(Route::currentRouteNamed('approverDetails') ? 'active' : '')}} {{(Route::currentRouteNamed('approverActivity') ? 'active' : '')}}">
                  <a class="" href="{{route('approver_listing')}}">
                  <img src="{{ asset('admin/images/tickbl.svg') }}" alt="">Manage Request</a>
                </li>
		            <li class="{{ (Route::currentRouteNamed('manage-request-by-sku') ? 'active' : '')}} ">
                  <a class="" href="{{route('manage-request-by-sku')}}">
                  <img src="{{ asset('admin/images/tickbl.svg') }}" alt="">Manage Request by SKU</a>
                </li>
                <li>
                  <a class="" href="#!">
                  <img src="{{ asset('admin/images/ticket.svg') }}" alt="">Raise a Ticket</a>
                </li>
                <li id="initiator_report">
                  <a href="#productwisereportMenu" data-toggle="collapse" aria-expanded="{{ Route::currentRouteNamed('approver_product_cumulative_report') ? 'true' : 'false' }}" class="menu-item dropdown-toggle {{ Route::currentRouteNamed('product_cumulative_report') ? 'admin_active' : '' }}" id="report_main_menu">
                      <img src="{{ asset('admin/images/graph.svg') }}" alt=""> Report
                  </a>
                  <ul class="collapse list-unstyled {{ Route::currentRouteNamed('approver_product_cumulative_report') ? 'show1' : '' }}" id="productwisereportMenu">
                      <li class="{{ Route::currentRouteNamed('approver_product_cumulative_report') ? 'active' : '' }}">
                          <a href="{{ route('approver_product_cumulative_report') }}"> Product wise cumulative report</a>
                      </li>
                  </ul>
                </li>
                @elseif(Session::get('type')=='normal_user')
                <li class="">
                  <a class="" href="{{route('user_dashboard')}}">
                    <img src="{{ asset('admin/images/home.svg') }}" alt="">
                  Home</a>
                </li>
                <li class="{{ (Route::currentRouteNamed('user_listing') ? 'active' : '')}} {{(Route::currentRouteNamed('userDetails') ? 'active' : '')}} ">
                  <a class="" href="{{route('user_listing')}}">
                  <img src="{{ asset('admin/images/eye.svg') }}" alt="">
                  View Request</a>
                </li>
                <!-- added for ceo starts -->
                @elseif(Session::get('type')=='ceo')
                <li class="{{ (Route::currentRouteNamed('approver_dashboard') ? 'active' : '')}}">
                  <a class="" href="{{route('approver_dashboard')}}"><img src="{{ asset('admin/images/home.svg') }}" alt="">Home</a>
                </li>
                <li class="{{ (Route::currentRouteNamed('ceo_approver_listing') ? 'active' : '')}} {{(Route::currentRouteNamed('approverDetails') ? 'active' : '')}} {{(Route::currentRouteNamed('approverActivity') ? 'active' : '')}}">
                  <a class="" href="{{route('ceo_approver_listing')}}">
                  <img src="{{ asset('admin/images/tickbl.svg') }}" alt="">Manage Request</a>
                </li>
                <li id="initiator_report">
                  <a href="#productwisereportMenu" data-toggle="collapse" aria-expanded="{{ Route::is('approver_product_cumulative_report') ? 'true' : 'false' }}" class="menu-item dropdown-toggle {{ Route::is('approver_product_cumulative_report') ? 'admin_active' : '' }}" id="report_main_menu">
                      <img src="{{ asset('admin/images/graph.svg') }}" alt=""> Report
                  </a>
                  <ul class="collapse list-unstyled {{ Route::is('approver_product_cumulative_report') ? 'show1' : '' }}" id="productwisereportMenu">
                      <li class="{{ Route::is('approver_product_cumulative_report') ? 'active' : '' }}">
                          <a href="{{ route('approver_product_cumulative_report') }}"> Product wise cumulative report</a>
                      </li>
                  </ul>
                </li>
                <!-- added for ceo ends -->
                @endif
                
                <li class="d-none">
                  <a href="https://sunpharma.com/" target="_blank">
	              <img src="{{ asset('admin/images/User manual icon_active.svg') }}" alt="">
	              My Sun Pharma</a>
                </li>
                <!-- added by arunchandru at 17-02-2025 -->
                <li id="user_manual">
                  <a href="#<?= (Session::get('type')=='approver' || Session::get('type')=='ceo')? 'Approver-Manual' : 'UserManual'; ?>" id="usermanual_main_menu" data-toggle="collapse" aria-expanded="false" class="menu-item dropdown-toggle collapsed">
                      <img src="{{ asset('admin/images/Vector-1.svg') }}" alt=""> User <br> Manual
                  </a>
                  <ul class="collapse list-unstyled" id="<?= (Session::get('type')=='approver' || Session::get('type')=='ceo')? 'Approver-Manual' : 'UserManual'; ?>">
                      @if( Session::get('type')=='initiator'|| Session::get('type')== 'poc' || Session::get('type')== 'distribution')
                        <li class="Initiator">
                          <a href="{{asset('frontend/Pdf/iDAP_Initiator_Guide.pdf')}}" target="_blank">  Initiator Manual </a>
                        </li>
                      @endif
                      @if(Session::get('type')=='initiator' || Session::get('type')== 'poc' || Session::get('type')== 'distribution' || Session::get('type')=='approver' || Session::get('type')=='ceo')
                      <li class="Approverlink">
                          <a href="{{asset('frontend/Pdf/iDAP_Approver_Guide.pdf')}}" target="_blank"> Approver Manual</a>
                      </li>
                      @endif
                  </ul>
                </li>
                <!-- hide by arunchandru at 17-02-2025 -->
                <!-- <li>
                  <a href="{{asset('frontend/Pdf/iDAP_User_Guide.pdf')}}" target="_blank">
	              <img src="{{ asset('admin/images/Vector-1.svg') }}" alt="">
	              User Manual</a>
                </li> --> 
                <li>
                  <a href="{{route('logout')}}">
	              <img src="{{ asset('admin/images/Logout.svg') }}" alt="">
	              Logout</a>
                </li>
              </ul>

<!--
              <a href="{{route('logout')}}">
              <img src="{{ asset('admin/images/eye.svg') }}" alt="">
              Logout</a>
-->
        </div>
    </div>