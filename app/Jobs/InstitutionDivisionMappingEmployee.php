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



use function Ramsey\Uuid\v1;

class InstitutionDivisionMappingEmployee implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $div_id;
    protected $emp_type;
    protected $emp_code;
    protected $jwt_code;

    /*protected $name;
    protected $division_name;*/
    public $timeout = 999999;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($div_id,$emp_type,$emp_code,$jwt_code)
    {
        //
        $this->div_id = $div_id;
        $this->emp_type = $emp_type;
        $this->emp_code = $emp_code;
        $this->jwt_code = $jwt_code;
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

        $headers = [
            'Content-Type' => 'application/json',
            'AccessToken' => 'key',
            'Authorization' => 'Bearer ' . $this->jwt_code,
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

        $ignoredInstitutions = IgnoredInstitutions::get();
        $ignoredInstitutions = array_map(function($inst){return $inst->institution_id;}, $ignoredInstitutions->all());
        
        $response = json_decode($response, true);
        $resp_collection = collect($response);
        
        
        $institutions = $resp_collection->whereNotIn('INST_ID',$ignoredInstitutions)->toArray();
        foreach ($institutions as $institution) {
            if(isset($institution['LSTZONEMAPPING']) && $institution['LSTZONEMAPPING'] != null){
                DB::beginTransaction();
                try 
                {
                    $div_id = $this->div_id;
                    $emp_type = $this->emp_type;
                    $emp_code = $this->emp_code;
                    $vqs = VoluntaryQuotation::leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id', '=', 'voluntary_quotation.id')
                    ->where('institution_id',$institution['INST_ID'])
                    ->where('voluntary_quotation.is_deleted',0)
                    ->where('voluntary_quotation_sku_listing.is_deleted',0)
                    ->where('voluntary_quotation_sku_listing.div_id',$div_id)
                    ->where('year',$year)
                    ->get();
                    foreach ($vqs as $vq) 
                    {
                        foreach($institution['LSTZONEMAPPING'] as $institutionMap)
                        {
                            if($emp_type == 'ZSM')
                            {
                                /*if($institutionMap['DIV_CODE'] == $div_id && $institutionMap['ZSM_CODE'] == $emp_code)*/
                                if($institutionMap['ZSM_CODE'] == $emp_code)
                                {
                                    $upd_zsm = InstitutionDivisionMapping::updateOrCreate(
                                        [
                                            'vq_id' => $vq->vq_id,
                                            'institution_id' => $institution['INST_ID'],
                                            'division_id'=> $institutionMap['DIV_CODE'],
                                            'zone' => $institutionMap['ZSM_ZONE'],
                                            'region' => NULL,
                                        ],
                                        [ 
                                            'employee_code' => $institutionMap['ZSM_CODE'],
                                            'updated_at' => date('Y-m-d H:i:s')
                                        ]
                                    );
                                }
                            }
                            else
                            {
                                /*if($institutionMap['DIV_CODE'] == $div_id  && $institutionMap['RSM_CODE'] == $emp_code)*/
                                if($institutionMap['RSM_CODE'] == $emp_code)
                                {
                                    $upd_rsm = InstitutionDivisionMapping::updateOrCreate(
                                        [
                                            'vq_id' => $vq->vq_id,
                                            'institution_id' => $institution['INST_ID'],
                                            'division_id'=> $institutionMap['DIV_CODE'],
                                            'zone' => NULL,
                                            'region' => $institutionMap['RSM_REGION'],
                                        ],
                                        [ 
                                            'employee_code' => $institutionMap['RSM_CODE'],
                                            'updated_at' => date('Y-m-d H:i:s')
                                        ]
                                    );
                                }
                            }
                        }
                    }
                    DB::commit();
                }
                catch (\Exception $e) {
                    DB::rollBack();
                    echo 'error please check log';
                    Log::error('Error in mapping: ' . $e->getMessage());
                    return 1;
                }
            }
        }
        Log::info('Mapping done successfully.');
        return 0;
    }
}
