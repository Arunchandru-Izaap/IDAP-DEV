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
use App\Jobs\SendEmailWorkflow;
use Illuminate\Support\Facades\DB;
use Session;
use DateTime; 
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Mail;

class WorkflowAdjustController extends Controller
{
    public function workflow_adjust_product_wise(){
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $brand_details = DB::table('brands')->select('item_code','brand_name')->orderBy('brand_name', 'ASC')->get()->toArray();
        
        $institutions = VoluntaryQuotation::select('institution_id','hospital_name')
        ->where('voluntary_quotation.year', $year)
        ->where('voluntary_quotation.vq_status', 0)
        ->where('is_deleted',0)
        ->groupBy('institution_id','hospital_name')
        ->get()->toArray();
        $data['institutions'] = $institutions;
        $data['brand_details'] = $brand_details;
        // dd($brand_details);
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.workflowadjust.product_wise_lisiting',compact('data'));
        }
        else
        {
            return view('admin.workflowadjust.product_wise_lisiting', compact('data'));
        }
    }

    public function get_pending_vq_data_workflow_product_wise(Request $request)
    {
        try 
        {
            $brand_name = $request->brand_name;
            $approver_level = $request->approver_level;

            $year = $this->getFinancialYear(date('Y-m-d'),"Y");

            $brand_name_string = implode('","', $brand_name);
            $brand_name_count = (isset($brand_name))? '1' : '1';

            $data_query = 'SELECT vq.id
            FROM voluntary_quotation_sku_listing AS vqsl
            LEFT JOIN voluntary_quotation AS vq ON vqsl.vq_id = vq.id
            WHERE vq.year = "'.$year.'"
            AND vq.vq_status = 0
            AND vq.current_level = "'.$approver_level.'"
            AND vq.is_deleted = 0
            AND vqsl.is_deleted = 0
            GROUP BY vqsl.vq_id
            HAVING 
            COUNT(*) = '. $brand_name_count .' AND  -- Exactly 2 items
            SUM(CASE WHEN vqsl.item_code IN ("' . $brand_name_string . '")  THEN 1 ELSE 0 END) = '. $brand_name_count .' ';  // Both items present

            $data = DB::select($data_query);

            $vq_ids = array_column($data, 'id');

            $data = DB::table('voluntary_quotation_sku_listing as vqsl')
            ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
            ->where('vq.year', $year)
            ->where('vq.vq_status', 0)
            ->where('vq.current_level', $approver_level)
            ->where('vq.is_deleted', 0)
            ->where('vqsl.is_deleted', 0)
            ->whereIn('vqsl.item_code', $brand_name)
            ->whereIn('vqsl.vq_id', $vq_ids)
            ->get();

            // print_r(count($data));die;

            return response()->json([
                'data' => $data,
            ]);
        }
        catch (\Exception $e) {
            \Log::error('Error in getVqgenerationData: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while processing your request.'.$e->getMessage()
            ], 500);
        }
    }
    public function workflow_adjust_forward_backward_levels(Request $request){
        $selected_brandname = $request->selected_brandname;
        $selected_approver_level = $request->selected_approver_level;
        $action = $request->action;
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");

        $brand_name_count = (isset($selected_brandname))? '1' : '1';
        $brand_name_string = implode('","', $selected_brandname);

        $data_query = 'SELECT *
        FROM voluntary_quotation_sku_listing AS vqsl
        LEFT JOIN voluntary_quotation AS vq ON vqsl.vq_id = vq.id
        WHERE vq.year = "'.$year.'"
        AND vq.vq_status = 0
        AND vq.current_level = "'.$selected_approver_level.'"
        AND vq.is_deleted = 0
        AND vqsl.is_deleted = 0
        GROUP BY vqsl.vq_id
        HAVING 
        COUNT(*) = '. $brand_name_count .' AND  -- Exactly 2 items
        SUM(CASE WHEN vqsl.item_code IN ("' . $brand_name_string . '")  THEN 1 ELSE 0 END) = '. $brand_name_count .' ';  // Both items present

        $get_counter_data = DB::select($data_query);
        $vq_ids = array_column($get_counter_data, 'id');
        $selected_rows = $get_counter_data;

        $mail_levels = array();
        $ceo_approval_level = 0;
        $level_names = [
            1 => 'RSM',
            2 => 'ZSM',
            3 => 'NSM',
            4 => 'SBU',
            5 => 'Semi Cluster',
            6 => 'Cluster',
            7 => 'Initiator',
            8 => 'CEO'
        ];
        if(!is_null($selected_rows)){
            DB::beginTransaction();
            try
            {
                if($action == 'move_up')
                {
                    foreach ($selected_rows as $single_vq) {
                        // dd($single_vq->id);
                        $vq = VoluntaryQuotation::where('id',$single_vq->id)->first();
                        $calc_level = $vq->current_level;
                        if($vq->fastforward_levels != NULL){
                            $fastforward = collect(explode(',',$vq->fastforward_levels))->sort();
                        }else{
                            $fastforward = NULL;
                        }
                        if($calc_level != 7)//if rev already in initator level cannot move up
                        {
                            if($calc_level == 6)//check in cluster level for more than 30% items
                            {
                                $checkItems = VoluntaryQuotation::select('item_code','voluntary_quotation.id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_vq->id)->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->exists();
                                if($checkItems == true)
                                {
                                    $checkExceptionsItems = VoluntaryQuotation::select('item_code','div_id', 'vqsl.id as vqslid', 'voluntary_quotation.id as vqid')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_vq->id)->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->get();
                                    $vq_ids = $checkExceptionsItems->pluck('vqid')->unique()->toArray();
                                    $vqsl_ids = $checkExceptionsItems->pluck('vqslid')->toArray();
                                    // print_r($vq_ids);
                                    // print_r($vqsl_ids);die;
                                    $allItemsPresent = true;
                                    foreach ($checkExceptionsItems as $item) {
                                        $exists = DB::table('exception_sku_list')
                                            ->where('item_code', $item['item_code'])
                                            ->where('div_id', $item['div_id'])
                                            ->where('year', $year)
                                            ->exists();

                                        if (!$exists) {
                                            $allItemsPresent = false;
                                            break;
                                        }
                                    }
                                    if ($allItemsPresent) {
                                        //All items are present in exception list
                                        $calc_level++;
                                    } else {
                                        //Some items are missing in exception list
                                        $calc_level = 8;//current level for ceo is 8
                                        $ceo_approval_level++; 
                                    }
                                }
                                else
                                {
                                    $calc_level++;
                                }
                            }
                            else if($calc_level == 8)
                            {
                                
                            }
                            else
                            {
                                if($fastforward != NULL){
                                    $checkItems = VoluntaryQuotation::select('item_code','voluntary_quotation.id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_vq->id)->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->exists();

                                    if($checkItems == true)
                                    {
                                        $checkExceptionsItems = VoluntaryQuotation::select('item_code','div_id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_vq->id)->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->get();
                                        $allItemsPresent = true;
                                        foreach ($checkExceptionsItems as $item) {
                                            $exists = DB::table('exception_sku_list')
                                                ->where('item_code', $item['item_code'])
                                                ->where('div_id', $item['div_id'])
                                                ->where('year', $year)
                                                ->exists();

                                            if (!$exists) {
                                                $allItemsPresent = false;
                                                break;
                                            }
                                        }
                                        if ($allItemsPresent) {
                                            //All items are present in exception list
                                            $determined_level = 7;
                                        } else {
                                            //Some items are missing in exception list
                                            $determined_level = 8;//current level for ceo is 8
                                        }
                                    }
                                    else
                                    {
                                        $determined_level = 7;
                                    }

                                    $firstHighest = $fastforward->filter(function ($number) use ($calc_level) {
                                        return $number > $calc_level;
                                    })->first();
                                    
                                    $calc_level = $firstHighest ? $firstHighest : $determined_level;
                                }else{
                                    $calc_level++;
                                }
                            }
                            if($calc_level != $vq->current_level){
                                $mail_levels[] = $calc_level;
                                $updation = VoluntaryQuotation::where('id',$single_vq->id)->where('is_deleted', 0)->update(['current_level'=>$calc_level,'updated_at'=>date('Y-m-d H:i:s')]);
                                /*if($calc_level == 8)
                                {
                                    $last_level = 6;
                                }
                                else
                                {
                                    $last_level = $calc_level-1 == 0 ? 1 : $calc_level-1;
                                }*/
                                $last_level = $vq->current_level;
                                $vq_details = VoluntaryQuotationSkuListing::select(
                                "div_name" ,
                                DB::raw("(sum(l".$last_level."_status)) as statuss")
                                )
                                ->where('vq_id',$single_vq->id)
                                ->where('is_deleted',0)
                                ->groupBy('div_name')
                                ->get();
                                if (isset($level_names[$last_level])) {
                                    $level_name = $level_names[$last_level];
                                } else {
                                    $level_name = $last_level;
                                }
                                if (isset($level_names[$calc_level])) {
                                    $level_name_current = $level_names[$calc_level];
                                } else {
                                    $level_name_current = $calc_level;
                                }
                                $emp_details = Session::get("emp_code").'-'.Session::get("emp_name");
                                foreach($vq_details as $vq_detail){
                                    //if($vq_detail->statuss < 1){
                                        //$vq_listing_controller->activityTracker($single_vq->id,'','VQ Moved Up By '.$emp_details.' of division - '.$vq_detail->div_name.' at level - '.$level_name,'workflowadjust');
                                        $vq_listing_controller->activityTracker($single_vq->id,'','VQ Moved Up By '.$emp_details.' of  division - '.$vq_detail->div_name.' from '.$level_name.' to '.$level_name_current,'workflowadjust');
                                    //}
                                }
                            }
                        }
                    }
                }
                else if($action == 'mv_initiator')
                {
                    foreach ($selected_rows as $single_vq) {
                        $vq = VoluntaryQuotation::where('id',$single_vq->id)->first();
                        $calc_level = $vq->current_level;
                        if($calc_level != 7)//if rev already in initator level cannot move up
                        {
                            $checkItems = VoluntaryQuotation::select('item_code','voluntary_quotation.id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_vq->id)->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->exists();
                            if($checkItems == true)
                            {
                                $checkExceptionsItems = VoluntaryQuotation::select('item_code','div_id', 'vqsl.id as vqslid', 'voluntary_quotation.id as vqid')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_vq->id)->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->get();
                                $vq_ids = $checkExceptionsItems->pluck('vqid')->unique()->toArray();
                                $vqsl_ids = $checkExceptionsItems->pluck('vqslid')->toArray();
                                $allItemsPresent = true;
                                foreach ($checkExceptionsItems as $item) {
                                    $exists = DB::table('exception_sku_list')
                                        ->where('item_code', $item['item_code'])
                                        ->where('div_id', $item['div_id'])
                                        ->where('year', $year)
                                        ->exists();

                                    if (!$exists) {
                                        $allItemsPresent = false;
                                        break;
                                    }
                                }
                                if ($allItemsPresent) {
                                    //All items are present in exception list
                                    $calc_level = 7;
                                } else {
                                    //Some items are missing in exception list
                                    $calc_level = 8;//current level for ceo is 8
                                    $ceo_approval_level++;
                                }
                            }
                            else
                            {
                                $calc_level = 7;
                            }
                            if($calc_level != $vq->current_level){
                                $mail_levels[] = $calc_level;
                                $last_level = $vq->current_level;
                                $updation = VoluntaryQuotation::where('id',$single_vq->id)->where('is_deleted', 0)->update(['current_level'=>$calc_level,'updated_at'=>date('Y-m-d H:i:s')]);
                                $vq_details = VoluntaryQuotationSkuListing::select(
                                "div_name" ,
                                DB::raw("(sum(l".$last_level."_status)) as statuss")
                                )
                                ->where('vq_id',$single_vq->id)
                                ->where('is_deleted',0)
                                ->groupBy('div_name')
                                ->get();
                                if (isset($level_names[$last_level])) {
                                    $level_name = $level_names[$last_level];
                                } else {
                                    $level_name = $last_level;
                                }
                                if (isset($level_names[$calc_level])) {
                                    $level_name_current = $level_names[$calc_level];
                                } else {
                                    $level_name_current = $calc_level;
                                }
                                $emp_details = Session::get("emp_code").'-'.Session::get("emp_name");
                                foreach($vq_details as $vq_detail){
                                    //if($vq_detail->statuss < 1){
                                        //$vq_listing_controller->activityTracker($single_vq->id,'','VQ Moved to Initiator By '.$emp_details.' of division - '.$vq_detail->div_name.' at level - '.$level_name,'workflowadjust');
                                    $vq_listing_controller->activityTracker($single_vq->id,'','VQ Moved to Initiator By '.$emp_details.' of division - '.$vq_detail->div_name.' from '.$level_name.' to '.$level_name_current,'workflowadjust');
                                    //}
                                }
                            }
                        }
                    }
                }
                else if($action == 'send_back')
                {
                    $invalid_selection = [];
                    foreach ($selected_rows as $single_vq) {
                        $vq = VoluntaryQuotation::where('id',$single_vq->id)->first();
                        $calc_level = $vq->current_level;
                        if($vq->fastforward_levels != NULL){
                            $fastforward = collect(explode(',',$vq->fastforward_levels))->sortDesc();
                            $firstHighest = $fastforward->filter(function ($number) use ($calc_level) {
                                return $number < $calc_level;
                            })->first();
                            
                            $calc_level = $firstHighest ? $firstHighest : $calc_level;
                            if($calc_level == $vq->current_level)
                            {
                                $invalid_selection[] = [
                                    'inst_name' => $vq->hospital_name,  
                                    'inst_id' => $vq->institution_id,      
                                    'rev_no' => $vq->rev_no,           
                                    'vq_id' => $vq->id,        
                                ];
                            }
                        }
                    }
                    if(count($invalid_selection) > 0)
                    {
                        return response()->json([
                            'success'=>false,
                            'type'=>'invalid_selection', 
                            'result' => $invalid_selection ?? ''
                        ]);
                    }
                    foreach ($selected_rows as $single_vq) {
                        $vq = VoluntaryQuotation::where('id',$single_vq->id)->first();
                        $calc_level = $vq->current_level;
                        if($vq->fastforward_levels != NULL){
                            $fastforward = collect(explode(',',$vq->fastforward_levels))->sortDesc();
                        }else{
                            $fastforward = NULL;
                        }
                        if($calc_level != 1)//if rev already in rsm level cannot move down
                        {
                            if($calc_level == 7)//check in cluster level for more than 30% items
                            {
                                $checkItems = VoluntaryQuotation::select('item_code','voluntary_quotation.id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_vq->id)->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->exists();
                                if($checkItems == true)
                                {
                                    $checkExceptionsItems = VoluntaryQuotation::select('item_code','div_id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_vq->id)->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->get();
                                    $allItemsPresent = true;
                                    foreach ($checkExceptionsItems as $item) {
                                        $exists = DB::table('exception_sku_list')
                                            ->where('item_code', $item['item_code'])
                                            ->where('div_id', $item['div_id'])
                                            ->where('year', $year)
                                            ->exists();

                                        if (!$exists) {
                                            $allItemsPresent = false;
                                            break;
                                        }
                                    }
                                    if ($allItemsPresent) {
                                        //All items are present in exception list
                                        if($fastforward != NULL){
                                            $firstHighest = $fastforward->filter(function ($number) use ($calc_level) {
                                                return $number < $calc_level;
                                            })->first();
                                            
                                            $calc_level = $firstHighest ? $firstHighest : $calc_level;
                                        }else{
                                            $calc_level--;
                                        }
                                    } else {
                                        //Some items are missing in exception list
                                        $calc_level = 8;//current level for ceo is 8
                                    }
                                }
                                else
                                {
                                   if($fastforward != NULL){
                                        $firstHighest = $fastforward->filter(function ($number) use ($calc_level) {
                                            return $number < $calc_level;
                                        })->first();
                                        
                                        $calc_level = $firstHighest ? $firstHighest : $calc_level;
                                    }else{
                                        $calc_level--;
                                    }
                                }
                            }
                            else if($calc_level == 8)
                            {
                                if($fastforward != NULL){
                                    $firstHighest = $fastforward->filter(function ($number) use ($calc_level) {
                                        return $number < $calc_level;
                                    })->first();
                                    
                                    $calc_level = $firstHighest ? $firstHighest : $calc_level;
                                }else{
                                    $calc_level = 6;
                                }
                            }
                            else
                            {
                                if($fastforward != NULL){
                                    $firstHighest = $fastforward->filter(function ($number) use ($calc_level) {
                                        return $number < $calc_level;
                                    })->first();
                                    
                                    $calc_level = $firstHighest ? $firstHighest : $calc_level;
                                }else{
                                    $calc_level--;
                                }
                            }
                            if($calc_level != $vq->current_level){
                                $mail_levels[] = $calc_level;
                                $last_level = $vq->current_level==7 ? $calc_level : $vq->current_level;
                                $sku_listing_status_column = 'l'.$calc_level.'_status';
                                $sku_listing_previous_column = 'l'.$last_level.'_status';
                                $updation = VoluntaryQuotation::where('id',$single_vq->id)->where('is_deleted', 0)->update(['current_level'=>$calc_level,'updated_at'=>date('Y-m-d H:i:s')]);
                                $updation = VoluntaryQuotationSkuListing::where('vq_id',$single_vq->id)->where('is_deleted',0)->update([$sku_listing_status_column=>0,$sku_listing_previous_column=>0,'updated_at'=>date('Y-m-d H:i:s')]);
                                $vq_details = VoluntaryQuotationSkuListing::select(
                                "div_name" ,
                                DB::raw("(sum(l".$last_level."_status)) as statuss")
                                )
                                ->where('vq_id',$single_vq->id)
                                ->where('is_deleted',0)
                                ->groupBy('div_name')
                                ->get();
                                $emp_details = Session::get("emp_code").'-'.Session::get("emp_name");
                                if (isset($level_names[$vq->current_level])) {
                                    $level_name = $level_names[$vq->current_level];
                                } else {
                                    $level_name = $vq->current_level;
                                }
                                if (isset($level_names[$calc_level])) {
                                    $level_name_current = $level_names[$calc_level];
                                } else {
                                    $level_name_current = $calc_level;
                                }
                                foreach($vq_details as $vq_detail){
                                    //if($vq_detail->statuss < 1){
                                        //$vq_listing_controller->activityTracker($single_vq->id,'','VQ Sent back By '.$emp_details.' of division - '.$vq_detail->div_name.' from level - '.$level_name,'workflowadjust');
                                    $vq_listing_controller->activityTracker($single_vq->id,'','VQ Sent back By '.$emp_details.' of division - '.$vq_detail->div_name.' from - '.$level_name.' to '.$level_name_current,'workflowadjust');
                                    //}
                                }
                            }
                        }
                    }
                }
                // Commit Transaction
                DB::commit();
                $mail_levels = array_unique($mail_levels);
                if(count($mail_levels) > 0)
                {
                    $this->dispatch(new SendEmailWorkflow($mail_levels,$action));
                }
                // if(count($vq_ids) > 0 && $ceo_approval_level != 0)
                // {
                //     if(now()->day == 1):
                //         $report_type = 'monthly';
                //     else:
                //         $report_type = 'daily';
                //     endif;
                //     // $this->dispatch(new SendEmailCEOApproval($calc_level, $action, $report_type)); // hide this CEO approval mails
                // }
                return response()->json([
                    'success'=>true, 
                    'type'=>'success',
                    'result' => $updation ?? ''
                ]);
            } catch (\Exception $e) {
                // Log the exception
                \Log::error('Error in workflow_adjust: ' . $e->getMessage());

                // Return a generic error response
                return response()->json([
                    'status' => false,
                    'type'=>'error',
                    'message' => 'An error occurred. Please try again later.'.$e->getMessage()
                ], 500);
            }
        }
        else{
            return response()->json([
                'success'=>false, 
                'result' => 'no selection'
            ]);
        }
    }
}
