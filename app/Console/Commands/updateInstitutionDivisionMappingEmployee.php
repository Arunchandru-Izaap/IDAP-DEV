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

class updateInstitutionDivisionMappingEmployee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'institution_division_mapping_emp:update';

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
        $jwt = JwtToken::orderBy('updated_at', 'desc')->first();
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");

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
                    $div_id = 'CHC';
                    $emp_type = 'ZSM';
                    $emp_code = 'E48119';
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
                                if($institutionMap['DIV_CODE'] == $div_id && $institutionMap['ZSM_CODE'] == $emp_code)
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
                                if($institutionMap['DIV_CODE'] == $div_id  && $institutionMap['RSM_CODE'] == $emp_code)
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
                    $this->info('Error'.$e->getMessage());
                    return 1;
                }
            }
        }
        $this->info('Mapping done successfully.');
        return 0;
    }
}
