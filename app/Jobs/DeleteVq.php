<?php

namespace App\Jobs;

use App\Http\Controllers\Api\VqListingController;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\VoluntaryQuotationSkuListingStockist;
use App\Models\IdapDiscTran;
use App\Models\PocMaster;
use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Mail;

class DeleteVq implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $vq_id;
    protected $jwt;
    public $timeout = 999999;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($vq_id,$jwt)
    {
        $this->vq_id = $vq_id;
        $this->jwt = $jwt;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $vq = VoluntaryQuotation::where('id',$this->vq_id)->first();

        $vq_listing_controller = new VqListingController;
        if($vq->parent_vq_id !=0){
            $revision_count=VoluntaryQuotation::where('parent_vq_id',$vq->parent_vq_id)->where('id','<=',$this->vq_id)->where('is_deleted', 0)->count();

        }else{
            $revision_count=0;
        }

        $listing_data = VoluntaryQuotationSkuListingStockist::join('voluntary_quotation_sku_listing', 'voluntary_quotation_sku_listing.id', '=', 'voluntary_quotation_sku_listing_stockist.sku_id')
                ->select('voluntary_quotation_sku_listing_stockist.payment_mode', 'voluntary_quotation_sku_listing_stockist.net_discount_percent',  'voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.item_code',  'voluntary_quotation_sku_listing.discount_percent', 'voluntary_quotation_sku_listing.discount_rate', 'voluntary_quotation_sku_listing.mrp','voluntary_quotation_sku_listing.ptr', 'stockist_master.stockist_code')
                ->join('stockist_master', 'stockist_master.id', '=', 'voluntary_quotation_sku_listing_stockist.stockist_id')
                ->where('voluntary_quotation_sku_listing_stockist.vq_id', $this->vq_id)
                ->where('voluntary_quotation_sku_listing.is_deleted', 0)
                ->where('voluntary_quotation_sku_listing_stockist.is_deleted', 0)
                ->where('stockist_master.stockist_type_flag', 1)
                ->get();
        
        $send_json = array();
        $date=date_create($vq->created_at);
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
        $send_json['institute_code'] = $vq->institution_id;
        $send_json['vq_id'] = $vq->id;
        $send_json['quotation_type'] = 'VQ';
        $send_json['revision_number'] = $revision_count;
        $send_json['quotation_start_date'] = $vq->contract_start_date;
        $send_json['quotation_end_date'] = $vq->contract_end_date;
        $send_json['Is_Deleted'] = 'Y';
        $send_json['Is_DELETED_STOCKIST'] = 'N';
        $send_json['sku'] = null;

        $without_send_json = array();
        foreach($listing_data as $single_data){
            
            $sku_arr['item_code'] = $single_data['item_code'];
            $sku_arr['div_code'] = $single_data['div_id'];
            $sku_arr['discount_percent'] = $single_data['discount_percent'];
            $sku_arr['discount_rate'] = $single_data['discount_rate'];
            $sku_arr['stockist_code'] = $single_data['stockist_code'];
            $sku_arr['payment_mode'] = $single_data['payment_mode'];
            $sku_arr['net_discount_percent'] = $single_data['net_discount_percent'];

            $without_send_json['sku'][]=$sku_arr;
        }

        $vq_listing_controller->activityTracker($vq->id,'1',json_encode($send_json), 'delete_vq_metis_object');
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

        $existingDiscTran = IdapDiscTran::where('INST_ID', $send_json['institute_code'])->where('FIN_YEAR', $send_json['fin_year'])->exists();
        if($existingDiscTran):
            $maildata['vq_id'] = $vq->id;
            $maildata['rev_no'] = $vq->rev_no;
            $maildata['institution_id'] = $vq->institution_id;
            $maildata['hospital_name'] = $vq->hospital_name;
            $Request_json = json_encode($send_json, JSON_PRETTY_PRINT);
            // This is the QuotationPush API. Insert data into the table and check the JSON data sent to the     API // added by arunchandru at 21-02-2025 
            DB::beginTransaction();
            try {
                print_r("DiscTran Start");
                $UpdateDiscTran = IdapDiscTran::where('INST_ID', $send_json['institute_code'])->where('FIN_YEAR', $send_json['fin_year'])->update(['IS_DELETED' => 'X', 'METIS_UPD_DATE' => now()]);
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
                    print_r("DiscTran Updated successfull");
                    // Call QuotationPush API
                    $r = $client->request('POST', env('API_URL').'/api/QuotationPush', [
                        'body' => $body
                    ]);
                    $response = $r->getBody()->getContents();
                    // Decode response
                    $jsondata = json_decode($response);
                    if($jsondata->status == 1):
                        // Track activity
                        $vq_listing_controller->activityTracker($vq->id,'1',$response, 'delete_vq_metis_response');
                        print_r("QuotationPush API Done");
                    else:
                        $maildata['Message'] = 'API call failed for vq_metis_response.';
                        $maildata['Error'] = $response;
                        $vq_listing_controller->activityTracker($vq->id, '1', json_encode($maildata), 'Idap_disc_tran_api_response_failed');
                        // $maildata['ErrorReason'] = json_encode($response);
                        $Reason = json_encode($maildata, JSON_PRETTY_PRINT);
                        // $poc_data = PocMaster::where('institution_id', $vq->institution_id)->first();
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
                    Log::error($e->getMessage());
                    $maildata['Message'] = 'API call failed for vq_metis_response.';
                    $maildata['Error'] = $e->getMessage();
                    $vq_listing_controller->activityTracker($vq->id, '1', json_encode($maildata), 'Idap_disc_tran_api_response_failed');
                    // $maildata['ErrorReason'] = json_encode($e->getMessage());
                    $Reason = json_encode($maildata, JSON_PRETTY_PRINT);
                    // $poc_data = PocMaster::where('institution_id', $vq->institution_id)->first();
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
                Log::error("Insert failed: " . $e->getMessage());
                $maildata['Message'] = "Database insert failed Quotation Push API is not called";
                $maildata['Error'] = json_encode($e->getMessage());
                $vq_listing_controller->activityTracker($vq->id, '1', json_encode($maildata), 'Idap_disc_tran_insert_failed');
                // $maildata['ErrorReason'] = json_encode($e->getMessage());
                $Reason = json_encode($maildata, JSON_PRETTY_PRINT);
                // $poc_data = PocMaster::where('institution_id', $vq->institution_id)->first();
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
        endif;

        
        
       /* $r = $client->request('POST', env('API_URL').'/api/QuotationPush', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        $vq_listing_controller->activityTracker($vq->id,'1',$response, 'delete_vq_metis_response'); */ // hide by arunchandru at 25022025

    }
}
