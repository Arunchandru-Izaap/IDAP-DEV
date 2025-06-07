<?php

namespace App\Jobs;
use App\Models\Institution;
use App\Models\CeilingMaster;
use App\Models\LastYearPrice;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;

use App\Models\ActivityTracker;
use App\Http\Controllers\Api\VqListingController;
use App\Models\InstitutionDivisionMapping;
use App\Models\Stockist_master;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;

use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Session;
class ReinitiateVQ implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $from;
    protected $to;
    protected $emp_code;
    protected $jwt_code;
    protected $institution_code;
    protected $sku_ids;

    protected $name;
    protected $division_name;
    protected $skip_approval;
    protected $selected_approval;

    public $timeout = 999999;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($from,$to,$emp_code,$jwt_code,$institution_code,$sku_ids,$name,$division_name, $skip_approval, $selected_approval)
    {
        //
        $this->from = $from;
        $this->to = $to;
        $this->emp_code = $emp_code;
        $this->jwt_code = $jwt_code;
        $this->institution_code = $institution_code;
        $this->sku_ids = $sku_ids;
        $this->name = $name;
        $this->division_name = $division_name;
        $this->skip_approval = $skip_approval;
        $this->selected_approval = $selected_approval;
        ini_set('max_execution_time', 18000);
        set_time_limit(0);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        set_time_limit(0);
        try 
        {
            $institutionCodeArr = json_decode($this->institution_code);
            $selected_approval = is_null(json_decode($this->selected_approval)) ? [] : json_decode($this->selected_approval);
            $fastforward_levels = "";
            foreach($selected_approval as $approval){
                $fastforward_levels = $fastforward_levels. $approval. ",";
            }
            $fastforward_levels = rtrim($fastforward_levels, ',');
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
                
            }';
            $startAPIProductTime = microtime(true);
            $r = $client->request('POST', env('API_URL').'/api/Products', [
                'body' => $body
            ]);
            $endAPIProductTime = microtime(true);
            Log::info('Product API Call took ' . ($endAPIProductTime - $startAPIProductTime) . ' seconds.');
            $response = $r->getBody()->getContents();
            
            // $data = json_decode($res->getBody());
            $productAPIdata = json_decode($response);
            // $byitemcode = $this->getProductByItemCode($productAPIdata, 'SSA4746');
            // print_r($byitemcode);die;
            foreach($institutionCodeArr as $institution_code){
                $startoverallTime = microtime(true);
                $sku_ids = $this->sku_ids;
                $this->checkSkuIsPending($sku_ids, $institution_code);
                if($sku_ids){
                    $vq_listing_controller = new VqListingController;
                    $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
                    $checkDiscountFlagEnabled = DB::table('check_discounted')->select('is_enabled')->where('year',$year)->where('is_enabled','Y')->get();// added for checking new item creation enabled 30052024
                    //$parent_vq=VoluntaryQuotation::where('institution_id',$institution_code)->where('year',$year)->where('parent_vq_id',0)->where('is_deleted', 0)->first();//commented for fetching only latest vq 15042024
                    //added for latest vq fetch start 15042024
                    $parent_vq=VoluntaryQuotation::query()
                    ->select('voluntary_quotation.*')
                    ->leftJoin('voluntary_quotation_sku_listing AS s', 'voluntary_quotation.id', '=', 's.vq_id')
                    ->where('voluntary_quotation.rev_no', function ($query) use ($year, $institution_code){
                        $query->select(DB::raw('MAX(v.rev_no)'))
                              ->from('voluntary_quotation AS v')
                              ->where('v.year', $year)
                              ->where('v.vq_status', 1)
                              ->where('v.institution_id',$institution_code)
                              ->where('v.is_deleted', 0);
                    })
                    ->where('institution_id',$institution_code)->where('year', $year)->where('vq_status', 1)->where('voluntary_quotation.is_deleted', 0)->first();//added to fetch the revisied vq and added year,vqstatus,isdeleted on 08052024
                    if (!$parent_vq) {//added on 17052024
                        $parent_vq = VoluntaryQuotation::where('institution_id',$institution_code)->where('year',$year)->where('parent_vq_id',0)->where('is_deleted', 0)->first();
                    }
                    if($parent_vq->rev_no == 0)
                    {
                        $parent_vq_id = $parent_vq->id;
                    }
                    else
                    {
                        $parent_vq_id = $parent_vq->parent_vq_id;
                    }
                    //added for latest vq fetch end 15042024
                    $phpdate1 = strtotime( str_replace('/', '-', $this->from) );
                    $start = date( 'Y-m-d H:i:s', $phpdate1 );
                    $phpdate2 = strtotime( str_replace('/', '-', $this->to) );
                    $end = date( 'Y-m-d H:i:s', $phpdate2 );
                    //added for rev no fetch for reinitiate vq 05042024 
                    /*$maxRevNo = DB::table('voluntary_quotation_sku_listing AS s')
                    ->leftJoin('voluntary_quotation AS v', 'v.id', '=', 's.vq_id')
                    ->where('v.year', $year)
                    ->where('v.is_deleted', 0)
                    ->where('v.institution_id', $institution_code)
                    ->max('v.rev_no');
                    print_r($maxRevNo+1);*///commented on 26062024 due to concurrency job issue
                    //added for rev no fetch for reinitiate vq 05042024 end
                    $create_child_arr = [
                        'hospital_name' => $parent_vq->hospital_name,
                        'institution_id' => $parent_vq->institution_id ,
                        'institution_key_account' => $parent_vq->institution_key_account,
                        'city' => $parent_vq->city,
                        'addr1'=> $parent_vq->addr1,
                        'addr2'=> $parent_vq->addr2,
                        'addr3'=> $parent_vq->addr3,
                        'current_level_start_date' => date('Y-m-d H:i:s'),
                        'stan_code'=>$parent_vq->stan_code,
                        'pincode'=>$parent_vq->pincode,
                        'state_name'=>$parent_vq->state_name,
                        'address' => $parent_vq->address,
                        'zone' => $parent_vq->zone,
                        'institution_zone' => $parent_vq->institution_zone,
                        'institution_region' => $parent_vq->institution_region,
                        'cfa_code' => $parent_vq->cfa_code,
                        'sap_code' => $parent_vq->sap_code,
                        'contract_start_date' => $start,
                        'contract_end_date' => $end,
                        'year' => $parent_vq->year,
                        'parent_vq_id' => $parent_vq_id,//changed from $parent_vq->id to parent_vq_id on 15042024
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                        //'rev_no' => $maxRevNo + 1 //added on 05042024 to add rev no for reinitiate vqcommented on 26062024 due to concurrency job issue
                    ];

                    if( $this->skip_approval && count($selected_approval) != 0 ){
                        $level_names = [
                            1 => 'RSM',
                            2 => 'ZSM',
                            3 => 'NSM',
                            4 => 'SBU',
                            5 => 'Semi Cluster',
                            6 => 'Cluster',
                        ];
                        $create_child_arr['current_level'] = min($selected_approval);  
                        $create_child_arr['fastforward_levels'] = $fastforward_levels; 
                        $mapped_levels = array_map(function($level) use ($level_names) {
                            return $level_names[$level];
                        }, $selected_approval);
                        $comma_separated_levels = implode(', ', $mapped_levels);
                        $activityTrackerAddlinfo = 'Add Product with Choose Approvals '.'- '.$comma_separated_levels; 
                    }
                    else
                    {
                        $activityTrackerAddlinfo = 'Add Product with Annual Approvals'; 
                    }
                    $startVQCreationStartTime = microtime(true);
                    $create_child = VoluntaryQuotation::Create($create_child_arr);
                    $startVQCreationEndTime = microtime(true);
                    Log::info('VQ table insertion took ' . ($startVQCreationEndTime - $startVQCreationStartTime) . ' seconds.');
                    print_r("vqcreated");
                    // update revision number using vq_id after insert 26062024 starts
                    $revision_no = DB::table('voluntary_quotation as vq1')
                    ->where('vq1.parent_vq_id', $create_child->parent_vq_id)
                    ->where('vq1.id', '<=', $create_child->id)
                    ->count();
                    $updation = VoluntaryQuotation::where('id', $create_child->id)
                    ->update(['rev_no' => $revision_no]);
                    //print_r($updation.' '.$revision_no);
                    // update revision number using vq_id after insert 26062024 ends
                    $startInstitutionDivisionMappingStartTime = microtime(true);
                    $institutionDivMap = InstitutionDivisionMapping::where('vq_id', $parent_vq->id)->distinct()->cursor();
                    print_r("mappingfound");
                    $institutionMappingData = [];
                    $batchSize = 1000;
                    $counter = 0;
                    foreach($institutionDivMap as $map){
                        /*InstitutionDivisionMapping::Create([
                            'vq_id' => $create_child->id,
                            'institution_id' => data_get($map, 'institution_id'),
                            'division_id' => data_get($map, 'division_id'),
                            'zone' => data_get($map, 'zone'),
                            'region' => data_get($map, 'region'),
                            'employee_code' => data_get($map, 'employee_code')
                        ]);*/
                        $institutionMappingData[] = [
                            'vq_id' => $create_child->id,
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
                    $startInstitutionDivisionMappingEndTime = microtime(true);
                    Log::info('InstitutionDivisionMapping table insertion took ' . ($startInstitutionDivisionMappingStartTime - $startVQCreationStartTime) . ' seconds.');
                    print_r("institution mapped");

                    // $vq_listing_controller->activityTracker($create_child->id,$this->emp_code,"VQ Re-Initiated".Session::put("emp_name",$emp_info->emp_name).'/'.Session::put("division_name",implode (",", $all_div_name)),'reinitiate');
                    
                    $headers = [
                        'Content-Type' => 'application/json',
                        'AccessToken' => 'key',
                        'Authorization' => 'Bearer '.$this->jwt_code,
                        ];
                    
                        $client = new GuzzleClient([
                            'headers' => $headers,
                            'verify' => false
                        ]);
                    $body1 = '{
                        "FIN_YEAR": "'.$year.'",
                        "ITEM_CODE": "",
                        "DIV_CODE": "",
                        "INSTITUTE_CODE": "'.$parent_vq->institution_id.'"
                    }';
                    $startPDMSAPICall = microtime(true);
                    $r = $client->request('GET', env('API_URL').'/API/PDMSData', [
                        'body' => $body1
                    ]);
                    $endPDMSAPICall = microtime(true);
                    Log::info('PDMS API Call took ' . ($endPDMSAPICall - $startPDMSAPICall) . ' seconds.');
                    $response = $r->getBody()->getContents();
                    $pdms_data = collect(json_decode($response));

                    print_r("pdms res found");

                    $listing_data=[];
                    $parent_vq_sku=VoluntaryQuotationSkuListing::where('vq_id',$parent_vq->id)->where('is_deleted', 0)->get();
                    $checkProductAll = DB::table('voluntary_quotation_sku_listing')->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
                        ->where('year',$year)->where('voluntary_quotation.is_deleted',0)->where('voluntary_quotation.institution_id',$institution_code)->select('voluntary_quotation_sku_listing.item_code')->get();
                    $checkProductAll = collect($checkProductAll);
                    $product_status = ' - Existing Product'; 
                    foreach($parent_vq_sku as $single_data){
                        if(count($sku_ids) == 0){
                            break;
                        }
                        if(in_array($single_data->item_code, $sku_ids)){
                            $startFilterTime = microtime(true);
                            /*$filtered_pdms = $pdms_data->filter(function ($item) use($single_data) {
                                return $item->ITEM_CODE == $single_data->item_code;
                            });*/
                            $filtered_pdms = $pdms_data->where('ITEM_CODE', $single_data->item_code);
                            $endFilterTime = microtime(true);
                            //Log::info('Filtering ITEM_CODE from pdms' . $single_data->item_code . ' took ' . ($endFilterTime - $startFilterTime) . ' seconds.');
                            //added to check new or old product starts here 22072024
                            /*$checkProduct_type = DB::table('voluntary_quotation_sku_listing')->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
                                ->where('year',$year)->where('item_code',$single_data->item_code)->where('voluntary_quotation_sku_listing.is_deleted',0)->where('voluntary_quotation.is_deleted',0)->where('voluntary_quotation.institution_id',$institution_code)->exists();*/
                            $checkProduct_type = $checkProductAll->contains('item_code', $single_data->item_code);
                            if($checkProduct_type)
                            {
                                $product_type = 'old';
                            }
                            else
                            {
                                $product_type = 'new';
                            }

                            /*  added by arunchandru 16042025 */
                            $byitemcode = $this->getProductByItemCode($productAPIdata, $single_data->item_code);
                            if(!empty($byitemcode)):
                                $product_api_ptr = $byitemcode->PTR;
                                $product_api_mrp = $byitemcode->MRP;
                                if($byitemcode->PTR != $single_data->ptr):
                                    $ceiling_rate_dic_rate = ($product_api_ptr - ($product_api_ptr * ($single_data->discount_percent/100)));
                                else:
                                    $ceiling_rate_dic_rate = ($single_data->ptr - ($single_data->ptr * ($single_data->discount_percent/100)));
                                endif;
                                $mrp_margin_val = ((($product_api_mrp - $ceiling_rate_dic_rate)/$product_api_mrp)*100 );
                            else:
                                $product_api_ptr = $single_data->ptr;
                                $product_api_mrp = $single_data->mrp;
                                $ceiling_rate_dic_rate = ($product_api_ptr - ($product_api_ptr * ($single_data->discount_percent/100)));
                                $mrp_margin_val = ((($product_api_mrp - $ceiling_rate_dic_rate)/$product_api_mrp)*100);
                            endif;


                            //added to check new or old product ends here 22072024
                            $listing_data[]=[
                                'vq_id' =>$create_child->id,
                                'item_code' => $single_data->item_code,
                                'brand_name' => $single_data->brand_name,
                                'mother_brand_name' => $single_data->mother_brand_name,
                                'hsn_code' => $single_data->hsn_code,
                                'applicable_gst' => $single_data->applicable_gst,
                                'composition' => '"'.$single_data->composition.'"',
                                'type' => $single_data->type,
                                'div_name' => $single_data->div_name,
                                'div_id' => $single_data->div_id,
                                'pack' => $single_data->pack,
                                'ptr' => $product_api_ptr, // $single_data->ptr,
                                'last_year_percent' => $single_data->last_year_percent,
                                'last_year_rate' => $single_data->last_year_rate,
                                'last_year_ptr' => $single_data->last_year_ptr,
                                'discount_percent' => $single_data->discount_percent,
                                'discount_rate' => $single_data->discount_rate,
                                'pdms_discount' => $filtered_pdms->isNotEmpty() ? $filtered_pdms->pluck('MAX_DISCOUNT')->first() : null,
                                'sap_itemcode' => $single_data->sap_itemcode,
                                'mrp' => $product_api_mrp, // $single_data->mrp,
                                'last_year_mrp' => $single_data->last_year_mrp,
                                'mrp_margin'=> $mrp_margin_val, //$single_data->mrp_margin,                
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                                'product_type'=>$product_type //aded on 22072024
                            ];
                            if (($key = array_search($single_data->item_code, $sku_ids)) !== false) {
                                unset($sku_ids[$key]);
                            }
                            print_r($single_data->item_code.' ');
                            // update exception list if new item revision is enabled starts
                            if ($checkDiscountFlagEnabled->count() != 0) {
                                $update_exception_item = DB::table('exception_sku_list_reinitiate')
                                ->where('item_code', $single_data->item_code)
                                ->where('div_id', $single_data->div_id)
                                ->where('year', $year)
                                ->update(['is_deleted' => 1]);
                            }
                            // update exception list if new item revision is enabled ends
                            if ($product_type === 'new') {
                                $product_status = ' - New Product -- '.$single_data->item_code.' '.$single_data->brand_name;
                            }
                        }
                    }
                    print_r("init also working");


                    if(count($sku_ids) != 0){
                        print_r("pending also working");
                        print('Count - '.count($sku_ids));

                        $time_minus = strtotime("-1 year", time());
                        $date_minus = date("Y-m-d", $time_minus);
                        $last_year = $vq_listing_controller->getFinancialYear($date_minus,"Y");
                        
                        $starttime = microtime(true);
                        $maxRevSubquery = DB::table('voluntary_quotation_sku_listing as s')
                        ->select(DB::raw('MAX(v2.rev_no) AS max_rev_no'), 's.item_code', 'v2.institution_id')
                        ->leftJoin('voluntary_quotation as v2', 'v2.id', '=', 's.vq_id')
                        ->where('v2.year', $year)
                        ->where('s.is_deleted', 0)
                        ->where('v2.vq_status', 1)
                        ->where('v2.is_deleted', 0)
                        ->where('v2.institution_id', $institution_code)
                        ->groupBy('s.item_code');

                        $latestdata = DB::table('voluntary_quotation_sku_listing as vqsl')
                        ->select('vqsl.*', 'vq.*')
                        ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
                        ->joinSub($maxRevSubquery, 'max_rev', function ($join) use($institution_code) {
                            $join->on('vqsl.item_code', '=', 'max_rev.item_code')
                                ->where('vq.institution_id',  $institution_code)
                                ->on('vq.rev_no', '=', 'max_rev.max_rev_no');
                        })
                        ->where('vq.institution_id',  $institution_code)
                        ->where('vq.year', $year)
                        ->where('vq.vq_status', 1)
                        ->where('vq.is_deleted', 0)
                        ->where('vqsl.is_deleted', 0)
                        ->get();

                        $latestdata = collect($latestdata);

                        $endtime = microtime(true);
                        Log::info('Latest revised record  fetching all ' . ($endtime - $starttime) . ' seconds.');
                        $starttime = microtime(true);
                        $lastYearDataAll = LastYearPrice::where('institution_id',$institution_code)->where('year',$last_year)->get();

                        $lastYearDataAll = collect($lastYearDataAll);

                        $endtime = microtime(true);
                        Log::info('Last year record  fetching all ' . ($endtime - $starttime) . ' seconds.');
                        foreach($productAPIdata as $single_data){
                            if(count($sku_ids) == 0){
                                break;
                            }
                            if(in_array($single_data->ITEM_CODE, $sku_ids)){
                                /*code to fetch lastyear data starts*/
                                $startlatestitemratetime = microtime(true);
                                $time_minus = strtotime("-1 year", time());
                                $date_minus = date("Y-m-d", $time_minus);
                                $last_year = $vq_listing_controller->getFinancialYear($date_minus,"Y");
                                //$last_year_data = LastYearPrice::where('institution_id',$institution_code)->where('division_id',$single_data->DIVISION_CODE)->where('year',$last_year)->where('sku_id',$single_data->ITEM_CODE)->first();
                                $last_year_data = $lastYearDataAll->where('division_id',$single_data->DIVISION_CODE)->where('sku_id',$single_data->ITEM_CODE)->first();

                                //$last_year_data = LastYearPrice::where('sku_id',$single_data->ITEM_CODE)->where('institution_id',$institution_code)->where('division_id',$single_data->DIVISION_CODE)->where('year',$last_year)->first();//commented for latest vq itemcode discount ptr fetch 20042024
                                /*code to fetch lastyear data ends*/
                                $single_data_item_code = $single_data->ITEM_CODE;
                                /*$old_data = VoluntaryQuotationSkuListing::select('ptr','mrp','discount_percent','discount_rate','mrp_margin')->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.institution_id',$institution_code)->where('voluntary_quotation_sku_listing.item_code',$single_data->ITEM_CODE)->where('year',$year)*/
                                    /*->where('voluntary_quotation.rev_no', function ($query) use ($year, $institution_code, $single_data_item_code){
                                    $query->select(DB::raw('MAX(v.rev_no)'))
                                          ->from('voluntary_quotation AS v')
                                          ->leftJoin('voluntary_quotation_sku_listing AS s', 'v.id', '=', 's.vq_id')
                                          ->where('v.year', $year)
                                          ->where('v.vq_status', 1)
                                          ->where('v.institution_id',$institution_code)
                                          ->where('s.item_code',$single_data_item_code)
                                          ->where('v.is_deleted', 0);//added on 20042024 for latest discount ptr fetch
                                    })*/
                                    /*->join('z_max_rev AS max_rev', function ($join) {
                                        $join->on('max_rev.item_code', '=', 'voluntary_quotation_sku_listing.item_code')
                                             ->on('max_rev.institution_id', '=', 'voluntary_quotation.institution_id')
                                             ->on('max_rev.max_rev_no', '=', 'voluntary_quotation.rev_no');
                                    })
                                ->first();*/
                                $old_data =  $latestdata->firstWhere('item_code', $single_data_item_code);

                                /*code to fetch latest revision item and last year data starts*/
                                /*if(is_null($old_data)){
                                    $ceiling_percent = 0;
                                    $ceiling_rate = $single_data->PTR;
                                    $mrp_margin = ((($single_data->MRP -$single_data->PTR)/$single_data->MRP)*100 );
                                }
                                else
                                {
                                    $ceiling_percent = $old_data['discount_percent'];
                                    $ceiling_rate = $old_data['discount_rate'];
                                    $mrp_margin = $old_data['mrp_margin'];
                                }*/
                                if (is_null($old_data)) {
                                    $ceiling_percent = 0;
                                    // $ceiling_rate = $single_data->PTR; // hide by arunchandru 10042025
                                    // $mrp_margin = (($single_data->MRP - $single_data->PTR) / $single_data->MRP) * 100; // hide by arunchandru 10042025
                                    
                                    $ceiling_rate = ($single_data->PTR - ($single_data->PTR * ($ceiling_percent/100))); /*  added by arunchandru 16042025 */
                                    $mrp_margin = ((($single_data->MRP - $ceiling_rate)/$single_data->MRP)*100); /*  added by arunchandru 16042025 */
                                } else {
                                    // Ensure fields exist in $old_data to avoid undefined index errors
                                    $ceiling_percent = $old_data->discount_percent ?? 0; // Default to 0 if not found
                                    // $ceiling_rate = $old_data->discount_rate ?? 0; // Default to 0 if not found // hide by arunchandru 10042025
                                    // $mrp_margin = $old_data->mrp_margin ?? 0; // Default to 0 if not found // hide by arunchandru 10042025
                                    if($single_data->PTR != $old_data->ptr): /*  added by arunchandru 16042025 */
                                        $ceiling_rate = ($single_data->PTR - ($single_data->PTR * ($ceiling_percent/100)));
                                    else:
                                        $ceiling_rate = ($old_data->ptr - ($old_data->ptr * ($ceiling_percent/100)));
                                    endif;
                                    $mrp_margin = ((($single_data->MRP - $ceiling_rate)/$single_data->MRP)*100);
                                }
                                if(!is_null($last_year_data)){
                                    $last_year_percent = $last_year_data['discount_percent'];
                                    $mid = ($single_data->PTR / 100) * $last_year_percent;
                                    $last_year_rate = $single_data->PTR - $mid;
                                    $last_year_mrp = $last_year_data['mrp'];
                                    $last_year_ptr = $last_year_data['ptr'];//added on 17052024 
                                }else{
                                    $last_year_percent = NULL;
                                    $last_year_rate = NULL;
                                    $last_year_mrp = NULL;
                                    $last_year_ptr = NULL;//added on 17052024
                                }
                                $endlatestitemratetime = microtime(true);
                                //Log::info('latest revision rate time ' . ($endlatestitemratetime - $startlatestitemratetime) . ' seconds.');
                                /*code to fetch latest revision item and last year data ends*/
                                $starttime = microtime(true);
                                /*$filtered_pdms = $pdms_data->filter(function ($item) use($single_data) {
                                    return $item->ITEM_CODE == $single_data->ITEM_CODE;
                                });*/
                                $filtered_pdms = $pdms_data->where('ITEM_CODE', $single_data->ITEM_CODE);
                                $endtime = microtime(true);
                                //Log::info('pdms filtering time ' . ($endlatestitemratetime - $startlatestitemratetime) . ' seconds.');
                                //added to check new or old product starts here 22072024
                                /*$checkProduct_type = DB::table('voluntary_quotation_sku_listing')->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
                                    ->where('year',$year)->where('item_code',$single_data->ITEM_CODE)->where('voluntary_quotation_sku_listing.is_deleted',0)->where('voluntary_quotation.is_deleted',0)->where('voluntary_quotation.institution_id',$institution_code)->exists();*/
                                $checkProduct_type = $checkProductAll->contains('item_code', $single_data->ITEM_CODE);

                                if($checkProduct_type)
                                {
                                    $product_type = 'old';
                                }
                                else
                                {
                                    $product_type = 'new';
                                }
                                //added to check new or old product ends here 22072024
                                $listing_data[]=[
                                    'vq_id' =>$create_child->id,
                                    'item_code' => $single_data->ITEM_CODE,
                                    'brand_name' => $single_data->BRAND_NAME,
                                    'mother_brand_name' => $single_data->MOTHER_BRAND_NAME,
                                    'hsn_code' => $single_data->HSN_CODE,
                                    'applicable_gst' => $single_data->APPLICABLE_GST,
                                    'composition' => $single_data->COMPOSITION,
                                    'type' => $single_data->ITEM_TYPE,
                                    'div_name' => $single_data->DIVISION_NAME,
                                    'div_id' => $single_data->DIVISION_CODE,
                                    'pack' => $single_data->PACK_SIZE,
                                    'ptr' => $single_data->PTR,
                                    'last_year_percent' => $last_year_percent,
                                    'last_year_rate' => $last_year_rate,
                                    'discount_percent' => $ceiling_percent,
                                    'discount_rate' => $ceiling_rate,
                                    'mrp' => $single_data->MRP,
                                    'last_year_mrp' => $last_year_mrp,
                                    //'mrp_margin'=>((($single_data->MRP -$single_data->PTR)/$single_data->MRP)*100 ),//commented on 20042024
                                    'mrp_margin'=>$mrp_margin,//added on 20042023
                                    'pdms_discount' => $filtered_pdms->isNotEmpty() ? $filtered_pdms->pluck('MAX_DISCOUNT')->first() : null,
                                    'sap_itemcode' => $single_data->SAP_ITEMCODE,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                    'last_year_ptr' => $last_year_ptr,//added on 17052024
                                    'product_type'=>$product_type //aded on 22072024
                                ];
                                if (($key = array_search($single_data->ITEM_CODE, $sku_ids)) !== false) {
                                    unset($sku_ids[$key]);
                                }
                                // update exception list if new item revision is enabled starts
                                if ($checkDiscountFlagEnabled->count() != 0) {
                                    $update_exception_item = DB::table('exception_sku_list_reinitiate')
                                    ->where('item_code', $single_data->ITEM_CODE)
                                    ->where('div_id', $single_data->DIVISION_CODE)
                                    ->where('year', $year)
                                    ->update(['is_deleted' => 1]);
                                }
                                // update exception list if new item revision is enabled ends
                                if ($product_type === 'new') {
                                    $product_status = ' - New Product -- '.$single_data->ITEM_CODE.' '.$single_data->BRAND_NAME;
                                }
                            }
                        }
                    }
                    $starttime = microtime(true);
                    foreach (array_chunk($listing_data,100) as $t)  
                    {
                        DB::table('voluntary_quotation_sku_listing')->insert($t); 
                    }
                    $endtime = microtime(true);
                    Log::info('vqsl insertion time ' . ($endtime - $starttime) . ' seconds.');
                    $vq_listing_controller->activityTracker($create_child->id,$this->emp_code,"VQ Re-Initiated (".$activityTrackerAddlinfo.$product_status.") by ".$this->name."/".$this->division_name, 'reinitiate');
                }
                $endoverallTime = microtime(true);
                Log::info('Total Job Processing Time for institution_id: '.$institution_code .' '. ($endoverallTime - $startoverallTime) . ' seconds.'); 
            }
            Log::info('Job processed successfully.');
        }catch (Exception $e) {
            // Handle the exception, log error, and perform any necessary actions
            Log::error('Error processing job: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }

    public function checkSkuIsPending(&$sku_ids, $institution_code){
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $vqs = VoluntaryQuotation::where('institution_id',$institution_code)->where('year',$year)->where('vq_status',0)->where('is_deleted', 0)->get();
        foreach($sku_ids as $k => $sku_code){
            
            foreach($vqs as $vq){
                $sku = VoluntaryQuotationSkuListing::where('item_code', $sku_code)->where('vq_id', $vq->id)->where('is_deleted', 0)->get();

                if(count($sku) > 0){
                    unset($sku_ids[$k]);
                    break;
                }
            }
        }
        
    }

    /*  added by arunchandru 16042025 */
    function getProductByItemCode($productAPIdata, $item_code) {
        $filtered = array_filter($productAPIdata, function($obj) use ($item_code) {
            return isset($obj->ITEM_CODE) && $obj->ITEM_CODE === $item_code;
        });
        
        return reset($filtered); // returns the first matched object or false if not found
    }

}
