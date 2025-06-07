<?php

namespace App\Jobs;
use App\Models\Institution;
use App\Models\ceilingMaster;
use App\Models\LastYearPrice;
use App\Models\VoluntaryQuotation;
use App\Models\PocMaster;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\ActivityTracker;
use App\Models\Employee;
use App\Http\Controllers\Api\VqListingController;
use Maatwebsite\Excel\Excel as BaseExcel;
use DB;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


use Illuminate\Support\Facades\Mail;
use App\Models\Signature;
use PDF;
use App\Exports\InitiatorExport;
use App\Models\IgnoredInstitutions;
use App\Models\VoluntaryQuotationSkuListingStockist;
use Excel;
class ApproveVq implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $vq_id;
    protected $jwt;
    protected $skuIdArr;
    protected $changePayModeData;//added for paymode and net disc percent change
    public $timeout = 999999;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($vq_id,$jwt, $skuIdArr=null, $changePayModeData = null)
    {
        //
        $this->vq_id = $vq_id;
        $this->jwt = $jwt;
        $this->skuIdArr = $skuIdArr;
        $this->changePayModeData = $changePayModeData;//added for paymode and net disc percent change

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $vq_listing_controller = new VqListingController;
        $vq = VoluntaryQuotation::where('id',$this->vq_id)->where('is_deleted', 0)->first();
        //code to update paymode and net discount percent starts
        if ($this->changePayModeData != null) {
            $changePayModeData1 = $this->changePayModeData;
            foreach($changePayModeData1 as $data){
                
                $id = data_get($data, 'id');
                $payMode = data_get($data, 'payMode');
                $netDiscountRateToStockist = data_get($data, 'netDiscPercent');

                VoluntaryQuotationSkuListingStockist::find($id)->update(['payment_mode' => $payMode, 'net_discount_percent' => $netDiscountRateToStockist]);
            }
        }
        //code to updat paymode and net discount rate ends
        $updation = VoluntaryQuotation::where('id',$this->vq_id)->where('is_deleted', 0)->update(['vq_status'=>1]);//added on 26052024 for updating the vq status earlier in vqlistingcontroller
        // $listing_data = VoluntaryQuotationSkuListing::where('vq_id',$this->vq_id)->where('is_deleted',0)->get();
        $listing_data = VoluntaryQuotationSkuListingStockist::join('voluntary_quotation_sku_listing', 'voluntary_quotation_sku_listing.id', '=', 'voluntary_quotation_sku_listing_stockist.sku_id')
                ->select('voluntary_quotation_sku_listing_stockist.payment_mode', 'voluntary_quotation_sku_listing_stockist.net_discount_percent',  'voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.item_code',  'voluntary_quotation_sku_listing.discount_percent', 'voluntary_quotation_sku_listing.discount_rate', 'voluntary_quotation_sku_listing.mrp','voluntary_quotation_sku_listing.ptr', 'stockist_master.stockist_code')
                ->join('stockist_master', 'stockist_master.id', '=', 'voluntary_quotation_sku_listing_stockist.stockist_id')
                ->where('voluntary_quotation_sku_listing_stockist.vq_id', $this->vq_id)
                ->where('voluntary_quotation_sku_listing.is_deleted', 0)
                ->where('stockist_master.stockist_type_flag', 1);
        if($this->skuIdArr == null){
            $listing_data = $listing_data->get();
        }else{
            /*added on 15052024 for revision wise activity log and send quotation api send starts*/
            //$listing_data = $listing_data->whereIn('voluntary_quotation_sku_listing_stockist.id', $this->skuIdArr)->get();//commented on 15052024
            $all_vq = VoluntaryQuotationSkuListingStockist::select('voluntary_quotation.id','voluntary_quotation.rev_no','voluntary_quotation.institution_id','voluntary_quotation.rev_no','voluntary_quotation.contract_start_date','voluntary_quotation.contract_end_date','voluntary_quotation.created_at')->join('voluntary_quotation', 'voluntary_quotation.id','=','voluntary_quotation_sku_listing_stockist.vq_id')
                ->whereIn('voluntary_quotation_sku_listing_stockist.id', $this->skuIdArr)->where('voluntary_quotation.is_deleted', 0)->distinct()->get();
            print_r(count($all_vq));
            foreach ($all_vq as $vq_data_final) {
                $listing_data = VoluntaryQuotationSkuListingStockist::join('voluntary_quotation_sku_listing', 'voluntary_quotation_sku_listing.id', '=', 'voluntary_quotation_sku_listing_stockist.sku_id')
                ->select('voluntary_quotation_sku_listing_stockist.payment_mode', 'voluntary_quotation_sku_listing_stockist.net_discount_percent',  'voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.item_code',  'voluntary_quotation_sku_listing.discount_percent', 'voluntary_quotation_sku_listing.discount_rate', 'voluntary_quotation_sku_listing.mrp','voluntary_quotation_sku_listing.ptr', 'stockist_master.stockist_code')
                ->join('stockist_master', 'stockist_master.id', '=', 'voluntary_quotation_sku_listing_stockist.stockist_id')
                ->where('voluntary_quotation_sku_listing_stockist.vq_id', $vq_data_final->id)
                ->whereIn('voluntary_quotation_sku_listing_stockist.id', $this->skuIdArr)
                ->where('voluntary_quotation_sku_listing.is_deleted', 0)->where('stockist_master.stockist_type_flag', 1)->get();
                print_r(count($listing_data));
                $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
                $send_json = array();
                $date=date_create($vq_data_final->created_at);
                if (date_format($date,"m") >= 4) {//On or After April (FY is current year - next year)
                    $financial_year = (date_format($date,'Y')) . '-' . (date_format($date,'y')+1);
                } else {//On or Before March (FY is previous year - current year)
                    $financial_year = (date_format($date,'Y')-1) . '-' . date_format($date,'y');
                }
                $send_json['fin_year'] = $financial_year;
                $send_json['institute_code'] = $vq_data_final->institution_id;
                $send_json['vq_id'] = $vq_data_final->id;
                $send_json['quotation_type'] = 'VQ';
                $send_json['revision_number'] = $vq_data_final->rev_no;
                $send_json['quotation_start_date'] = $vq_data_final->contract_start_date;
                $send_json['quotation_end_date'] = $vq_data_final->contract_end_date;
                $send_json['DiscountModeFlag'] = "Y";
                foreach($listing_data as $single_data){
                    $lastYear = LastYearPrice::updateOrCreate(['sku_id' => $single_data['item_code'],'institution_id' => $vq->institution_id ,'division_id' => $single_data['div_id'],'year' => $year], [ 'discount_percent' => $single_data['discount_percent'], 'ptr' => $single_data['ptr'], 'mrp' => $single_data['mrp'], 'updated_at' => date('Y-m-d H:i:s') ]);
                    $sku_arr['item_code'] = $single_data['item_code'];
                    $sku_arr['div_code'] = $single_data['div_id'];
                    $sku_arr['discount_percent'] = $single_data['discount_percent'];
                    $sku_arr['discount_rate'] = $single_data['discount_rate'];
                    $sku_arr['stockist_code'] = $single_data['stockist_code'];
                    $sku_arr['payment_mode'] = $single_data['payment_mode'];
                    $sku_arr['net_discount_percent'] = $single_data['net_discount_percent'];

                    $send_json['sku'][]=$sku_arr;
                }
                print_r("send json object created");

                $vq_listing_controller->activityTracker($vq_data_final->id,'1',json_encode($send_json), 'vq_metis_object');
                $headers = [
                    'Content-Type' => 'application/json',
                    'AccessToken' => 'key',
                    'Authorization' => 'Bearer '.$this->jwt,
                ];
                
                $client = new GuzzleClient([
                    'headers' => $headers,
                    'verify' => false
                ]);
                
                $body = json_encode($send_json);
                // $ignoredVq = IgnoredInstitutions::where('parent_institution_id', $vq_data_final->institution_id)->get();
                // foreach($ignoredVq as $child){
                //     //print_r("send json object sent child");
                //     $send_json['institute_code'] = $child->institution_id;
                //     $send_json['vq_id'] = $vq_data_final->id.'.'.$child->id;
                //     $body_activity = json_encode($send_json);
                //     $vq_listing_controller->activityTracker($vq_data_final->id,'1',$body_activity, 'vq_metis_child_request');
                // } // hide by arunchandru at 23-01-2025
                try {
                    $r = $client->request('POST', env('API_URL').'/api/QuotationPush', [
                        'body' => $body
                    ]);
                    $response = $r->getBody()->getContents();
                    $data = json_decode($response);
                    $vq_listing_controller->activityTracker($vq_data_final->id,'1',$response, 'vq_metis_response');
                } catch (\Exception $e) {
                    \Log::error("API call failed for vq_metis_response for : ".$vq_data_final->id.'-'.$vq_data_final->institution_id.' - ' . $e->getMessage());
                }
                
                // foreach($ignoredVq as $child){
                //     print_r("send json object sent child");
                //     $send_json['institute_code'] = $child->institution_id;
                //     $send_json['vq_id'] = $vq_data_final->id.'.'.$child->id;
                //     $body = json_encode($send_json);
                //     try {
                //         $r = $client->request('POST', env('API_URL').'/api/QuotationPush', [
                //             'body' => $body
                //         ]);
                //         $response = $r->getBody()->getContents();
                //         $data = json_decode($response);
                //         //$vq_listing_controller->activityTracker($vq_data_final->id,'1',$body, 'vq_metis_child_request');

                //         // $vq_listing_controller->activityTracker($vq_data_final->id,'1',$response, 'vq_metis_child_response'); // hide by arunchandru at 23-01-2025
                //     } catch (\Exception $e) {
                //         \Log::error("API call failed for vq_metis_child_response for : ".$vq_data_final->id.'-'.$child->institution_id.' - ' . $e->getMessage());
                //     }
                // } // hide by arunchandru at 23-01-2025
            }
            /*added on 15052024 for revision wise activity log and send quotation api send ends*/
        }
        
        // Add code to send mail and trigger metis api to update code here

        // poc email
        $id = $this->vq_id;
        $data = array();
        $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();

        $data['vq_data']= $vq;
        /*$data['stockist_data'] = VoluntaryQuotation::join('stockist_master','stockist_master.institution_code','=','voluntary_quotation.institution_id')
        ->where('voluntary_quotation.id',$id)
        ->where('stockist_master.stockist_type_flag',1)
        ->where('voluntary_quotation.is_deleted', 0)
        ->select('stockist_master.*')->get();*/
        $spll_stockist_data = VoluntaryQuotation::join('stockist_master','stockist_master.institution_code','=','voluntary_quotation.institution_id')
        ->where('voluntary_quotation.id',$id)
        ->where('stockist_master.stockist_type_flag',1)
        ->where('voluntary_quotation.is_deleted', 0)
        ->where(function($query) {
            $query->whereNull('stockist_master.stockist_type')
                  ->orWhere('stockist_master.stockist_type', 'SPLL');
        })
        ->select('stockist_master.*')->get();

        $spil_stockist_data = VoluntaryQuotation::join('stockist_master','stockist_master.institution_code','=','voluntary_quotation.institution_id')
        ->where('voluntary_quotation.id',$id)
        ->where('stockist_master.stockist_type_flag',1)
        ->where('voluntary_quotation.is_deleted', 0)
        ->where(function($query) {
            $query->whereNull('stockist_master.stockist_type')
                  ->orWhere('stockist_master.stockist_type', 'SPIL');
        })
        ->select('stockist_master.*')->get();
        
        $data['poc_data'] = VoluntaryQuotation::join('poc_master','poc_master.institution_id','=','voluntary_quotation.institution_id')
        ->where('voluntary_quotation.id',$id)
        ->where('voluntary_quotation.is_deleted', 0)
        ->select('poc_master.*')->first();

        /*if($vq->parent_vq_id !=0){
            $data['revision_count']=VoluntaryQuotation::where('parent_vq_id',$vq->parent_vq_id)->where('id','<=',$this->vq_id)->where('is_deleted', 0)->count();

        }else{
            $data['revision_count']=0;
        }*/
        $revision_number = VoluntaryQuotation::where('id',$this->vq_id)->first();
        $data['revision_count'] = $revision_number->rev_no;
        
        print_r("first");
        $data['signature']=Signature::first();
        $type1 = pathinfo(base_path().'/public/images/'.$data['signature']->spll_sign, PATHINFO_EXTENSION);
        $type2 = pathinfo(base_path().'/public/images/'.$data['signature']->spil_sign, PATHINFO_EXTENSION);
        $data['signature']->spll_sign = 'data:image/' . $type1 . ';base64,' . base64_encode(file_get_contents(base_path().'/public/images/'.$data['signature']->spll_sign));
        $data['signature']->spil_sign = 'data:image/' . $type2 . ';base64,' . base64_encode(file_get_contents(base_path().'/public/images/'.$data['signature']->spil_sign));

        // $data["client_name"]=$request->get("client_name");

        // Code to fetch Excel data count to check SPIL and SPLL file is empty or not starts here

        $query = VoluntaryQuotationSkuListing::leftJoin('employee_master','employee_master.div_code','=','voluntary_quotation_sku_listing.div_id')->select('voluntary_quotation_sku_listing.item_code',
        'voluntary_quotation_sku_listing.brand_name',
        'voluntary_quotation_sku_listing.hsn_code',
        'voluntary_quotation_sku_listing.applicable_gst',
        'voluntary_quotation_sku_listing.composition',
        'voluntary_quotation_sku_listing.type',
        'voluntary_quotation_sku_listing.div_name',
        'voluntary_quotation_sku_listing.pack',
        'voluntary_quotation_sku_listing.discount_rate',
        'voluntary_quotation_sku_listing.mrp')
        ->selectRaw('ROUND((voluntary_quotation_sku_listing.mrp - voluntary_quotation_sku_listing.discount_rate )* 100.0 / voluntary_quotation_sku_listing.mrp,2) as percentt')
        ->where('vq_id',$id)
        ->where('voluntary_quotation_sku_listing.is_deleted',0);

        $spilExcelDataCount = (clone $query)->where('employee_master.div_type','SPIL')->distinct()->count();

        $spllExcelDataCount = (clone $query)->where('employee_master.div_type','SPLL')->distinct()->count();

        // Code to fetch Excel data count to check SPIL and SPLL file is empty or not ends here
        $spllPdf = null; 
        $spilPdf = null;
        if(count($spll_stockist_data) > 0)
        {
            $data['stockist_data'] = $spll_stockist_data;
            $spllPdf = PDF::loadView('admin.pdf.spllpdf', compact('data'));
        }
        if(count($spil_stockist_data) > 0)
        {
            $data['stockist_data'] = $spil_stockist_data;
            $spilPdf = PDF::loadView('admin.pdf.spilpdf', compact('data'));
        }

        $spllExcel = Excel::raw(new InitiatorExport($id,'SPLL'), BaseExcel::XLSX);
        $spilExcel = Excel::raw(new InitiatorExport($id,'SPIL'), BaseExcel::XLSX);
        print_r("second");

        // $data["email"]="mansoor@noesis.tech";
        $data["subject"]="IDAP Quotation Mail for ". data_get($data, 'poc_data.institution_id') . "  " . data_get($data, 'poc_data.institution_name');
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $data['year']=$year;
        $data['institution_name']=$vq->hospital_name;

        $poc_data = PocMaster::where('institution_id',$vq->institution_id)->first();
        $data['email']=$poc_data->fsm_email;
        //$data['email']='Devendra.Yede@sunpharma.com';
        $data['email_cc']=array();
        array_push($data['email_cc'],$poc_data->zsm_email);
        array_push($data['email_cc'],$poc_data->rsm_email);
        // array_push($data['email_cc'],'vijaya@noesis.com');
        array_push($data['email_cc'],'ImranKhan.IT@sunpharma.com');
        // array_push($data['email_cc'],'bhagyeshVijay.Joshi@sunpharma.com');
        // $data['email_cc']=array('abhishek@noesis.tech', 'venkitaraman@noesis.tech','vijaya@noesis.tech');

        if(env('APP_URL') == 'https://idap.noesis.dev'){
            $data['email'] = 'sumeet@noesis.tech';
            $data['email_cc'] = 'mansoor@noesis.tech';
        }
        elseif(env('APP_URL') == 'http://172.16.8.192/' || env('APP_URL') == 'https://172.16.8.192/'){
            $data['email'] = 'ImranKhan.IT@sunpharma.com';
            $data['email_cc'] = 'bhagyeshvijay.joshi@sunpharma.com';
        }

	//$spllExcelDataCount = 1;
        try{
            if($spilExcelDataCount != 0 && $spllExcelDataCount != 0){

                Mail::send('admin.emails.send_quotation', $data, function($message)use($data,$spllPdf,$spilPdf,$spllExcel,$spilExcel,$spll_stockist_data,$spil_stockist_data) {
                    $message->to($data['email'])
                    ->subject($data["subject"])
                    ->cc($data['email_cc'])
                    // ->replyTo('idap.support@sunpharma.com')
                    /*->attachData($spllPdf->output(), $data['institution_name']."-CL-SPLL.pdf")
                    ->attachData($spilPdf->output(), $data['institution_name']."-CL-SPIL.pdf")*/
                    ->attachData($spllExcel, $data['institution_name']."-PS-SPLL.xlsx")
                    ->attachData($spilExcel, $data['institution_name']."-PS-SPIL.xlsx");
                    if(count($spll_stockist_data) > 0) {
                        $message->attachData($spllPdf->output(), $data['institution_name'] . "-CL-SPLL.pdf");
                    }


                    if(count($spil_stockist_data) > 0) {
                        $message->attachData($spilPdf->output(), $data['institution_name'] . "-CL-SPIL.pdf");
                    }
                });
            }
            else if($spilExcelDataCount != 0 && $spllExcelDataCount == 0){

                Mail::send('admin.emails.send_quotation', $data, function($message)use($data,$spllPdf,$spilPdf,$spilExcel,$spll_stockist_data,$spil_stockist_data) {
                    $message->to($data['email'])
                    ->subject($data["subject"])
                    ->cc($data['email_cc'])
                    // ->replyTo('idap.support@sunpharma.com')
                    //->attachData($spilPdf->output(), $data['institution_name']."-CL-SPIL.pdf")
                    ->attachData($spilExcel, $data['institution_name']."-PS-SPIL.xlsx");
                    if(count($spll_stockist_data) > 0)
                    {
                        $message->attachData($spllPdf->output(), $data['institution_name'] . "-CL-SPLL.pdf");
                    }

                    if(count($spil_stockist_data) > 0){
                        $message->attachData($spilPdf->output(), $data['institution_name'] . "-CL-SPIL.pdf");
                    }
                });
            }
            else if($spilExcelDataCount == 0 && $spllExcelDataCount != 0){

                Mail::send('admin.emails.send_quotation', $data, function($message)use($data,$spllPdf,$spilPdf,$spllExcel,$spll_stockist_data,$spil_stockist_data) {
                    $message->to($data['email'])
                    ->subject($data["subject"])
                    ->cc($data['email_cc'])
                    // ->replyTo('idap.support@sunpharma.com')
                    //->attachData($spllPdf->output(), $data['institution_name']."-CL-SPLL.pdf")
                    ->attachData($spllExcel, $data['institution_name']."-PS-SPLL.xlsx");
                    if(count($spll_stockist_data) > 0){
                        $message->attachData($spllPdf->output(), $data['institution_name'] . "-CL-SPLL.pdf");
                    }

                    if(count($spil_stockist_data) > 0){
                        $message->attachData($spilPdf->output(), $data['institution_name'] . "-CL-SPIL.pdf");
                    }
                });
            }
            
        }catch(JWTException $exception){
            $this->serverstatuscode = "0";
            $this->serverstatusdes = $exception->getMessage();
        }
        if (Mail::failures()) {
             $this->statusdesc  =   "Error sending mail";
             $this->statuscode  =   "0";
 
        }else{
 
           $this->statusdesc  =   "Message sent Succesfully";
           $this->statuscode  =   "1";
        }
        print_r("mail sent");



        //completion email
       /* $data1["email"]="mansoor@noesis.tech";
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $data1['year']=$year;
        $data1["subject"]="IDAP VQ Process Completed for ".$year;
        $data1['institution_name']=$vq->hospital_name;

        $emp_email = Employee::where('emp_level','L1')->pluck('emp_email')->toArray();
        $data1['actual_email_to']=$emp_email;
        $data1['actual_email_cc']=array();
        array_push($data1['actual_email_cc'],'abhishek@noesis.tech');
        array_push($data1['actual_email_cc'],'venkitaraman@noesis.tech');
        array_push($data1['actual_email_cc'],'vijaya@noesis.tech');
        $data1['email_cc']=array('abhishek@noesis.tech', 'venkitaraman@noesis.tech','vijaya@noesis.tech');

        try{
            Mail::send('admin.emails.vq_completion', $data1, function($message)use($data1) {
            $message->to($data1["email"])
            ->subject($data1["subject"])
            ->cc($data1['email_cc']);
            });
        }catch(JWTException $exception){
            $this->serverstatuscode = "0";
            $this->serverstatusdes = $exception->getMessage();
        }
        if (Mail::failures()) {
             $this->statusdesc  =   "Error sending mail";
             $this->statuscode  =   "0";
 
        }else{
 
           $this->statusdesc  =   "Message sent Succesfully";
           $this->statuscode  =   "1";
        }*/


        if($this->skuIdArr == null){
            //Update last year data with current year
            $send_json = array();
            $date=date_create($vq->created_at);
            if (date_format($date,"m") >= 4) {//On or After April (FY is current year - next year)
                $financial_year = (date_format($date,'Y')) . '-' . (date_format($date,'y')+1);
            } else {//On or Before March (FY is previous year - current year)
                $financial_year = (date_format($date,'Y')-1) . '-' . date_format($date,'y');
            }
            $send_json['fin_year'] = $financial_year;
            $send_json['institute_code'] = $vq->institution_id;
            $send_json['vq_id'] = $vq->id;
            $send_json['quotation_type'] = 'VQ';
            $send_json['revision_number'] = $data['revision_count'];
            $send_json['quotation_start_date'] = $vq->contract_start_date;
            $send_json['quotation_end_date'] = $vq->contract_end_date;
            if($this->skuIdArr != null){
                $send_json['DiscountModeFlag'] = "Y";
            }

            //Update last year data with current year
            foreach($listing_data as $single_data){
                $lastYear = LastYearPrice::updateOrCreate(['sku_id' => $single_data['item_code'],'institution_id' => $vq->institution_id ,'division_id' => $single_data['div_id'],'year' => $year], [ 'discount_percent' => $single_data['discount_percent'], 'ptr' => $single_data['ptr'], 'mrp' => $single_data['mrp'], 'updated_at' => date('Y-m-d H:i:s') ]);
                $sku_arr['item_code'] = $single_data['item_code'];
                $sku_arr['div_code'] = $single_data['div_id'];
                $sku_arr['discount_percent'] = $single_data['discount_percent'];
                $sku_arr['discount_rate'] = $single_data['discount_rate'];
                $sku_arr['stockist_code'] = $single_data['stockist_code'];
                $sku_arr['payment_mode'] = $single_data['payment_mode'];
                $sku_arr['net_discount_percent'] = $single_data['net_discount_percent'];

                $send_json['sku'][]=$sku_arr;
            }
            print_r("send json object created");

            $vq_listing_controller->activityTracker($vq->id,'1',json_encode($send_json), 'vq_metis_object');
            $headers = [
                'Content-Type' => 'application/json',
                'AccessToken' => 'key',
                'Authorization' => 'Bearer '.$this->jwt,
            ];
            
            $client = new GuzzleClient([
                'headers' => $headers,
                'verify' => false
            ]);
            
            $body = json_encode($send_json);
            /* hide by arunchandru 29-01-2025 */
            // $ignoredVq = IgnoredInstitutions::where('parent_institution_id', $vq->institution_id)->get();
            // foreach($ignoredVq as $child){
            //     //print_r("send json object sent child");
            //     $send_json['institute_code'] = $child->institution_id;
            //     $send_json['vq_id'] = $vq->id.'.'.$child->id;
            //     $body_activity = json_encode($send_json);
            
            //     $vq_listing_controller->activityTracker($vq->id,'1',$body_activity, 'vq_metis_child_request');
            // }
            try {
                $r = $client->request('POST', env('API_URL').'/api/QuotationPush', [
                    'body' => $body
                ]);
                $response = $r->getBody()->getContents();
                $data = json_decode($response);
                $vq_listing_controller->activityTracker($vq->id,'1',$response, 'vq_metis_response');
            } catch (\Exception $e) {
                \Log::error("API call failed for vq_metis_response for : ".$vq->id.'-'.$vq->institution_id.' - ' . $e->getMessage());
            }
            // Check if Child VQ are present in ignored_institutions table
            
            // foreach($ignoredVq as $child){
            //     print_r("send json object sent child");
            //     $send_json['institute_code'] = $child->institution_id;
            //     $send_json['vq_id'] = $vq->id.'.'.$child->id;
            //     $body = json_encode($send_json);
                
            //     try {
            //         $r = $client->request('POST', env('API_URL').'/api/QuotationPush', [
            //             'body' => $body
            //         ]);
            //         $response = $r->getBody()->getContents();
            //         $data = json_decode($response);
            //         //$vq_listing_controller->activityTracker($vq->id,'1',$body, 'vq_metis_child_request');

            //         $vq_listing_controller->activityTracker($vq->id,'1',$response, 'vq_metis_child_response');
            //     } catch (\Exception $e) {
            //         \Log::error("API call failed for vq_metis_child_response for : ".$vq->id.'-'.$child->institution_id.' - ' . $e->getMessage());
            //     }
            // } // hide by arunchandru 29-01-2025
            print_r("send json object sent");
        }
        // update z_max_rev table starts
        /*DB::table('z_max_rev')->truncate();
        DB::statement("
            INSERT INTO z_max_rev (max_rev_no, item_code, institution_id)
            SELECT 
                MAX(v2.rev_no) AS max_rev_no,
                s.item_code,
                v2.institution_id
            FROM 
                voluntary_quotation_sku_listing AS s 
            LEFT JOIN 
                voluntary_quotation AS v2 ON v2.id = s.vq_id 
            WHERE 
                v2.year = '".$year."' 
                AND s.is_deleted = 0 
                AND v2.vq_status = 1 
                AND v2.is_deleted = 0
            GROUP BY 
                s.item_code, v2.institution_id
        ");*/
        // update z_max_rev table ends
        // return response()->json(compact('this'));
        return 0;

    }

}
