<?php

namespace App\Jobs;

use App\Http\Controllers\Api\VqListingController;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\VoluntaryQuotationSkuListingStockist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\IgnoredInstitutions;
use GuzzleHttp\Client as GuzzleClient;
use DB;
class AddStockist implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $uniqueVqIds;
    protected $stockist_ids;
    protected $stockist_value;
    protected $jwt;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($uniqueVqIds, $stockist_ids, $stockist_value, $jwt)
    {
        $this->uniqueVqIds = $uniqueVqIds;
        $this->stockist_ids = $stockist_ids;
        $this->stockist_value = $stockist_value;
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
            $send_json = array();
            $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
            $date=date_create($singleVq->created_at);
            if (date_format($date,"m") >= 4) {//On or After April (FY is current year - next year)
                $financial_year = (date_format($date,'Y')) . '-' . (date_format($date,'y')+1);
            } else {//On or Before March (FY is previous year - current year)
                $financial_year = (date_format($date,'Y')-1) . '-' . date_format($date,'y');
            }
            $send_json['fin_year'] = $financial_year;
            $send_json['institute_code'] = $singleVq->institution_id;
            $send_json['vq_id'] = $singleVq->id;
            $send_json['quotation_type'] = 'VQ';
            $send_json['revision_number'] = $singleVq->rev_no;
            $send_json['quotation_start_date'] = $singleVq->contract_start_date;
            $send_json['quotation_end_date'] = $singleVq->contract_end_date;
            $send_json['Is_Added'] = 'N';
            $send_json['Is_ADDED_STOCKIST'] = 'Y';
            // $send_json['sku'] = null;

            $institution_code = $singleVq->institution_id;
            // $maxRevSubquery = DB::table('voluntary_quotation_sku_listing as s')
            // ->select(DB::raw('MAX(v2.rev_no) AS max_rev_no'), 's.item_code', 'v2.institution_id')
            // ->leftJoin('voluntary_quotation as v2', 'v2.id', '=', 's.vq_id')
            // ->where('v2.year', $year)
            // ->where('s.is_deleted', 0)
            // ->where('v2.vq_status', 1)
            // ->where('v2.is_deleted', 0)
            // ->where('v2.institution_id', $institution_code)
            // ->groupBy('s.item_code');

            // $vqslStockistData = DB::table('voluntary_quotation_sku_listing as vqsl')
            // ->select('vqsl.*', 'vq.*','vq.id as vq_id','vqsl.id as sku_id','rev_no as revision_count')
            // ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
            // ->joinSub($maxRevSubquery, 'max_rev', function ($join) use($institution_code) {
            //     $join->on('vqsl.item_code', '=', 'max_rev.item_code')
            //         ->where('vq.institution_id',  $institution_code)
            //         ->on('vq.rev_no', '=', 'max_rev.max_rev_no');
            // })
            // ->where('vq.institution_id',  $institution_code)
            // ->where('vq.year', $year)
            // ->where('vq.vq_status', 1)
            // ->where('vq.is_deleted', 0)
            // ->where('vqsl.is_deleted', 0)
            // ->get();

            $skulisiting_data = VoluntaryQuotationSkuListing::where('voluntary_quotation_sku_listing.vq_id', $singleVq->id)
            ->where('voluntary_quotation_sku_listing.is_deleted', 0)
            ->get();
            // dd($skulisiting_data);die;
            foreach($skulisiting_data as $s){
                $ptr = $s->ptr;
                $discount_percent = $s->discount_percent;
                $inputMargin = 10;
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
                /** get json data for activity tracker  */
                $sku_arr['item_code'] = $s->item_code;
                $sku_arr['div_code'] = $s->div_id;
                $sku_arr['discount_percent'] = $s->discount_percent;
                $sku_arr['discount_rate'] = $s->discount_rate;
                $sku_arr['stockist_code'] = $this->stockist_value;
                $sku_arr['payment_mode'] = 'DM';
                $sku_arr['net_discount_percent'] = $netDiscountRateToStockist;

                $send_json['sku'][] = $sku_arr;
            }
            // $send_json['product_count'] = (!empty($skulisiting_data->toArray())) ? count($vqslStockistData->toArray()) : '0';
            $send_json['stockist_id'] = $this->stockist_value;

            $vq_listing_controller->activityTracker($singleVq->id,'1',json_encode($send_json), 'stockist_add_object');
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
            
            $r = $client->request('POST', env('API_URL').'/api/QuotationPush', [
                'body' => $body
            ]);
            $response = $r->getBody()->getContents();
            $vq_listing_controller->activityTracker($singleVq->id, '1', $response, 'stockist_add_response');
        }
    }
}
