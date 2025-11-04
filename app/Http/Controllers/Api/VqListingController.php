<?php

namespace App\Http\Controllers\Api;
use App\Jobs\CreateVq;
use App\Jobs\ApproveVq;
use App\Jobs\CreateVqForNew;
use App\Jobs\newReinitiateVQ;
use App\Jobs\ReinitiateVQ;
use App\Jobs\ReinitiateVQNewPack;
use App\Jobs\SendMetisAll;
use App\Jobs\ReportDownload;
use App\Jobs\HistoricalReportDownload;
use App\Jobs\FinancialHistoricalReportDownload;
use App\Models\Stockist_master;
use App\Exports\InitiatorExport;
use App\Exports\LatestExport;
use App\Exports\VqExport;
use App\Exports\VqExportCriteria;//added on 03052024
use App\Exports\CumulativeReport;//added on 27012025
use App\Exports\PendingItemExport;//added on 21092024
use App\Exports\InstitutionsExport;//added on 21092024
use App\Models\VoluntaryQuotationSkuListingStockist;//added on 20062024
use Illuminate\Support\Facades\Mail;//aaded on 24062024
use App\Models\Employee;//aaded on 24062024
use App\Models\IdapDiscTran;
use App\Models\DiscountMarginMaster;
use App\Jobs\DeleteStockist;//aaded on 24062024
use App\Jobs\AddStockist;//aaded on 27122024
use App\Jobs\GenerateVq;//aaded on 19072024
use App\Exports\InitiatorExportNew;//aaded on 23072024
use App\Jobs\ReinitiateVQCopyCounter;//aaded on 25072024
use App\Jobs\SendEmailWorkflow;//aaded on 29082024
use App\Jobs\SendEmailCEOApproval;//aaded on 31012025
use Excel;
use Artisan;
use Illuminate\Support\Facades\File;
use Session;
use DB;
use PDF;
use Storage;
use Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\ActivityTracker;
use App\Models\JwtToken;
use App\Models\Signature;
use DateTime;
use GuzzleHttp\Client as GuzzleClient;
use App\Models\IgnoredInstitutions;
use App\Jobs\DeleteVq;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
set_time_limit(0);
class VqListingController extends Controller
{
    public function updateSku(Request $request){
        foreach ($request['id'] as $key => $value) {
            // $mrp_margin = (count($request['mrp_margin']) > $key)? round($request['mrp_margin'][$key],2) : '0';
            // $prt_percent =  $request['prt_percent'][$key];
            // $ptr_rate = $request['ptr_rate'][$key];
            // $vq_id = $request['vq_id'][$key];
            // $item_code = $request['item_code'][$key];
            // echo $key; echo '<br>';
            // echo $vq_id.'/'.$item_code.'/'.$mrp_margin.'/'.$prt_percent.'/'.$ptr_rate;echo '<br>';

            //$updation = VoluntaryQuotationSkuListing::where(['id'=>$value,'brand_name'=>$request['brand_name']])
            $updation = VoluntaryQuotationSkuListing::where(['id'=>$value])
            ->update(['discount_percent'=>$request['prt_percent'][$key],'discount_rate'=>$request['ptr_rate'][$key], 'mrp_margin'=>round($request['mrp_margin'][$key],2), 'updated_at'=>date('Y-m-d H:i:s')]);
            //$this->activityTracker($value,Session::get("emp_code"),ucwords(strtolower(Session::get("emp_name"))).', '.$this->getLevelName(Session::get("level")).', '.Session::get("division_name").' applied '.$request['prt_percent'][$key].'% discount to item '.$request['item_code'][$key],'update_through_manage_request_sku');
            $this->activityTracker($request['vq_id'][$key],Session::get("emp_code"),ucwords(strtolower(Session::get("emp_name"))).', '.$this->getLevelName(Session::get("level")).', '.Session::get("division_name").' applied '.$request['prt_percent'][$key].'% discount to item '.$request['item_code'][$key],'update_through_manage_request_sku');
        }
        return "added data";  
    }

    public function vqExport(){
        return Excel::download(new VqExport, 'voluntary_quotation.xlsx');
    }

    public function getInitiatorVqListing(Request $request){
        return response()->json([
            'success'=>true, 
            'message'=>'string', 
            'data'=>VoluntaryQuotation::where('is_deleted', 0)->get()
        ]);
    }

    public function SendApprovedVq(Request $request){
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        $this->dispatch(new SendMetisAll($jwt->jwt_token));
        return response()->json([
            'success'=>true, 
            'result' => "Success"
        ]);
    }

    public function createVq(Request $request){
        $name = Session::get("emp_name");
        $division_name = Session::get("division_name");
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $vq_checker = VoluntaryQuotation::where('year',$year)->where('parent_vq_id',0)->where('is_deleted', 0)->exists();
       if(!$vq_checker) {
            $this->dispatch(new CreateVqForNew($request->from,$request->to,Session::get("emp_code"),$jwt->jwt_token,$name,$division_name));
        }else {
            $this->dispatch(new CreateVq($request->from,$request->to,Session::get("emp_code"),$jwt->jwt_token,$name,$division_name));
        }
    }

    public function reinitiateNewVQ(Request $request){
        $name = Session::get("emp_name");
        $division_name = Session::get("division_name");
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();

        $this->dispatch(new newReinitiateVQ($request->from,$request->to,Session::get("emp_code"),$jwt->jwt_token,$institution_code,$name,$division_name));
    }

    public function reinitiateVQWithNewPack(Request $request){
        $name = Session::get("emp_name");
        $division_name = Session::get("division_name");
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        //added condition to check any pending sku starts
        /*$skuPendingCheckPack = $this->checkSkuIsPendingPack($request->oldpack);
        if($skuPendingCheckPack['state'] == false)
            return response()->json([
                'state'=>false, 
                'message'=>'Old Pack '.$skuPendingCheckPack['item_code']. ' is pending with approver', 
            ]);
        else
        {
            $this->dispatch(new ReinitiateVQNewPack($request->oldpack,$request->newpack,Session::get("emp_code"),$jwt->jwt_token,$name,$division_name));
            return response()->json([
                'state'=>true, 
                'message'=>'VQ Reinitiated Successfully', 
            ]);
        }*/
        $this->dispatch(new ReinitiateVQNewPack($request->oldpack,$request->newpack,Session::get("emp_code"),$jwt->jwt_token,$name,$division_name));
        return response()->json([
                'state'=>true, 
                'message'=>'VQ Reinitiated Successfully', 
            ]);
        //added condition to check any pending sku ends
    }

    public function reinitiateVQApi(Request $request){
        $name = Session::get("emp_name");
        $division_name = Session::get("division_name");
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        $skip_approval = data_get($request, 'skip_approval');
        $selected_approval = data_get($request, 'selected_approval');
        /*check for pending item code starts*/
        $institutionCodeArr = json_decode($request->institution_code);
        $allPendingItems = [];
        $allMessages = [];
        $pendingInstitutions = [];
        $hasPendingItems = false;
        $pendingItemInst = [];

        foreach ($institutionCodeArr as $institution_code) {
            $sku_ids = $request->item_codes;
            $skuPendingCheck = $this->checkSkuIsPending($sku_ids, $institution_code);

            if ($skuPendingCheck['state'] == false) {
                $hasPendingItems = true; 
                $pendingInstitutions[$skuPendingCheck['item_list'][0]['hospital_name']] = count($skuPendingCheck['item_list']);
                foreach ($skuPendingCheck['item_list'] as $pendingItems) {
                    $message = 'Item code ' . $pendingItems['item_code'] . ' for the institution ' . $pendingItems['hospital_name'].'-'.$pendingItems['institution_id'];
                    //$allMessages[] = $message;
                    //$allPendingItems[] = $pendingItems['item_code'];
                    if (!in_array($pendingItems['item_code'], $allPendingItems)) {
                        $allPendingItems[] = $pendingItems['item_code'];
                    }
                    /*$pendingItemInst[] = [
                        'item_code' => $pendingItems['item_code'],
                        'hospital_name' => $pendingItems['hospital_name'] . '-' . $pendingItems['institution_id']
                    ];*/
                    $pendingItemInst[$pendingItems['hospital_name'] . '-' . $pendingItems['institution_id']][] = $pendingItems['item_code'];
                }
            }
        }


        if ($hasPendingItems) {
            
            //$uniquePendingItems = array_values(array_unique($allPendingItems));
            $uniquePendingItems = $allPendingItems;

            /*if(count($institutionCodeArr)>1)
            {
                $removeApprovedItems = $this->removeApprovedItems($uniquePendingItems,$institutionCodeArr);
                $uniquePendingItems = $removeApprovedItems;
            }*/
            $pendingInstitutionsArray = [];
            foreach ($pendingInstitutions as $institution_code => $count) {
                $pendingInstitutionsArray[] = $institution_code . ' (' . $count . ' items)';
            }
            $pendingInstitutionsString = implode(', ', $pendingInstitutionsArray);

            Session::put('pendingItem', $uniquePendingItems);
            Session::put('selected_institutions',$request->institution_code);
            return response()->json([
                'state' => false,
                //'message' => nl2br(implode('<br>', $allMessages)),
                'count' => count($uniquePendingItems),
                'pendingItems' => $uniquePendingItems,
                'pendingInstitutions' => $pendingInstitutionsString,
                'pendingItem_listInst' => $pendingItemInst
            ]);
        } else {
            $this->dispatch(new ReinitiateVQ($request->from,$request->to,Session::get("emp_code"),$jwt->jwt_token,$request->institution_code,$request->item_codes,$name,$division_name, $skip_approval, $selected_approval));
            return response()->json([
                'state'=>true, 
                'message'=>'VQ Reinitiated Successfully', 
                'count' => 0,
                'pendingItems' => '',
                'pendingInstitutions' => ''
            ]);
        }
        /*check for pending item code ends*/
    }
    
    public function getInitiatorVqDetail(Request $request){
        return response()->json([
            'success'=>true, 
            'message'=>'string', 
            'data'=>VoluntaryQuotation::where('vq_id',$request->id)->where('is_deleted', 0)->get()
        ]);
    }

    public function updateDiscount(Request $request){
        $vq_id = $request->vq_id ? $request->vq_id : NULL;
        $item_code = $request->item_code ? $request->item_code : NULL;
        $discount_percent = $request->discount_percent ? $request->discount_percent : NULL;
        $discount_rate = $request->discount_rate ? $request->discount_rate : NULL;
        $mrp = $request->mrp ? $request->mrp : NULL;
		$mrp_margin = (($mrp - $discount_rate)/$mrp)*100;
		
        $updation = VoluntaryQuotationSkuListing::where('vq_id',$vq_id)->where('item_code',$item_code)->whereIn('div_id',explode(',',Session::get("division_id")))->update(['discount_percent'=>$discount_percent,'discount_rate'=>$discount_rate, 'mrp_margin'=>round($mrp_margin,2), 'updated_at'=>date('Y-m-d H:i:s')]);
        //$this->activityTracker($request->vq_id,Session::get("emp_code"),'Update discount of item '.$request->item_code.' by '.$request->discount_percent.'%, done by '.Session::get("emp_name").' of division - '.Session::get("division_name").' and  level - '.Session::get("level"),'update');
        $this->activityTracker($request->vq_id,Session::get("emp_code"),ucwords(strtolower(Session::get("emp_name"))).', '.$this->getLevelName(Session::get("level")).', '.Session::get("division_name").' applied '.$request->discount_percent.'% discount to item '.$request->item_code,'update');

        return response()->json([
            'success'=>true,
            'mrp_margin'=> round($mrp_margin,2),
            'result' => $updation
        ]);
    }

    public function addCommentSku(Request $request){
        $vq_id = $request->vq_id ? $request->vq_id : NULL;
        $item_code = $request->item_code ? $request->item_code : NULL;
        $add_comment = $request->comment ? $request->comment : NULL;
        $vq_listing_data = VoluntaryQuotationSkuListing::where('vq_id',$vq_id)->where('item_code',$item_code)->whereIn('div_id',explode(',',Session::get("division_id")))->first();
        $comment = ($vq_listing_data->comments == NULL) ? array() : json_decode($vq_listing_data->comments,true);
        $cmt['comment'] = $add_comment;
        $cmt['level'] = preg_replace('/[^0-9.]+/', '', Session::get("level"));
        $cmt['emp_code'] = Session::get("emp_code");
        $cmt['emp_name'] = Session::get("emp_name");

        $comment[]=$cmt;
        $updation = VoluntaryQuotationSkuListing::where('vq_id',$vq_id)->where('item_code',$item_code)->whereIn('div_id',explode(',',Session::get("division_id")))->update(['comments'=>$comment]);
        return response()->json([
            'success'=>true, 
            'result' => $updation
        ]);
    }

    public function deleteVQ(Request $request){
        $vq_id = $request->vq_id ? $request->vq_id : NULL;
        $add_comment = $request->comment ? $request->comment : NULL;
        if($add_comment){
            $vq_data = VoluntaryQuotation::where('id',$vq_id)->where('is_deleted', 0)->first();
            $comment = ($vq_data->comments == NULL) ? array() : json_decode($vq_data->comments,true);
            $cmt['comment'] = $add_comment;
            $cmt['type'] = 'Deletion';
            $cmt['level'] = Session::get("level");
            $cmt['emp_code'] = Session::get("emp_code");
            $cmt['emp_name'] = Session::get("emp_name");

            $comment[Session::get("division_name")][]=$cmt;
            $updation = VoluntaryQuotation::where('id',$vq_id)->where('is_deleted', 0)->update(['comments'=>$comment]);
            if(preg_replace('/[^0-9.]+/', '', Session::get("level"))==5 || preg_replace('/[^0-9.]+/', '', Session::get("level"))==6)//added by govind on 020525 start
            {
                $divisionName = [];
                $divisionId = [];
 
                foreach ($request->div_id as $id) {
                    $parts = explode('-', $id);
                    if (count($parts) === 2) {
                        $divisionName[] = $parts[0];
                        $divisionId[] = $parts[1];  
                    }
                }
                //$pendingDivisions = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.div_id')->where('vq_id',$vq_id)->whereIn('div_id',explode(',',Session::get("division_id")))->where('is_deleted', 0)->where('voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status', 0)->groupBy('div_id')->get()->pluck('div_id')->toArray();
                $pendingDivisions = $divisionId;
                $deletion = VoluntaryQuotationSkuListing::where('vq_id',$vq_id)->whereIn('div_id',$pendingDivisions)->where('is_deleted', 0)->update(['is_deleted'=>1,'deleted_by'=>preg_replace('/[^0-9.]+/', '', Session::get("level"))]);
                $this->activityTracker($vq_id,Session::get("emp_code"),ucwords(strtolower(Session::get("emp_name"))).', '.$this->getLevelName(Session::get("level")).', '.implode(',', $divisionName).' Cancelled the VQ','delete',$add_comment);
            }
            else
            {//added by govind on 020525 end
                $deletion = VoluntaryQuotationSkuListing::where('vq_id',$vq_id)->whereIn('div_id',explode(',',Session::get("division_id")))->where('is_deleted', 0)->update(['is_deleted'=>1,'deleted_by'=>preg_replace('/[^0-9.]+/', '', Session::get("level"))]);
                //$this->activityTracker($vq_id,Session::get("emp_code"),'VQ Cancelled by '.Session::get("emp_name").' of division - '.Session::get("division_name").' and  level - '.$this->getLevelName(Session::get("level")),'delete',$add_comment);
                $this->activityTracker($vq_id,Session::get("emp_code"),ucwords(strtolower(Session::get("emp_name"))).', '.$this->getLevelName(Session::get("level")).', '.Session::get("division_name").' Cancelled the VQ','delete',$add_comment);
            }
            return response()->json([
                'success'=>true, 
                'result' => $deletion
            ]);
        }else{
            return response()->json([
                'success'=>false, 
                'result' => 'please add comment'
            ]);
        }
    }

    public function bulkUpdate(Request $request){

        if(preg_replace('/[^0-9.]+/', '', Session::get("level"))==5 || preg_replace('/[^0-9.]+/', '', Session::get("level"))==6)//added by govind on 020525 start
        {
            $datas = VoluntaryQuotationSkuListing::where('vq_id',$request->vq_id)->whereIn('div_id',explode(',',Session::get("division_id")))->where('is_deleted', 0)->where('voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status', 0)->get();
            $pendingDivisions = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.div_name')->where('vq_id',$request->vq_id)->whereIn('div_id',explode(',',Session::get("division_id")))->where('is_deleted', 0)->where('voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status', 0)->groupBy('div_name')->get()->pluck('div_name')->toArray();
            $divisions_activity_tracker = implode(',', $pendingDivisions);
        }
        else
        {//added by govind on 020525 end
            $datas = VoluntaryQuotationSkuListing::where('vq_id',$request->vq_id)->whereIn('div_id',explode(',',Session::get("division_id")))->where('is_deleted', 0)->get();
            $divisions_activity_tracker = Session::get("division_name");
        }        
        if(VoluntaryQuotationSkuListing::where('vq_id',$request->vq_id)->whereIn('div_id',explode(',',Session::get("division_id")))->exists()){
            //$this->activityTracker($request->vq_id,Session::get("emp_code"),'Bulk update discount of '.$request->discount_percent.'% by '.Session::get("emp_name").' of division - '.Session::get("division_name").' and  level - '.$this->getLevelName(Session::get("level")),'bulkupdate');
           // $this->activityTracker($request->vq_id,Session::get("emp_code"),'Bulk update discount of '.$request->discount_percent.'% by '.Session::get("emp_name").' of division - '.Session::get("division_name").' and  level - '.$this->getLevelName(Session::get("level")),'bulkupdate');
            $this->activityTracker($request->vq_id,Session::get("emp_code"),Session::get("emp_name").', '.$divisions_activity_tracker.' applied bulk discount of '.$request->discount_percent.'% on all SKUs','bulkupdate');
        
            DB::beginTransaction();
            // do all your updates here
            foreach($datas as $data){
                if($request->discount_percent != 0){
                $discount_rate = $data['ptr']-($data['ptr']*($request->discount_percent/100));
                }else{
                    $discount_rate = $data['ptr'];
                }

                if($request->discount_percent>99){
                    $request->discount_percent = 99;
                }

		        $mrp_margin = (($data['mrp'] - $discount_rate) / $data['mrp'])*100;
                DB::table('voluntary_quotation_sku_listing')
                ->where('id',$data['id'])->update(['discount_percent'=>$request->discount_percent,'discount_rate'=>$discount_rate, 'mrp_margin'=>$mrp_margin, 'updated_at'=>date('Y-m-d H:i:s')]);
            }
            // when done commit
            DB::commit();
            /*foreach($datas as $data){
                $discount_rate = $data['ptr']-($data['ptr']*($request->discount_percent/100));
                $updation = VoluntaryQuotationSkuListing::where('id',$data['id'])->update(['discount_percent'=>$request->discount_percent,'discount_rate'=>$discount_rate]);
            }*/
            return response()->json([
                'success'=>true, 
                'result' => 1
            ]);
        }
        return response()->json([
            'success'=>false, 
            'result' => 0
        ]);
    }

    public function singleApprove(Request $request){
        if($request->comment){
            $vq_data = VoluntaryQuotation::where('id',$request->vq_id)->where('is_deleted', 0)->first();
            $comment = ($vq_data->comments == NULL) ? array() : json_decode($vq_data->comments,true);
            $cmt['comment'] = $request->comment;
            $cmt['type'] = 'Approval';
            $cmt['level'] = Session::get("level");
            $cmt['emp_code'] = Session::get("emp_code");
            $cmt['emp_name'] = Session::get("emp_name");

            $comment[Session::get("division_name")][]=$cmt;
            $updations = VoluntaryQuotation::where('id',$request->vq_id)->where('is_deleted', 0)->update(['comments'=>$comment]);

            if(preg_replace('/[^0-9.]+/', '', Session::get("level"))==5 || preg_replace('/[^0-9.]+/', '', Session::get("level"))==6)//added by govind on 170425 start
            {
                $divisionName = [];
                $divisionId = [];
 
                foreach ($request->div_id as $id) {
                    $parts = explode('-', $id);
                    if (count($parts) === 2) {
                        $divisionName[] = $parts[0];
                        $divisionId[] = $parts[1];  
                    }
                }
                $updation = VoluntaryQuotationSkuListing::where('vq_id',$request->vq_id)->whereIn('div_id',$divisionId)->where('is_deleted', 0)->update([strtolower(Session::get("level")).'_status'=>1]);
                $this->activityTracker($request->vq_id,Session::get("emp_code"),'VQ Approved by '.Session::get("emp_name").' of division - '.implode(',', $divisionName).' and level - '.$this->getLevelName(Session::get("level")),'approve',$request->comment);
            }
            else
            {   //added by govind on 170425 end
                $updation = VoluntaryQuotationSkuListing::where('vq_id',$request->vq_id)->whereIn('div_id',explode(',',Session::get("division_id")))->where('is_deleted', 0)->update([strtolower(Session::get("level")).'_status'=>1]);
                $this->activityTracker($request->vq_id,Session::get("emp_code"),'VQ Approved by '.Session::get("emp_name").' of division - '.Session::get("division_name").' and  level - '.$this->getLevelName(Session::get("level")),'approve',$request->comment);
            }
            return response()->json([
                'success'=>true, 
                'result' => $updation
            ]);
        }else{
            return response()->json([
                'success'=>false, 
                'result' => 'please add comment'
            ]);
        }
    }

    public function singleApproveCriteria(Request $request){
        if($request->comment){
            $comment = [];
            $cmt['comment'] = $request->comment;
            $cmt['type'] = 'Approval';
            $cmt['level'] = Session::get("level");
            $cmt['emp_code'] = Session::get("emp_code");
            $cmt['emp_name'] = Session::get("emp_name");

            $year = $request->year;
            $clusters = $request->cluster;
            $institutionNames = $request->institutionName;
            $criteria = $request->criteria;
            $bulk_update_flag = $request->bulk_update_flag;

            $selectedRows = $request->selected_rows;
            $selectedRows = json_decode($selectedRows, true);

            DB::beginTransaction();
            $unique_vq = [];
            try {
                foreach ($selectedRows as $row) {
                    $sku_id = $row['sku_id'];
                    $item_code = $row['item_code'];
                    $discount_percent = $row['ptr_percent'];
                    $discount_rate = $row['ptr_rate'];
                    $vq_id = $row['vq_id'];
                    $mrp = $row['mrp_value'];
                    $update_flag = $row['update_flag'];
                    $add_comment = $request->comment ? $request->comment : NULL;
                    $vq_listing_data = VoluntaryQuotationSkuListing::where('id',$sku_id)->first();
                    $comment = ($vq_listing_data->comments == NULL) ? array() : json_decode($vq_listing_data->comments,true);
                    $cmt['comment'] = $add_comment;
                    $cmt['level'] = preg_replace('/[^0-9.]+/', '', Session::get("level"));
                    $cmt['emp_code'] = Session::get("emp_code");
                    $cmt['emp_name'] = Session::get("emp_name");

                    $comment[]=$cmt;
                    if($update_flag == 'true')
                    {
                        $mrp_margin = (($mrp - $discount_rate)/$mrp)*100;
                        
                        $updation = VoluntaryQuotationSkuListing::where('id',$sku_id)->update(['discount_percent'=>$discount_percent,'discount_rate'=>$discount_rate, 'mrp_margin'=>round($mrp_margin,2), 'updated_at'=>date('Y-m-d H:i:s'), strtolower(Session::get("level")).'_status'=>1,'comments'=>$comment]);
                        
                        $this->activityTracker($vq_id,Session::get("emp_code"),ucwords(strtolower(Session::get("emp_name"))).', '.$this->getLevelName(Session::get("level")).', '.Session::get("division_name").' applied '.$discount_percent.'% discount to item '.$item_code,'update');
                    }
                    else
                    {
                        $updation = VoluntaryQuotationSkuListing::where('id',$sku_id)->update(['updated_at'=>date('Y-m-d H:i:s'), strtolower(Session::get("level")).'_status'=>1,'comments'=>$comment]);

                        $this->activityTracker($vq_id,Session::get("emp_code"),ucwords(strtolower(Session::get("emp_name"))).', '.$this->getLevelName(Session::get("level")).', '.Session::get("division_name").' approved item '.$item_code,'update');
                    }
                    if (!in_array($vq_id, $unique_vq)) {
                        $unique_vq[] = $vq_id;
                    }
                }
                $comment[Session::get("division_name")][]=$cmt;
                $checkvq = VoluntaryQuotationSkuListing::select('vq_id')->whereIn('vq_id',$unique_vq)->where('discount_percent','>=',30)->where(strtolower(Session::get("level")).'_status',0)->where('is_deleted', 0)->pluck('vq_id')->toArray();
                $filtered_vq = array_diff($unique_vq, $checkvq);
                $voluntaryQuotations = VoluntaryQuotation::whereIn('id', $filtered_vq)->get();
                foreach ($voluntaryQuotations as $voluntaryQuotation) {
                    $existing_comments = ($voluntaryQuotation->comments == null) ? [] : json_decode($voluntaryQuotation->comments, true);
                    $existing_comments[Session::get("division_name")][] = $cmt;
                    $voluntaryQuotation->comments = json_encode($existing_comments);
                    $voluntaryQuotation->current_level = 7;
                    
                    $this->activityTracker(
                        $voluntaryQuotation->id,
                        Session::get("emp_code"),
                        'VQ Approved by ' . Session::get("emp_name") . ' level - ' . $this->getLevelName(Session::get("level")),
                        'approve',
                        $request->comment
                    );
                    $voluntaryQuotation->save();
                }
                if ($voluntaryQuotations->isNotEmpty()) {

                    $data_email['year']=$year;
                    $data_email["subject"]="IDAP VQ Process apporval  for ".$year." has been done and ready to send";
            
                    $emp_email = Employee::where('emp_category','initiator')->pluck('emp_email')->toArray();
                    $data_email['email_to']=$emp_email;

                    $data_email['email_cc'] = array();
                    // array_push($data_email['email_cc'],'thomas.edakalathoor@sunpharma.com'); //hide by arunchandru at 07042025
                    // array_push($data_email['email_cc'],'Achyut.Redkar@Sunpharma.Com'); //hide by arunchandru at 07042025
                    array_push($data_email['email_cc'],'IDAP.INSTRA@sunpharma.com');
                    $data_email['link'] = env('APP_URL').'/login';
                    try{
                        if(env('APP_URL') == 'https://idap.noesis.dev'){
                            Mail::send('admin.emails.approval_complete', $data_email, function($message)use($data_email) {
                                $message->to('mansoor@noesis.tech')
                                ->cc('vijaya@noesis.tech')
                                ->replyTo('idap.support@sunpharma.com')
                                ->subject($data_email["subject"]);
                                });
                        }
                        elseif(env('APP_URL') == 'http://172.16.8.192' || env('APP_URL') == 'https://172.16.8.192'){
                            Mail::send('admin.emails.approval_complete', $data_email, function($message)use($data_email) {
                                $message->to('BhagyeshVijay.Joshi@sunpharma.com')
                                ->cc('ImranKhan.IT@sunpharma.com')
                                ->subject($data_email["subject"]);
                                });
                        }
                        else{
                            Mail::send('admin.emails.approval_complete', $data_email, function($message)use($data_email) {
                                $message->to($data_email["email_to"])
                                ->replyTo('idap.support@sunpharma.com')
                                ->cc($data_email['email_cc'])
                                ->subject($data_email["subject"]);
                                });
                        }//commented for email issue 22052024
                        
                    }catch(JWTException $exception){
                        Log::error('Error in singleApproveCriteria: ' . $exception->getMessage(), [
                            'exception' => $exception,
                            'request' => request()->all()
                        ]);
                        $statusdesc = "0";
                        $statuscode = $exception->getMessage();
                    }
                    if (Mail::failures()) {
                        $statusdesc  =   "Error sending mail";
                        $statuscode  =   "0";
            
                    }else{
                        $statusdesc  =   "Message sent Succesfully";
                        $statuscode  =   "1";
                    }
                }
            
                
                // Commit Transaction
                DB::commit();
                return response()->json([
                    'success'=>true, 
                    'result' => true
                ]);
            }
            catch (\Exception $e) {
                Log::error('Error in singleApproveCriteria: ' . $e->getMessage(), [
                    'exception' => $e,
                    'request' => request()->all()
                ]);
                // Rollback Transaction
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'result' => 'An error occurred: ' . $e->getMessage()
                ]);
            }
        }else{
            return response()->json([
                'success'=>false, 
                'result' => 'please add comment'
            ]);
        }
    }
    
    public function bulkApprove(Request $request){

        $year = $this->getFinancialYear(date('Y-m-d'),"Y");        
        if(preg_replace('/[^0-9.]+/', '', Session::get("level"))>2){
            if(preg_replace('/[^0-9.]+/', '', Session::get("level"))==5 || preg_replace('/[^0-9.]+/', '', Session::get("level"))==6)//added by govind on 170425 start
            {
                $divisionName = [];
                $divisionId = [];
                foreach ($request->div_id as $id) {
                    $parts = explode('-', $id);
                    if (count($parts) === 2) {
                        $divisionName[] = $parts[0];
                        $divisionId[] = $parts[1];  
                    }
                }
 
                $voluntaryQuotations = VoluntaryQuotationSkuListing::where('voluntary_quotation.is_deleted',0)->where('voluntary_quotation.year',$year)->where('voluntary_quotation.current_level',preg_replace('/[^0-9.]+/', '', Session::get("level")))->whereIn('div_id',$divisionId)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation_sku_listing.is_deleted', 0)->select('voluntary_quotation.id as vq_id')->distinct()->get();
                $updation = VoluntaryQuotationSkuListing::where('voluntary_quotation.is_deleted',0)->where('voluntary_quotation.year',$year)->where('voluntary_quotation.current_level',preg_replace('/[^0-9.]+/', '', Session::get("level")))->whereIn('div_id',$divisionId)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation_sku_listing.is_deleted', 0)->update(['voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status'=>1]);
                foreach ($voluntaryQuotations as $voluntaryQuotation) {
                    $this->activityTracker(
                    $voluntaryQuotation->vq_id,
                    Session::get("emp_code"),
                    'Bulk VQ Approved by ' . Session::get("emp_name") . ' of division - ' . implode(',', $divisionName) . ' and level - ' . $this->getLevelName(Session::get("level")),
                    'bulkapprove'
                    );
                }
            }
            else
            {//added by govind on 170425 end
                $voluntaryQuotations = VoluntaryQuotationSkuListing::where('voluntary_quotation.is_deleted',0)->where('voluntary_quotation.year',$year)->where('voluntary_quotation.current_level',preg_replace('/[^0-9.]+/', '', Session::get("level")))->whereIn('div_id',explode(',',Session::get("division_id")))->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation_sku_listing.is_deleted', 0)->select('voluntary_quotation.id as vq_id')->distinct()->get();

                $updation = VoluntaryQuotationSkuListing::where('voluntary_quotation.is_deleted',0)->where('voluntary_quotation.year',$year)->where('voluntary_quotation.current_level',preg_replace('/[^0-9.]+/', '', Session::get("level")))->whereIn('div_id',explode(',',Session::get("division_id")))->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation_sku_listing.is_deleted', 0)->update(['voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status'=>1]);
                foreach ($voluntaryQuotations as $voluntaryQuotation) {
                    
                    $this->activityTracker(
                        $voluntaryQuotation->vq_id, 
                        Session::get("emp_code"),
                        'Bulk VQ Approved by ' . Session::get("emp_name") . ' of division - ' . Session::get("division_name") . ' and level - ' . $this->getLevelName(Session::get("level")),
                        'bulkapprove'
                    );
                }
            }
        }else{
            $voluntaryQuotations = VoluntaryQuotationSkuListing::where('voluntary_quotation.is_deleted', 0)
            ->where('voluntary_quotation.year', $year)
            ->where('voluntary_quotation_sku_listing.is_deleted', 0)
            ->where('voluntary_quotation.current_level', preg_replace('/[^0-9.]+/', '', Session::get("level")))
            ->leftJoin('voluntary_quotation', 'voluntary_quotation_sku_listing.vq_id', '=', 'voluntary_quotation.id')
            ->leftJoin('institution_division_mapping', 'institution_division_mapping.vq_id', '=', 'voluntary_quotation.id')
            ->whereIn('voluntary_quotation_sku_listing.div_id', explode(',', Session::get("division_id")))
            ->where('institution_division_mapping.employee_code', Session::get("emp_code"))
            ->select('voluntary_quotation.id as vq_id') 
            ->distinct()
            ->get();

            $updation = VoluntaryQuotationSkuListing::where('voluntary_quotation.is_deleted',0)
            ->where('voluntary_quotation.year',$year)
            ->where('voluntary_quotation_sku_listing.is_deleted',0)
            ->where('voluntary_quotation.current_level',preg_replace('/[^0-9.]+/', '', Session::get("level")))
            ->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            ->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','voluntary_quotation.id')
            ->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))
            ->where('institution_division_mapping.employee_code',Session::get("emp_code"))
            ->update(['voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status'=>1]);
            foreach ($voluntaryQuotations as $voluntaryQuotation) {
                
                $this->activityTracker(
                    $voluntaryQuotation->vq_id, 
                    Session::get("emp_code"),
                    'Bulk VQ Approved by ' . Session::get("emp_name") . ' of division - ' . Session::get("division_name") . ' and level - ' . $this->getLevelName(Session::get("level")),
                    'bulkapprove'
                );
            }
        }   
        //$this->activityTracker(null,Session::get("emp_code"),'Bulk VQ Approved by '.Session::get("emp_name").' of division - '.Session::get("division_name").' and  level - '.$this->getLevelName(Session::get("level")),'bulkapprove');
        
        return response()->json([
            'success'=>true, 
            'result' => $updation
        ]);
    }

    public function approveVq(Request $request){
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();

        $vq = VoluntaryQuotation::where('id',$request->vq_id)->where('is_deleted', 0)->first();
        // $updation = VoluntaryQuotation::where('id', $request->vq_id)->where('is_deleted', 0)->update(['vq_status'=>1]);//added on 05122024 for updating the vq status earlier in vqlistingcontroller
        /** code written off by arunchandru at 21-11-24 */
        $code_execution_start_time = microtime(true);
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $vq_created = date('Y-m-d H:i:s');
        $phpdate1 = strtotime( $vq_created );
        $start = date( 'Y-m-d H:i:s', $phpdate1 ); //contract_start_date
        $finddayyear = date("Y") + 1;
        $finddaymonth = date("3");
        $days = cal_days_in_month(CAL_GREGORIAN, $finddaymonth, $finddayyear);
        $enddateyear = strtotime( $finddayyear.'-'.$finddaymonth.'-'.$days );
        $end = date('Y-m-d H:i:s', $enddateyear);  //contract_end_date
    
        // /** Check Duplication Start*/
        // $vqskulistingExists = IgnoredInstitutions::select('ignored_institutions.*','voluntary_quotation.*', 'voluntary_quotation.id as vqid')
        // ->leftJoin('voluntary_quotation', 'voluntary_quotation.institution_id', '=', 'ignored_institutions.institution_id')
        // ->leftJoin('voluntary_quotation_sku_listing', 'voluntary_quotation_sku_listing.vq_id', '=', 'voluntary_quotation.id')
        // ->where('ignored_institutions.parent_institution_id', data_get($vq, 'institution_id'))
        // ->where('voluntary_quotation.year', $year)
        // ->where('voluntary_quotation.current_level', 7)
        // ->where('voluntary_quotation.vq_status', 0)
        // ->where('voluntary_quotation.is_deleted', 0)
        // ->get();
        // $ignoredInstitutions_child_ids = array_map(function($inst){return $inst->vqid;}, $vqskulistingExists->all());
       
        // $check_duplication_data = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.*','voluntary_quotation.*')
        // ->leftJoin('voluntary_quotation', 'voluntary_quotation.id', '=', 'voluntary_quotation_sku_listing.vq_id')
        // // ->leftJoin('voluntary_quotation_sku_listing', 'voluntary_quotation.item_code', '=', 'voluntary_quotation_sku_listing.item_code')
        // ->whereIn('voluntary_quotation.id', $ignoredInstitutions_child_ids)
        // ->whereIn('voluntary_quotation_sku_listing.vq_id', $ignoredInstitutions_child_ids)
        // ->where('voluntary_quotation.is_deleted', 0)
        // ->get()
        // ->toArray();
        // /** Check Dulication End*/
        $level_process = '';
        $edit_paymode = Session::get('edit_paymode_vq_id_listing');
        if(empty($edit_paymode)): // && empty($check_duplication_data)
            /** Get IgnoredInstitutions table data's by VoluntaryQuotation institution_id */
            $ignoredinstitutions = IgnoredInstitutions::where('parent_institution_id', data_get($vq, 'institution_id'))->select('parent_institution_id','institution_id')->get();
            $paymode_vq_ids = Session::get("paymode_vq_ids");
            $stored_insert_ids = [];
            if(!empty($ignoredinstitutions->toArray())){
                DB::beginTransaction();
                try {
                    $listing_data = [];
                    foreach($ignoredinstitutions as $ig_inst):
                        /** Check data there or not in VoluntaryQuotation table query */
                        $ignoreinstitution_vq = VoluntaryQuotation::where('institution_id', $ig_inst->institution_id)->where('parent_vq_id', 0)->where('year', $year)->where('is_deleted', 0)->first();
                        // print_r($ignoreinstitution_vq);
                        if(!empty($ignoreinstitution_vq)):
                            // echo 'if';die;
                            /** Get VoluntaryQuotation last rev_no query */
                            $newestClient = VoluntaryQuotation::where('institution_id', $ig_inst->institution_id)->where('year', $year)->where('is_deleted', 0)->orderBy('rev_no', 'desc')->first(); // gets the one row
                            $rev_no = (!empty($newestClient->toArray()))? $newestClient->rev_no+1 : '0';
                            /** Insert VoluntaryQuotation Table */
                            $inst = VoluntaryQuotation::Create([
                                'hospital_name' => $ignoreinstitution_vq->hospital_name,
                                'institution_id' => $ignoreinstitution_vq->institution_id,
                                'institution_key_account' => $ignoreinstitution_vq->institution_key_account,
                                'city' => $ignoreinstitution_vq->city,
                                'addr1'=>$ignoreinstitution_vq->addr1,
                                'addr2'=>$ignoreinstitution_vq->addr2,
                                'addr3'=>$ignoreinstitution_vq->addr3,
                                'stan_code'=>$ignoreinstitution_vq->stan_code,
                                'pincode'=>$ignoreinstitution_vq->pincode,
                                'state_name'=>$ignoreinstitution_vq->state_name,
                                'current_level_start_date' => $vq_created,
                                'current_level' => "7",
                                'address' => $ignoreinstitution_vq->address,
                                'zone' => $ignoreinstitution_vq->zone,
                                'institution_zone' => $ignoreinstitution_vq->institution_zone,
                                'institution_region' => $ignoreinstitution_vq->institution_region,
                                'cfa_code' => $ignoreinstitution_vq->cfa_code,
                                'contract_start_date' => $start,
                                'contract_end_date' => $end,
                                'year' => $year,
                                'sap_code' => $ignoreinstitution_vq->sap_code,
                                'created_at' => $vq_created,
                                'updated_at' => $vq_created,
                                'vq_status' => 0,
                                'parent_vq_id' => $ignoreinstitution_vq->id,
                                'rev_no' => $rev_no
                            ]);
                            $stored_insert_ids[] = $inst->id;
                 
                            $get_vq_sku_listing = VoluntaryQuotationSkuListing::where('vq_id', $request->vq_id)
                            ->where('is_deleted', 0)->get();
                            foreach($get_vq_sku_listing as $single_data):
                                $listing_data[] = [
                                    'vq_id' => $inst->id, // last insert vq id
                                    'item_code' => $single_data->item_code,
                                    'brand_name' => $single_data->brand_name,
                                    'mother_brand_name' => $single_data->mother_brand_name,
                                    'hsn_code' => $single_data->hsn_code,
                                    'applicable_gst' => $single_data->applicable_gst,
                                    'composition' => $single_data->composition,
                                    'type' => $single_data->type,
                                    'div_name' => $single_data->div_name,
                                    'div_id' => $single_data->div_id,
                                    'pack' => $single_data->pack,
                                    'ptr' => $single_data->ptr,
                                    'last_year_ptr' => $single_data->last_year_ptr,
                                    'last_year_percent' => $single_data->last_year_percent,
                                    'last_year_rate' => $single_data->last_year_rate,
                                    'pdms_discount' => $single_data->pdms_discount,
                                    'discount_percent' => $single_data->discount_percent,
                                    'discount_rate' => $single_data->discount_rate,
                                    'sap_itemcode' => $single_data->sap_itemcode,
                                    'mrp' => $single_data->mrp,
                                    'last_year_mrp' => $single_data->last_year_mrp,
                                    'mrp_margin'=>$single_data->mrp_margin,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ];
                            endforeach;
                        else:
                            // echo 'else';die;
                            /** Insert VoluntaryQuotation Table */
                            $headers = [
                                'Content-Type' => 'application/json',
                                'AccessToken' => 'key',
                                'Authorization' => 'Bearer '.$jwt['jwt_token'],
                            ];
                            $client = new GuzzleClient([
                                'headers' => $headers,
                                'verify' => false
                            ]);
                            $body = '{}';
                            $r = $client->request('POST', env('API_URL').'/API/InstitutionOnly', [
                                'body' => $body
                            ]);
                            $response = $r->getBody()->getContents();
                            
                            $response = json_decode($response, true);
                            $resp_collection = collect($response);
                            $chain_hospital_institution = $resp_collection->where('INST_ID', $ig_inst->institution_id)->first();
                            /** Get VQ id by IG-INT parent insutition id */
                            $parent_vq = VoluntaryQuotation::where('institution_id', $ig_inst->parent_institution_id)->where('year', $year)->where('is_deleted', 0)->orderBy('id', 'desc')->first();
                            /** API Insert Data */
                            $institution_vq = VoluntaryQuotation::Create([
                                'hospital_name' => $chain_hospital_institution['INST_NAME'],
                                'institution_id' => $chain_hospital_institution['INST_ID'],
                                'institution_key_account' => $chain_hospital_institution['KEY_ACC_NAME'],
                                'city' => $chain_hospital_institution['CITY'],
                                'addr1'=>$chain_hospital_institution['ADDR1'],
                                'addr2'=>$chain_hospital_institution['ADDR2'],
                                'addr3'=>$chain_hospital_institution['ADDR3'],
                                'stan_code'=>$chain_hospital_institution['STAN_CODE'],
                                'pincode'=>$chain_hospital_institution['PINCODE'],
                                'state_name'=>$chain_hospital_institution['STATE_NAME'],
                                'current_level_start_date' => $vq_created,
                                'current_level' => "7",
                                'address' => $chain_hospital_institution['ADDRESS'],
                                'zone' => $chain_hospital_institution['ZONE'],
                                'institution_zone' => data_get($chain_hospital_institution, 'LSTZONEMAPPING.0.ZSM_ZONE'),
                                'institution_region' => data_get($chain_hospital_institution, 'LSTZONEMAPPING.0.RSM_REGION'),
                                'cfa_code' => $chain_hospital_institution['CFA_CODE'],
                                'contract_start_date' => $start,
                                'contract_end_date' => $end,
                                'year' => $year,
                                'sap_code' => $chain_hospital_institution['SAP_CODE'],
                                // 'institution_zone' => isset($chain_hospital_institution['LSTZONEMAPPING'][0]->ZSM_ZONE) ? $chain_hospital_institution['LSTZONEMAPPING'][0]->ZSM_ZONE : '',
                                // 'institution_region' => isset($chain_hospital_institution['LSTZONEMAPPING'][0]->RSM_REGION) ? $chain_hospital_institution['LSTZONEMAPPING'][0]->RSM_REGION : '',
                                'created_at' => $vq_created,
                                'updated_at' => $vq_created,
                                'vq_status' => 0,
                                'parent_vq_id' => 0,
                                'rev_no' => 0//added on 05042024 to add rev no for create vq
                            ]);
                            $stored_insert_ids[] = $institution_vq->id;
                            $rateTransferInstitution = $parent_vq->institution_id;
                            
                            /** Insert VoluntaryQuotation Table */
                            $maxRevSubquery = DB::table('voluntary_quotation_sku_listing as s')
                            ->select(DB::raw('MAX(v2.rev_no) AS max_rev_no'), 's.item_code', 'v2.institution_id')
                            ->leftJoin('voluntary_quotation as v2', 'v2.id', '=', 's.vq_id')
                            ->where('v2.year', $year)
                            ->where('s.is_deleted', 0)
                            ->where('v2.vq_status', 1)
                            ->where('v2.is_deleted', 0)
                            ->where('v2.institution_id', $rateTransferInstitution)
                            ->groupBy('s.item_code');
                            $vq_sku_listing_datas = DB::table('voluntary_quotation_sku_listing as vqsl')
                            ->select('vqsl.*', 'vq.*')
                            ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
                            ->joinSub($maxRevSubquery, 'max_rev', function ($join) use ($rateTransferInstitution) {
                                $join->on('vqsl.item_code', '=', 'max_rev.item_code')
                                    ->where('vq.institution_id', $rateTransferInstitution)
                                    ->on('vq.rev_no', '=', 'max_rev.max_rev_no');
                            })
                            ->where('vq.institution_id', $rateTransferInstitution)
                            ->where('vq.year', $year)
                            ->where('vq.vq_status', 1)
                            ->where('vq.is_deleted', 0)
                            ->where('vqsl.is_deleted', 0)
                            ->get();
                            // $vq_sku_listing_datas = VoluntaryQuotationSkuListing::where('vq_id', $request->vq_id)->where('is_deleted', 0)->get();
                            foreach($vq_sku_listing_datas as $single_data){
                                $listing_data[] = [
                                    'vq_id' => $institution_vq->id, // last insert vq id
                                    'item_code' => $single_data->item_code,
                                    'brand_name' => $single_data->brand_name,
                                    'mother_brand_name' => $single_data->mother_brand_name,
                                    'hsn_code' => $single_data->hsn_code,
                                    'applicable_gst' => $single_data->applicable_gst,
                                    'composition' => $single_data->composition,
                                    'type' => $single_data->type,
                                    'div_name' => $single_data->div_name,
                                    'div_id' => $single_data->div_id,
                                    'pack' => $single_data->pack,
                                    'ptr' => $single_data->ptr,
                                    'last_year_ptr' => $single_data->last_year_ptr,
                                    'last_year_percent' => $single_data->last_year_percent,
                                    'last_year_rate' => $single_data->last_year_rate,
                                    'pdms_discount' => $single_data->pdms_discount,
                                    'discount_percent' => $single_data->discount_percent,
                                    'discount_rate' => $single_data->discount_rate,
                                    'sap_itemcode' => $single_data->sap_itemcode,
                                    'mrp' => $single_data->mrp,
                                    'last_year_mrp' => $single_data->last_year_mrp,
                                    'mrp_margin'=>$single_data->mrp_margin,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ];
                            }
                        endif;
                    endforeach;
                    Session::put('paymode_vq_ids', $stored_insert_ids);
                   
                    // added to optimise the skulisting table insert starts
                    foreach (array_chunk($listing_data, 100) as $t)  
                    {
                        DB::table('voluntary_quotation_sku_listing')->insert($t); 
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Batch insert failed: " . $e->getMessage());
                }
            }
            if(!empty($paymode_vq_ids)):
                $stored_insert_ids = array_values($paymode_vq_ids);
                Session::put('paymode_vq_ids', $stored_insert_ids);
                $level_process = 'Paymode';
            else:
                $stored_insert_ids = $stored_insert_ids;
                $level_process = 'Paymode';
            endif;
        else:
            // $stored_insert_ids = array_values($ignoredInstitutions_child_ids);
            // Session::put('paymode_vq_ids', $stored_insert_ids);
            // $level_process = 'Paymode'; // three lines hide by arunchandru at 11/02/2025
            $stored_insert_ids = array();
            $level_process = '';
        endif;


        //$updation = VoluntaryQuotation::where('id',$request->vq_id)->where('is_deleted', 0)->update(['vq_status'=>1]);//commented on 26052024 and added the updation in approvevq job
        $this->dispatch(new ApproveVq($request->vq_id,$jwt->jwt_token, Session::get('modeofscreen'), Session::get('idArr'),Session::get('changePayModeData')));//added idArr from session 15052024
        //$this->dispatch(new ApproveVq($request->vq_id,$jwt->jwt_token,null,Session::get('changePayModeData')));//changed for paymode and net disc percent change exisiting $this->dispatch(new ApproveVq($request->vq_id,$jwt->jwt_token
        return response()->json([
            'success'=>true, 
            'result' => "Success",
            'data' => $stored_insert_ids,
            'level_progress' => $level_process
        ]); 
    }

    public function vqComments(Request $request){
        $data = VoluntaryQuotation::select('comments')->where('id',$request->vq_id)->where('is_deleted', 0)->first();
        return response()->json([
            'success'=>true, 
            'result' => $data
        ]);
    }

    public function skuComments(Request $request){
        $data = VoluntaryQuotationSkuListing::select('comments')->where('id',$request->line_id)->first();
        return response()->json([
            'success'=>true, 
            'result' => $data
        ]);
    }

    public function parentVqChecker(Request $request){
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $data = VoluntaryQuotation::where('institution_id',$request->institution_id)->where('year',$year)->where('parent_vq_id',0)->where('is_deleted', 0)->exists();
        if($data == true){
            $dates = VoluntaryQuotation::select('contract_start_date','contract_end_date')->where('institution_id',$request->institution_id)->where('year',$year)->where('parent_vq_id',0)->where('is_deleted', 0)->first();
        }else{
            $dates = array();
        }

        return response()->json([
            'success'=>true, 
            'result' => $data,
            'dates' => $dates
        ]);
    }

    public function saveStockist(Request $request){
        $codes = $request->stockist_codes;
        if(!is_null($codes)){
            $updation = Stockist_master::where('institution_code',$request->institution_code)->update(['stockist_type_flag'=>0]);
            foreach($codes as $code){
                //$updation = Stockist_master::where('institution_code',$request->institution_code)->where('stockist_code',$code)->update(['stockist_type_flag'=>1]);
                $updation = Stockist_master::where('id',$code)->update(['stockist_type_flag'=>1]);
            }
            return response()->json([
                'success'=>true, 
                'result' => $updation
            ]);
        }else{
            return response()->json([
                'success'=>false, 
                'result' => 'no codes'
            ]);
        }
    }
    
    public function export($id) 
    {
        $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        // $dt = new DateTime($utc);
        // $tz = new DateTimeZone('Asia/Kolkata'); // or whatever zone you're after

        // $dt->setTimezone($tz);
        // echo $dt->format('Y-m-d H:i:s');
        // $date = new DateTime($vq['year']);

        $SPLL_data = VoluntaryQuotationSkuListing::leftJoin('employee_master','employee_master.div_code','=','voluntary_quotation_sku_listing.div_id')->select(
            'voluntary_quotation_sku_listing.sap_itemcode',
            'voluntary_quotation_sku_listing.item_code',
            'voluntary_quotation_sku_listing.brand_name',
            'voluntary_quotation_sku_listing.hsn_code',
            'voluntary_quotation_sku_listing.applicable_gst',
            'voluntary_quotation_sku_listing.composition',
            'voluntary_quotation_sku_listing.type',
            'voluntary_quotation_sku_listing.div_name',
            'voluntary_quotation_sku_listing.pack',
            'voluntary_quotation_sku_listing.discount_rate',
            'voluntary_quotation_sku_listing.mrp',
            'voluntary_quotation_sku_listing.discount_rate')
            ->selectRaw('ROUND((voluntary_quotation_sku_listing.mrp - voluntary_quotation_sku_listing.discount_rate )* 100.0 / voluntary_quotation_sku_listing.mrp,2) as percentt')
            ->where('vq_id',$id)
            ->where('voluntary_quotation_sku_listing.is_deleted',0)
            ->where('employee_master.div_type','SPLL')
            ->distinct()
            ->get(); // added on 16122024
        $SPIL_data = VoluntaryQuotationSkuListing::leftJoin('employee_master','employee_master.div_code','=','voluntary_quotation_sku_listing.div_id')->select(
            'voluntary_quotation_sku_listing.sap_itemcode',
            'voluntary_quotation_sku_listing.item_code',
            'voluntary_quotation_sku_listing.brand_name',
            'voluntary_quotation_sku_listing.hsn_code',
            'voluntary_quotation_sku_listing.applicable_gst',
            'voluntary_quotation_sku_listing.composition',
            'voluntary_quotation_sku_listing.type',
            'voluntary_quotation_sku_listing.div_name',
            'voluntary_quotation_sku_listing.pack',
            'voluntary_quotation_sku_listing.discount_rate',
            'voluntary_quotation_sku_listing.mrp',
            'voluntary_quotation_sku_listing.discount_rate')
            ->selectRaw('ROUND((voluntary_quotation_sku_listing.mrp - voluntary_quotation_sku_listing.discount_rate )* 100.0 / voluntary_quotation_sku_listing.mrp,2) as percentt')
            ->where('vq_id',$id)
            ->where('voluntary_quotation_sku_listing.is_deleted',0)
            ->where('employee_master.div_type','SPIL')
            ->distinct()
            ->get(); // added on 16122024

        $data = explode("-",$vq['year']);
        $vq_year = $data[0].substr($data[1], 2);
        $zip_file = $vq['hospital_name'].'_SPLL_SPIL_VQ'.$vq_year.'.zip'; // Name of our archive to download
        // dd($zip_file);
        // Initializing PHP class
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        // Adding file: second parameter is what will the path inside of the archive
        // So it will create another folder called "storage/" inside ZIP, and put the file there.
        if(count($SPLL_data->toArray()) > 0):
            $zip->addFile(Excel::download(new InitiatorExport($id,"SPLL"), 'Initiator-export.xlsx')->getFile(),ucwords(strtolower($vq['hospital_name'])).'_SPLL_VQ'.$vq_year.'.xlsx');
        endif;
        if(count($SPIL_data->toArray()) > 0):
            $zip->addFile(Excel::download(new InitiatorExport($id,"SPIL"), 'Initiator-export.xlsx')->getFile(),ucwords(strtolower($vq['hospital_name'])).'_SPIL_VQ'.$vq_year.'.xlsx');
        endif;
        $zip->close();

	    //dd($zip_file);

        // We return the file immediately after download
        return response()->download($zip_file);
        //return Excel::download(new InitiatorExport($id,'SPLL'), 'Initiator-export.csv');
        // return[ 
        //     Excel::download(new InitiatorExport, 'Initiator-export.csv'),
        //     Excel::download(new UsersExport, 'users.csv')
        // ];
    }

    public function latestReport(){
        $deletedFile = File::delete(public_path().'/latestreport'.Session::get('emp_code').'.zip');

        $this->dispatch(new ReportDownload(Session::get('emp_code'),Session::get('type'), Session::get('level'), Session::get("division_id")));
        return redirect()->back()->with([
            'status' => true,
            'message' => 'Report will be generated and will be available to download soon.'
        ]);
    }

    public function historyReport(){
        $deletedFile = File::delete(public_path().'/historicalreport'.Session::get('emp_code').'.zip');

        $this->dispatch(new HistoricalReportDownload(Session::get('emp_code'),Session::get('type'), Session::get('level'), Session::get("division_id")));
        return redirect()->back()->with([
            'status' => true,
            'message' => 'Report will be generated and will be available to download soon.'
        ]);
    }

    public function downloadPDF($id) {
        $data = array();
        $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $vq_date = explode("-",$vq['year']);
        $vq_year = $vq_date[0].substr($vq_date[1], 2);
        // $date = new DateTime($vq['created_at']);
        $data['vq_data']= $vq;
        /*$data['stockist_data'] = VoluntaryQuotation::join('stockist_master','stockist_master.institution_code','=','voluntary_quotation.institution_id')
        ->where('voluntary_quotation.id',$id)
        ->where('stockist_master.stockist_type_flag',1)
        ->where('voluntary_quotation.is_deleted', 0)
        ->select('stockist_master.*')->get();*/
        
        $data['poc_data'] = VoluntaryQuotation::join('poc_master','poc_master.institution_id','=','voluntary_quotation.institution_id')
        ->where('voluntary_quotation.id',$id)
        ->where('voluntary_quotation.is_deleted', 0)
        ->select('poc_master.*')->first();

        /*if($vq->parent_vq_id !=0){
            $data['revision_count']=VoluntaryQuotation::where('parent_vq_id',$vq->parent_vq_id)->where('id','<=',$id)->where('is_deleted', 0)->count();

        }else{
            $data['revision_count']=0;
        }*/
        $revision_count = VoluntaryQuotation::select('rev_no')->where('id',$id)->where('is_deleted', 0)->first();
        $data['revision_count'] = $revision_count->rev_no;
        
        //dd($data['vq_data']['institution_id']);
        //dd($data['stockist_data']['stockist_code']);
	    //dd($data['poc_data']['institution_id']);
        // pdf for spll
        $data['signature']=Signature::first();
        $type1 = pathinfo(base_path().'/public/images/'.$data['signature']->spll_sign, PATHINFO_EXTENSION);
        $type2 = pathinfo(base_path().'/public/images/'.$data['signature']->spil_sign, PATHINFO_EXTENSION);
        $data['signature']->spll_sign = 'data:image/' . $type1 . ';base64,' . base64_encode(file_get_contents(base_path().'/public/images/'.$data['signature']->spll_sign));
        $data['signature']->spil_sign = 'data:image/' . $type2 . ';base64,' . base64_encode(file_get_contents(base_path().'/public/images/'.$data['signature']->spil_sign));

        $spll_stockist_data = VoluntaryQuotation::join('stockist_master','stockist_master.institution_code','=','voluntary_quotation.institution_id')
        ->where('voluntary_quotation.id',$id)
        ->where('stockist_master.stockist_type_flag',1)
        ->where('voluntary_quotation.is_deleted', 0)
        ->where(function($query) {
            $query->whereNull('stockist_master.stockist_type')
                  ->orWhere('stockist_master.stockist_type', 'SPLL');
        })
        ->select('stockist_master.*')->get();
        $data['stockist_data'] = $spll_stockist_data;
        $data['stockist_count'] = ($spll_stockist_data->toArray() != '')? count($spll_stockist_data->toArray()) : '0';
        $pdf1 = PDF::loadView('admin.pdf.spllpdf', compact('data'));
        Storage::put('spll_cover.pdf', $pdf1->output());

	    //dd($pdf1);

        //pdf for spil
        $spil_stockist_data = VoluntaryQuotation::join('stockist_master','stockist_master.institution_code','=','voluntary_quotation.institution_id')
        ->where('voluntary_quotation.id',$id)
        ->where('stockist_master.stockist_type_flag',1)
        ->where('voluntary_quotation.is_deleted', 0)
        ->where(function($query) {
            $query->whereNull('stockist_master.stockist_type')
                  ->orWhere('stockist_master.stockist_type', 'SPIL');
        })
        ->select('stockist_master.*')->get();
        $data['stockist_data'] = $spil_stockist_data;
        $data['stockist_count'] = ($spil_stockist_data->toArray() != '')? count($spil_stockist_data->toArray()) : '0';
        $pdf2 = PDF::loadView('admin.pdf.spilpdf', compact('data'));
        Storage::put('spil_cover.pdf', $pdf2->output());


        $zip_file = $vq['hospital_name'].'_cover_letter.zip'; // Name of our archive to download
        // Initializing PHP class
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        // Adding file: second parameter is what will the path inside of the archive
        // So it will create another folder called "storage/" inside ZIP, and put the file there.
        if(count($spll_stockist_data) > 0)
        {
            $zip->addFile(storage_path('app/spll_cover.pdf'),ucwords(strtolower($vq['hospital_name'])).'_SPLL_VQ'.$vq_year.'.pdf');
        }
        if(count($spil_stockist_data) > 0)
        {
            $zip->addFile(storage_path('app/spil_cover.pdf'),ucwords(strtolower($vq['hospital_name'])).'_SPIL_VQ'.$vq_year.'.pdf');
        }
        $zip->close();

	    //dd($zip_file);
        // We return the file immediately after download
        return response()->download($zip_file);

	    //dd(response());

        /*$show = Institution::all();
        $pdf = PDF::loadView('admin.pdf.pdf', compact('show'));
        return $pdf->download('institute.pdf');*/
    }

    public function activityTracker($vq_id,$emp_code,$activity,$type="",$meta=NULL){
        $updation = ActivityTracker::Create([
            'vq_id' => $vq_id,
            'emp_code' => $emp_code,
            'activity' => $activity,
            'type'=>$type,
            'meta_data'=>$meta,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }


    public function getLevelName($level){
        $return_level = '';
        if($level == 'L1'){
            $return_level = 'RSM';
        }elseif($level == 'L2'){
            $return_level = 'ZSM';
        }elseif($level == 'L3'){
            $return_level = 'NSM';
        }elseif($level == 'L4'){
            $return_level = 'SBU';
        }elseif($level == 'L5'){
            $return_level = 'Semi Cluster';
        }elseif($level == 'L6'){
            $return_level = 'Cluster';
        }elseif($level == 'L8'){
            $return_level = 'CEO';
        }
        return $return_level;
    }

    public function checkSkuIsPending(&$sku_ids, $institution_code){
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $checkPending = VoluntaryQuotation::select('item_code','voluntary_quotation.id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->whereIn('vqsl.item_code',$sku_ids)->where('institution_id',$institution_code)->where('year',$year)->where('vq_status',0)->where('voluntary_quotation.is_deleted', 0)->where('vqsl.is_deleted',0)->groupBy('item_code')->exists();
        if($checkPending)
        {
            $checkPending = VoluntaryQuotation::select('vqsl.item_code','voluntary_quotation.rev_no','voluntary_quotation.hospital_name','voluntary_quotation.institution_id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->whereIn('vqsl.item_code',$sku_ids)->where('institution_id',$institution_code)->where('year',$year)->where('vq_status',0)->where('voluntary_quotation.is_deleted', 0)->where('vqsl.is_deleted',0)->groupBy('item_code')->get();
            $status['state'] = false;
            $status['item_list'] = $checkPending;

        }
        else
        {
            $status['state'] = true;
        }
        return $status;
    }
    public function removeApprovedItems($sku_ids, $institution_code){//added on 29052024
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $checkApproved = VoluntaryQuotation::select('item_code','voluntary_quotation.id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->whereIn('vqsl.item_code',$sku_ids)->whereIn('institution_id',$institution_code)->where('year',$year)->where('vq_status',1)->where('voluntary_quotation.is_deleted', 0)->where('vqsl.is_deleted',0)->groupBy('item_code')->exists();
        if($checkApproved)
        {
            $approvedItems = VoluntaryQuotation::select('vqsl.item_code')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->whereIn('vqsl.item_code',$sku_ids)->whereIn('institution_id',$institution_code)->where('year',$year)->where('vq_status',1)->where('voluntary_quotation.is_deleted', 0)->where('vqsl.is_deleted',0)->groupBy('item_code')->get()->pluck('item_code')->toArray();
            $sku_ids = array_diff($sku_ids, $approvedItems);

        }
        return $sku_ids;
    }
    public function checkSkuIsPendingPack($old_pack){
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $status['state'] = true;
        $sku = VoluntaryQuotationSkuListing::select('item_code','sap_itemcode')->join('voluntary_quotation','voluntary_quotation.id','=','voluntary_quotation_sku_listing.vq_id')->where('vq_status', '!=',1)->where('item_code', $old_pack['item_code'])->where('year', $year)->where('voluntary_quotation.is_deleted', '!=', 1)->where('voluntary_quotation_sku_listing.is_deleted','!=',1)->get();
        if(count($sku) > 0){
            $status['state'] = false;
            $status['item_code'] = $old_pack['sap_itemcode'];
            return $status;
        }
        else
        {
            $status['state'] = true;
        }
        return $status;
    }
    public function criteriaExport(Request $request)
    {
        $status = $request->input('status');
        $year = $request->input('year');
        $criteria = json_decode($request->input('criteria'), true);
        $institutionNames = json_decode($request->input('institutionNames'), true);
        $clusters = json_decode($request->input('clusters'), true);

        $response = Excel::download(new VqExportCriteria($year, $status, $criteria, $institutionNames, $clusters), 'voluntary_quotation.xlsx');
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');

        return $response;
    }
    public function editStockist(Request $request){
        $checkedStockist = $request->stockist_change_data;
        // $vq_listing_controller = new VqListingController;
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $vq_id = $request->vq_id;
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        if(!is_null($checkedStockist)){
            DB::beginTransaction();
            try
            {
                $insert_data = [];
                $stockist_id = [];
                //$updation = Stockist_master::where('institution_code',$request->institution_code)->update(['stockist_type_flag'=>0]);
                $maxRevSubquery = DB::table('voluntary_quotation_sku_listing as s')
                    ->select(DB::raw('MAX(v2.rev_no) AS max_rev_no'), 's.item_code', 'v2.institution_id')
                    ->leftJoin('voluntary_quotation as v2', 'v2.id', '=', 's.vq_id')
                    ->where('v2.year', $year)
                    ->where('s.is_deleted', 0)
                    ->where('v2.vq_status', 1)
                    ->where('v2.is_deleted', 0)
                    ->where('v2.institution_id', $request->institution_code)
                    ->groupBy('s.item_code');

                $vqslStockistData = DB::table('voluntary_quotation_sku_listing as vqsl')
                ->select('vqsl.*', 'vq.*','vq.id as vq_id','vqsl.id as sku_id','rev_no as revision_count')
                ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
                ->joinSub($maxRevSubquery, 'max_rev', function ($join) use($request) {
                    $join->on('vqsl.item_code', '=', 'max_rev.item_code')
                        ->where('vq.institution_id',  $request->institution_code)
                        ->on('vq.rev_no', '=', 'max_rev.max_rev_no');
                })
                ->where('vq.institution_id',  $request->institution_code)
                ->where('vq.year', $year)
                ->where('vq.vq_status', 1)
                ->where('vq.is_deleted', 0)
                ->where('vqsl.is_deleted', 0)
                ->get();
                /*$vqslStockistData = DB::table('voluntary_quotation_sku_listing AS parent_sku')
                    ->select('vq.id as vq_id', 'hospital_name', 'vq.institution_id', 'institution_key_account', 'sap_code', 'city', 'zone', 'YEAR', 
                     'institution_zone', 'institution_region', 'parent_sku.item_code', 'sap_itemcode', 'brand_name', 'mother_brand_name', 
                     'hsn_code', 'applicable_gst', 'type', 'div_name', 'div_id', 'pack', 'last_year_ptr', 'last_year_percent', 
                     'last_year_rate', 'last_year_mrp', 'mrp', 'ptr', 'parent_sku.discount_percent', 
                     'parent_sku.discount_rate', 'parent_sku.mrp_margin', 'rev_no as revision_count','composition','parent_sku.id as sku_id','vq.parent_vq_id')
                    ->leftJoin('voluntary_quotation AS vq', 'vq.id', '=', 'parent_sku.vq_id')
                    ->join('z_max_rev AS max_rev', function ($join) {
                        $join->on('max_rev.item_code', '=', 'parent_sku.item_code')
                        ->on('max_rev.max_rev_no', '=', 'vq.rev_no');
                    })
                    ->where('vq.year', $year)
                    ->where('vq.institution_id',   $request->institution_code)
                    ->where('max_rev.institution_id',   $request->institution_code)
                    ->where('parent_sku.is_deleted', 0)
                    ->where('vq.is_deleted',0)
                ->get();*/
                $uniqueVqIds = $vqslStockistData->pluck('vq_id')->unique()->toArray();

                foreach($checkedStockist as $code){
                    if($code['state']!='unchanged')
                    {
                        if($code['state']=='checked')
                        {
                            /*$stockist_sku_exists = VoluntaryQuotationSkuListingStockist::where('stockist_id', $code['id'])->whereIn('vq_id',$uniqueVqIds)->where('is_deleted',0)->exists();*/
                            if(count($vqslStockistData)>0 /*&& !$stockist_sku_exists*/)
                            {
                                //$updation = Stockist_master::where('institution_code',$request->institution_code)->where('stockist_code',$code['value'])->update(['stockist_type_flag'=>1]);
                                $updation = Stockist_master::where('id',$code['id'])->update(['stockist_type_flag'=>1]);
                                $stockist_id[] = $code['id'];
                                $stockist_value[] = $code['value'];
                                foreach($vqslStockistData as $s){
                                    $exists = VoluntaryQuotationSkuListingStockist::where('stockist_id', $code['id'])
                                    ->where('vq_id', $s->vq_id)
                                    ->where('item_code', $s->item_code)
                                    ->where('is_deleted', 0)
                                    ->exists();
                                    if (!$exists) {
                                        $DiscountMargin = DiscountMarginMaster::where('item_code', $s->item_code)->get()->toArray();
                                        $inputMargin = ($DiscountMargin)? (($DiscountMargin[0]['discount_margin'])? $DiscountMargin[0]['discount_margin'] : 10) : 10 ;
                                        $ptr = $s->ptr;
                                        $discount_percent = $s->discount_percent;
                                        if($ptr == 0)
                                        {
                                            $netDiscountRateToStockist = 0;
                                        }
                                        else
                                        {
                                            $discountamt = $ptr - (($ptr * $discount_percent) / 100);
                                            $marginamt = $discountamt * $inputMargin / 100;
                                            $nrv = $discountamt - $marginamt;
                                            $netDiscountRateToStockist = ($ptr - $nrv) / $ptr * 100;
                                            $netDiscountRateToStockist = number_format((float)$netDiscountRateToStockist, 2, '.', '');
                                        }
                                        
                                        $insert_data[] = [
                                            'vq_id' => $s->vq_id,
                                            'sku_id' => $s->sku_id,
                                            'item_code' => $s->item_code,
                                            'stockist_id' => $code['id'],
                                            'parent_vq_id' => $s->parent_vq_id,
                                            'revision_count' => $s->revision_count,
                                            'payment_mode' => 'DM',
                                            'net_discount_percent' => $netDiscountRateToStockist,
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ];
                                    }
                                }
                                /** added code for activity tracker */
                                // AddStockist::dispatch($uniqueVqIds,  $stockist_id, $stockist_value, $jwt->jwt_token);
                                /** ended code activity tracker  */
                            }
                        }
                        else if($code['state']=='unchecked')
                        {
                            $stockist_id = [];
                            $stockist_value = [];
                            $stockist_sku_exists = VoluntaryQuotationSkuListingStockist::where('stockist_id', $code['id'])->whereIn('vq_id',$uniqueVqIds)->where('is_deleted',0)->exists();
                            if(count($vqslStockistData)>0 && $stockist_sku_exists)
                            {
                                $stockist_ids = VoluntaryQuotationSkuListingStockist::where('stockist_id', $code['id'])->whereIn('vq_id',$uniqueVqIds)->where('is_deleted',0)->get()->pluck('id')->toArray();
                                //$updation = Stockist_master::where('institution_code',$request->institution_code)->where('stockist_code',$code['value'])->update(['stockist_type_flag'=>0]);
                                $updation = Stockist_master::where('id',$code['id'])->update(['stockist_type_flag'=>0]);
                                $updation = VoluntaryQuotationSkuListingStockist::whereIn('id', $stockist_ids)
                                ->update(['is_deleted' => 1]);
                                DeleteStockist::dispatch($uniqueVqIds,$stockist_ids,$jwt->jwt_token);
                            }
                        }
                    }
                }
                if(count($insert_data) > 0)
                {
                    $chunkSize = 100;
                    $insertedIds = [];
                    foreach (array_chunk($insert_data, $chunkSize) as $chunk) {
                        //VoluntaryQuotationSkuListingStockist::insert($chunk);
                        foreach ($chunk as $data) {
                            $record = VoluntaryQuotationSkuListingStockist::create($data);
                            $insertedIds[] = $record->id;
                        }
                    }
                    //ApproveVq::dispatch($vq_id, $jwt->jwt_token,$insertedIds);
                    if(count($stockist_id) > 0)
                    {
                        Session::put('stockist_id_arr', $stockist_id);
                    }
                }
                // Commit Transaction
                DB::commit();
                return response()->json([
                    'success'=>true, 
                    'result' => $updation ?? ''
                ]);
            }
            catch (\Exception $e) {
                // Rollback Transaction
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'result' => 'An error occurred: ' . $e->getMessage()
                ]);
            }
        }else{
            return response()->json([
                'success'=>false, 
                'result' => 'no codes'
            ]);
        }
    }

    public function checkFinancialReport(Request $request){
        $selectedYear = $request->financialYear;
        $filePath = public_path() . '/financialyearhistoricalreport' . Session::get('emp_code') . '-'.$selectedYear.'.zip';
        if (file_exists($filePath)) {
            $historical_file_link ='financialyearhistoricalreport' . Session::get('emp_code') . '-'.$selectedYear. '.zip';
            $historical_file_creation_date = date("j F Y H:i:s", filectime(public_path().'/'.$historical_file_link));
        }else{
            $historical_file_link = null;
            $historical_file_creation_date = null;
        }
        return response()->json([
            'result' => $historical_file_link,
            'created_date'=>$historical_file_creation_date
        ]);
    }
    public function financialhistoryReport(Request $request){
        $selectedYear = $request->financialYear;
        $selectedDivisions = $request->div_id;
        $deletedFile = File::delete(public_path().'/financialyearhistoricalreport'.Session::get('emp_code'). '-'.$selectedYear.'.zip');

        if (!in_array('all', $selectedDivisions)) {
            $division_comma_seperated = implode(",", $selectedDivisions);
            $this->dispatch(new FinancialHistoricalReportDownload(Session::get('emp_code'),Session::get('type'), Session::get('level'), $division_comma_seperated, $selectedYear));
        }
        else
        {
            $this->dispatch(new FinancialHistoricalReportDownload(Session::get('emp_code'),Session::get('type'), Session::get('level'), Session::get("division_id"), $selectedYear));
        }
        return response()->json([
            'status' => true,
            'message' => 'Report will be generated and will be available to download soon.'
        ]);
    }
    public function filterLatestReport(Request $request){
        $selectedDivisions = $request->div_id;
        $deletedFile = File::delete(public_path().'/latestreport'.Session::get('emp_code').'.zip');

        if (!in_array('all', $selectedDivisions)) {
            $division_comma_seperated = implode(",", $selectedDivisions);
            $this->dispatch(new ReportDownload(Session::get('emp_code'),Session::get('type'), Session::get('level'), $division_comma_seperated));
        }
        else
        {
            $this->dispatch(new ReportDownload(Session::get('emp_code'),Session::get('type'), Session::get('level'), Session::get("division_id")));
        }
        return response()->json([
            'status' => true,
            'message' => 'Report will be generated and will be available to download soon.'
        ]);
    }
    public function generate_vq_save_change(Request $request){
        $selected_rows = $request->input('selected_rows', []);
        //$jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        if(!is_null($selected_rows)){
            DB::beginTransaction();
            try
            {
                $data = [];
                foreach($selected_rows as $row){
                    $updation = Stockist_master::where('id', $row['stockist_id'])->update(['payment_mode' => $row['payment_mode'], 'updated_at'=>now()]);
                    $vqslStockistExists = VoluntaryQuotationSkuListingStockist::
                    where('vq_id', $row['vq_id'])
                    ->where('sku_id',$row['sku_id'])
                    ->where('stockist_id',$row['stockist_id'])
                    ->where('item_code',$row['item_code'])
                    ->exists();
                    if(!$vqslStockistExists){
                        /*$data[] = [
                            'vq_id' => $row['vq_id'],
                            'sku_id' => $row['sku_id'],
                            'item_code' => $row['item_code'],
                            'stockist_id' => $row['stockist_id'],
                            'parent_vq_id' => $row['parent_vq_id'],
                            'revision_count' => $row['rev_no'],
                            'payment_mode' => $row['payment_mode'],
                            'net_discount_percent' => $row['net_discount_percent'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];*/
                    }
                    else
                    {
                        $updation = VoluntaryQuotationSkuListingStockist::where('vq_id', $row['vq_id'])
                        ->where('sku_id', $row['sku_id'])
                        ->where('stockist_id', $row['stockist_id'])
                        ->where('item_code', $row['item_code'])
                        ->update(['payment_mode' => $row['payment_mode'],'net_discount_percent' => $row['net_discount_percent'], 'updated_at'=>now()]);
                    }
                }
                // if(count($data)>0)
                // {
                //     $chunkSize = 100;
                //     foreach (array_chunk($data, $chunkSize) as $chunk) {
                //         VoluntaryQuotationSkuListingStockist::insert($chunk);
                //     }
                // }  // hide by arunchandru 05042025
                
                // Commit Transaction
                DB::commit();
                return response()->json([
                    'success'=>true, 
                    'result' => $updation ?? ''
                ]);
            }
            catch (\Exception $e) {
                // Rollback Transaction
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'result' => 'An error occurred: ' . $e->getMessage()
                ]);
                Log::error('An error occurred: ' . $e->getMessage());
            }
        }else{
            return response()->json([
                'success'=>false, 
                'result' => 'no selection'
            ]);
        }
    }
    public function generate_vq_send_quotation(Request $request){
        $selected_rows = $request->selected_rows;

        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        if(!is_null($selected_rows)){
            DB::beginTransaction();
            try
            {
                $stockist_id = [];
                $uniqueRows = [];
                $seen = [];

                $uniqueInstitutionIds = array_unique(array_column($selected_rows, 'institution_id'));
                $availablePocInstitutions = DB::table('poc_master')
                    ->whereIn('institution_id', $uniqueInstitutionIds)
                    ->pluck('institution_id')
                    ->toArray();
                $institutionIdsWithoutPoc = array_diff($uniqueInstitutionIds, $availablePocInstitutions);
                if (!empty($institutionIdsWithoutPoc)) {
                    $hospitalsWithoutPoc = [];
                    foreach ($selected_rows as $row) {
                        if (in_array($row['institution_id'], $institutionIdsWithoutPoc)) {
                            $hospitalsWithoutPoc[$row['institution_id']] = $row['hospital_name'].'-'.$row['institution_id'];
                        }
                    }
                    $hospitalNames = array_unique(array_values($hospitalsWithoutPoc));
                    $missingInstitutionIds = array_unique(array_keys($hospitalsWithoutPoc));
                    Session::put('selected_item_generate_vq_poc', $missingInstitutionIds);
                    return response()->json([
                        'success' => 'poc_missing',
                        'result' => 'No POC available for the following institution(s): ' . implode(', ', $hospitalNames),
                        'missing_institution_ids' => $missingInstitutionIds
                    ]);
                }

                foreach($selected_rows as $row){
                    $stockist_id[] = VoluntaryQuotationSkuListingStockist::where('vq_id', $row['vq_id'])
                    ->where('sku_id', $row['sku_id'])
                    ->where('stockist_id', $row['stockist_id'])
                    ->where('item_code', $row['item_code'])->pluck('id')->toArray();
                }
                // print_r($stockist_id);

                if($stockist_id != NULL):
                    $stockist_id = array_reduce($stockist_id, 'array_merge', []);
                    // print_r($stockist_id);die;
                    /*added on 15052024 for revision wise activity log and send quotation api send starts*/
                    $all_vq = VoluntaryQuotationSkuListingStockist::select('voluntary_quotation.id','voluntary_quotation.rev_no','voluntary_quotation.institution_id','voluntary_quotation.rev_no','voluntary_quotation.contract_start_date','voluntary_quotation.contract_end_date','voluntary_quotation.created_at', 'voluntary_quotation.hospital_name')
                    ->join('voluntary_quotation', 'voluntary_quotation.id','=','voluntary_quotation_sku_listing_stockist.vq_id')
                    ->whereIn('voluntary_quotation_sku_listing_stockist.id', $stockist_id)
                   ->distinct()->get();
                    // print_r('all');
                    // print_r(count($all_vq));
                    $html = '';
                    foreach ($all_vq as $vq_data_final) {
                        
                        $listing_data = VoluntaryQuotationSkuListingStockist::join('voluntary_quotation_sku_listing', 'voluntary_quotation_sku_listing.id', '=', 'voluntary_quotation_sku_listing_stockist.sku_id')
                        ->select('voluntary_quotation_sku_listing_stockist.payment_mode', 'voluntary_quotation_sku_listing_stockist.net_discount_percent',  'voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.item_code',  'voluntary_quotation_sku_listing.discount_percent', 'voluntary_quotation_sku_listing.discount_rate', 'voluntary_quotation_sku_listing.mrp','voluntary_quotation_sku_listing.ptr', 'stockist_master.stockist_code', 'stockist_master.stockist_name')
                        ->join('stockist_master', 'stockist_master.id', '=', 'voluntary_quotation_sku_listing_stockist.stockist_id')
                        ->where('voluntary_quotation_sku_listing_stockist.vq_id', $vq_data_final->id)
                        ->where('voluntary_quotation_sku_listing_stockist.is_deleted', 0)
                        ->whereIn('voluntary_quotation_sku_listing_stockist.id', $stockist_id)
                        ->where('voluntary_quotation_sku_listing.is_deleted', 0)
                        ->where('stockist_master.stockist_type_flag', 1)->get();
                        // print_r('VQ_ID - ');
                        // print_r(count($listing_data));
                        // print_r( $vq_data_final->id);
                        $vq_listing_controller = new VqListingController;
                        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
                        $send_json = array();
                        $exist_data = array();
                        $validate_empty_datas = array();
                        $date=date_create($vq_data_final->created_at);
                        if (date_format($date,"m") >= 4) {//On or After April (FY is current year - next year)
                            $financial_year = (date_format($date,'Y')) . '-' . (date_format($date,'y')+1);
                        } else {//On or Before March (FY is previous year - current year)
                            $financial_year = (date_format($date,'Y')-1) . '-' . date_format($date,'y');
                        }

                        $is_IdapDiscTran = IdapDiscTran::where('IDAP_PRNT_Q_ID', $vq_data_final->id)->exists();
                        if($is_IdapDiscTran == true):
                            $base_idap_prnt_q_id = $vq_data_final->id; // Base vq_id
                            // Fetch all IDs matching the base pattern
                            $max_idap_prnt_q_id = IdapDiscTran::where('IDAP_PRNT_Q_ID', 'LIKE', $base_idap_prnt_q_id . "%")->max('IDAP_PRNT_Q_ID');
                            
                            if ($max_idap_prnt_q_id) {
                                // Extract the decimal part of max_idap_prnt_q_id
                                $parts = explode('_', $max_idap_prnt_q_id);
                                
                                if (isset($parts[1])) {
                                    // $max_idap_prnt_q_id = IdapDiscTran::where('idap_prnt_q_id', 'LIKE', $base_idap_prnt_q_id . "_%")->max('idap_prnt_q_id');
                                    $sql = "SELECT IDAP_PRNT_Q_ID FROM IDAP_DISC_TRAN WHERE IDAP_PRNT_Q_ID LIKE '{$base_idap_prnt_q_id}_%' ORDER BY CAST(SUBSTRING_INDEX(IDAP_PRNT_Q_ID, '_', -1) AS DECIMAL(10,2)) DESC LIMIT 1";
                                    $get_max_idap_prnt_q_id = DB::select($sql);
                                    $max_idap_prnt_q_id = $get_max_idap_prnt_q_id[0]->IDAP_PRNT_Q_ID;
                                    $parts1 = explode('_', $max_idap_prnt_q_id);
                                    $numeric_parts = (string) $parts1[1];
                                    $new_numeric_part = bcadd($numeric_parts, '0.01', 2); // Increment by 0.1
                                    // Generate the new unique ID
                                    $idap_prnt_q_id =  $base_idap_prnt_q_id . '_' . $new_numeric_part;
                                } else {
                                    $idap_prnt_q_id = $base_idap_prnt_q_id . '_0.01'; // First increment
                                }
                            } else {
                                $idap_prnt_q_id = $base_idap_prnt_q_id; // No existing record, use base ID
                            }
                        else:
                            $idap_prnt_q_id = $vq_data_final->id;
                        endif;

                        $send_json['fin_year'] = $financial_year;
                        $send_json['institute_code'] = $vq_data_final->institution_id;
                        $send_json['vq_id'] = $vq_data_final->id;
                        $send_json['revision_number'] = $vq_data_final->rev_no;
                        foreach($listing_data as $single_data){
                            $sku_arr['hospital_name'] = $vq_data_final->hospital_name;;
                            $sku_arr['institution_id'] = $vq_data_final->institution_id;
                            $sku_arr['item_code'] = $single_data['item_code'];
                            $sku_arr['div_code'] = $single_data['div_id'];
                            $sku_arr['discount_percent'] = $single_data['discount_percent'];
                            $sku_arr['discount_rate'] = $single_data['discount_rate'];
                            $sku_arr['stockist_code'] = $single_data['stockist_code'];
                            $sku_arr['stockist_name'] = $single_data['stockist_name'];
                            $sku_arr['payment_mode'] = $single_data['payment_mode'];
                            $sku_arr['net_discount_percent'] = $single_data['net_discount_percent'];

                            if($single_data['net_discount_percent'] === null || $single_data['net_discount_percent'] === 0):
                                $validate_empty_datas[] = $sku_arr;
                            endif;
                            if($single_data['payment_mode'] === null || $single_data['payment_mode'] === 0):
                                $validate_empty_datas[] = $sku_arr;
                            endif;
                            
                            $send_json['sku'][]=$sku_arr;

                        }

                        foreach ($send_json['sku'] as $json_data) {
                            $exist_data[] = [
                                'FIN_YEAR' => $send_json['fin_year'],
                                'HOSPITAL_NAME' => $vq_data_final->hospital_name,
                                'INST_ID' => $send_json['institute_code'],
                                'DIV_CODE' => $json_data['div_code'],
                                'ITEM_CODE' => $json_data['item_code'],
                                'IDAP_PRNT_Q_ID' => $idap_prnt_q_id,
                                'REV_NO' => $send_json['revision_number'],
                                'STOCKIST_CODE' => $json_data['stockist_code'],
                                'STOCKIST_NAME' => $json_data['stockist_name'],
                                'DISCOUNT_MODE' => $json_data['payment_mode'],
                            ];
                        }
                        
                        $serialized_data = array_map('serialize', $exist_data);
                        $duplicates = array_unique(array_diff_assoc($serialized_data, array_unique($serialized_data)));
                        $duplicate_arrays = array_map('unserialize', $duplicates);
                        
                        $duplicate_array_datas[] = $duplicate_arrays; 
                        $validate_empty_array_datas[] = $validate_empty_datas;

                    }
                    // echo '<pre>';
                    // echo 'Duplicate';
                    // print_r($duplicate_array_datas);
                    // echo 'empty';
                    // print_r($validate_empty_array_datas);die;
                    // echo 'html';
                    // print_r($html);
                    if(!empty(array_filter($duplicate_array_datas))):
                        $html .= '<p>Following item(s) have Duplicate information:</p>
                        <div id="pendingMessage">
                            <ul>';
                            foreach(array_filter($duplicate_array_datas) as $first_loop):
                                foreach($first_loop as $second_loop):
                                    $html .= '<li>'.$second_loop['ITEM_CODE'].' in '.$second_loop['INST_ID'].'-'.$second_loop['HOSPITAL_NAME'].' - Stockist Code '.$second_loop['STOCKIST_CODE'].' '.$second_loop['STOCKIST_NAME'].'</li>';
                                endforeach;
                            endforeach;
                        $html .= '</ul>
                        </div>';
                    endif;
                    if(!empty(array_filter($validate_empty_array_datas))):
                        // if($vq_data_final->vq_status != 0):
                            $html .= '<p>Following item(s) have missing datas:</p>
                            <div id="pendingMessage">
                                <ul>';
                                    foreach(array_filter($validate_empty_array_datas) as $empty_data_first_loop):
                                        $empty_data_first_loop = array_map("unserialize", array_unique(array_map("serialize", $empty_data_first_loop)));
                                        foreach($empty_data_first_loop as $empty_data_second_loop):
                                            $payment_mode = ($empty_data_second_loop['payment_mode'] === null || $empty_data_second_loop['payment_mode'] === 0)? 'is Pay mode value is null' : '' ;
                                            $net_discount_percent = ($empty_data_second_loop['net_discount_percent'] === null || $empty_data_second_loop['net_discount_percent'] === 0)? 'is net_discount_percent value is null' : '' ;
                                            $html .= '<li>'.$empty_data_second_loop['item_code'].' in '.$empty_data_second_loop['institution_id'].'-'.$empty_data_second_loop['hospital_name'].' - Stockist Code '.$empty_data_second_loop['stockist_code'].' '.$empty_data_second_loop['stockist_name'].' '.$payment_mode.', '.$net_discount_percent.'</li>';
                                        endforeach;
                                    endforeach;
                                $html .= '</ul>
                            </div>';
                        // endif;
                    endif;
                    $duplicate_and_empty_datas = array($duplicate_array_datas, $validate_empty_array_datas);
                    $idap_disc_tran_exist_html = $html;

                    if(!empty($idap_disc_tran_exist_html) && !empty($duplicate_and_empty_datas)):
                        return response()->json([
                            'success' => 'send_quotation_failed',
                            'result' => $idap_disc_tran_exist_html
                            // 'duplicate_and_empty_datas' => $duplicate_and_empty_datas
                        ]);
                    endif;

                endif; 


                GenerateVq::dispatch($jwt->jwt_token,$stockist_id);
                foreach($selected_rows as $row){
                    $key = $row['vq_id'] . '-' . $row['item_code'];
                    if (!isset($seen[$key])) {
                        $seen[$key] = true;
                        $uniqueRows[] = $row;
                    }
                }
                foreach($uniqueRows as $row)
                {
                    $updation = VoluntaryQuotationSkuListing::where('vq_id',$row['vq_id'])->update(['product_type' => 'old']);
                    /*$items = VoluntaryQuotationSkuListing::where('vq_id', $row['vq_id'])
                    ->pluck('item_code')->toArray();
                    $checkSkulistItems = VoluntaryQuotationSkuListingStockist::whereIn('item_code',$items)
                    ->where('vq_id',$row['vq_id'])
                    ->whereNull('payment_mode')
                    ->whereNull('net_discount_percent')
                    ->exists();
                    if(!$checkSkulistItems)
                    {
                        $updation = VoluntaryQuotation::where('id',$row['vq_id'])->update(['vq_status' => 1]);
                    }*/
                }
                // Commit Transaction
                DB::commit();
                return response()->json([
                    'success'=>true, 
                    'result' => $updation ?? ''
                ]);
            }
            catch (\Exception $e) {
                // Rollback Transaction
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'result' => 'An error occurred: ' . $e->getMessage()
                ]);
            }
        }else{
            return response()->json([
                'success'=>false, 
                'result' => 'no selection'
            ]);
        }
    }
    public function newPriceSheet ($id) 
    {
        $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        

        $data = explode("-",$vq['year']);
        $vq_year = $data[0].substr($data[1], 2);
        if(Session::get('type') == 'approver')
        {
            $SPLLcheck = Employee::where('div_type', "SPLL")->whereNotNull('div_code')->whereIn('div_code', explode(',', Session::get('division_id')))->exists();
            $SPILcheck = Employee::select('div_code')->where('div_type', "SPIL")->whereNotNull('div_code')->whereIn('div_code', explode(',', Session::get('division_id')))->exists();
        }
        else
        {
            $SPLLcheck = true;
            $SPILcheck = true;
        }
        if(!$SPLLcheck && !$SPILcheck)
        {
            return;
        }
        $zip_file = $vq['hospital_name'].'_SPLL_SPIL_VQ'.$vq_year.'.zip'; // Name of our archive to download
        // dd($zip_file);
        // Initializing PHP class
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        // Adding file: second parameter is what will the path inside of the archive
        // So it will create another folder called "storage/" inside ZIP, and put the file there.
        if($SPLLcheck)
        {
            $zip->addFile(Excel::download(new InitiatorExportNew($id,"SPLL"), 'Initiator-export.xlsx')->getFile(),ucwords(strtolower($vq['hospital_name'])).'_SPLL_VQ'.$vq_year.'.xlsx');
        }
        if($SPILcheck)
        {
            $zip->addFile(Excel::download(new InitiatorExportNew($id,"SPIL"), 'Initiator-export.xlsx')->getFile(),ucwords(strtolower($vq['hospital_name'])).'_SPIL_VQ'.$vq_year.'.xlsx');
        }
        $zip->close();

        
        return response()->download($zip_file);

    }
    public function reinitiateVQCopyCounter(Request $request){
        $name = Session::get("emp_name");
        $division_name = Session::get("division_name");
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        $this->dispatch(new ReinitiateVQCopyCounter($request->fromcounter,$request->tocounter,Session::get("emp_code"),$jwt->jwt_token,$name,$division_name));
        return response()->json([
            'state'=>true, 
            'message'=>'VQ Reinitiated Successfully', 
        ]);
    }
    public function filterLatestReportDP(Request $request){
        $selectedDivisions = $request->div_id;
        $deletedFile = File::delete(public_path().'/latestreport'.Session::get('emp_code').'.zip');

        if (!in_array('all', $selectedDivisions)) {
            $filter = array();
            $division_comma_seperated = implode(",", $selectedDivisions);
            $filter['session_div_id'] = Session::get("division_id");
            $filter['filter_div_id'] = $division_comma_seperated;
            $this->dispatch(new ReportDownload(Session::get('emp_code'),Session::get('type'), Session::get('level'), $filter));
        }
        else
        {
            $filter = array();
            $filter['session_div_id'] = Session::get("division_id");
            $filter['filter_div_id'] = 'all';
            $this->dispatch(new ReportDownload(Session::get('emp_code'),Session::get('type'), Session::get('level'), $filter));
        }
        return response()->json([
            'status' => true,
            'message' => 'Report will be generated and will be available to download soon.'
        ]);
    }
    public function filterHistoricalReport(Request $request){
        $selectedDivisions = $request->div_id;
        $deletedFile = File::delete(public_path().'/latestreport'.Session::get('emp_code').'.zip');

        if (!in_array('all', $selectedDivisions)) {
            $filter = array();
            $division_comma_seperated = implode(",", $selectedDivisions);
            $filter['session_div_id'] = Session::get("division_id");
            $filter['filter_div_id'] = $division_comma_seperated;
            $this->dispatch(new HistoricalReportDownload(Session::get('emp_code'),Session::get('type'), Session::get('level'), $division_comma_seperated));
        }
        else
        {
            $filter = array();
            $filter['session_div_id'] = Session::get("division_id");
            $filter['filter_div_id'] = 'all';
            $this->dispatch(new HistoricalReportDownload(Session::get('emp_code'),Session::get('type'), Session::get('level'), Session::get("division_id")));
        }
        return response()->json([
            'status' => true,
            'message' => 'Report will be generated and will be available to download soon.'
        ]);
    }
    public function checkMultiInstituteNewItemFn(Request $request)
    {
        try {
            $vq_listing_controller = new VqListingController;
            $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'), "Y");
            $item_codes = $request->item_codes;
            $institution_ids = $request->selectedInstitutions;

            $newItems = [];
            $itemInstitutionMapping = [];

            foreach ($institution_ids as $institution_code) {
                // Fetch existing items for the current institution
                /*$existingItems = VoluntaryQuotation::select('item_code')
                    ->leftJoin('voluntary_quotation_sku_listing as vqsl', 'vqsl.vq_id', '=', 'voluntary_quotation.id')
                    ->whereIn('vqsl.item_code', $item_codes)
                    ->where('institution_id', $institution_code)
                    ->where('year', $year)
                    ->where('voluntary_quotation.is_deleted', 0)
                    ->where('vqsl.is_deleted', 0)
                    ->groupBy('item_code')
                    ->pluck('item_code')
                    ->toArray();*/
                $existingItems = VoluntaryQuotationSkuListing::select('item_code')
                    ->whereIn('voluntary_quotation_sku_listing.vq_id', function ($query) use ($institution_code,$year){
                        $query->select('id')
                                ->from('voluntary_quotation')
                                ->where('institution_id', $institution_code)
                                ->where('year', $year)
                                ->where('voluntary_quotation.is_deleted', 0);
                    })
                    ->whereIn('voluntary_quotation_sku_listing.item_code', $item_codes)
                    ->groupBy('item_code')
                    ->pluck('item_code')
                    ->toArray();

                // Determine if there are any missing items
                $missingItems = array_diff($item_codes, $existingItems);
                if (!empty($missingItems)) {
                    foreach ($missingItems as $item_code) {
                        $newItems[] = $item_code;
                        $itemInstitutionMapping[$item_code][] = $institution_code;
                    }
                }
            }

            if (!empty($newItems)) {
                $messages = [];
                foreach ($itemInstitutionMapping as $item_code => $institutions) {
                    //$messages[] = "Item code ".$item_code." is new for institutions: " . implode(', ', $institutions);
                     $itemInstitutionMappingArray[] = [
                        'item_code' => $item_code,
                        'institutions' => $institutions
                    ];
                }
                return response()->json([
                    'status' => false,
                    'data' => $itemInstitutionMappingArray
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'No new items are present'
                ]);
            }

        } catch (\Exception $e) {
            // Log the exception
            \Log::error('Error in checkMultiInstituteNewItemFn: ' . $e->getMessage());

            // Return a generic error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again later.'.$e->getMessage()
            ], 500);
        }
    }
    public function workflow_adjust(Request $request){
        $selected_rows = $request->selected_rows;
        $action = $request->action;
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
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
            // $vq_ids = array_column($selected_rows, 'vq_id');
            $vq_ids = array();
            DB::beginTransaction();
            try
            {
                if($action == 'move_up')
                {
                    foreach ($selected_rows as $single_vq) {
                        $vq = VoluntaryQuotation::where('id',$single_vq['vq_id'])->first();
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
                                $checkItems = VoluntaryQuotation::select('item_code','voluntary_quotation.id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_vq['vq_id'])->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->exists();
                                if($checkItems == true)
                                {
                                    $checkExceptionsItems = VoluntaryQuotation::select('item_code','div_id', 'vqsl.id as vqslid', 'voluntary_quotation.id as vqid')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_vq['vq_id'])->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->get();
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
                                    $checkItems = VoluntaryQuotation::select('item_code','voluntary_quotation.id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_vq['vq_id'])->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->exists();

                                    if($checkItems == true)
                                    {
                                        $checkExceptionsItems = VoluntaryQuotation::select('item_code','div_id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_vq['vq_id'])->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->get();
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
                                $updation = VoluntaryQuotation::where('id',$single_vq['vq_id'])->where('is_deleted', 0)->update(['current_level'=>$calc_level,'updated_at'=>date('Y-m-d H:i:s')]);
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
                                ->where('vq_id',$single_vq['vq_id'])
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
                                        //$vq_listing_controller->activityTracker($single_vq['vq_id'],'','VQ Moved Up By '.$emp_details.' of division - '.$vq_detail->div_name.' at level - '.$level_name,'workflowadjust');
                                        $vq_listing_controller->activityTracker($single_vq['vq_id'],'','VQ Moved Up By '.$emp_details.' of  division - '.$vq_detail->div_name.' from '.$level_name.' to '.$level_name_current,'workflowadjust');
                                    //}
                                }
                            }
                        }
                    }
                }
                else if($action == 'mv_initiator')
                {
                    foreach ($selected_rows as $single_vq) {
                        $vq = VoluntaryQuotation::where('id',$single_vq['vq_id'])->first();
                        $calc_level = $vq->current_level;
                        if($calc_level != 7)//if rev already in initator level cannot move up
                        {
                            $checkItems = VoluntaryQuotation::select('item_code','voluntary_quotation.id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_vq['vq_id'])->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->exists();
                            if($checkItems == true)
                            {
                                $checkExceptionsItems = VoluntaryQuotation::select('item_code','div_id', 'vqsl.id as vqslid', 'voluntary_quotation.id as vqid')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_vq['vq_id'])->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->get();
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
                                $updation = VoluntaryQuotation::where('id',$single_vq['vq_id'])->where('is_deleted', 0)->update(['current_level'=>$calc_level,'updated_at'=>date('Y-m-d H:i:s')]);
                                $vq_details = VoluntaryQuotationSkuListing::select(
                                "div_name" ,
                                DB::raw("(sum(l".$last_level."_status)) as statuss")
                                )
                                ->where('vq_id',$single_vq['vq_id'])
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
                                        //$vq_listing_controller->activityTracker($single_vq['vq_id'],'','VQ Moved to Initiator By '.$emp_details.' of division - '.$vq_detail->div_name.' at level - '.$level_name,'workflowadjust');
                                    $vq_listing_controller->activityTracker($single_vq['vq_id'],'','VQ Moved to Initiator By '.$emp_details.' of division - '.$vq_detail->div_name.' from '.$level_name.' to '.$level_name_current,'workflowadjust');
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
                        $vq = VoluntaryQuotation::where('id',$single_vq['vq_id'])->first();
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
                        $vq = VoluntaryQuotation::where('id',$single_vq['vq_id'])->first();
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
                                $checkItems = VoluntaryQuotation::select('item_code','voluntary_quotation.id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_vq['vq_id'])->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->exists();
                                if($checkItems == true)
                                {
                                    $checkExceptionsItems = VoluntaryQuotation::select('item_code','div_id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_vq['vq_id'])->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->get();
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
                                $updation = VoluntaryQuotation::where('id',$single_vq['vq_id'])->where('is_deleted', 0)->update(['current_level'=>$calc_level,'updated_at'=>date('Y-m-d H:i:s')]);
                                $updation = VoluntaryQuotationSkuListing::where('vq_id',$single_vq['vq_id'])->where('is_deleted',0)->update([$sku_listing_status_column=>0,$sku_listing_previous_column=>0,'updated_at'=>date('Y-m-d H:i:s')]);
                                $vq_details = VoluntaryQuotationSkuListing::select(
                                "div_name" ,
                                DB::raw("(sum(l".$last_level."_status)) as statuss")
                                )
                                ->where('vq_id',$single_vq['vq_id'])
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
                                        //$vq_listing_controller->activityTracker($single_vq['vq_id'],'','VQ Sent back By '.$emp_details.' of division - '.$vq_detail->div_name.' from level - '.$level_name,'workflowadjust');
                                    $vq_listing_controller->activityTracker($single_vq['vq_id'],'','VQ Sent back By '.$emp_details.' of division - '.$vq_detail->div_name.' from - '.$level_name.' to '.$level_name_current,'workflowadjust');
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
                if(count($vq_ids) > 0 && $ceo_approval_level != 0)
                {
                    if(now()->day == 1):
                        $report_type = 'monthly';
                    else:
                        $report_type = 'daily';
                    endif;
                    // $this->dispatch(new SendEmailCEOApproval($calc_level, $action, $report_type)); // hide this CEO approval mails
                }
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
    public function change_locking_period(Request $request){
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        if($request->locking_period == "true")
        {
            $locking = 'Y';
        }
        else
        {
            $locking = 'N';
        }
        $ip_address = request()->ip();
        $userAgent = request()->header('User-Agent');
        DB::beginTransaction();
        try
        {
            $updation = DB::table('check_discounted')->update(['is_enabled'=>$locking,'year'=>$year,'updated_at'=>date('Y-m-d H:i:s')]);
            $addl_params = array();
            $addl_params['fin_year'] = $year;
            $addl_params['ip_address'] = $ip_address;
            $addl_params['changed_at'] = date('Y-m-d H:i:s');
            $addl_params['user_agent'] = $userAgent;
            $addl_params['changed_to'] = $locking;
            $vq_listing_controller->activityTracker(1,Session::get("emp_code"),json_encode($addl_params),'change_locking_period');
            DB::commit();
        }
        catch (\Exception $e) {
            // Log the exception
            \Log::error('Error in changing locking period: ' . $e->getMessage());

            // Return a generic error response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again later.'.$e->getMessage()
            ], 500);
        }
    }
    public function pendingItemExport()
    {
        $pendingItem = Session::get('pendingItem');
        $selected_institutions = Session::get('selected_institutions');
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        /*$response = Excel::download(new PendingItemExport($pendingItem, $selected_institutions, $year), 'pending_items.xlsx');
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');*/
        $filePath = storage_path('app/public/pending_items.xlsx');
        $export = new PendingItemExport($pendingItem, $selected_institutions, $year);
        $export->export($filePath);
        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="pending_items.xlsx"');
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');

        return $response;
    }
    public function pendingInstitutionExport()
    {
        $selected_institutions = Session::get('selected_institutions');
        $pendingItem = Session::get('pendingItem');
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        //$response = Excel::download(new InstitutionsExport($selected_institutions, $year), 'pending_institutions.xlsx');
        $response = Excel::download(new PendingItemExport($pendingItem, $selected_institutions, $year), 'pending_institutions.xlsx');
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');

        return $response;
    }
    public function activity_filter(Request $request)
    {
        $vq_id = $request->vq_id;
        $activity_filter_value = $request->activity_value;

        $query = ActivityTracker::where('vq_id',$vq_id);
        if (!empty($activity_filter_value)) {
            $query->whereIn('type', $activity_filter_value);
        }

        $data = $query->orderBy('id', 'DESC')->get();
        foreach ($data as $activity) {
            $activity->json_data = $this->extractJson($activity->activity);
            $activity->activity_text = $this->stripJson($activity->activity);
        }
        if(count($data) == 0)
        {
            $status = false;
        }
        else
        {
            $status = true;
        }
        return response()->json([
            'data' => $data,
            'status'=> $status
        ]);
    }
    // Helper method to extract JSON from activity column
    private function extractJson($string) {
        $jsonStart = strpos($string, '{'); // Look for the start of the JSON object
        if ($jsonStart !== false) {
            $json = substr($string, $jsonStart);
            json_decode($json);
            // Check if it's valid JSON
            if (json_last_error() == JSON_ERROR_NONE) {
                return $json;
            }
        }
        return null;
    }

    // Helper method to strip JSON from activity column
    private function stripJson($string) {
        $jsonStart = strpos($string, '{'); // Look for the start of the JSON object
        if ($jsonStart !== false) {
            return substr($string, 0, $jsonStart); // Return everything before the JSON
        }
        return $string; // Return original if no JSON
    }
    public function make_parent(Request $request)
    {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $date = new DateTime();

        $id = $request->selected_vq_delete_id;
        $parent_institution_selection = $request->parent_institution_selection;
        $data = VoluntaryQuotation::where('id',$id)->first();

        $checkPending_and_get_items = VoluntaryQuotation::where('institution_id',$parent_institution_selection)
        ->where('year',$year)
        ->where('vq_status',0)
        ->where('is_deleted', 0)
        ->get();

        if(!empty($checkPending_and_get_items->toArray())):
            $toarray = $checkPending_and_get_items->toArray();
            $rev_no_impld = implode(',', array_column($toarray, 'rev_no'));
            return response()->json([
                'success'=> false, 
                'type'=>'error',
                'message' => $toarray[0]['hospital_name'] .' - '.$toarray[0]['institution_id'].' Revision No: '.$rev_no_impld.' send quotation is pending so we cannot make it as parent counter.'
            ]);
        endif;

     
        $year1 = $date->format('Y');
        $month = $date->format('m');

        if ($month < 4) {
            $year1 -= 1;
        }
        // if(empty($checkPending_and_get_items)):
            $ignoredinstitutions = IgnoredInstitutions::where('parent_institution_id', data_get($data, 'institution_id'))->select('parent_institution_id','institution_id')->get()->toArray();
            $check_selection_counter = array_column($ignoredinstitutions, 'institution_id');
            if (($key = array_search($parent_institution_selection, $check_selection_counter)) !== false) {
                unset($check_selection_counter[$key]);
            }
            $financialYearEndDate = new DateTime(($year1 + 1) . '-03-31 00:00:00');
            $child_institutionCheckVQ = VoluntaryQuotation::where('year', $year)->where('is_deleted', 0)->where('institution_id', $parent_institution_selection)->exists();
            $vq_created = date('Y-m-d H:i:s');
            DB::beginTransaction();
            try 
            {
                /** hide by arunchandru at 17122024 */
                // if($child_institutionCheckVQ)
                // {
                //     $chain_hospital_institution = VoluntaryQuotation::where('year', $year)->where('institution_id', $parent_institution_selection)->first();
                //     $inst = VoluntaryQuotation::Create([
                //         'hospital_name' => $chain_hospital_institution->hospital_name,
                //         'institution_id' => $chain_hospital_institution->institution_id,
                //         'institution_key_account' => $chain_hospital_institution->institution_key_account,
                //         'city' => $chain_hospital_institution->city,
                //         'addr1' => $chain_hospital_institution->addr1,
                //         'addr2' => $chain_hospital_institution->addr2,
                //         'addr3' => $chain_hospital_institution->addr3,
                //         'parent_vq_id' => 0,
                //         'current_level' => "7",
                //         'stan_code' => $chain_hospital_institution->stan_code,
                //         'pincode' => $chain_hospital_institution->pincode,
                //         'current_level_start_date' => date('Y-m-d H:i:s'),
                //         'state_name' => $chain_hospital_institution->state_name,
                //         'address' => $chain_hospital_institution->address,
                //         'zone' => $chain_hospital_institution->zone,
                //         'cfa_code' => $chain_hospital_institution->cfa_code,
                //         'institution_zone' => $chain_hospital_institution->institution_zone,
                //         'institution_region' => $chain_hospital_institution->institution_region,
                //         'contract_start_date' => date('Y-m-d H:i:s'),
                //         'contract_end_date' => $chain_hospital_institution->contract_end_date,
                //         'year' => $year,
                //         'sap_code' => $chain_hospital_institution->sap_code,
                //         'created_at' => date('Y-m-d H:i:s'),
                //         'updated_at' => date('Y-m-d H:i:s'),
                //         'rev_no' => 0
                //     ]);
                // }
                // else
                // {
                //     $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
                //     $headers = [
                //         'Content-Type' => 'application/json',
                //         'AccessToken' => 'key',
                //         'Authorization' => 'Bearer '.$jwt['jwt_token'],
                //     ];
                    
                //     $client = new GuzzleClient([
                //         'headers' => $headers,
                //         'verify' => false
                //     ]);
                    
                //     $body = '{}';
                    
                //     $r = $client->request('POST', env('API_URL').'/API/InstitutionOnly', [
                //         'body' => $body
                //     ]);
                //     $response = $r->getBody()->getContents();

                    
                //     $response = json_decode($response, true);
                //     $resp_collection = collect($response);
                //     $chain_hospital_institution = $resp_collection->where('INST_ID', $parent_institution_selection)->first();
                //     $inst = VoluntaryQuotation::Create([
                //         'hospital_name' => $chain_hospital_institution['INST_NAME'],
                //         'institution_id' => $chain_hospital_institution['INST_ID'],
                //         'institution_key_account' => $chain_hospital_institution['KEY_ACC_NAME'],
                //         'city' => $chain_hospital_institution['CITY'],
                //         'addr1'=>$chain_hospital_institution['ADDR1'],
                //         'addr2'=>$chain_hospital_institution['ADDR2'],
                //         'addr3'=>$chain_hospital_institution['ADDR3'],
                //         'parent_vq_id' => 0,
                //         'stan_code'=>$chain_hospital_institution['STAN_CODE'],
                //         'pincode'=>$chain_hospital_institution['PINCODE'],
                //         'state_name'=>$chain_hospital_institution['STATE_NAME'],
                //         'current_level_start_date' => $vq_created,
                //         'current_level' => "7",
                //         'address' => $chain_hospital_institution['ADDRESS'],
                //         'zone' => $chain_hospital_institution['ZONE'],
                //         'institution_zone' => data_get($chain_hospital_institution, 'LSTZONEMAPPING.0.ZSM_ZONE'),
                //         'institution_region' => data_get($chain_hospital_institution, 'LSTZONEMAPPING.0.RSM_REGION'),
                //         'cfa_code' => $chain_hospital_institution['CFA_CODE'],
                //         'contract_start_date' => date('Y-m-d H:i:s'),
                //         'contract_end_date' => $financialYearEndDate->format('Y-m-d H:i:s'),
                //         'year' => $year,
                //         'sap_code' => $chain_hospital_institution['SAP_CODE'],
                //         'institution_zone' => isset($chain_hospital_institution['LSTZONEMAPPING'][0]['ZSM_ZONE']) ? $chain_hospital_institution['LSTZONEMAPPING'][0]['ZSM_ZONE'] : '',
                //         'institution_region' => isset($chain_hospital_institution['LSTZONEMAPPING'][0]['RSM_REGION']) ? $chain_hospital_institution['LSTZONEMAPPING'][0]['RSM_REGION'] : '',
                //         'created_at' => $vq_created,
                //         'updated_at' => $vq_created,
                //         'rev_no' =>0
                //     ]);
                // }
            
                /** added by arunchandru at 17122024 */
                if(empty($child_institutionCheckVQ))
                {
                    $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
                    $headers = [
                        'Content-Type' => 'application/json',
                        'AccessToken' => 'key',
                        'Authorization' => 'Bearer '.$jwt['jwt_token'],
                    ];
                    
                    $client = new GuzzleClient([
                        'headers' => $headers,
                        'verify' => false
                    ]);
                    
                    $body = '{}';
                    
                    $r = $client->request('POST', env('API_URL').'/API/InstitutionOnly', [
                        'body' => $body
                    ]);
                    $response = $r->getBody()->getContents();

                    
                    $response = json_decode($response, true);
                    $resp_collection = collect($response);
                    $chain_hospital_institution = $resp_collection->where('INST_ID', $parent_institution_selection)->first();
                    $inst = VoluntaryQuotation::Create([
                        'hospital_name' => $chain_hospital_institution['INST_NAME'],
                        'institution_id' => $chain_hospital_institution['INST_ID'],
                        'institution_key_account' => $chain_hospital_institution['KEY_ACC_NAME'],
                        'city' => $chain_hospital_institution['CITY'],
                        'addr1'=>$chain_hospital_institution['ADDR1'],
                        'addr2'=>$chain_hospital_institution['ADDR2'],
                        'addr3'=>$chain_hospital_institution['ADDR3'],
                        'parent_vq_id' => 0,
                        'stan_code'=>$chain_hospital_institution['STAN_CODE'],
                        'pincode'=>$chain_hospital_institution['PINCODE'],
                        'state_name'=>$chain_hospital_institution['STATE_NAME'],
                        'current_level_start_date' => $vq_created,
                        'current_level' => "7",
                        'address' => $chain_hospital_institution['ADDRESS'],
                        'zone' => $chain_hospital_institution['ZONE'],
                        'institution_zone' => data_get($chain_hospital_institution, 'LSTZONEMAPPING.0.ZSM_ZONE'),
                        'institution_region' => data_get($chain_hospital_institution, 'LSTZONEMAPPING.0.RSM_REGION'),
                        'cfa_code' => $chain_hospital_institution['CFA_CODE'],
                        'contract_start_date' => date('Y-m-d H:i:s'),
                        'contract_end_date' => $financialYearEndDate->format('Y-m-d H:i:s'),
                        'year' => $year,
                        'sap_code' => $chain_hospital_institution['SAP_CODE'],
                        'institution_zone' => isset($chain_hospital_institution['LSTZONEMAPPING'][0]['ZSM_ZONE']) ? $chain_hospital_institution['LSTZONEMAPPING'][0]['ZSM_ZONE'] : '',
                        'institution_region' => isset($chain_hospital_institution['LSTZONEMAPPING'][0]['RSM_REGION']) ? $chain_hospital_institution['LSTZONEMAPPING'][0]['RSM_REGION'] : '',
                        'created_at' => $vq_created,
                        'updated_at' => $vq_created,
                        'rev_no' =>0,
                        'vq_status' => 1,
                    ]);
                    $stored_insert_ids = $inst->id;
                
                    $vq_listing_controller = new VqListingController;
                    $vq_listing_controller->activityTracker($inst->id,Session::get("emp_code"),'Chain Hospital VQ Created from Parent VQ '.$data->institution_id, 'child_transfered_parent');
                
                    $maxRevSubquery = DB::table('voluntary_quotation_sku_listing as s')
                    ->select(DB::raw('MAX(v2.rev_no) AS max_rev_no'), 's.item_code', 'v2.institution_id')
                    ->leftJoin('voluntary_quotation as v2', 'v2.id', '=', 's.vq_id')
                    ->where('v2.year', $year)
                    ->where('s.is_deleted', 0)
                    ->where('v2.vq_status', 1)
                    ->where('v2.is_deleted', 0)
                    ->where('v2.institution_id', $data->institution_id)
                    ->groupBy('s.item_code');

                    $revisedData = DB::table('voluntary_quotation_sku_listing as vqsl')
                    ->select('vqsl.*', 'vq.*')
                    ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
                    ->joinSub($maxRevSubquery, 'max_rev', function ($join) use($data) {
                        $join->on('vqsl.item_code', '=', 'max_rev.item_code')
                            ->where('vq.institution_id',  $data->institution_id)
                            ->on('vq.rev_no', '=', 'max_rev.max_rev_no');
                    })
                    ->where('vq.institution_id',  $data->institution_id)
                    ->where('vq.year', $year)
                    ->where('vq.vq_status', 1)
                    ->where('vq.is_deleted', 0)
                    ->where('vqsl.is_deleted', 0)
                    ->get();
                    foreach($revisedData as $single_data){
                        $listing_data[]=[
                            'vq_id' =>$inst->id,
                            'item_code' => $single_data->item_code,
                            'brand_name' => $single_data->brand_name,
                            'mother_brand_name' => $single_data->mother_brand_name,
                            'hsn_code' => $single_data->hsn_code,
                            'applicable_gst' => $single_data->applicable_gst,
                            'composition' => $single_data->composition,
                            'type' => $single_data->type,
                            'div_name' => $single_data->div_name,
                            'div_id' => $single_data->div_id,
                            'pack' => $single_data->pack,
                            'ptr' => $single_data->ptr,
                            'last_year_ptr' => $single_data->last_year_ptr,
                            'last_year_percent' => $single_data->last_year_percent,
                            'last_year_rate' => $single_data->last_year_rate,
                            'pdms_discount' => $single_data->pdms_discount,
                            'discount_percent' => $single_data->discount_percent,

                            'discount_rate' => $single_data->discount_rate,
                            'sap_itemcode' => $single_data->sap_itemcode,
                            'mrp' => $single_data->mrp,
                            'last_year_mrp' => $single_data->last_year_mrp,
                            'mrp_margin'=>$single_data->mrp_margin,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                    foreach (array_chunk($listing_data,100) as $t)  
                    {
                        DB::table('voluntary_quotation_sku_listing')->insert($t); 
                    }
                }

                $oldParentIgnoredInstitution = IgnoredInstitutions::where('parent_institution_id', $data->institution_id)->count();
                if($oldParentIgnoredInstitution != 0)
                {
                    if($oldParentIgnoredInstitution == 1)
                    {
                        $deletion = IgnoredInstitutions::where('parent_institution_id', $data->institution_id)->where('institution_id', $parent_institution_selection)->delete();
                        //$updation =  IgnoredInstitutions::where('parent_institution_id',$data->institution_id)->where('institution_id', $parent_institution_selection)->update(['parent_institution_id' => $data->institution_id.'_old', 'updated_at'=>now()]);
                    }
                    else
                    {
                        $deletion =  IgnoredInstitutions::where('parent_institution_id',$data->institution_id)->where('institution_id', $parent_institution_selection)->delete();
                        $updation =  IgnoredInstitutions::where('parent_institution_id',$data->institution_id)->update(['parent_institution_id' => $parent_institution_selection, 'updated_at'=>now()]);
                    }
                }
                if($data->parent_vq_id == 0){
                    $result = VoluntaryQuotation::find($id)->update(['is_deleted' => 1]);
                    $child = VoluntaryQuotation::where('parent_vq_id',$id)->update(['is_deleted' => 1]);
                }else{
                    $parent = VoluntaryQuotation::find($data->parent_vq_id)->update(['is_deleted' => 1]);
                    $result = VoluntaryQuotation::where('parent_vq_id',$data->parent_vq_id)->update(['is_deleted' => 1]);
                }
                $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
                DeleteVq::dispatch($id, $jwt->jwt_token);

                DB::commit();
                if($result){
                    return response()->json([
                        'success'=>true, 
                        'type'=>'success',
                        'message' => 'Counter has been deleted successfully!'
                    ]);
                }else{
                    return response()->json([
                        'status' => false,
                        'type'=>'error',
                        'message' => 'Counter not deleted. Please try again later!'
                    ], 500);
                }
            }
            catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'type'=>'error',
                    'message' => 'An error occurred. Please try again later.'.$e->getMessage()
                ], 500);
            }
        // endif;
    
    }
    public function getStockists($institutionId)
    {
        $stockists = Stockist_master::where('institution_code', $institutionId)->get();
        return response()->json($stockists);
    }



    public function getPayment($stockistId)
    {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        if (is_numeric($stockistId)) {
            // Handle as id
            $payment = Stockist_master::where('id', $stockistId)->get();
        } else {
            // Handle as stockist_code
            //$payment = Stockist_master::where('stockist_code', $stockistId)->get();
            $payment = Stockist_master::leftJoin('voluntary_quotation','voluntary_quotation.institution_id','=', 'stockist_master.institution_code')->where('voluntary_quotation.is_deleted',0)->where('stockist_code', $stockistId)->where('voluntary_quotation.year', $year)->where('voluntary_quotation.parent_vq_id', 0)->select('stockist_master.*','voluntary_quotation.hospital_name','voluntary_quotation.institution_id')->get();
        }

        return response()->json($payment);
        
    }

    public function updateStockist(Request $request, $stockistCode)
    {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        // Determine if $stockistCode is an array
        $stockistCodes = is_array($stockistCode) ? $stockistCode : [$stockistCode];

        // Fetch stockists based on the provided stockist codes
        if (is_numeric($stockistCodes[0])) { // Check the first element for numeric
            $stockist = Stockist_master::where('id', $stockistCodes[0])->first();
            $stockists = $stockist ? collect([$stockist]) : collect(); // Create a collection
        } else {
            $stockists = Stockist_master::whereIn('stockist_code', $stockistCodes)->get();
        }
        // Check if any stockist was found
        if ($stockists->isEmpty()) {
            return response()->json(['message' => 'No stockist found.'], 404);
        }

        // Initialize an array to hold the updated fields
        $updatedFields = [];

        // Check if the request has 'payment_mode' or 'stockist_type_flag'
        if ($request->has('payment_mode')) {
            $updatedFields['payment_mode'] = $request->input('payment_mode');
        }

        if ($request->has('stockist_type_flag')) {
            $updatedFields['stockist_type_flag'] = $request->input('stockist_type_flag');
        }

        // Handle stockist_type
        if ($request->has('stockist_type')) {
            $stockistType = $request->input('stockist_type');

            if ($stockistType === 'BOTH') {
                $updatedFields['stockist_type'] = null; 
            } else {
                $updatedFields['stockist_type'] = $stockistType;
            }
        }
        $institutionVQ = VoluntaryQuotation::select('hospital_name','institution_id')->where('year', $year)->where('parent_vq_id', 0)->where('is_deleted', 0)->get();
        $institutionVQ = collect($institutionVQ);
        // Update all stockists with the new fields
        foreach ($stockists as $stockist) {
            $original = Stockist_master::find($stockist->id);
            $filteredInstitution = $institutionVQ->where('institution_id', $original->institution_code)->first();
            $ip_address = request()->ip();
            $userAgent = request()->header('User-Agent');
            $addl_params = array();
            $addl_params['fin_year'] = $year;
            $addl_params['ip_address'] = $ip_address;
            $addl_params['changed_at'] = date('Y-m-d H:i:s');
            $addl_params['user_agent'] = $userAgent;
            $addl_params['institution'] = $filteredInstitution == null ? $original->institution_code : $filteredInstitution->hospital_name.' - '.$filteredInstitution->institution_id;
            $addl_params['stockist'] = $original->stockist_name.'-'.$original->stockist_code;
            $changes = [];
            
            $stockistType = $request->input('stockist_type') == 'BOTH' ? null: $request->input('stockist_type');
            if ($original->stockist_type !== $stockistType) {
                $changes['stockist_type'] = [
                    'from' => $original->stockist_type == null?'BOTH':$original->stockist_type,
                    'to' => $request->input('stockist_type')
                ];
            }
            if (!empty($changes)) {
                $addl_params['changes'] = $changes;
                $vq_listing_controller = new VqListingController;
                $vq_listing_controller->activityTracker(1, Session::get("emp_code"), json_encode($addl_params), 'stockist_wise');
            }

            $stockist->update($updatedFields);
        }

        // Flash success message if session type is 'initiator'
        /*if (Session::get("type") === 'initiator') {
            session()->flash('success', 'Data has been saved successfully!');
        }*/

        return response()->json(['success' => true, 'message' => 'Stockists updated successfully.']);
    }
    public function stockist_update_institution(Request $request)
    {
        $stockist_master_id = $request->stockist_master_id;
        $payment_mode = $request->payment_mode;
        $stockistType = $request->stockistType;
        $isActive = $request->isActive == "true" ? 1 : 0;
        $selectedInstitution = $request->selectedInstitution;
        if (empty($stockist_master_id) || empty($payment_mode) || empty($stockistType)) {
            return response()->json([
                'status' => false,
                'message' => 'All fields are required. Please provide valid inputs.'
            ], 400); 
        }
        DB::beginTransaction();
        try 
        {
            $original = Stockist_master::find($stockist_master_id);
            $ip_address = request()->ip();
            $userAgent = request()->header('User-Agent');
            $year = $this->getFinancialYear(date('Y-m-d'),"Y");
            $addl_params = array();
            $addl_params['fin_year'] = $year;
            $addl_params['ip_address'] = $ip_address;
            $addl_params['changed_at'] = date('Y-m-d H:i:s');
            $addl_params['user_agent'] = $userAgent;
            $addl_params['institution'] = $selectedInstitution;
            $addl_params['stockist'] = $original->stockist_name.'-'.$original->stockist_code;
            $changes = [];
            if ($original->payment_mode !== $payment_mode) {
                $changes['payment_mode'] = [
                    'from' => $original->payment_mode,
                    'to' => $payment_mode
                ];
            }
            $stockistType = $request->stockistType == 'BOTH' ? null: $request->stockistType;
            if ($original->stockist_type !== $stockistType) {
                $changes['stockist_type'] = [
                    'from' => $original->stockist_type == null?'BOTH':$original->stockist_type,
                    'to' => $request->stockistType
                ];
            }
            if ($original->stockist_type_flag !== $isActive) {
                $changes['stockist_type_flag'] = [
                    'from' => $original->stockist_type_flag,
                    'to' => $isActive
                ];
            }
            
            if (!empty($changes)) {
                $addl_params['changes'] = $changes;
                $vq_listing_controller = new VqListingController;
                $vq_listing_controller->activityTracker(1, Session::get("emp_code"), json_encode($addl_params), 'institution_wise');
            }
            $updation = Stockist_master::where('id',$stockist_master_id)->update(['stockist_type' => $stockistType,'payment_mode' => $payment_mode, 'stockist_type_flag' =>$isActive, 'updated_at'=>now()]);
            DB::commit();
            if($updation)
            {
                return response()->json([
                    'success'=>true, 
                    'type'=>'success',
                    'message' => 'Updation done successfully'
                ]);
            }
            else
            {
                return response()->json([
                    'success'=>true, 
                    'type'=>'success',
                    'message' => 'Updation not done'
                ]);
            }
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again later.'.$e->getMessage()
            ], 500);
        }
    }
    public function activity_filter_new(Request $request)
    {
        $institution_id = $request->institution_id;
        $activity_filter_value = $request->activity_value;
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");

        $query = ActivityTracker::select('activity_trackers.*','voluntary_quotation.*','voluntary_quotation.id as vq_id','activity_trackers.id as activity_id', 'activity_trackers.created_at as activity_tracker_created_at')->LeftJoin('voluntary_quotation','activity_trackers.vq_id','=','voluntary_quotation.id')->whereIn('institution_id',$institution_id)->where('activity_trackers.vq_id', '!=',1)->where('voluntary_quotation.year' , $year); // modifiy line created_at to activity_tracker_created_at 07/02/2025
        if (!in_array('all', $activity_filter_value)) {
            $query->whereIn('type', $activity_filter_value);
        }

        $data = $query->orderBy('activity_trackers.id', 'DESC')->get();
        foreach ($data as $activity) {
            $activity->json_data = $this->extractJson($activity->activity);
            $activity->activity_text = $this->stripJson($activity->activity);
        }
        if(count($data) == 0)
        {
            $status = false;
        }
        else
        {
            $status = true;
        }
        return response()->json([
            'data' => $data,
            'status'=> $status
        ]);
    }
    function generate_vq_save_selected_paymode(Request $request)
    {
        $data = $request->selected_items;
        $institutionIds = array_map(function($item) {
            return $item['institution_id'];
        }, $data);
        $uniqueInstitutionIds = array_unique($institutionIds); 
        Session::put('selected_item_generate_vq_paymode', $uniqueInstitutionIds);
        return response()->json([
            'data' => $uniqueInstitutionIds,
            'success'=> true
        ]);
    }
    function generate_vq_save_selected_stockist(Request $request)
    {
        $data = $request->selected_items;
        $institutionIds = array_map(function($item) {
            return $item['institution_id'];
        }, $data);
        $uniqueInstitutionIds = array_unique($institutionIds); 
        Session::put('selected_item_generate_vq_stockist', $uniqueInstitutionIds);
        return response()->json([
            'data' => $uniqueInstitutionIds,
            'success'=> true
        ]);
    }
    public function searchInstitutions(Request $request)
    {
        $search = $request->get('q');
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
         $institutions = VoluntaryQuotation::where('year', $year)
        ->where('vq_status', 1)
        ->where('poc_status', 0)
        ->where('is_deleted', 0)
        ->where(function($query) use ($search) {
            $query->where('hospital_name', 'LIKE', "%{$search}%")
                  ->orWhere('institution_id', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%");
        })
        ->limit(500)
        ->orderBy('hospital_name')
        ->orderBy('rev_no')
        ->get(['id', 'hospital_name', 'institution_id', 'city', 'rev_no']);
        
        return response()->json($institutions);
    }

    public function cumulative_report_new(Request $request)
    {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $divisionName = $request->divisionName;
        $brandName = $request->brandName;
        // Process DataTables' request parameters
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $common_search = $request->input('search.value');
        $searchValue1 = $request->input('columns.1.search.value');//hospital_name
        $searchValue2 = $request->input('columns.2.search.value');//institution_id
        $orderColumnIndex = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir');

        $columns = ['hospital_name', 'institution_id', 'city', 'state_name', 'rev_no', 'div_name', 'mother_brand_name', 'brand_name', 'item_code', 'sap_code', 'discount_percent', 'discount_rate','applicable_gst', 'pack', 'cfa_code', 'mrp', 'ptr', 'mrp_margin', 'product_type', 'hsn_code', 'composition', 'contract_start_date', 'contract_end_date', 'year'];

        $divisionNameimplode = implode('","', $divisionName);
        $brandNameimplode = implode('","', $brandName);
        // $sql = "SELECT 
        // voluntary_quotation.hospital_name, voluntary_quotation.institution_id, voluntary_quotation.city, voluntary_quotation.state_name, voluntary_quotation.rev_no, vqsl.div_name, vqsl.mother_brand_name, vqsl.brand_name, vqsl.item_code, voluntary_quotation.sap_code, vqsl.discount_percent, vqsl.discount_rate, vqsl.applicable_gst, vqsl.pack, cfa_code, vqsl.mrp, vqsl.ptr, vqsl.mrp_margin, vqsl.product_type, vqsl.hsn_code, vqsl.composition, voluntary_quotation.contract_start_date, voluntary_quotation.contract_end_date, voluntary_quotation.year,
        // -- vqsl.*,
        // -- voluntary_quotation.*,
        // MAX(voluntary_quotation.rev_no) as max_rev_no
        // FROM voluntary_quotation
        // LEFT JOIN voluntary_quotation_sku_listing as vqsl 
        // ON vqsl.vq_id = voluntary_quotation.id
        // WHERE vqsl.div_id IN ('" . implode("','", $divisionName) . "')
        // AND vqsl.brand_name IN ('" . implode("','", $brandName) . "')
        // AND vqsl.is_deleted = 0
        // AND voluntary_quotation.is_deleted = 0
        // AND voluntary_quotation.year = '$year'
        // AND voluntary_quotation.vq_status = 1";

        $sql = 'SELECT 
            vq.hospital_name, 
            vq.institution_id, 
            vq.city, 
            vq.state_name, 
            vq.rev_no, 
            vqsl.div_name, 
            vqsl.mother_brand_name, 
            vqsl.brand_name, 
            vqsl.item_code, 
            vqsl.sap_itemcode,
            vq.id as vq_id, 
            vqsl.id as vqsl_id, 
            -- voluntary_quotation.sap_code, 
            vqsl.discount_percent, 
            vqsl.discount_rate, 
            vqsl.applicable_gst, 
            vqsl.pack, 
            cfa_code, 
            vqsl.mrp, 
            vqsl.ptr, 
            vqsl.mrp_margin, 
            vqsl.product_type, 
            vqsl.hsn_code, 
            vqsl.composition, 
            vq.contract_start_date, 
            vq.contract_end_date, 
            vq.year
        FROM voluntary_quotation as vq
        LEFT JOIN voluntary_quotation_sku_listing as vqsl 
            ON vqsl.vq_id = vq.id
        INNER JOIN (
            SELECT vqsl2.brand_name, vq2.institution_id, MAX(vq2.rev_no) AS max_rev_no
            FROM voluntary_quotation AS vq2
            LEFT JOIN voluntary_quotation_sku_listing AS vqsl2 
                ON vqsl2.vq_id = vq2.id
            WHERE 
                vqsl2.div_id IN ("' . $divisionNameimplode . '")
                ';
                if(!in_array('all', $brandName)):
                    $sql .= ' AND vqsl2.brand_name IN ("' . $brandNameimplode . '")';
                endif;
                $sql .= '
                AND vqsl2.is_deleted = 0
                AND vq2.is_deleted = 0
                AND vq2.year = "'.$year.'"
                AND vq2.vq_status = 1
            GROUP BY vq2.institution_id, vqsl2.brand_name
        ) AS latest_rev
        ON vq.institution_id = latest_rev.institution_id
        AND vq.rev_no = latest_rev.max_rev_no
        AND vqsl.brand_name = latest_rev.brand_name
        WHERE 
            vqsl.div_id IN ("' . $divisionNameimplode . '")';
            if(!in_array('all', $brandName)):
                $sql .= ' AND vqsl.brand_name IN ("' . $brandNameimplode . '")';
            endif;
        $sql .= 'AND vqsl.is_deleted = 0
            AND vq.is_deleted = 0
            AND vq.year = "'.$year.'"
            AND vq.vq_status = 1';

        // $sql = 'SELECT 
        //     vq.hospital_name, 
        //     vq.institution_id, 
        //     vq.city, 
        //     vq.state_name, 
        //     -- vq.rev_no, 
        //     vqsl.div_name, 
        //     vqsl.mother_brand_name, 
        //     vqsl.brand_name, 
        //     vqsl.item_code, 
        //     vqsl.sap_itemcode,
        //     vq.id as vq_id, 
        //     vqsl.id as vqsl_id, 
        //     vqsl.discount_percent, 
        //     vqsl.discount_rate, 
        //     vqsl.applicable_gst, 
        //     vqsl.pack, 
        //     cfa_code, 
        //     vqsl.mrp, 
        //     vqsl.ptr, 
        //     vqsl.mrp_margin, 
        //     vqsl.product_type, 
        //     vqsl.hsn_code, 
        //     vqsl.composition, 
        //     vq.contract_start_date, 
        //     vq.contract_end_date, 
        //     vq.year,
        //     MAX(vq.rev_no) AS rev_no
        //     FROM voluntary_quotation AS vq
        //     LEFT JOIN voluntary_quotation_sku_listing AS vqsl
        //         ON vqsl.vq_id = vq.id
        //     WHERE vqsl.is_deleted = 0';
        //     if(!in_array('all', $divisionName)):
        //         $sql .= ' AND vqsl.div_id IN ("' . $divisionNameimplode . '")';
        //     endif;
        //     if(!in_array('all', $brandName)):
        //         $sql .= ' AND vqsl.brand_name IN ("' . $brandNameimplode . '")';
        //     endif;
        //     $sql .='
        //     AND vq.is_deleted = 0
        //     AND vq.year = "'.$year.'"
        //     AND vq.vq_status = 1';
       
        if(Session::get("type") == 'poc'):
            $poc_master_institution_id = DB::table('poc_master')->select('institution_id')->where(strtolower(Session::get("emp_type")).'_code', Session::get("emp_code"))->groupBy('institution_id')->orderBy('institution_id', 'ASC')->pluck('institution_id')->toArray();
            $poc_master_institution_id = implode('","', $poc_master_institution_id);
            $sql .= ' AND vq.institution_id IN ("' . $poc_master_institution_id . '")';
        endif;
        if(Session::get("type") == 'distribution'):
            $expolde_session_div_id = implode('","', explode(',',Session::get("division_id")));
            $sql .= ' AND vq.cfa_code IN ("' . $expolde_session_div_id . '")';
        endif;

        // Apply search filters
        if (!empty($searchValue1)) {
            $searchValue1 = str_replace('-', '%', $searchValue1);
            $sql .= " AND vq.hospital_name LIKE '%$searchValue1%'";
        }
        if (!empty($searchValue2)) {
            $sql .= " AND vq.institution_id LIKE '%$searchValue2%'";
        }

        // Apply common search
        if (!empty($common_search)) {
            $common_search = str_replace('-', '%', $common_search);
            $common_search = addslashes($common_search);
            $sql .= " AND (
                vq.hospital_name LIKE '%$common_search%' OR
                vq.institution_id LIKE '%$common_search%' OR
                vq.city LIKE '%$common_search%' OR
                vq.sap_code LIKE '%$common_search%' OR
                vqsl.mother_brand_name LIKE '%$common_search%' OR
                vqsl.item_code LIKE '%$common_search%' OR
                vqsl.brand_name LIKE '%$common_search%' OR
                vqsl.applicable_gst LIKE '%$common_search%' OR
                vqsl.last_year_percent LIKE '%$common_search%' OR
                vqsl.last_year_rate LIKE '%$common_search%' OR
                vqsl.mrp LIKE '%$common_search%' OR
                vqsl.last_year_mrp LIKE '%$common_search%' OR
                vqsl.ptr LIKE '%$common_search%' OR
                vqsl.discount_percent LIKE '%$common_search%' OR
                vqsl.discount_rate LIKE '%$common_search%' OR
                vqsl.mrp_margin LIKE '%$common_search%' OR
                vqsl.sap_itemcode LIKE '%$common_search%' OR
                vqsl.composition LIKE '%$common_search%' OR
                vqsl.div_name LIKE '%$common_search%'
            )";
        }

        // $sql .= ' GROUP BY vq.institution_id, vqsl.brand_name';
        // Apply Sorting
        if (isset($orderColumnIndex) && array_key_exists($orderColumnIndex, $columns)) {
            $orderColumnName = $columns[$orderColumnIndex];
            $sql .= " ORDER BY $orderColumnName $orderDirection";
        }

        // Get Total Records Before Pagination
        $totalCountSql = "SELECT COUNT(*) as total FROM ($sql) AS subquery";
        $totalCountResult = DB::select($totalCountSql);
        $recordsTotal = $totalCountResult[0]->total; 

        // Apply Pagination
        $sql .= " LIMIT $length OFFSET $start";
        // print_r($sql);die;
        // Execute the final SQL query
        $data = DB::select($sql);

        // Convert to array
        $dataArray = json_decode(json_encode($data), true);

        // Return JSON response
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal, // This should be updated if filters are applied separately
            'data' => $dataArray,
        ]);
    }

    public function CumulativeReportExport(Request $request)
    {
        $reporttype = $request->input('reporttype');
        /*$brandName = json_decode($request->input('brandName'), true);
        $divisionName = json_decode($request->input('divisionName'), true);*/
        $brandName = Session::get("report_brandName");
        $divisionName = Session::get("report_divisionName");

        $division_Names = DB::table('brands')->select('div_name')->whereIn('div_id', $divisionName)->groupBy('div_name')->orderBy('div_name', 'ASC')->pluck('div_name')->toArray();

        if(count($divisionName) == 1 && count($brandName) == 1):
            $export_excel_filename = str_replace(' ', '_', implode('-', array_merge($division_Names, $brandName))).'-'.date('d-m-Y His').'.xlsx';
        elseif(count($divisionName) == 1 && count($brandName) > 1):
            $export_excel_filename = str_replace(' ', '_', implode('-', $division_Names)).'-'.date('d-m-Y His').'.xlsx';
        elseif(count($divisionName) > 1 && count($brandName) == 1):
            $export_excel_filename = str_replace(' ', '_', implode('-', array_merge($division_Names, $brandName))).'-'.date('d-m-Y His').'.xlsx';
        elseif(count($divisionName) > 1 && count($brandName) > 1):
            $export_excel_filename = str_replace(' ', '_', implode('-', $division_Names)).'-'.date('d-m-Y His').'.xlsx';
        else:
            $export_excel_filename = 'Product-Cumulative-Report-'.date('d-m-Y His').'.xlsx';
        endif;

        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $response = Excel::download(new CumulativeReport($year, $reporttype, $brandName, $divisionName), $export_excel_filename);
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');

        return $response;
    }
    public function filter_division_by_brand(Request $request)
    {
        $division_id = $request->div_id;
        if(!in_array('all', $division_id)):
            $brand_Names = DB::table('brands')->select('id','brand_name')->whereIn('div_id', $division_id)->groupBy('brand_name')->orderBy('brand_name', 'ASC')->get()->toArray();
        else:
            $brand_Names = DB::table('brands')->select('id','brand_name')->groupBy('brand_name')->orderBy('brand_name', 'ASC')->get()->toArray();
        endif;
        return response()->json($brand_Names);
    }
    public function cumulative_report_get_stockist(Request $request)
    {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $vq_id = $request->vq_id;
        $vqsl_id = $request->vqsl_id;
        $draw = $request->input('draw');
        $start = 0;
        $length = 50;
        $common_search = $request->input('search.value');
        $searchValue1 = $request->input('columns.0.search.value');
        $searchValue2 = $request->input('columns.1.search.value');
        $orderColumnIndex = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir');

        $columns = ['stockist_code', 'stockist_name', 'payment_mode','net_discount_percent'];


        $query = VoluntaryQuotationSkuListingStockist::select(
            'stockist_master.stockist_code',
            'stockist_master.stockist_name',
            'voluntary_quotation_sku_listing_stockist.item_code',
            'voluntary_quotation_sku_listing_stockist.payment_mode',
            'voluntary_quotation_sku_listing_stockist.net_discount_percent')
        ->leftJoin('stockist_master','stockist_master.id','=','voluntary_quotation_sku_listing_stockist.stockist_id')
        ->where('voluntary_quotation_sku_listing_stockist.vq_id',$vq_id)
        ->where('voluntary_quotation_sku_listing_stockist.sku_id',$vqsl_id)
        ->where('voluntary_quotation_sku_listing_stockist.is_deleted', 0);
        if(isset($orderColumnIndex)&&array_key_exists($orderColumnIndex, $columns))
        {
            $orderColumnName = $columns[$orderColumnIndex];
            $query->orderBy($orderColumnName, $orderDirection);
        }
        $data = $query->offset($start)->limit($length)->get();
        $recordsTotal = $query->count();
        return response()->json([
            /*'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,*/
            'data' => $data,
        ]);
    }

    public function set_session_filter_report(Request $request)
    {
        $brandName = json_decode($request->brandName, true);
        $divisionName = json_decode($request->divisionName, true);
        Session::put('report_brandName', $brandName);
        Session::put('report_divisionName',$divisionName);

        if (
            Session::has('report_brandName') &&
            Session::has('report_divisionName') &&
            Session::get('report_brandName') === $brandName &&
            Session::get('report_divisionName') === $divisionName
        ) {
            return response()->json(['status' => true]);
        } else {
            return response()->json(['status' => false]);
        }
    }

    
    /* added by arunchadnru at 13052025 */
    public function bulk_counter_update_vq_data(Request $request)
    {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $institution_id = $request->institution_id;
        $item_code = $request->item_code;
        // Process DataTables' request parameters
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $common_search = $request->input('search.value');
        $searchValue1 = $request->input('columns.1.search.value');//hospital_name
        $searchValue2 = $request->input('columns.2.search.value');//institution_id
        $orderColumnIndex = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir');

        $columns = ['hospital_name', 'sap_code', 'mother_brand_name', 'item_code', 'brand_name', 'rev_no', 'discount_percent', 'discount_rate',  'id',  'id',  'id', 'applicable_gst', 'last_year_percent', 'last_year_rate', 'last_year_mrp', 'mrp', 'ptr', 'mrp_margin', 'type', 'hsn_code', 'composition'];

        
        $item_code_string = implode('","', $item_code);
     

        if(in_array('all', $institution_id)):
            $data_institution_id = VoluntaryQuotation::where('year',$year)->where('parent_vq_id','=',0)->where('is_deleted', 0)->get()->pluck('institution_id')->toArray();
            $institution_id_string = implode('","', $data_institution_id);
        else:
            $institution_id_string = implode('","', $institution_id);
        endif;
        // print_r($institution_id_string);die;

        $sql = 'SELECT 
            vq.hospital_name,
            vq.sap_code,
            vqsl.mother_brand_name, 
            vqsl.item_code, 
            vq.rev_no, 
            vqsl.brand_name,
            vqsl.discount_percent, 
            vqsl.discount_rate,
            sm.stockist_code,
            sm.stockist_name,
            vqsl.vq_id as vq_id,
            vq.parent_vq_id,
            vq.institution_id,
            vqsl.id as sku_id, 
            sm.id as stockist_id, 
            vqsls.id as vqsls_id,
            vqsl.applicable_gst, 
            vqsl.last_year_percent, 
            vqsl.last_year_rate,
            vqsl.last_year_mrp,
            vqsl.mrp, 
            vqsl.ptr, 
            vqsl.mrp_margin, 
            vqsl.type, 
            vqsl.hsn_code, 
            vqsl.composition,
            vqsls.payment_mode
        FROM voluntary_quotation AS vq
        INNER JOIN voluntary_quotation_sku_listing_stockist AS vqsls 
            ON vqsls.vq_id = vq.id 
        INNER JOIN voluntary_quotation_sku_listing AS vqsl 
            ON vqsl.vq_id = vq.id AND vqsl.item_code = vqsls.item_code
        LEFT JOIN stockist_master AS sm 
            ON sm.id = vqsls.stockist_id
        INNER JOIN (
            SELECT 
                vq2.institution_id, 
                vqsls2.item_code, 
                MAX(vq2.rev_no) AS max_rev_no
            FROM voluntary_quotation AS vq2
            INNER JOIN voluntary_quotation_sku_listing_stockist AS vqsls2 
                ON vqsls2.vq_id = vq2.id
            WHERE 
                vqsls2.item_code IN ("' . $item_code_string . '") 
                AND vq2.institution_id IN ("' . $institution_id_string . '")
                AND vq2.year = "' . $year . '"
                AND vq2.vq_status = 1
                AND vq2.is_deleted = 0
                AND vqsls2.is_deleted = 0
            GROUP BY vq2.institution_id, vqsls2.item_code
        ) AS latest_rev
        ON vq.institution_id = latest_rev.institution_id
        AND vq.rev_no = latest_rev.max_rev_no
        AND vqsl.item_code = latest_rev.item_code
        WHERE 
            vqsls.item_code IN ("' . $item_code_string . '")
            AND vq.institution_id IN ("' . $institution_id_string . '")
            AND vq.year = "' . $year . '"
            AND vq.vq_status = 1
            AND vq.is_deleted = 0
            AND vqsls.is_deleted = 0
            AND vqsl.is_deleted = 0
            AND sm.stockist_type_flag = 1';

        // print_r($sql);die;
        // Apply common search
        if (!empty($common_search)) {
            $common_search = str_replace('-', '%', $common_search);
            $sql .= " AND (
                vq.hospital_name LIKE '%$common_search%' OR
                vq.institution_id LIKE '%$common_search%' OR
                vq.city LIKE '%$common_search%' OR
                vq.sap_code LIKE '%$common_search%' OR
                vqsl.mother_brand_name LIKE '%$common_search%' OR
                vqsl.item_code LIKE '%$common_search%' OR
                vqsl.brand_name LIKE '%$common_search%' OR
                vqsl.applicable_gst LIKE '%$common_search%' OR
                vqsl.last_year_percent LIKE '%$common_search%' OR
                vqsl.last_year_rate LIKE '%$common_search%' OR
                vqsl.mrp LIKE '%$common_search%' OR
                vqsl.last_year_mrp LIKE '%$common_search%' OR
                vqsl.ptr LIKE '%$common_search%' OR
                vqsl.discount_percent LIKE '%$common_search%' OR
                vqsl.discount_rate LIKE '%$common_search%' OR
                vqsl.mrp_margin LIKE '%$common_search%' OR
                vqsl.sap_itemcode LIKE '%$common_search%' OR
                vqsl.composition LIKE '%$common_search%' OR
                vqsl.div_name LIKE '%$common_search%'
            )";
        }

        // $sql .= ' GROUP BY vq.institution_id, vqsl.brand_name';
        // Apply Sorting
        if (isset($orderColumnIndex) && array_key_exists($orderColumnIndex, $columns)) {
            $orderColumnName = $columns[$orderColumnIndex];
            $sql .= " ORDER BY $orderColumnName $orderDirection";
        }

        // Get Total Records Before Pagination
        $totalCountSql = "SELECT COUNT(*) as total FROM ($sql) AS subquery";
        $totalCountResult = DB::select($totalCountSql);
        $recordsTotal = $totalCountResult[0]->total; 

        // Apply Pagination
        // $sql .= " LIMIT $length OFFSET $start";
        // print_r($sql);die;
        // Execute the final SQL query
        $data = DB::select($sql);

        foreach ($data as $index => $row) {
            $row->unique_id = $index + 1; 
        }

        // Convert to array
        $dataArray = json_decode(json_encode($data), true);

        // Return JSON response
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal, // This should be updated if filters are applied separately
            'data' => $dataArray,
        ]);
    }
    public function BulkUpdateCounterSendQuotation(Request $request)
    {
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $changePayModeData = json_decode($request->data);

        $stockist_id = [];
        //code to update paymode and net discount percent starts
        if ($changePayModeData != null) {
            foreach($changePayModeData as $data){
                $id = data_get($data, 'id');
                $stockist_id[] = $id;
                $payMode = data_get($data, 'payMode');
                $netDiscountRateToStockist = data_get($data, 'netDiscPercent');
                VoluntaryQuotationSkuListingStockist::find($id)->update(['payment_mode' => $payMode, 'net_discount_percent' => $netDiscountRateToStockist]);
            }
        }
        // dd($stockist_id);
        GenerateVq::dispatch($jwt->jwt_token, $stockist_id);
        return response()->json([
            'success'=>true, 
            'message' => "Payment mode has been updated for the selected SKU's, and send quoation job will be start.",
            'data' => $stockist_id
        ]);
    }

    public function approvalemailscheduleDays(Request $request)
    {
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $approval_period_details = DB::table('approval_period')->where('level', $request->level)->where('type', $request->type)->get()->toArray();
        // print_r($approval_period_details);die;
        return response()->json([
            'success'=>true, 
            'message' => "Payment mode has been updated for the selected SKU's, and send quoation job will be start.",
            'data' => $approval_period_details
        ]);
    }

    public function productwise_discard_getdata(Request $request)
    {
        $item_code = $request->item_code;
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $data = VoluntaryQuotationSkuListing::select('voluntary_quotation.*','voluntary_quotation_sku_listing.*','voluntary_quotation_sku_listing.id as sku_id', 'voluntary_quotation_sku_listing.vq_id as vq_id')
            ->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            ->where('voluntary_quotation_sku_listing.item_code',$item_code)
            ->where('voluntary_quotation.year', $year)
            //->where('voluntary_quotation.current_level', 7)
            ->where('voluntary_quotation.vq_status', 0)
            ->where('voluntary_quotation.is_deleted', 0)
            ->where('voluntary_quotation_sku_listing.is_deleted', 0)
        ->get();
        return response()->json([
            'data' => $data,
        ]);
    }
    
    public function productwise_discard_selection(Request $request)
    {
        $selected_rows = $request->input('selected_rows', []);
        if(!is_null($selected_rows)){
            DB::beginTransaction();
            try
            {
                $skuIds = collect($selected_rows)->pluck('sku_id');
                $updation = VoluntaryQuotationSkuListing::whereIn('id', $skuIds)->update(['is_deleted' => 1,'is_discarded' => 1, 'updated_at'=>now()]);
                $emp_details = Session::get("emp_code").'-'.Session::get("emp_name");
                foreach ($selected_rows as $selected_row) {
                    $this->activityTracker($selected_row['vq_id'],Session::get("emp_code"),'Item Code ' .$selected_row['item_code'].' has been discarded by '. $emp_details.' in Revision '.$selected_row['rev_no'],'productwise_discard');
                }
                DB::commit();
                return response()->json([
                    'success'=>true, 
                    'result' => $updation ?? ''
                ]);
            }
            catch (\Exception $e) {
                // Rollback Transaction
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'result' => 'An error occurred: ' . $e->getMessage()
                ]);
                //Log::error('An error occurred: ' . $e->getMessage());
            }
        }else{
            return response()->json([
                'success'=>false, 
                'result' => 'no selection'
            ]);
        }
    }
}
