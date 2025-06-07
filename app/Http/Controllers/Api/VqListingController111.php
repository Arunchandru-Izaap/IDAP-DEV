<?php

namespace App\Http\Controllers\Api;
use App\Jobs\CreateVq;
use App\Jobs\ApproveVq;
use App\Jobs\newReinitiateVQ;
use App\Jobs\ReinitiateVQ;
use App\Jobs\ReinitiateVQNewPack;
use App\Jobs\SendMetisAll;
use App\Jobs\ReportDownload;
use App\Jobs\HistoricalReportDownload;
use App\Models\Stockist_master;
use App\Exports\InitiatorExport;
use App\Exports\LatestExport;
use App\Exports\VqExport;
use Excel;
use Artisan;
use Illuminate\Support\Facades\File;
use Session;
use DB;
use PDF;
use Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\ActivityTracker;
use App\Models\JwtToken;
use App\Models\Signature;
use DateTime;
class VqListingController extends Controller
{
    public function updateSku(Request $request){
        foreach ($request['id'] as $key => $value) {
            $updation = VoluntaryQuotationSkuListing::where(['id'=>$value,'brand_name'=>$request['brand_name']])
            ->update(['discount_percent'=>$request['prt_percent'][$key],'discount_rate'=>$request['ptr_rate'][$key], 'mrp_margin'=>round($request['mrp_margin'][$key],2), 'updated_at'=>date('Y-m-d H:i:s')]);
            $this->activityTracker($value,Session::get("emp_code"),ucwords(strtolower(Session::get("emp_name"))).', '.$this->getLevelName(Session::get("level")).', '.Session::get("division_name").' applied '.$request['prt_percent'][$key].'% discount to item '.$request['item_code'][$key],'update');
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
        $this->dispatch(new CreateVq($request->from,$request->to,Session::get("emp_code"),$jwt->jwt_token,$name,$division_name));
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
        $this->dispatch(new ReinitiateVQNewPack($request->oldpack,$request->newpack,Session::get("emp_code"),$jwt->jwt_token,$name,$division_name));
    }

    public function reinitiateVQApi(Request $request){
        $name = Session::get("emp_name");
        $division_name = Session::get("division_name");
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        $skip_approval = data_get($request, 'skip_approval');
        $selected_approval = data_get($request, 'selected_approval');
        $this->dispatch(new ReinitiateVQ($request->from,$request->to,Session::get("emp_code"),$jwt->jwt_token,$request->institution_code,$request->item_codes,$name,$division_name, $skip_approval, $selected_approval));
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
            $deletion = VoluntaryQuotationSkuListing::where('vq_id',$vq_id)->whereIn('div_id',explode(',',Session::get("division_id")))->update(['is_deleted'=>1,'deleted_by'=>preg_replace('/[^0-9.]+/', '', Session::get("level"))]);
           //$this->activityTracker($vq_id,Session::get("emp_code"),'VQ Cancelled by '.Session::get("emp_name").' of division - '.Session::get("division_name").' and  level - '.$this->getLevelName(Session::get("level")),'delete',$add_comment);
            $this->activityTracker($vq_id,Session::get("emp_code"),ucwords(strtolower(Session::get("emp_name"))).', '.$this->getLevelName(Session::get("level")).', '.Session::get("division_name").' Cancelled the VQ','delete',$add_comment);

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

        $datas = VoluntaryQuotationSkuListing::where('vq_id',$request->vq_id)->whereIn('div_id',explode(',',Session::get("division_id")))->get();
        if(VoluntaryQuotationSkuListing::where('vq_id',$request->vq_id)->whereIn('div_id',explode(',',Session::get("division_id")))->exists()){
            //$this->activityTracker($request->vq_id,Session::get("emp_code"),'Bulk update discount of '.$request->discount_percent.'% by '.Session::get("emp_name").' of division - '.Session::get("division_name").' and  level - '.$this->getLevelName(Session::get("level")),'bulkupdate');
           // $this->activityTracker($request->vq_id,Session::get("emp_code"),'Bulk update discount of '.$request->discount_percent.'% by '.Session::get("emp_name").' of division - '.Session::get("division_name").' and  level - '.$this->getLevelName(Session::get("level")),'bulkupdate');
            $this->activityTracker($request->vq_id,Session::get("emp_code"),Session::get("emp_name").', '.Session::get("division_name").' applied bulk discount of '.$request->discount_percent.'% on all SKUs','bulkupdate');
        
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
            $updation = VoluntaryQuotationSkuListing::where('vq_id',$request->vq_id)->whereIn('div_id',explode(',',Session::get("division_id")))->update([strtolower(Session::get("level")).'_status'=>1]);
            $this->activityTracker($request->vq_id,Session::get("emp_code"),'VQ Approved by '.Session::get("emp_name").' of division - '.Session::get("division_name").' and  level - '.$this->getLevelName(Session::get("level")),'approve',$request->comment);

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
    
    public function bulkApprove(Request $request){

        $year = $this->getFinancialYear(date('Y-m-d'),"Y");        
        $updation = VoluntaryQuotationSkuListing::where('voluntary_quotation.is_deleted',0)->where('voluntary_quotation.year',$year)->where('voluntary_quotation.current_level',preg_replace('/[^0-9.]+/', '', Session::get("level")))->whereIn('div_id',explode(',',Session::get("division_id")))->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->update(['voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status'=>1]);
        $this->activityTracker(null,Session::get("emp_code"),'Bulk VQ Approved by '.Session::get("emp_name").' of division - '.Session::get("division_name").' and  level - '.$this->getLevelName(Session::get("level")),'bulkapprove');
        return response()->json([
            'success'=>true, 
            'result' => $updation
        ]);
    }

    public function approveVq(Request $request){
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        $updation = VoluntaryQuotation::where('id',$request->vq_id)->where('is_deleted', 0)->update(['vq_status'=>1]);
        $this->dispatch(new ApproveVq($request->vq_id,$jwt->jwt_token));
        return response()->json([
            'success'=>true, 
            'result' => "Success"
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
                $updation = Stockist_master::where('institution_code',$request->institution_code)->where('stockist_code',$code)->update(['stockist_type_flag'=>1]);
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

        $data = explode("-",$vq['year']);
        $vq_year = $data[0].substr($data[1], 2);
        $zip_file = $vq['hospital_name'].'_SPLL_SPIL_VQ'.$vq_year.'.zip'; // Name of our archive to download
        // dd($zip_file);
        // Initializing PHP class
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        // Adding file: second parameter is what will the path inside of the archive
        // So it will create another folder called "storage/" inside ZIP, and put the file there.
        $zip->addFile(Excel::download(new InitiatorExport($id,"SPLL"), 'Initiator-export.xlsx')->getFile(),ucwords(strtolower($vq['hospital_name'])).'_SPLL_VQ'.$vq_year.'.xlsx');
        $zip->addFile(Excel::download(new InitiatorExport($id,"SPIL"), 'Initiator-export.xlsx')->getFile(),ucwords(strtolower($vq['hospital_name'])).'_SPIL_VQ'.$vq_year.'.xlsx');
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
        $data['stockist_data'] = VoluntaryQuotation::join('stockist_master','stockist_master.institution_code','=','voluntary_quotation.institution_id')
        ->where('voluntary_quotation.id',$id)
        ->where('stockist_master.stockist_type_flag',1)
        ->where('voluntary_quotation.is_deleted', 0)
        ->select('stockist_master.*')->get();
        
        $data['poc_data'] = VoluntaryQuotation::join('poc_master','poc_master.institution_id','=','voluntary_quotation.institution_id')
        ->where('voluntary_quotation.id',$id)
        ->where('voluntary_quotation.is_deleted', 0)
        ->select('poc_master.*')->first();

        if($vq->parent_vq_id !=0){
            $data['revision_count']=VoluntaryQuotation::where('parent_vq_id',$vq->parent_vq_id)->where('id','<=',$id)->where('is_deleted', 0)->count();

        }else{
            $data['revision_count']=0;
        }
        
        //dd($data['vq_data']['institution_id']);
        //dd($data['stockist_data']['stockist_code']);
	    //dd($data['poc_data']['institution_id']);
        // pdf for spll
        $data['signature']=Signature::first();
        $type1 = pathinfo(base_path().'/public/images/'.$data['signature']->spll_sign, PATHINFO_EXTENSION);
        $type2 = pathinfo(base_path().'/public/images/'.$data['signature']->spil_sign, PATHINFO_EXTENSION);
        $data['signature']->spll_sign = 'data:image/' . $type1 . ';base64,' . base64_encode(file_get_contents(base_path().'/public/images/'.$data['signature']->spll_sign));
        $data['signature']->spil_sign = 'data:image/' . $type2 . ';base64,' . base64_encode(file_get_contents(base_path().'/public/images/'.$data['signature']->spil_sign));

        $pdf1 = PDF::loadView('admin.pdf.spllpdf', compact('data'));
        Storage::put('spll_cover.pdf', $pdf1->output());

	    //dd($pdf1);

        //pdf for spil
        $pdf2 = PDF::loadView('admin.pdf.spilpdf', compact('data'));
        Storage::put('spil_cover.pdf', $pdf2->output());


        $zip_file = $vq['hospital_name'].'_cover_letter.zip'; // Name of our archive to download
        // Initializing PHP class
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        // Adding file: second parameter is what will the path inside of the archive
        // So it will create another folder called "storage/" inside ZIP, and put the file there.
        $zip->addFile(storage_path('app/spll_cover.pdf'),ucwords(strtolower($vq['hospital_name'])).'_SPLL_VQ'.$vq_year.'.pdf');
        $zip->addFile(storage_path('app/spil_cover.pdf'),ucwords(strtolower($vq['hospital_name'])).'_SPIL_VQ'.$vq_year.'.pdf');
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
        }
        return $return_level;
    } 
}
