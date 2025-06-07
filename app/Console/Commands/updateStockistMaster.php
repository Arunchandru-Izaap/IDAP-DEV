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

class updateStockistMaster extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateStockistMaster:update';

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

        /*$r = $client->request('POST', env('API_URL') . '/api/Institutions', [
            'body' => $body,
        ]);

        $response = $r->getBody()->getContents();

        $ignoredInstitutions = IgnoredInstitutions::get();
        $ignoredInstitutions = array_map(function($inst){return $inst->institution_id;}, $ignoredInstitutions->all());
        
        $response = json_decode($response, true);
        $resp_collection = collect($response);
        
        
        $institutions = $resp_collection->whereNotIn('INST_ID',$ignoredInstitutions)->toArray();*/
        $institutions = VoluntaryQuotation::select('institution_id')->where('year',$year)->where('parent_vq_id', 0)->where('is_deleted', 0)->pluck('institution_id')->toArray();
        foreach ($institutions as $institution) {
            $vq_checker = VoluntaryQuotation::where('year',$year)->where('institution_id',$institution)->where('parent_vq_id',0)->where('is_deleted', 0)->exists();
            if($vq_checker){
                $body1 = '{"INST_ID": "'.$institution.'"}';
                $r1 = $client->request('POST', env('API_URL').'/api/Stockists', [
                    'body' => $body1
                ]);
                $response = $r1->getBody()->getContents();
                $resp = json_decode($response);
                // DB::beginTransaction();
                // try 
                // { // hide by arunchandru at 30022025
                    $stock_cnt = 0;
                    foreach($resp as $itm){
                        /*if($stock_cnt<3){
                            $stock_flag = 1;
                        }else{
                            $stock_flag = 0;
                        }
                        $stock_cnt++;*/

                        $stockist_data = Stockist_master::where('institution_code',$institution)->where('stockist_code', $itm->STOCKIST_CODE)->exists();
                        if(!$stockist_data){
                            $stock_flag = 0;
                            $stock = Stockist_master::Create([
                                'institution_code' => $institution,
                                'stockist_name' => $itm->STOCKIST_NAME,
                                'stockist_address' => $itm->STOCKIST_ADDRESS,
                                'email_id' => $itm->STOCKIST_EMAIL,
                                'stockist_code' => $itm->STOCKIST_CODE,
                                'stockist_type_flag' => $stock_flag,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }
               /*  DB::commit();
                }
                catch (\Exception $e) {
                    DB::rollBack();
                    $this->info('Error'.$e->getMessage());
                    return 1;
                }
                }else{ */ // hide by arunchandru at 30022025
                    
                    /* if(count($resp)>0) // hide by arunchandru at 13022025
                    {
                        //$stockist_data = Stockist_master::where('institution_code',$institution)->update(['stockist_type_flag'=>0,'updated_at'=> now()]);
                    }
                    foreach($resp as $itm){
                        $upd = Stockist_master::updateOrCreate(['institution_code' => $institution,'stockist_code' => $itm->STOCKIST_CODE ], [ 
                            'stockist_name' => $itm->STOCKIST_NAME,
                            'stockist_address' => $itm->STOCKIST_ADDRESS,
                            'email_id'=> $itm->STOCKIST_EMAIL,
                            'stockist_type_flag' => 1,
                            'updated_at'=> now()
                        ]);
                    } */
                // }  // hide by arunchandru at 30022025
            }
        }
        $this->info('Stockist updated successfully.');
        return 0;
    }
}
