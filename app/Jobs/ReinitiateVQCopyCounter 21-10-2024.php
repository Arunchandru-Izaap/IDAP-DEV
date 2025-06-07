<?php

namespace App\Jobs;
use App\Models\Institution;
use App\Models\CeilingMaster;
use App\Models\LastYearPrice;
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
use App\Models\PocMaster;
use Session;

use function Ramsey\Uuid\v1;

class ReinitiateVQCopyCounter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $fromcounter;
    protected $tocounter;
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
    public function __construct($fromcounter,$tocounter,$emp_code,$jwt_code,$name,$division_name)
    {
        //
        $this->fromcounter = $fromcounter;
        $this->tocounter = $tocounter;
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

        
        //$fromInstitution = VoluntaryQuotation::select('*')->where('parent_vq_id', 0)->where('year',$year)->where('is_deleted', 0)->where('institution_id',$this->fromcounter)->get();
        $toCounters = $this->tocounter;
        foreach($toCounters as $tocounter)
        {
            $toInstitution = VoluntaryQuotation::select('*')->where('parent_vq_id', 0)->where('year',$year)->where('is_deleted', 0)->where('institution_id',$tocounter)->first();
            print_r("init ");
            $inst = VoluntaryQuotation::Create([
                'hospital_name' => $toInstitution->hospital_name,
                'institution_id' => $toInstitution->institution_id,
                'institution_key_account' => $toInstitution->institution_key_account,
                'city' => $toInstitution->city,
                'addr1' => $toInstitution->addr1,
                'addr2' => $toInstitution->addr2,
                'addr3' => $toInstitution->addr3,
                'parent_vq_id' => $toInstitution->id,
                'current_level' => "7",
                'stan_code' => $toInstitution->stan_code,
                'pincode' => $toInstitution->pincode,
                'current_level_start_date' => date('Y-m-d H:i:s'),
                'state_name' => $toInstitution->state_name,
                'address' => $toInstitution->address,
                'zone' => $toInstitution->zone,
                'cfa_code' => $toInstitution->cfa_code,
                'institution_zone' => $toInstitution->institution_zone,
                'institution_region' => $toInstitution->institution_region,
                //'contract_start_date' => $toInstitution->contract_start_date,//commented on 09052024 to fetch current date
                'contract_start_date' => date('Y-m-d H:i:s'),//added on 09052024 for fetching current date
                'contract_end_date' => $toInstitution->contract_end_date,
                'year' => $year,
                'sap_code' => $toInstitution->sap_code,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'copy_counter' => 1
            ]);
            print_r("vq created ".$toInstitution->institution_id);
            // update revision number using vq_id after insert 26062024 starts
            $revision_no = DB::table('voluntary_quotation as vq1')
            ->where('vq1.parent_vq_id', $inst->parent_vq_id)
            ->where('vq1.id', '<=', $inst->id)
            ->count();
            $updation = VoluntaryQuotation::where('id', $inst->id)
            ->update(['rev_no' => $revision_no]);
            //print_r($updation.' '.$revision_no);
            // update revision number using vq_id after insert 26062024 ends
            $institutionDivMap = InstitutionDivisionMapping::where('vq_id', $toInstitution->id)->distinct()->cursor();
            print_r("mappingfound");
            $institutionMappingData = [];
            $batchSize = 1000;
            $counter = 0;
            foreach($institutionDivMap as $map){
                /*InstitutionDivisionMapping::Create([
                    'vq_id' => $inst->id,
                    'institution_id' => data_get($map, 'institution_id'),
                    'division_id' => data_get($map, 'division_id'),
                    'zone' => data_get($map, 'zone'),
                    'region' => data_get($map, 'region'),
                    'employee_code' => data_get($map, 'employee_code')
                ]);*/
                $institutionMappingData[] = [
                    'vq_id' => $inst->id,
                    'institution_id' => data_get($map, 'institution_id'),
                    'division_id' => data_get($map, 'division_id'),
                    'zone' => data_get($map, 'zone'),
                    'region' => data_get($map, 'region'),
                    'employee_code' => data_get($map, 'employee_code'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                // Insert in batches
                if (++$counter % $batchSize == 0) {
                    InstitutionDivisionMapping::insert($institutionMappingData);
                    $institutionMappingData = []; // Reset array for next batch
                }
            }
            if (!empty($institutionMappingData)) {
                InstitutionDivisionMapping::insert($institutionMappingData);
            }
            print_r("institution mapped");
            print_r("mapping done ".$toInstitution->institution_id);

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
                "INST_ID": "'.$toInstitution->institution_id.'"
            }';
            $r = $client->request('POST', env('API_URL').'/api/Stockists', [
                'body' => $body
            ]);
            $response = $r->getBody()->getContents();
            $resp = json_decode($response);
            print_r("stockist api done ".$toInstitution->institution_id);

            $body1 = '{
                "FIN_YEAR": "'.$year.'",
                "ITEM_CODE": "",
                "DIV_CODE": "",
                "INSTITUTE_CODE": "'.$toInstitution->institution_id.'"
            }';
            $r = $client->request('GET', env('API_URL').'/API/PDMSData', [
                'body' => $body1
            ]);
            $response = $r->getBody()->getContents();
            $pdms_data = collect(json_decode($response));
            print_r("PDMSData api done ".$toInstitution->institution_id);
            $vq_listing_controller->activityTracker($inst->id,$this->emp_code,"VQ Re-Initiated (Copy counter) by ".$this->name.'/'.$this->division_name, 'reinitiate');
            
            $last_year_data = Stockist_master::where('institution_code',$toInstitution->institution_id)->exists();
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
                        'institution_code' => $toInstitution->institution_id,
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
                    $upd = Stockist_master::updateOrCreate(['institution_code' => $toInstitution->institution_id,'stockist_code' => $itm->STOCKIST_CODE ], [ 
                        'stockist_name' => $itm->STOCKIST_NAME,
                        'stockist_address' => $itm->STOCKIST_ADDRESS,
                        'email_id'=> $itm->STOCKIST_EMAIL,
                    ]);
                }
            }
            $toInstitutionPocChecker = PocMaster::where('institution_id', $toInstitution->institution_id)->exists();
            if(!$toInstitutionPocChecker)
            {
                $pocMasterFromInst = PocMaster::where('institution_id', $this->fromcounter)->first();
                if ($pocMasterFromInst) {
                    $newRecord = new PocMaster();
                    $newRecord->institution_id = $toInstitution->institution_id;
                    $newRecord->institution_name = $toInstitution->hospital_name;
                    $newRecord->city = $pocMasterFromInst->city;
                    $newRecord->fsm_code = $pocMasterFromInst->fsm_code;
                    $newRecord->fsm_name = $pocMasterFromInst->fsm_name;
                    $newRecord->fsm_number = $pocMasterFromInst->fsm_number;
                    $newRecord->fsm_email = $pocMasterFromInst->fsm_email;
                    $newRecord->fsm_ho = $pocMasterFromInst->fsm_ho;
                    $newRecord->rsm_code = $pocMasterFromInst->rsm_code;
                    $newRecord->rsm_name = $pocMasterFromInst->rsm_name;
                    $newRecord->rsm_number = $pocMasterFromInst->rsm_number;
                    $newRecord->rsm_email = $pocMasterFromInst->rsm_email;
                    $newRecord->rsm_ho = $pocMasterFromInst->rsm_ho;
                    $newRecord->zsm_code = $pocMasterFromInst->zsm_code;
                    $newRecord->zsm_name = $pocMasterFromInst->zsm_name;
                    $newRecord->zsm_number = $pocMasterFromInst->zsm_number;
                    $newRecord->zsm_email = $pocMasterFromInst->zsm_email;
                    $newRecord->zsm_ho = $pocMasterFromInst->zsm_ho;

                    $newRecord->save();
                } else {
                    
                }
            }
            // Code for fetching the latest item details for the from counter from db starts here
            
            $maxRevSubquery = DB::table('voluntary_quotation_sku_listing as s')
            ->select(DB::raw('MAX(v2.rev_no) AS max_rev_no'), 's.item_code', 'v2.institution_id')
            ->leftJoin('voluntary_quotation as v2', 'v2.id', '=', 's.vq_id')
            ->where('v2.year', $year)
            ->where('s.is_deleted', 0)
            ->where('v2.vq_status', 1)
            ->where('v2.is_deleted', 0)
            ->where('v2.institution_id', $this->fromcounter)
            ->groupBy('s.item_code');

            $data = DB::table('voluntary_quotation_sku_listing as vqsl')
            ->select('vqsl.*', 'vq.*')
            ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
            ->joinSub($maxRevSubquery, 'max_rev', function ($join) {
                $join->on('vqsl.item_code', '=', 'max_rev.item_code')
                    ->where('vq.institution_id', $this->fromcounter)
                    ->on('vq.rev_no', '=', 'max_rev.max_rev_no');
            })
            ->where('vq.institution_id', $this->fromcounter)
            ->where('vq.year', $year)
            ->where('vq.vq_status', 1)
            ->where('vq.is_deleted', 0)
            ->where('vqsl.is_deleted', 0)
            ->get();

            // Code for fetching the latest item details for the from counter from db ends here
            $listing_data = array();
            
            foreach($data as $single_data){
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
        return 0;
    }
}
