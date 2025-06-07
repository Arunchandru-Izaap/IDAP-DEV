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

class updateInstitutionDivisionMapping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'institution_division_mapping:update';

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
                    $vqs = VoluntaryQuotation::where('institution_id',$institution['INST_ID'])->where('is_deleted',0)->where('year',$year)->get();
                    foreach ($vqs as $vq) 
                    {
                        foreach($institution['LSTZONEMAPPING'] as $institutionMap)
                        {
                            $upd_zsm = InstitutionDivisionMapping::updateOrCreate(
                                [
                                    'vq_id' => $vq->id,
                                    'institution_id' => $institution['INST_ID'],
                                    'division_id'=> $institutionMap['DIV_CODE'],
                                    'zone' => $institutionMap['ZSM_ZONE'],
                                    'region' => NULL,
                                    //'employee_code' => $institutionMap['ZSM_CODE'],
                                ],
                                [ 
                                    /*'vq_id' => $vq->id,
                                    'institution_id' => $institution['INST_ID'],
                                    'division_id'=> $institutionMap['DIV_CODE'],
                                    'zone' => $institutionMap['ZSM_ZONE'],
                                    'region' => NULL,*/
                                    'employee_code' => $institutionMap['ZSM_CODE'],
                                    //'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]
                            );
                            $upd_rsm = InstitutionDivisionMapping::updateOrCreate(
                                [
                                    'vq_id' => $vq->id,
                                    'institution_id' => $institution['INST_ID'],
                                    'division_id'=> $institutionMap['DIV_CODE'],
                                    'zone' => NULL,
                                    'region' => $institutionMap['RSM_REGION'],
                                    //'employee_code' => $institutionMap['RSM_CODE'],
                                ],
                                [ 
                                    /*'vq_id' => $vq->id,
                                    'institution_id' => $institution['INST_ID'],
                                    'division_id'=> $institutionMap['DIV_CODE'],
                                    'zone' => NULL,
                                    'region' => $institutionMap['RSM_REGION'],*/
                                    'employee_code' => $institutionMap['RSM_CODE'],
                                    //'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]
                            );
                        }
                    }
                    DB::commit();
                }
                catch (\Exception $e) {
                    DB::rollBack();
                    echo 'error please check log';
                    $this->info('Error'.$e->getMessage());
                    return 1;
                }
            }
        }
        $this->info('Mapping done successfully.');
        return 0;
    }
}
