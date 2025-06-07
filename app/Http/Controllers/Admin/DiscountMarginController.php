<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityTracker;
use App\Models\DiscountMarginMaster;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\VoluntaryQuotationSkuListingStockist;
use App\Models\JwtToken;
use App\Http\Controllers\Api\VqListingController;
use Illuminate\Support\Facades\DB;
use Session;
use DateTime; 
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Mail;

class DiscountMarginController extends Controller
{
    public function index(){
        $brand_details = DB::table('brands')->orderBy('brand_name', 'ASC')->get();
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.discount_margin.add',compact('brand_details'));
        }
        else
        {
            return view('admin.discount_margin.add', compact('brand_details'));
        }
    }
    public function list(){
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $log = ActivityTracker::select('activity_trackers.*','employee_master.emp_code','employee_master.emp_name')
        ->leftJoin('employee_master','employee_master.emp_code','=','activity_trackers.emp_code')
        ->where('type','update_discount_margin')
        ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.fin_year")) = ?', [$year])
        ->orderBy('activity_trackers.created_at', 'DESC')->get();

        $brand_details = DB::table('brands')->orderBy('brand_name', 'ASC')->get()->toArray();
        // print_r($brand_details);die;
        $listdata = DiscountMarginMaster::select('discount_margin_master.*', 'discount_margin_master.item_code as disc_marg_itemcode','discount_margin_master.id as dm_id', 'brands.*', 'brands.id as brand_id', 'brands.id as brand_id')
        ->leftJoin('brands','brands.item_code','=','discount_margin_master.item_code')
        ->orderBy('discount_margin_master.created_at', 'DESC')->get();
        // print_r($log);die;
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.discount_margin.list',compact('listdata', 'log', 'brand_details'));
        }
        else
        {
            return view('admin.discount_margin.list',compact('listdata', 'log', 'brand_details'));
        }
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'item_code' => 'required',
            'discount_margin' => 'required'
        ]);

        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $ip_address = request()->ip();
        $userAgent = request()->header('User-Agent');

        $selected_item_code = $request->item_code;
        foreach($selected_item_code as $single_itemcode):

            $existingItmeCode = \DB::table('discount_margin_master')
            ->where('item_code', $single_itemcode)->get()->first();
            // print_r($existingItmeCode);

            $itemcodemargin_data['item_code']= $single_itemcode;
            $itemcodemargin_data['discount_margin']= $request->discount_margin;

            $get_before_update_details = DB::table('brands')->orderBy('brand_name', 'ASC') ->where('item_code',$single_itemcode)->get()->first();
            // print_r($get_before_update_details);die;
            $addl_params = array();
            $addl_params['fin_year'] = $year;
            $addl_params['ip_address'] = $ip_address;
            $addl_params['changed_at'] = date('Y-m-d H:i:s');
            
            $addl_params['user_agent'] = $userAgent;
            $addl_params['item_code'] = $single_itemcode;
            
            if(!empty($existingItmeCode)):
                $addl_params['changed_to'] = 'Updated';
                $addl_params['brand_name'] = $get_before_update_details->brand_name;
                $addl_params['sap_itemcode'] = $get_before_update_details->sap_itemcode;
                $addl_params['changed_discount_margin'] = $request->discount_margin;
                $addl_params['pervious_discount_margin'] = $existingItmeCode->discount_margin;
                $vq_listing_controller->activityTracker(1,Session::get("emp_code"),json_encode($addl_params),'update_discount_margin');
                DiscountMarginMaster::where('item_code',$single_itemcode)->update($itemcodemargin_data);
            else:
                $addl_params['changed_to'] = 'Added';
                $addl_params['brand_name'] = $get_before_update_details->brand_name;
                $addl_params['sap_itemcode'] = $get_before_update_details->sap_itemcode;
                $addl_params['changed_discount_margin'] = $request->discount_margin;
                $addl_params['pervious_discount_margin'] = '-';
                $vq_listing_controller->activityTracker(1,Session::get("emp_code"),json_encode($addl_params),'update_discount_margin');
                DiscountMarginMaster::create($itemcodemargin_data);
            endif;
        endforeach;
      
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/discount-margin-list')->with('message', 'Discount Margin is successfully validated and data has been saved');
        }
        else
        {
            return redirect('/admin/discount-margin-list')->with('message', 'Discount Margin is successfully validated and data has been saved');
        }
    }

    public function edit($id){
        $data = DiscountMarginMaster::find($id);
        $brand_details = DB::table('brands')->orderBy('brand_name', 'ASC')->get();
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.discount_margin.edit',compact('data', 'brand_details'));
        }
        else
        {
            return view('admin.discount_margin.edit',compact('data', 'brand_details'));
        }
    }
    public function update(Request $request){
        $validatedData = $request->validate([
            // 'item_code' => 'required',
            'discount_margin' => 'required'
        ]);
        
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $ip_address = request()->ip();
        $userAgent = request()->header('User-Agent');
        
        $get_before_update_details = DiscountMarginMaster::select('discount_margin_master.*', 'discount_margin_master.item_code as disc_marg_itemcode','discount_margin_master.id as dm_id', 'brands.*', 'brands.id as brand_id', 'brands.id as brand_id')
        ->leftJoin('brands','brands.item_code','=','discount_margin_master.item_code')
        ->where('discount_margin_master.id',$request['id'])
        ->get()->first();
       
        $addl_params = array();
        $addl_params['fin_year'] = $year;
        $addl_params['ip_address'] = $ip_address;
        $addl_params['changed_at'] = date('Y-m-d H:i:s');
        $addl_params['user_agent'] = $userAgent;
        $addl_params['item_code'] = $get_before_update_details->item_code;
        $addl_params['changed_to'] = 'Updated';
        $addl_params['brand_name'] = $get_before_update_details->brand_name;
        $addl_params['sap_itemcode'] = $get_before_update_details->sap_itemcode;
        $addl_params['pervious_discount_margin'] = $get_before_update_details->discount_margin;
        $addl_params['changed_discount_margin'] = $request->discount_margin;
        $vq_listing_controller->activityTracker(1,Session::get("emp_code"),json_encode($addl_params),'update_discount_margin');
       
        DiscountMarginMaster::where('id',$request['id'])->update($validatedData);
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/discount-margin-list')->with('message', 'Discount Margin is successfully validated and data has been updated');
        }
        else
        {
            return redirect('/admin/discount-margin-list')->with('message', 'Discount Margin is successfully validated and data has been updated');
        }
    }
    public function delete($id){
        $itemcode=DiscountMarginMaster::find($id);

        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $ip_address = request()->ip();
        $userAgent = request()->header('User-Agent');
        
        $get_before_update_details = DiscountMarginMaster::select('discount_margin_master.*', 'discount_margin_master.item_code as disc_marg_itemcode','discount_margin_master.id as dm_id', 'brands.*', 'brands.id as brand_id', 'brands.id as brand_id')
        ->leftJoin('brands','brands.item_code','=','discount_margin_master.item_code')
        ->where('discount_margin_master.id',$id)
        ->get()->first();
       
        $addl_params = array();
        $addl_params['fin_year'] = $year;
        $addl_params['ip_address'] = $ip_address;
        $addl_params['changed_at'] = date('Y-m-d H:i:s');
        $addl_params['user_agent'] = $userAgent;
        $addl_params['item_code'] = $get_before_update_details->item_code;
        $addl_params['changed_to'] = 'Deleted';
        $addl_params['brand_name'] = $get_before_update_details->brand_name;
        $addl_params['sap_itemcode'] = $get_before_update_details->sap_itemcode;
        $addl_params['pervious_discount_margin'] = $get_before_update_details->discount_margin;
        $addl_params['changed_discount_margin'] = $get_before_update_details->discount_margin;
        $vq_listing_controller->activityTracker(1,Session::get("emp_code"),json_encode($addl_params),'update_discount_margin');
        $itemcode->delete();
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/discount-margin-list')->with('message', 'Discount Margin is successfully deleted');
        }
        else
        {
            return redirect('/admin/discount-margin-list')->with('message', 'Discount Margin is successfully deleted');
        }
    } 
}
