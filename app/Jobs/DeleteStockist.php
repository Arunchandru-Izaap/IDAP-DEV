<?php

namespace App\Jobs;

use App\Http\Controllers\Api\VqListingController;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListingStockist;
use App\Models\IdapDiscTran;
use App\Models\PocMaster;
use DB;
use Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\IgnoredInstitutions;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Mail;

class DeleteStockist implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $uniqueVqIds;
    protected $stockist_ids;
    protected $jwt;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($uniqueVqIds,$stockist_ids,$jwt)
    {
        $this->uniqueVqIds = $uniqueVqIds;
        $this->stockist_ids = $stockist_ids;
        $this->jwt = $jwt;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $vq_listing_controller = new VqListingController;
        $vq = voluntaryQuotation::whereIn('id',$this->uniqueVqIds)->get();
        foreach($vq as $singleVq)
        {
            $without_send_json = array();
            $send_json = array();
            $date=date_create($singleVq->created_at);
            if (date_format($date,"m") >= 4) {//On or After April (FY is current year - next year)
                $financial_year = (date_format($date,'Y')) . '-' . (date_format($date,'y')+1);
            } else {//On or Before March (FY is previous year - current year)
                $financial_year = (date_format($date,'Y')-1) . '-' . date_format($date,'y');
            }
            // short financial year
            $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
            list($yearstart, $yearend) = explode('-', $year);
            // Take only the last 2 digits of the end year
            $short_end = substr($yearend, -2);
            // Combine
            $short_financial_year = $yearstart . '-' . $short_end;
            $send_json['fin_year'] = $short_financial_year;
            $send_json['institute_code'] = $singleVq->institution_id;
            $send_json['vq_id'] = $singleVq->id;
            $send_json['quotation_type'] = 'VQ';
            $send_json['revision_number'] = $singleVq->rev_no;
            $send_json['quotation_start_date'] = $singleVq->contract_start_date;
            $send_json['quotation_end_date'] = $singleVq->contract_end_date;
            $send_json['Is_Deleted'] = 'N';
            $send_json['Is_DELETED_STOCKIST'] = 'Y';
            $send_json['sku'] = null;
            $stockist_sku_deleted = VoluntaryQuotationSkuListingStockist::join('voluntary_quotation_sku_listing', 'voluntary_quotation_sku_listing.id', '=', 'voluntary_quotation_sku_listing_stockist.sku_id')->join('stockist_master', 'stockist_master.id', '=', 'voluntary_quotation_sku_listing_stockist.stockist_id')->
            select('voluntary_quotation_sku_listing_stockist.payment_mode', 'voluntary_quotation_sku_listing_stockist.net_discount_percent',  'voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.item_code',  'voluntary_quotation_sku_listing.discount_percent', 'voluntary_quotation_sku_listing.discount_rate', 'voluntary_quotation_sku_listing.mrp','voluntary_quotation_sku_listing.ptr', 'stockist_master.stockist_code')->
            whereIn('voluntary_quotation_sku_listing_stockist.id', $this->stockist_ids)->where('voluntary_quotation_sku_listing_stockist.vq_id',$singleVq->id)->get();
            foreach($stockist_sku_deleted as $single_data){
                /*$sku_arr['item_code'] = $single_data['item_code'];
                $sku_arr['div_code'] = $single_data['div_id'];
                $sku_arr['discount_percent'] = $single_data['discount_percent'];
                $sku_arr['discount_rate'] = $single_data['discount_rate'];
                $sku_arr['stockist_code'] = $single_data['stockist_code'];
                $sku_arr['payment_mode'] = $single_data['payment_mode'];
                $sku_arr['net_discount_percent'] = $single_data['net_discount_percent'];
                $sku_arr['is_deleted'] = 'X';

                $send_json['stockist_id'][]=$sku_arr; */
                $sku_arr['item_code'] = $single_data['item_code'];
                $sku_arr['div_code'] = $single_data['div_id'];
                $sku_arr['discount_percent'] = $single_data['discount_percent'];
                $sku_arr['discount_rate'] = $single_data['discount_rate'];
                $sku_arr['stockist_code'] = $single_data['stockist_code'];
                $sku_arr['payment_mode'] = $single_data['payment_mode'];
                $sku_arr['net_discount_percent'] = $single_data['net_discount_percent'];

                $without_send_json['sku'][]=$sku_arr;
                if (!isset($send_json['stockist_id'])) {
                    $send_json['stockist_id'] = array();
                }
                if (!in_array($single_data['stockist_code'], $send_json['stockist_id'])) {
                    $send_json['stockist_id'][] = $single_data['stockist_code'];
                }
            }
            $vq_listing_controller->activityTracker($singleVq->id,'1',json_encode($send_json), 'stockist_delete_object');
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
            $maildata['vq_id'] = $singleVq->id;
            $maildata['rev_no'] = $singleVq->rev_no;
            $maildata['institution_id'] = $singleVq->institution_id;
            $maildata['hospital_name'] = $singleVq->hospital_name;
            $Request_json = json_encode($send_json, JSON_PRETTY_PRINT);
            // This is the QuotationPush API. Insert data into the table and check the JSON data sent to the     API // added by arunchandru at 21-02-2025 
            DB::beginTransaction();
            try {
                print_r("DiscTran Start");

                // Update all existing records to mark them as deleted
                $UpdateDiscTran = IdapDiscTran::where('INST_ID', $send_json['institute_code'])
                // ->where('REV_NO', $send_json['revision_number'])
                ->whereIn('STOCKIST_CODE', $send_json['stockist_id'])
                ->where('FIN_YEAR', $send_json['fin_year'])
                ->update(['IS_DELETED' => 'X', 'METIS_UPD_DATE' => now()]);

                DB::commit();

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
                
                try {
                    print('-');
                    print_r(date('H:i:s'));
                    print('-');
                    // Call QuotationPush API
                    $r = $client->request('POST', env('API_URL').'/api/QuotationPush', [
                        'body' => $body
                    ]);
                    $response = $r->getBody()->getContents();
                    // Decode response
                    $jsondata = json_decode($response);
                    if($jsondata->status == 1):
                        $vq_listing_controller->activityTracker($singleVq->id,'1',$response, 'stockist_delete_response');
                        print_r("API Response Done");
                        print('-');
                        print_r(date('H:i:s'));
                        print('-');
                    else:
                        print_r("API Response Failed");
                        $maildata['Message'] = 'API call failed for vq_metis_response.';
                        $maildata['Error'] = $response;
                        $vq_listing_controller->activityTracker($singleVq->id, '1', json_encode($maildata), 'Idap_disc_tran_api_response_failed');
                        // $maildata['ErrorReason'] = json_encode($response);
                        $Reason = json_encode($maildata, JSON_PRETTY_PRINT);
                        // $poc_data = PocMaster::where('institution_id', $singleVq->institution_id)->first();
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
                    endif;
                } catch (\Exception $e) {
                    print_r("API Response Failed");
                    Log::error($e->getMessage());
                    $maildata['Message'] = 'API call failed for vq_metis_response.';
                    $maildata['Error'] = $e->getMessage();
                    $vq_listing_controller->activityTracker($singleVq->id, '1', json_encode($maildata), 'Idap_disc_tran_api_response_failed');
                    // $maildata['ErrorReason'] = json_encode($e->getMessage());
                    $Reason = json_encode($maildata, JSON_PRETTY_PRINT);
                    // $poc_data = PocMaster::where('institution_id', $singleVq->institution_id)->first();
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
                }
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Update failed: " . $e->getMessage());
                print_r("Update failed");
                $maildata['Message'] = "Database insert failed Quotation Push API is not called";
                $maildata['Error'] =  json_encode($e->getMessage());
                $vq_listing_controller->activityTracker($singleVq->id, '1', json_encode($maildata), 'Idap_disc_tran_insert_failed');
                // $maildata['ErrorReason'] = json_encode($e->getMessage());
                $Reason = json_encode($maildata, JSON_PRETTY_PRINT);
                // $poc_data = PocMaster::where('institution_id', $singleVq->institution_id)->first();
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
            }

            

            // $ignoredVq = IgnoredInstitutions::where('parent_institution_id', $singleVq->institution_id)->get();
            // foreach($ignoredVq as $child){
            //     $send_json['institute_code'] = $child->institution_id;
            //     $send_json['vq_id'] = $singleVq->id.'.'.$child->id;
            //     $body = json_encode($send_json);
            
            //     $r = $client->request('POST', env('API_URL').'/api/QuotationPush', [
            //         'body' => $body
            //     ]);
            //     $response = $r->getBody()->getContents();
            //     $data = json_decode($response);
            //     $vq_listing_controller->activityTracker($singleVq->id,'1',$body, 'stockist_delete_child_request');

            //     $vq_listing_controller->activityTracker($singleVq->id,'1',$response, 'stockist_delete_child_response');
            // } // hide by arunchandru 29012025
        }
    }
}
