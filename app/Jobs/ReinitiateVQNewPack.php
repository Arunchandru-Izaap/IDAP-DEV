<?php

namespace App\Jobs;
use App\Models\Institution;
use App\Models\CeilingMaster;
use App\Models\LastYearPrice;
use App\Jobs\PocMaster;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\ActivityTracker;
use App\Http\Controllers\Api\VqListingController;
use App\Models\IgnoredInstitutions;
use App\Models\InstitutionDivisionMapping;
use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\Stockist_master;
use App\Models\VoluntaryQuotationSkuListingStockist;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Session;

use function Ramsey\Uuid\v1;

class ReinitiateVQNewPack implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $oldpack;
    protected $newpack;
    protected $emp_code;
    protected $jwt_code;

    protected $name;
    protected $division_name;
    public $timeout = 999999;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($oldpack,$newpack,$emp_code,$jwt_code,$name,$division_name)
    {
        //
        $this->oldpack = $oldpack;
        $this->newpack = $newpack;
        $this->emp_code = $emp_code;
        $this->jwt_code = $jwt_code;

        $this->name = $name;
        $this->division_name = $division_name;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");

        $time_minus = strtotime("-1 year", time());
        $date_minus = date("Y-m-d", $time_minus);
        $last_year = $vq_listing_controller->getFinancialYear($date_minus,"Y");

        $headers = [
            'Content-Type' => 'application/json',
            'AccessToken' => 'key',
            'Authorization' => 'Bearer '.$this->jwt_code,
        ];
        
        $client = new GuzzleClient([
            'headers' => $headers,
            'verify' => false
        ]);
        
        $body = '{}';

        $ignoredInstitutions = IgnoredInstitutions::get();
        $ignoredInstitutions = array_map(function($inst){return $inst->institution_id;}, $ignoredInstitutions->all());
        
        // $vqInstitutions = VoluntaryQuotation::select('*')->where('parent_vq_id', 0)->where('year',$year)->whereNotIn('institution_id',$ignoredInstitutions)->where('is_deleted', 0)->get(); // Command by arunchandru 13122024
        $vqInstitutions = VoluntaryQuotation::select('*')->where('parent_vq_id', 0)->where('year',$year)->where('is_deleted', 0)->get();
        $ceiling_data_main = CeilingMaster::select('sku_id','discount_percent')->get();
        print_r("init ");
        foreach($vqInstitutions as $vq){
            //added for rev no fetch for reinitiate vq 05042024 
            /*$maxRevNo = DB::table('voluntary_quotation_sku_listing AS s')
            ->leftJoin('voluntary_quotation AS v', 'v.id', '=', 's.vq_id')
            ->where('v.year', $year)
            ->where('v.is_deleted', 0)
            ->where('v.institution_id', $vq->institution_id)
            ->lockForUpdate() // Add lock for update on 24062024
            ->max('v.rev_no');
            print_r('rev no'.$maxRevNo+1);*///commented on 26062024 due to concurrency job issue
            //added for rev no fetch for reinitiate vq 05042024 end
            $inst = VoluntaryQuotation::Create([
                'hospital_name' => $vq->hospital_name,
                'institution_id' => $vq->institution_id,
                'institution_key_account' => $vq->institution_key_account,
                'city' => $vq->city,
                'addr1' => $vq->addr1,
                'addr2' => $vq->addr2,
                'addr3' => $vq->addr3,
                'parent_vq_id' => $vq->id,
                'current_level' => "7",
                'stan_code' => $vq->stan_code,
                'pincode' => $vq->pincode,
                'current_level_start_date' => date('Y-m-d H:i:s'),
                'state_name' => $vq->state_name,
                'address' => $vq->address,
                'zone' => $vq->zone,
                'cfa_code' => $vq->cfa_code,
                'institution_zone' => $vq->institution_zone,
                'institution_region' => $vq->institution_region,
                //'contract_start_date' => $vq->contract_start_date,//commented on 09052024 to fetch current date
                'contract_start_date' => date('Y-m-d H:i:s'),//added on 09052024 for fetching current date
                'contract_end_date' => $vq->contract_end_date,
                'year' => $year,
                'sap_code' => $vq->sap_code,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                //'rev_no' => $maxRevNo + 1 //added on 05042024 to add rev no for reinitiate vqcommented on 26062024 due to concurrency job issue
            ]);
            print_r("vq created ".$vq->institution_id);
            // update revision number using vq_id after insert 26062024 starts
            $revision_no = DB::table('voluntary_quotation as vq1')
            ->where('vq1.parent_vq_id', $inst->parent_vq_id)
            ->where('vq1.id', '<=', $inst->id)
            ->count();
            $updation = VoluntaryQuotation::where('id', $inst->id)
            ->update(['rev_no' => $revision_no]);
            //print_r($updation.' '.$revision_no);
            // update revision number using vq_id after insert 26062024 ends
            $institutionDivMap = InstitutionDivisionMapping::where('institution_id', $vq->institution_id)->where('vq_id', $vq->id)->where('division_id',$this->newpack['DIVISION_CODE'])->get();
            // print_r(' count '.InstitutionDivisionMapping::where('institution_id', $vq->institution_id)->where('vq_id', $vq->id)->where('division_id',$this->newpack['DIVISION_CODE'])->count()); // command by arunchandru 13122024
            print_r(' count '.count($institutionDivMap->toArray()));
            foreach($institutionDivMap as $map){
                InstitutionDivisionMapping::Create([
                    'vq_id' => $inst->id,
                    'institution_id' => data_get($map, 'institution_id'),
                    'division_id' => data_get($map, 'division_id'),
                    'zone' => data_get($map, 'zone'),
                    'region' => data_get($map, 'region'),
                    'employee_code' => data_get($map, 'employee_code')
                ]);
            }
            print_r("mapping done ".$vq->institution_id);

            $headers = [
                'Content-Type' => 'application/json',
                'AccessToken' => 'key',
                'Authorization' => 'Bearer '.$this->jwt_code,
            ];
            $client = new GuzzleClient([
                'headers' => $headers,
                'verify' => false
            ]);
            $body = '{
                "INST_ID": "'.$vq->institution_id.'"
            }';
            $r = $client->request('POST', env('API_URL').'/api/Stockists', [
                'body' => $body
            ]);
            $response = $r->getBody()->getContents();
            $resp = json_decode($response);
            print_r("stockist api done ".$vq->institution_id);

            $body1 = '{
                "FIN_YEAR": "'.$year.'",
                "ITEM_CODE": "",
                "DIV_CODE": "",
                "INSTITUTE_CODE": "'.$vq->institution_id.'"
            }';
            $r = $client->request('GET', env('API_URL').'/API/PDMSData', [
                'body' => $body1
            ]);
            $response = $r->getBody()->getContents();
            $pdms_data = collect(json_decode($response));
            print_r("PDMSData api done ".$vq->institution_id);
            $vq_listing_controller->activityTracker($inst->id,$this->emp_code,"VQ Reinitiated (Add new pack) by ".$this->name.'/'.$this->division_name, 'reinitiate');
            
            $last_year_data = Stockist_master::where('institution_code',$vq->institution_id)->exists();
            if(!$last_year_data){
                $stock_cnt = 0;
                foreach($resp as $itm){
                    if($stock_cnt < 3){
                        $stock_flag = 1;
                    }else{
                        $stock_flag = 0;
                    }
                    $stock_cnt++;
                    $stock = Stockist_master::Create([
                        'institution_code' => $vq->institution_id,
                        'stockist_name' => $itm->STOCKIST_NAME,
                        'stockist_address' => $itm->STOCKIST_ADDRESS,
                        'email_id' => $itm->STOCKIST_EMAIL,
                        'stockist_code' => $itm->STOCKIST_CODE,
                        'stockist_type_flag' => $stock_flag,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }else{
                foreach($resp as $itm){
                    $upd = Stockist_master::updateOrCreate(['institution_code' => $vq->institution_id,'stockist_code' => $itm->STOCKIST_CODE ], [ 
                        'stockist_name' => $itm->STOCKIST_NAME,
                        'stockist_address' => $itm->STOCKIST_ADDRESS,
                        'email_id'=> $itm->STOCKIST_EMAIL,
                    ]);
                }
            }

            // Code for fetching the latest pack details for the sku from db starts here
            
            $vqIdArr = VoluntaryQuotation::where('id', $vq->id)->orWhere('parent_vq_id', $vq->id)->where('is_deleted', 0)->where('vq_status', 1)->select('id')->get();
            $vqIdArr = $vqIdArr->map(function($v){
                return $v->id;
            })->toArray();

            $oldPackDetails = VoluntaryQuotationSkuListing::whereIn('vq_id', $vqIdArr)->where('item_code', $this->oldpack['item_code'])->orderBy('id', 'DESC')->first();
            $oldpack = (isset($oldPackDetails->discount_percent))? 'Yes': 'No';
            print_r('Old Pack '.$oldpack);
            // Code for fetching the latest pack details for the sku from db ends here

            $listing_data = array();
            
            // $ceiling_percent = $oldPackDetails->discount_percent; // hide by arunchandru 03022025
            $ceiling_percent = (isset($oldPackDetails->discount_percent)) ? $oldPackDetails->discount_percent : '0'; // modify and set condition by arunchandru 03022025
            $oldPack_discount_percent = (isset($oldPackDetails->discount_percent)) ? $oldPackDetails->discount_percent : '0'; // added by arunchandru 03022025
            //$ceiling_rate = $this->newpack['PTR'] - ($this->newpack['PTR'] * ($oldPackDetails->last_year_percent/100));
            $ceiling_rate = $this->newpack['PTR'] - ($this->newpack['PTR'] * ($oldPack_discount_percent/100)); // modify and set condition by arunchandru 03022025
           
            /*  added by arunchandru 16042025 */
            if(is_null($oldPackDetails)):
                $recalcluate_ceiling_rate = ($this->newpack['PTR'] - ($this->newpack['PTR'] * ($ceiling_percent/100)));
                $recalcluate_mrp_margin = ((($this->newpack['MRP'] - $ceiling_rate)/$this->newpack['MRP'])*100);
            else:
                // if($this->newpack['PTR'] != $oldPackDetails->ptr):
                    $recalcluate_ceiling_rate = ($this->newpack['PTR'] - ($this->newpack['PTR'] * ($ceiling_percent/100)));
                // else:
                //     $recalcluate_ceiling_rate = ($oldPackDetails->ptr - ($oldPackDetails->ptr * ($ceiling_percent/100)));
                // endif;
                $recalcluate_mrp_margin = ((($this->newpack['MRP'] - $ceiling_rate)/$this->newpack['MRP'])*100);
            endif;

            $new_item_code = $this->newpack['ITEM_CODE'];
            $filtered_pdms = $pdms_data->filter(function ($item) use($new_item_code) {
                return $item->ITEM_CODE == $new_item_code;
            });
            print_r($this->newpack['ITEM_CODE']);
            $listing_data = [
                'vq_id' => $inst->id,
                'item_code' => $this->newpack['ITEM_CODE'],
                'brand_name' => $this->newpack['BRAND_NAME'],
                'mother_brand_name' => $this->newpack['MOTHER_BRAND_NAME'],
                'hsn_code' => (isset($this->newpack['HSN_CODE']))? $this->newpack['HSN_CODE'] : '',
                'applicable_gst' => $this->newpack['APPLICABLE_GST'],
                'composition' => $this->newpack['COMPOSITION'],
                'type' => $this->newpack['ITEM_TYPE'],
                'div_name' => $this->newpack['DIVISION_NAME'],
                'div_id' => $this->newpack['DIVISION_CODE'],
                'pack' => $this->newpack['PACK_SIZE'],
                'ptr' => $this->newpack['PTR'],
                /*'last_year_percent' => $oldPackDetails->last_year_percent,
                'last_year_rate' => $oldPackDetails->last_year_rate,
                'last_year_ptr' => $oldPackDetails->last_year_ptr,
                'last_year_mrp' => $oldPackDetails->last_year_mrp,*///commented on 29052024
                'discount_percent' => $ceiling_percent,
                'pdms_discount' => $filtered_pdms->isNotEmpty() ? $filtered_pdms->pluck('MAX_DISCOUNT')->first() : null,
                'discount_rate' => $recalcluate_ceiling_rate,
                'mrp' => $this->newpack['MRP'],
                // 'mrp_margin'=> ((($this->newpack['MRP'] - $ceiling_rate)/$this->newpack['MRP'])*100 ), // hide by arunchandru at 16042025
                'mrp_margin'=> $recalcluate_mrp_margin,
                'sap_itemcode' => $this->newpack['SAP_ITEMCODE'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $createdSku = VoluntaryQuotationSkuListing::Create($listing_data);
            print_r("sku created ".$vq->institution_id);
            
            // check for quotation status for inst and item starts added on 07052024
            //$checkQuotation = VoluntaryQuotation::select('vq_id')->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.institution_id',$vq->institution_id)->where('voluntary_quotation_sku_listing.item_code',$this->oldpack['item_code'])->where('year',$year)->where('voluntary_quotation.is_deleted',0)->where('vq_status', 0)->exists();
            if(isset($oldPackDetails->discount_percent)):
                $checkQuotation = VoluntaryQuotation::select('id')->where('id',$oldPackDetails->vq_id)->where('vq_status', 0)->exists();
                if($checkQuotation)
                {
                    print_r("vq_not_sent");
                    $updation = VoluntaryQuotation::where('id',$inst->id)->update(['vq_status'=>0]);//vq status update to 0 added on 07052024
                }
                else
                {
                    print_r("vq_sent");
                    // $vqslStockistData = VoluntaryQuotationSkuListingStockist::where('sku_id', $oldPackDetails->id)->get();//added the skulistingstockist table insertion if only vq sent for that item, institute, year
                    $vqslStockistData = VoluntaryQuotationSkuListingStockist::where('sku_id', $oldPackDetails->id)->where('is_deleted', 0)->get();//added the skulistingstockist table insertion if only vq sent for that item, institute, year
                
                    foreach($vqslStockistData as $data){
                        
                        $vqSkuStockListingData = [
                            'sku_id' => $createdSku->id,
                            'stockist_id' => $data->stockist_id,
                            'payment_mode' => $data->payment_mode,
                            'vq_id' => $inst->id,
                            'item_code' => $new_item_code,
                            'parent_vq_id' => $vq->id,
                            'net_discount_percent' => $data->net_discount_percent,
                            'revision_count' => $revision_no
                        ];
                        VoluntaryQuotationSkuListingStockist::Create($vqSkuStockListingData);
                    }

                    /* hide by arunchandru 20032025*/
                    // $updation = VoluntaryQuotation::where('id',$inst->id)->update(['vq_status'=>1]);//vq status update to 1 added on 16042024
                    ApproveVq::dispatch($inst->id, $this->jwt_code, 'Paymode');

                }
            else: // if old pack is not there vq status update 0 send quotation is pending
                print_r("Oldpack Missing VQstatus Pending");
                $updation = VoluntaryQuotation::where('id',$inst->id)->update(['vq_status'=>0]);//vq status update to 0 added on 03022025
            endif;

            print_r("dispacted ".$vq->institution_id);
            // check for quotation status for inst and item ends added on 07052024

        }
        return 0;
    }
}
