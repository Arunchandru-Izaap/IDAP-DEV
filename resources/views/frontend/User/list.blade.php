@extends('layouts.frontend.app')
@section('content')
<div id="page-content-wrapper">
                <!-- Top navigation-->
                <nav class="navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid">
                        <div class="collapse navbar-collapse show" id="navbarSupportedContent">
                            <ul class="navbar-nav dashboard-nav">
                                <li class="nav-item active"><a class="nav-link" href="{{route('user_dashboard')}}"> <img src="../admin/images/back.svg">VQ Request Listing</a></li>
                            </ul>

                            <ul class="d-flex ml-auto user-name">
                                <li>
                                    <h3>{{Session::get('emp_name')}}</h3>
                                    <p>User</p>
                                </li>

                                <li>
                                    <img src="../admin/images/Sun_Pharma_logo.png">
                                </li>
                            </ul>
                        </div>
                        
                    </div>

                </nav>
                <ul class="bradecram-menu">
                    <li><a href="{{route('user_dashboard')}}">
                        Home
                    </a></li>
                    <li class="active">
                      <a href="">
                        VQ Request Listing
                      </a>
                    </li>
                </ul>
                <!-- Page content-->
                <div class="container-fluid">
                    <!-- <h1 class="mt-4">Simple Sidebar</h1> -->
                    <div class="row">
                        <div class="col">
                            <div class="actions-dashboard table-ct">

                              <table class="table VQ Request Listing vq-request-listing-tb nowrap" id="zero_config">
                                <thead>
                                  <tr>
                                    <th>Institution Name</th>
                                    <th>City</th>
                                    <th>State</th>
                                    <th>Zone</th>
                                    <th>Region</th>
                                    <th>Revision no</th>
                                    <th>CFA Code</th>
                                    <th>SAP Code</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>VQ Year</th>
                                    <th>VQ Status</th>
                                    <th>Quotation Status</th>
                                    <th>Actions</th>
                                  </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $item)
                                    <tr>
                                        <!-- <td>{{$item->id}}</td> -->
                                        <td>{{$item->hospital_name}}</td>
                                        <td>{{$item->city}}</td>
                                        <td>{{$item->state_name}}</td>
                                        <td>{{$item->institution_zone}}</td>
                                        <td>{{$item->institution_region}}</td>
                                        <td>{{$item->revision_count}}</td>
                                        <td>{{$item->cfa_code ? $item->cfa_code : "-"}}</td>
                                        <td>{{$item->sap_code || $item->sap_code != "" ? $item->sap_code : "-"}}</td>

                                        <td>{{date('d M Y',strtotime($item->contract_start_date))}}</td>
                                        <td>{{date('d M Y',strtotime($item->contract_end_date))}}</td>
                                        <td>{{$item->year}}</td>

                                        <td><!-- {{$item->contract_end_date }} -->
                                            <p class="pending">
                                            @if($item->current_level!='7')
                                                Pending with Level {{$item->current_level}}
                                            @else 
                                                Approved
                                            @endif
                                            </p>
                                        </td>
                            
                                        <td><!-- {{$item->vq_status}} -->
                                             <p class="pending">
                                             @if($item->vq_status=='0')
                                                Pending
                                            @else
                                                Sent
                                            @endif
                                            </p>
                                        </td>

                                        <td>
                                            <a href="{{url('user/listing',$item->id)}}" data-title="View Details">
                                                <img src="../admin/images/down.svg" alt="">
                                            </a>
                                            <a href="{{url('user/activity',$item->id)}}" data-title="View Activity Tracker">
                                                <img src="../admin/images/clock.png" alt="">
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                              </table>


                            </div>
                        </div><!-- col close -->



<script src="{{asset('admin/extra-libs/DataTables/datatables.min.js')}}"></script>
<script>
      /****************************************
       *       Basic Table                   *
       ****************************************/
      $("#zero_config").DataTable(

        {  language: {    'paginate': {      'previous': "<img src='../admin/images/left.svg' class='left-block' alt=''> <img src='../admin/images/left1.svg' class='disbpr' alt=''>",      'next': "<img src='../admin/images/right.svg' class='neblk' alt=''> <img src='../admin/images/right1.svg' class='disbn' alt=''>"    }  },
        scrollX: true,
        },
        
        );

    </script>
    <style type="text/css">
        .copy-center{
            position:inherit !important;margin: 20px 0;
        }
    </style>
@endsection