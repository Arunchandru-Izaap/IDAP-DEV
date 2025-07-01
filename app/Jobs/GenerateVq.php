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
use App\Models\IdapDiscTran;
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
class GenerateVq implements ShouldQueue
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
    public function __construct($jwt, $skuIdArr=null, $changePayModeData = null)
    {
        //
        //$this->vq_id = $vq_id;
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
        $listing_data = VoluntaryQuotationSkuListingStockist::join('voluntary_quotation_sku_listing', 'voluntary_quotation_sku_listing.id', '=', 'voluntary_quotation_sku_listing_stockist.sku_id')
                ->select('voluntary_quotation_sku_listing_stockist.payment_mode', 'voluntary_quotation_sku_listing_stockist.net_discount_percent',  'voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.item_code',  'voluntary_quotation_sku_listing.discount_percent', 'voluntary_quotation_sku_listing.discount_rate', 'voluntary_quotation_sku_listing.mrp','voluntary_quotation_sku_listing.ptr', 'stockist_master.stockist_code')
                ->join('stockist_master', 'stockist_master.id', '=', 'voluntary_quotation_sku_listing_stockist.stockist_id')
                ->where('voluntary_quotation_sku_listing_stockist.vq_id', $this->vq_id)
                ->where('voluntary_quotation_sku_listing.is_deleted', 0)
                ->where('voluntary_quotation_sku_listing_stockist.is_deleted', 0)
                ->where('stockist_master.stockist_type_flag', 1);
        if($this->skuIdArr == null){
            $listing_data = $listing_data->get();
        }else{
            /*added on 15052024 for revision wise activity log and send quotation api send starts*/
            $all_vq = VoluntaryQuotationSkuListingStockist::select('voluntary_quotation.id','voluntary_quotation.rev_no','voluntary_quotation.institution_id','voluntary_quotation.rev_no','voluntary_quotation.contract_start_date','voluntary_quotation.contract_end_date','voluntary_quotation.created_at','voluntary_quotation.hospital_name')->join('voluntary_quotation', 'voluntary_quotation.id','=','voluntary_quotation_sku_listing_stockist.vq_id')
                ->whereIn('voluntary_quotation_sku_listing_stockist.id', $this->skuIdArr)->where('voluntary_quotation.is_deleted', 0)->distinct()->get();
            print_r(count($all_vq));
            $check_quotation_success_status = true;
            foreach ($all_vq as $vq_data_final) {
                $listing_data = VoluntaryQuotationSkuListingStockist::join('voluntary_quotation_sku_listing', 'voluntary_quotation_sku_listing.id', '=', 'voluntary_quotation_sku_listing_stockist.sku_id')
                ->select('voluntary_quotation_sku_listing_stockist.payment_mode', 'voluntary_quotation_sku_listing_stockist.net_discount_percent',  'voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.item_code',  'voluntary_quotation_sku_listing.discount_percent', 'voluntary_quotation_sku_listing.discount_rate', 'voluntary_quotation_sku_listing.mrp','voluntary_quotation_sku_listing.ptr', 'stockist_master.stockist_code')
                ->join('stockist_master', 'stockist_master.id', '=', 'voluntary_quotation_sku_listing_stockist.stockist_id')
                ->where('voluntary_quotation_sku_listing_stockist.vq_id', $vq_data_final->id)
                ->whereIn('voluntary_quotation_sku_listing_stockist.id', $this->skuIdArr)
                ->where('voluntary_quotation_sku_listing_stockist.is_deleted', 0)
                ->where('voluntary_quotation_sku_listing.is_deleted', 0)->where('stockist_master.stockist_type_flag', 1)->get();
                print_r(count($listing_data));
                // $updation = VoluntaryQuotation::where('id',$vq_data_final->id)->update(['vq_status' => 1]);
                $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
                $send_json = array();
                $date=date_create($vq_data_final->created_at);
                if (date_format($date,"m") >= 4) {//On or After April (FY is current year - next year)
                    $financial_year = (date_format($date,'Y')) . '-' . (date_format($date,'y')+1);
                } else {//On or Before March (FY is previous year - current year)
                    $financial_year = (date_format($date,'Y')-1) . '-' . date_format($date,'y');
                }
                // short financial year
                list($yearstart, $yearend) = explode('-', $year);
                // Take only the last 2 digits of the end year
                $short_end = substr($yearend, -2);
                // Combine
                $short_financial_year = $yearstart . '-' . $short_end;
                $send_json['fin_year'] = $short_financial_year;
                $send_json['institute_code'] = $vq_data_final->institution_id;
                $send_json['vq_id'] = $vq_data_final->id;
                $send_json['quotation_type'] = 'VQ';
                $send_json['revision_number'] = $vq_data_final->rev_no;
                $send_json['quotation_start_date'] = $vq_data_final->contract_start_date;
                $send_json['quotation_end_date'] = $vq_data_final->contract_end_date;
                $send_json['DiscountModeFlag'] = "Y";
                foreach($listing_data as $single_data){
                    $lastYear = LastYearPrice::updateOrCreate(['sku_id' => $single_data['item_code'],'institution_id' => $vq_data_final->institution_id ,'division_id' => $single_data['div_id'],'year' => $year], [ 'discount_percent' => $single_data['discount_percent'], 'ptr' => $single_data['ptr'], 'mrp' => $single_data['mrp'], 'updated_at' => date('Y-m-d H:i:s') ]);
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
                print('-');
                print_r(date('H:i:s'));
                print('-');
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

                $maildata['vq_id'] = $vq_data_final->id;
                $maildata['rev_no'] = $vq_data_final->rev_no;
                $maildata['institution_id'] = $vq_data_final->institution_id;
                $maildata['hospital_name'] = $vq_data_final->hospital_name;
                $Request_json = json_encode($send_json, JSON_PRETTY_PRINT);
                
                $failed_quotationtoEmails = DB::table('email_configurations')
                    ->where('email_type', 'TO')
                    ->where('status', 'ACTIVE')
                    ->where('used_for', 'idap_disc_trans_failed_quotation')
                    ->pluck('email_address')
                    ->toArray();

                $failed_quotationccEmails = DB::table('email_configurations')
                    ->where('email_type', 'CC')
                    ->where('status', 'ACTIVE')
                    ->where('used_for', 'idap_disc_trans_failed_quotation')
                    ->pluck('email_address')
                    ->toArray();

                $email = $failed_quotationtoEmails;
                $email_cc = $failed_quotationccEmails;
                // This is the QuotationPush API. Insert data into the table and check the JSON data sent to the API // added by arunchandru at 21-02-2025 
                DB::beginTransaction();
                try {
                    print_r("DiscTran Start ");
                    print_r(count($send_json['sku']));
                    $is_IdapDiscTran = IdapDiscTran::where('IDAP_PRNT_Q_ID', $send_json['vq_id'])->exists();
                    if($is_IdapDiscTran == true):
                        $base_idap_prnt_q_id = $send_json['vq_id']; // Base vq_id
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
                        $idap_prnt_q_id = $send_json['vq_id'];
                    endif;

                    $check_inst_id = [];
                    foreach ($send_json['sku'] as $json_data) {
                        // if($send_json['revision_number'] != 0): // 0th revision not getting and update in idap trans table
                            $discountMode = $json_data['payment_mode'];
                            $updateMode = ($discountMode === 'CN') ? ['CN', 'DM'] : ['DM', 'CN'];
                            if($discountMode == 'CN'):
                                IdapDiscTran::where('INST_ID', $send_json['institute_code'])
                                    // ->where('REV_NO', $send_json['revision_number'])
                                    ->where('ITEM_CODE', $json_data['item_code'])
                                    ->where('STOCKIST_CODE', $json_data['stockist_code'])
                                    ->where('DISCOUNT_MODE', 'DM')
                                    ->update(['IS_DELETED' => 'X', 'METIS_UPD_DATE' => now()]);
                            elseif($discountMode == 'DM'):
                                IdapDiscTran::where('INST_ID', $send_json['institute_code'])
                                    // ->where('REV_NO', $send_json['revision_number'])
                                    ->where('ITEM_CODE', $json_data['item_code'])
                                    ->where('STOCKIST_CODE', $json_data['stockist_code'])
                                    ->where('DISCOUNT_MODE', 'CN')
                                    ->update(['IS_DELETED' => 'X', 'METIS_UPD_DATE' => now()]);
                            endif;
                        // endif;
                        
                        $DiscTran = IdapDiscTran::create([
                            'FIN_YEAR' => $send_json['fin_year'],
                            'INST_ID' => $send_json['institute_code'],
                            'DIV_CODE' => $json_data['div_code'],
                            'ITEM_CODE' => $json_data['item_code'],
                            'DISC_PCT' => $json_data['discount_percent'],
                            'DISC_RATE' => $json_data['discount_rate'],
                            'IDAP_PRNT_Q_ID' => $idap_prnt_q_id,
                            'METIS_Q_ID' => '',
                            'Q_TYPE' => 'VQ',
                            'REV_NO' => $send_json['revision_number'],
                            'UPD_FLAG' => '0',
                            'Q_DATE' => now(),
                            'METIS_UPD_DATE' => now(),
                            'Q_STRT_DATE' => $send_json['quotation_start_date'],
                            'Q_END_DATE' => $send_json['quotation_end_date'],
                            'STOCKIST_CODE' => $json_data['stockist_code'],
                            'DISCOUNT_MODE' => $json_data['payment_mode'],
                            'NETDISCOUNTPERC_STOCKIST' => $json_data['net_discount_percent'],
                        ]);
                        
                        $check_inst_id[] = $DiscTran->INST_ID;
                    }
                    
                    DB::commit();
                    print('-');
                    print_r(date('H:i:s'));
                    print('-');
                   
                    if (count($send_json['sku']) === count($check_inst_id)) {
                        $DiscTran_count = count($check_inst_id);
                        print_r("Inserted Count:{$DiscTran_count} ");
                        try {
                            // Call QuotationPush API
                            $r = $client->request('POST', env('API_URL').'/api/QuotationPush', [
                                'body' => $body
                            ]);
                            $response = $r->getBody()->getContents();
                            $jsondata = json_decode($response);
                            if($jsondata->status == 1):
                                // Track activity
                                $vq_listing_controller->activityTracker($vq_data_final->id, '1', $response, 'vq_metis_response');
                                //code to update VQ Status
                                $updation = VoluntaryQuotation::where('id',$vq_data_final->id)->where('is_deleted', 0)->update(['vq_status'=>1]);
                                print_r("API response Done");
                                print('-');
                                print_r(date('H:i:s'));
                                print('-');
                            else:
                                print_r("API response Failed");
                                $maildata['Message'] = 'API call failed for vq_metis_response.';
                                $maildata['Error'] = $jsondata;
                                $vq_listing_controller->activityTracker($vq_data_final->id, '1', json_encode($maildata), 'Idap_disc_tran_api_response_failed');
                                // $maildata['ErrorReason'] = json_encode($jsondata);
                                $Reason = json_encode($maildata, JSON_PRETTY_PRINT);
                                // $poc_data = PocMaster::where('institution_id', $vq_data_final->institution_id)->first();
                                // $email = (isset($poc_data->fsm_email))? $poc_data->fsm_email : 'ImranKhan.IT@sunpharma.com';
                                // $email_cc = 'ImranKhan.IT@sunpharma.com';
                                try{
                                    Mail::send('admin.emails.send_quotation_failed', $maildata, function($message)use($email,$email_cc, $Request_json, $Reason) {
                                        $message->to($email)
                                        ->subject('Your send to Quotation failed')
                                        ->cc($email_cc)
                                        ->attachData($Request_json, "Request_json.txt")
                                        ->attachData($Reason, "Response_json.txt");
                                    });
                                }catch(JWTException $exception){
                                    $this->serverstatuscode = "0";
                                    $this->serverstatusdes = $exception->getMessage();
                                }
                                $check_quotation_success_status = false;
                            endif;
                        } catch (\Exception $e) {
                            Log::error($e->getMessage());
                            print_r("API response Failed");
                            $maildata['Message'] = 'API call failed for vq_metis_response.';
                            $maildata['Error'] = $e->getMessage();
                            $vq_listing_controller->activityTracker($vq_data_final->id, '1', json_encode($maildata), 'Idap_disc_tran_api_response_failed');
                            // $maildata['ErrorReason'] = json_encode($e->getMessage());
                            $Reason = json_encode($maildata, JSON_PRETTY_PRINT);
                            // $poc_data = PocMaster::where('institution_id', $vq_data_final->institution_id)->first();
                            // $email = (isset($poc_data->fsm_email))? $poc_data->fsm_email : 'ImranKhan.IT@sunpharma.com';
                            // $email_cc = 'ImranKhan.IT@sunpharma.com';
                            try{
                                Mail::send('admin.emails.send_quotation_failed', $maildata, function($message)use($email,$email_cc, $Request_json, $Reason) {
                                    $message->to($email)
                                    ->subject('Your send to Quotation failed')
                                    ->cc($email_cc)
                                    ->attachData($Request_json, "Request_json.txt")
                                    ->attachData($Reason, "Response_json.txt");
                                });
                            }catch(JWTException $exception){
                                $this->serverstatuscode = "0";
                                $this->serverstatusdes = $exception->getMessage();
                            }
                            $check_quotation_success_status = false;
                        }
                    } else {
                        print_r("Missmatch count");
                        $maildata['Message'] = 'SKU count and IDAP DISC TRAN table count not equal Quotation Push API is not called';
                        $maildata['Error'] = '';
                        $vq_listing_controller->activityTracker($vq_data_final->id, '1', json_encode($maildata), 'Idap_disc_tran_insert_failed');
                        // $maildata['ErrorReason'] = "SKU count and IDAP DISC TRAN table count not equal";
                        $Reason = json_encode($maildata, JSON_PRETTY_PRINT);
                        // $poc_data = PocMaster::where('institution_id', $vq_data_final->institution_id)->first();
                        // $email = (isset($poc_data->fsm_email))? $poc_data->fsm_email : 'ImranKhan.IT@sunpharma.com';
                        // $email_cc = 'ImranKhan.IT@sunpharma.com';
                        try{
                            Mail::send('admin.emails.send_quotation_failed', $maildata, function($message)use($email,$email_cc, $Request_json, $Reason) {
                                $message->to($email)
                                ->subject('Your send to Quotation failed')
                                ->cc($email_cc)
                                ->attachData($Request_json, "Request_json.txt")
                                ->attachData($Reason, "Response_json.txt");
                            });
                        }catch(JWTException $exception){
                            $this->serverstatuscode = "0";
                            $this->serverstatusdes = $exception->getMessage();
                        }
                        $check_quotation_success_status = false;
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("Insert failed: " . $e->getMessage());
                    $maildata['Message'] = "Database insert failed Quotation Push API is not called";
                    $maildata['Error'] = (isset($send_json['sku']))? json_encode($e->getMessage()) : 'SKU items not found';
                    $vq_listing_controller->activityTracker($vq_data_final->id, '1', json_encode($maildata), 'Idap_disc_tran_insert_failed');
                    // $maildata['ErrorReason'] = (isset($send_json['sku']))? json_encode($e->getMessage()) : 'SKU items not found';
                    $Reason = json_encode($maildata, JSON_PRETTY_PRINT);
                    // $poc_data = PocMaster::where('institution_id', $vq_data_final->institution_id)->first();
                    // $email = (isset($poc_data->fsm_email))? $poc_data->fsm_email : 'ImranKhan.IT@sunpharma.com';
                    // $email_cc = 'ImranKhan.IT@sunpharma.com';
                    try{
                        Mail::send('admin.emails.send_quotation_failed', $maildata, function($message)use($email,$email_cc, $Request_json, $Reason) {
                            $message->to($email)
                            ->subject('Your send to Quotation failed')
                            ->cc($email_cc)
                            ->attachData($Request_json, "Request_json.txt")
                            ->attachData($Reason, "Response_json.txt");
                        });
                    }catch(JWTException $exception){
                        $this->serverstatuscode = "0";
                        $this->serverstatusdes = $exception->getMessage();
                    }
                    $check_quotation_success_status = false;
                } 
                print('-');
                print_r(date('H:i:s'));
                print('-');

               /* $ignoredVq = IgnoredInstitutions::where('parent_institution_id', $vq_data_final->institution_id)->get();
                foreach($ignoredVq as $child){
                    print_r("send json object sent child");
                    $send_json['institute_code'] = $child->institution_id;
                    $send_json['vq_id'] = $vq_data_final->id.'.'.$child->id;
                    $body = json_encode($send_json);    
                    $r = $client->request('POST', env('API_URL').'/api/QuotationPush', [
                        'body' => $body
                    ]);
                    $response = $r->getBody()->getContents();
                    $data = json_decode($response);
                    $vq_listing_controller->activityTracker($vq_data_final->id,'1',$body, 'vq_metis_child_request');
                    $vq_listing_controller->activityTracker($vq_data_final->id,'1',$response, 'vq_metis_child_response');          
                } */ // hide by arunchandru at 25022025
                // poc email
                $id = $vq_data_final->id;
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

                $data['revision_count']=$vq_data_final->rev_no;
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


                /*$spllPdf = PDF::loadView('admin.pdf.spllpdf', compact('data'));
                $spilPdf = PDF::loadView('admin.pdf.spilpdf', compact('data'));*/
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

                if($check_quotation_success_status == true):
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
                endif;



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
            }
            /*added on 15052024 for revision wise activity log and send quotation api send ends*/
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
            GROUP BY 
                s.item_code, v2.institution_id
        ");*/
        // update z_max_rev table ends
        // return response()->json(compact('this'));
        return 0;

    }

}
