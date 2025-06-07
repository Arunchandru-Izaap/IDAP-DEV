<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client as GuzzleClient;
use DB;
use App\Models\JwtToken;
use App\Models\InstitutionDivisionMapping;
use App\Models\IgnoredInstitutions;
use App\Models\VoluntaryQuotation;
use App\Http\Controllers\Api\VqListingController;
use App\Models\Stockist_master;
use DateTime; 

class updateChainHospital extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateChainHospital:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $jwt = JwtToken::whereNotNull('jwt_token')->where('jwt_token','!=','')->orderBy('updated_at', 'desc')->first();
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $date = new DateTime();

        $year1 = $date->format('Y');
        $month = $date->format('m');

        if ($month < 4) {
            $year1 -= 1;
        }

        $financialYearEndDate = new DateTime(($year1 + 1) . '-03-31 00:00:00');
        if (!$jwt) {
            $this->error('JWT token not found');
            return;
        }

        $headers = [
            'Content-Type' => 'application/json',
            'AccessToken' => 'key',
            'Authorization' => 'Bearer ' . $jwt->jwt_token,
        ];

        $client = new GuzzleClient([
            'headers' => $headers,
            'verify' => false,
        ]);

        $body = '{}';
        $r = $client->request('POST', env('API_URL') . '/api/InstitutionOnly', [
            'body' => $body,
        ]);

        $response = $r->getBody()->getContents();

        
        $response = json_decode($response, true);
        $resp_collection = collect($response);
        
        
        $initiator_level_parent_vqs = VoluntaryQuotation::select('voluntary_quotation.*','ignored_institutions.institution_id as ignored_institution','ignored_institutions.parent_institution_id')->leftJoin('ignored_institutions','voluntary_quotation.institution_id','=','ignored_institutions.parent_institution_id')->where('current_level',7)->where('vq_status',0)->whereNotNull('ignored_institutions.institution_id')->where('is_deleted', 0)->where('year', $year)->get();
        $vq_created = date('Y-m-d H:i:s');
        foreach ($initiator_level_parent_vqs as $single_initiator_level_parent_vq) {
            DB::beginTransaction();
            try 
            {
                $parent_institution_id = $single_initiator_level_parent_vq['institution_id'];
                $chain_hospital_institution_id = $single_initiator_level_parent_vq['ignored_institution'];
                $checkChildInst = VoluntaryQuotation::where('institution_id',$chain_hospital_institution_id)->where('year', $year)->where('is_deleted', 0)->exists();
                if(!$checkChildInst)
                {
                    $chain_hospital_institution = $resp_collection->where('INST_ID', $chain_hospital_institution_id)->first();
                    $maxRevSubquery = DB::table('voluntary_quotation_sku_listing as s')
                    ->select(DB::raw('MAX(v2.rev_no) AS max_rev_no'), 's.item_code', 'v2.institution_id')
                    ->leftJoin('voluntary_quotation as v2', 'v2.id', '=', 's.vq_id')
                    ->where('v2.year', $year)
                    ->where('s.is_deleted', 0)
                    ->where('v2.vq_status', 1)
                    ->where('v2.is_deleted', 0)
                    ->where('v2.institution_id', $parent_institution_id)
                    ->groupBy('s.item_code');

                    $data = DB::table('voluntary_quotation_sku_listing as vqsl')
                    ->select('vqsl.*', 'vq.*')
                    ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
                    ->joinSub($maxRevSubquery, 'max_rev', function ($join) use($parent_institution_id) {
                        $join->on('vqsl.item_code', '=', 'max_rev.item_code')
                            ->where('vq.institution_id',  $parent_institution_id)
                            ->on('vq.rev_no', '=', 'max_rev.max_rev_no');
                    })
                    ->where('vq.institution_id',  $parent_institution_id)
                    ->where('vq.year', $year)
                    ->where('vq.vq_status', 1)
                    ->where('vq.is_deleted', 0)
                    ->where('vqsl.is_deleted', 0)
                    ->get();
                    $inst = VoluntaryQuotation::Create([
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
                        'contract_start_date' => date('Y-m-d H:i:s'),
                        'contract_end_date' => $financialYearEndDate->format('Y-m-d H:i:s'),
                        'year' => $year,
                        'sap_code' => $chain_hospital_institution['SAP_CODE'],
                        'institution_zone' => isset($chain_hospital_institution['LSTZONEMAPPING'][0]['ZSM_ZONE']) ? $chain_hospital_institution['LSTZONEMAPPING'][0]['ZSM_ZONE'] : '',
                        'institution_region' => isset($chain_hospital_institution['LSTZONEMAPPING'][0]['RSM_REGION']) ? $chain_hospital_institution['LSTZONEMAPPING'][0]['RSM_REGION'] : '',
                        'created_at' => $vq_created,
                        'updated_at' => $vq_created,
                        'rev_no' =>0
                    ]);
                    $vq_listing_controller->activityTracker($inst->id,'','Chain Hospital VQ Initiated by Parent VQ '.$parent_institution_id, 'chain_hospital_revision');
                    if(count($data) == 0)
                    {
                        $this->info('rev table empty');
                        return 1;
                    }
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
                else
                {
                    $check_pending_chain_hospital = VoluntaryQuotation::where('institution_id', $chain_hospital_institution_id)->where('is_deleted', 0)->where('vq_status', 0)->where('year', $year)->exists();

                    $check_pending_chain_hospital = false;
                    if($check_pending_chain_hospital)
                    {

                    }
                    else
                    {

                        $chain_hospital_institution = VoluntaryQuotation::where('institution_id', $chain_hospital_institution_id)->where('is_deleted', 0)->where('year', $year)->where('parent_vq_id', 0)->first();
                        $inst = VoluntaryQuotation::Create([
                            'hospital_name' => $chain_hospital_institution->hospital_name,
                            'institution_id' => $chain_hospital_institution->institution_id,
                            'institution_key_account' => $chain_hospital_institution->institution_key_account,
                            'city' => $chain_hospital_institution->city,
                            'addr1' => $chain_hospital_institution->addr1,
                            'addr2' => $chain_hospital_institution->addr2,
                            'addr3' => $chain_hospital_institution->addr3,
                            'parent_vq_id' => $chain_hospital_institution->id,
                            'current_level' => "7",
                            'stan_code' => $chain_hospital_institution->stan_code,
                            'pincode' => $chain_hospital_institution->pincode,
                            'current_level_start_date' => date('Y-m-d H:i:s'),
                            'state_name' => $chain_hospital_institution->state_name,
                            'address' => $chain_hospital_institution->address,
                            'zone' => $chain_hospital_institution->zone,
                            'cfa_code' => $chain_hospital_institution->cfa_code,
                            'institution_zone' => $chain_hospital_institution->institution_zone,
                            'institution_region' => $chain_hospital_institution->institution_region,
                            'contract_start_date' => date('Y-m-d H:i:s'),
                            'contract_end_date' => $chain_hospital_institution->contract_end_date,
                            'year' => $year,
                            'sap_code' => $chain_hospital_institution->sap_code,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                        $revision_no = DB::table('voluntary_quotation as vq1')
                        ->where('vq1.parent_vq_id', $inst->parent_vq_id)
                        ->where('vq1.id', '<=', $inst->id)
                        ->count();
                        $updation = VoluntaryQuotation::where('id', $inst->id)
                        ->update(['rev_no' => $revision_no]);
                    }
                }
                DB::commit();
            }
            catch (\Exception $e) {
                DB::rollBack();
                $this->info('Error'.$e->getMessage());
                return 1;
            }
        }
        $this->info('process completed successfully.');
        return 0;
    }
}
