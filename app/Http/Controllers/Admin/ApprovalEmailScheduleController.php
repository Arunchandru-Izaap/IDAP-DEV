<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityTracker;
use App\Models\ApprovalEmailScheduleMaster;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\VoluntaryQuotationSkuListingStockist;
use App\Models\JwtToken;
use App\Models\ApprovalPeriod;
use App\Http\Controllers\Api\VqListingController;
use Illuminate\Support\Facades\DB;
use Session;
use DateTime; 
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Mail;

class ApprovalEmailScheduleController extends Controller
{
    public function index(){
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $year_arr = explode('-', $year);
        $date['maxDate'] = $year_arr[1].'-'.'03-31';
        $today = Carbon::now();
        $date['minDate'] = $today->toDateString();
        $brand_details = DB::table('brands')->orderBy('brand_name', 'ASC')->get();
        $approval_period_details = DB::table('approval_period')->orderBy('id', 'ASC')->get()->toArray();

        // print_r($approval_period_details);die;
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.approval_email_schedule.add',compact('brand_details', 'date'));
        }
        else
        {
            return view('admin.approval_email_schedule.add', compact('brand_details', 'date'));
        }
    }
    public function list(){
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $log = ActivityTracker::select('activity_trackers.*','employee_master.emp_code','employee_master.emp_name')
        ->leftJoin('employee_master','employee_master.emp_code','=','activity_trackers.emp_code')
        ->where('type','update_approval_email_schedule')
        ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.fin_year")) = ?', [$year])
        ->orderBy('activity_trackers.created_at', 'DESC')->get();

        $listdata = ApprovalEmailScheduleMaster::orderBy('created_at', 'DESC')->get();
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.approval_email_schedule.list',compact('listdata', 'log',));
        }
        else
        {
            return view('admin.approval_email_schedule.list',compact('listdata', 'log'));
        }
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'level' => 'required',
            'type' => 'required',
            'start_days' => 'required',
            'frequency_days' => ''
        ]);

        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $ip_address = request()->ip();
        $userAgent = request()->header('User-Agent');
        
        $existingleveltype = \DB::table('approval_email_schedule')->where('level', $request->level)->where('type', $request->type)->get()->first();
        
        $addl_params = array();
        $addl_params['fin_year'] = $year;
        $addl_params['ip_address'] = $ip_address;
        $addl_params['changed_at'] = date('Y-m-d H:i:s');
        $addl_params['user_agent'] = $userAgent;
        if($existingleveltype):
            $addl_params['action'] = 'Updated';
            $addl_params['level'] = $request->level;
            $addl_params['type'] = $request->type;
            $addl_params['changed_start_days'] = $request->start_days;
            $addl_params['pervious_start_days'] = $existingleveltype->start_days;
            $addl_params['changed_frequency_days'] = $request->frequency_days;
            $addl_params['pervious_frequency_days'] = $existingleveltype->frequency_days;
            $vq_listing_controller->activityTracker(1,Session::get("emp_code"),json_encode($addl_params),'update_approval_email_schedule');
            ApprovalEmailScheduleMaster::where('id', $existingleveltype->id)->update($validatedData);
        else:
            $addl_params['action'] = 'Added';
            $addl_params['level'] = $request->level;
            $addl_params['type'] = $request->type;
            $addl_params['changed_start_days'] = $request->start_days;
            $addl_params['pervious_start_days'] = '-';
            $addl_params['changed_frequency_days'] = $request->frequency_days;
            $addl_params['pervious_frequency_days'] = '-';
            $vq_listing_controller->activityTracker(1,Session::get("emp_code"),json_encode($addl_params),'update_approval_email_schedule');
            ApprovalEmailScheduleMaster::create($validatedData);
        endif;

        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/approval-email-schedule-list')->with('message', 'Email Schedule is successfully validated and data has been saved');
        }
        else
        {
            return redirect('/admin/approval-email-schedule-list')->with('message', 'Email Schedule is successfully validated and data has been saved');
        }
    }

    public function edit($id){
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $year_arr = explode('-', $year);
        $date['maxDate'] = $year_arr[1].'-'.'03-31';
        $today = Carbon::now();
        $date['minDate'] = $today->toDateString();
        $data = ApprovalEmailScheduleMaster::find($id);

        $nextLevels = ApprovalPeriod::where('type', $data->type)
        ->where('level',  $data->level)
        ->get()->toArray();
        if(!empty($nextLevels)):
            $dmdata['days'] = $nextLevels[0]['days'];
            $dmdata['start_date'] = $nextLevels[0]['start_date'];
            $dmdata['end_date'] = $nextLevels[0]['end_date'];
        else:
            $dmdata['days'] = '';
            $dmdata['start_date'] = '';
            $dmdata['end_date'] = '';
        endif;

        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.approval_email_schedule.edit',compact('data', 'date', 'dmdata'));
        }
        else
        {
            return view('admin.approval_email_schedule.edit',compact('data', 'date', 'dmdata'));
        }
    }
    public function update(Request $request){
        $validatedData = $request->validate([
            'level' => 'required',
            'type' => 'required',
            'start_days' => 'required',
            'frequency_days' => ''
        ]);

        $existingleveltype = \DB::table('approval_email_schedule')->where('level', $request->level)->where('type', $request->type)->get()->first();
        
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $ip_address = request()->ip();
        $userAgent = request()->header('User-Agent');
       
        $addl_params = array();
        $addl_params['fin_year'] = $year;
        $addl_params['ip_address'] = $ip_address;
        $addl_params['changed_at'] = date('Y-m-d H:i:s');
        $addl_params['user_agent'] = $userAgent;
        $addl_params['action'] = 'Updated';
        $addl_params['level'] = $request->level;
        $addl_params['type'] = $request->type;
        $addl_params['changed_start_days'] = $request->start_days;
        $addl_params['pervious_start_days'] = ($existingleveltype != '')?$existingleveltype->start_days:'-';
        $addl_params['changed_frequency_days'] = $request->frequency_days;
        $addl_params['pervious_frequency_days'] = ($existingleveltype != '')?$existingleveltype->frequency_days:'-';
        $vq_listing_controller->activityTracker(1,Session::get("emp_code"),json_encode($addl_params),'update_approval_email_schedule');
       
        ApprovalEmailScheduleMaster::where('id',$request['id'])->update($validatedData);
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/approval-email-schedule-list')->with('message', 'Email Schedule is successfully validated and data has been updated');
        }
        else
        {
            return redirect('/admin/approval-email-schedule-list')->with('message', 'Email Schedule is successfully validated and data has been updated');
        }
    }
    public function delete($id){
        $delete_level = ApprovalEmailScheduleMaster::find($id);

        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $ip_address = request()->ip();
        $userAgent = request()->header('User-Agent');
        

        $addl_params = array();
        $addl_params['fin_year'] = $year;
        $addl_params['ip_address'] = $ip_address;
        $addl_params['changed_at'] = date('Y-m-d H:i:s');
        $addl_params['user_agent'] = $userAgent;
        $vq_listing_controller->activityTracker(1,Session::get("emp_code"),json_encode($addl_params),'update_approval_email_schedule');
        $delete_level->delete();
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/approval-email-schedule-list')->with('message', 'Discount Margin is successfully deleted');
        }
        else
        {
            return redirect('/admin/approval-email-schedule-list')->with('message', 'Discount Margin is successfully deleted');
        }
    } 
}
