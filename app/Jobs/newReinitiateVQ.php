<?php

namespace App\Jobs;
use App\Models\Institution;
use App\Models\CeilingMaster;
use App\Models\LastYearPrice;
use App\Models\VoluntaryQuotation;
use App\Models\ActivityTracker;
use App\Http\Controllers\Api\VqListingController;
use App\Models\IgnoredInstitutions;
use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\Stockist_master;
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
class newReinitiateVQ implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $from;
    protected $to;
    protected $emp_code;
    protected $jwt_code;
    protected $institution_code;

    protected $name;
    protected $division_name;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($from,$to,$emp_code,$jwt_code,$institution_code,$name,$division_name)
    {
        //
        $this->from = $from;
        $this->to = $to;
        $this->emp_code = $emp_code;
        $this->jwt_code = $jwt_code;
        $this->institution_code = $institution_code;

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

        $ignoredInstitutions = IgnoredInstitutions::get();
        $ignoredInstitutions = array_map(function($inst){return $inst->institution_id;}, $ignoredInstitutions->all());

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
        
        $r = $client->request('POST', env('API_URL').'/api/Products', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        $data = json_decode($response);
        
        
        $r = $client->request('POST', env('API_URL').'/API/InstitutionOnly', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        $response = json_decode($response);
        $resp_collection = collect($response);
        $institutions = $resp_collection->whereNotIn('INST_ID',$ignoredInstitutions)->toArray();



        $time_minus = strtotime("-1 year", time());
        $date_minus = date("Y-m-d", $time_minus);
        $last_year = $vq_listing_controller->getFinancialYear($date_minus,"Y");
        $phpdate1 = strtotime( $this->from );
        $start = date( 'Y-m-d H:i:s', $phpdate1 );
        $phpdate2 = strtotime( $this->to );
        $end = date( 'Y-m-d H:i:s', $phpdate2 );
        $ceiling_data_main = CeilingMaster::select('sku_id','discount_percent')->get();

        foreach($institutions as $institution){
            if($institution->INST_ID == $this->institution_code){
                $inst = VoluntaryQuotation::Create([
                    'hospital_name' => $institution->INST_NAME,
                    'institution_id' => $institution->INST_ID,
                    'institution_key_account' => $institution->KEY_ACC_NAME,
                    'city' => $institution->CITY,
                    'addr1'=>$institution->ADDR1,
                    'addr2'=>$institution->ADDR2,
                    'addr3'=>$institution->ADDR3,
                    'current_level_start_date' => date('Y-m-d H:i:s'),
                    'stan_code'=>$institution->STAN_CODE,
                    'pincode'=>$institution->PINCODE,
                    'state_name'=>$institution->STATE_NAME,
                    'address' => $institution->ADDRESS,
                    'zone' => $institution->ZONE,
                    'contract_start_date' => $start,
                    'contract_end_date' => $end,
                    'year' => $year,
                    'institution_zone' => isset($institution->LSTZONEMAPPING[0]->ZSM_ZONE) ? $institution->LSTZONEMAPPING[0]->ZSM_ZONE : '',
                    'institution_region' => isset($institution->LSTZONEMAPPING[0]->RSM_REGION) ? $institution->LSTZONEMAPPING[0]->RSM_REGION : '',
                    'sap_code' => $institution->SAP_CODE,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                foreach($institution->LSTZONEMAPPING as $institutionMap){
                    $mapListing[] = [
                        'vq_id' => $inst->id,
                        'institution_id' => $institution->INST_ID,
                        'division_id' => $institutionMap->DIV_CODE,
                        'zone' => $institutionMap->ZSM_ZONE,
                        'region' => NULL,
                        'employee_code' => $institutionMap->ZSM_CODE
                    ];
                    $mapListing[] = [
                        'vq_id' => $inst->id,
                        'institution_id' => $institution->INST_ID,
                        'division_id' => $institutionMap->DIV_CODE,
                        'zone' => NULL,
                        'region' => $institutionMap->RSM_REGION,
                        'employee_code' => $institutionMap->RSM_CODE
                    ];
                }
                foreach (array_chunk($mapListing,100) as $t)  
                {
                    DB::table('institution_division_mapping')->insert($t); 
                }
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
                    "INST_ID": "'.$institution->INST_ID.'"
                }';
                $r = $client->request('POST', env('API_URL').'/api/Stockists', [
                    'body' => $body
                ]);
                $response = $r->getBody()->getContents();
                $resp = json_decode($response);

                $body1 = '{
                    "FIN_YEAR": "'.$year.'",
                    "ITEM_CODE": "",
                    "DIV_CODE": "",
                    "INSTITUTE_CODE": "'.$institution->INST_ID.'"
                }';
                $r = $client->request('GET', env('API_URL').'/API/PDMSData', [
                    'body' => $body1
                ]);
                $response = $r->getBody()->getContents();
                $pdms_data = collect(json_decode($response));
                // $vq_listing_controller->activityTracker($inst->id,$this->emp_code,"VQ Initiated".Session::put("emp_name",$emp_info->emp_name).'/'.Session::put("division_name",implode (",", $all_div_name)),'initiate');
                $vq_listing_controller->activityTracker($inst->id,$this->emp_code,"VQ Initiated by ".$this->name.'/'.$this->division_name, 'initiate');
                
                $last_year_data = Stockist_master::where('institution_code',$institution->INST_ID)->exists();
                if(!$last_year_data){
                    $stock_cnt = 0;
                    foreach($resp as $itm){
                        if($stock_cnt<3){
                            $stock_flag = 1;
                        }else{
                            $stock_flag = 0;
                        }
                        $stock_cnt++;
                        $stock = Stockist_master::Create([
                            'institution_code' => $institution->INST_ID,
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
                        $upd = Stockist_master::updateOrCreate(['institution_code' => $institution->INST_ID,'stockist_code' => $itm->STOCKIST_CODE ], [ 
                            'stockist_name' => $itm->STOCKIST_NAME,
                            'stockist_address' => $itm->STOCKIST_ADDRESS,
                            'email_id'=> $itm->STOCKIST_EMAIL,
                        ]);
                    }
                }
                $listing_data = array();
                $last_year_main = LastYearPrice::select('sku_id','division_id','discount_percent')->where('institution_id',$institution->INST_ID)->where('year',$last_year)->get();
                foreach($data as $single_data){
                    $last_year_data = $last_year_main->where('sku_id',$single_data->ITEM_CODE)->where('division_id',$single_data->DIVISION_CODE)->first();
                    if(!is_null($last_year_data)){
                        $last_year_percent = $ceiling_percent = $last_year_data['discount_percent'];
                        $last_year_rate = $ceiling_rate = ($single_data->PTR - ($single_data->PTR * ($last_year_percent/100)));
                        //$mid = ($single_data->PTR / 100) * $last_year_percent;
                        //$last_year_rate = $single_data->PTR - $mid;
                        
                    }else{
                        $last_year_percent = NULL;
                        $last_year_rate = NULL;
                    }
                    $filtered_pdms = $pdms_data->filter(function ($item) use($single_data) {
                        return $item->ITEM_CODE == $single_data->item_code;
                    });
                    $listing_data[]=[
                        'vq_id' =>$inst->id,
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
                        'pdms_discount' => $filtered_pdms->isNotEmpty() ? $filtered_pdms->pluck('MAX_DISCOUNT')->first() : null,
                        'mrp' => $single_data->MRP,
                        'mrp_margin'=>((($single_data->MRP - $ceiling_rate)/$single_data->MRP)*100 ),
                        'sap_itemcode' => $single_data->SAP_ITEMCODE,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                }
                foreach (array_chunk($listing_data,100) as $t)  
                {
                    DB::table('voluntary_quotation_sku_listing')->insert($t); 
                }

                $data = array();
                $vq = VoluntaryQuotation::where('id',$inst->id)->where('is_deleted', 0)->first();
                $poc_data = PocMaster::where('institution_id',$vq->institution_id)->first();
                // $data['email']=$poc_data->fsm_email;
                $data['email']='Devendra.Yede@sunpharma.com';
                $data['email_cc']=array();
                array_push($data['email_cc'],$poc_data->zsm_email);
                array_push($data['email_cc'],$poc_data->rsm_email);
                array_push($data['email_cc'],'IDAP.INSTRA@sunpharma.com');
                // $data["email"]=array("mansoor@noesis.tech","mithaiwala16@gmail.com");
                $data["subject"]="iDAP VQ Process initiated for ".$year."  for 1 Institutions";
                $data['year']=$year;
                $emp_email = Employee::where('emp_level','L'.$mail_level)->pluck('emp_email')->toArray();
                $data1['email']=$emp_email;
                $data['link'] = env('APP_URL').'/login';

                try{
                    if(env('APP_URL') == 'https://idap.noesis.dev'){
                        Mail::send('admin.emails.auto_approval', $data1, function($message)use($data1) {
                            $message->to('ashok@noesis.tech')
                            ->cc('vijaya@noesis.tech')
                            ->replyTo('idap.support@sunpharma.com')
                            ->subject($data1["subject"]);
                            });
                    }
                    elseif(env('APP_URL') == 'http://172.16.8.192' || env('APP_URL') == 'https://172.16.8.192'){
                        Mail::send('admin.emails.auto_approval', $data1, function($message)use($data1) {
                            $message->to('BhagyeshVijay.Joshi@sunpharma.com')
                            ->cc('ImranKhan.IT@sunpharma.com')
                            ->subject($data1["subject"]);
                            });
                    }
                    else{
                        Mail::send('admin.emails.createvq', $data, function($message)use($data) {
                            $message->to($data["email"])
                            ->replyTo('idap.support@sunpharma.com')
                            ->cc($data['email_cc'])
                            ->subject($data["subject"]);
                //           ->cc('abhishek@noesis.tech', 'Mr. Abhishek')
                            });
                    }
                    
                }catch(JWTException $exception){
                    $this->serverstatuscode = "0";
                    $this->serverstatusdes = $exception->getMessage();
                }
                if (Mail::failures()) {
                    $this->statusdesc  =   "Error sending mail";
                    $this->statuscode  =   "0";
        
                }else{
                Log::debug('VQ created');
                $this->statusdesc  =   "Message sent Succesfully";
                $this->statuscode  =   "1";
                }
                break;
            }
            
        }
    }

}
