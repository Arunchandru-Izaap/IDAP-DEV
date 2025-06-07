<?php

namespace App\Jobs;
use App\Models\Institution;
use App\Models\CeilingMaster;
use App\Models\LastYearPrice;
use App\Models\VoluntaryQuotation;
use App\Models\ActivityTracker;
use App\Models\InstitutionDivisionMapping;
use App\Http\Controllers\Api\VqListingController;
use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\Stockist_master;
use App\Models\Employee;
use App\Models\IgnoredInstitutions;
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
class CreateVq implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $from;
    protected $to;
    protected $emp_code;
    protected $jwt_code;

    protected $name;
    protected $division_name;
    protected $institution_id_arr;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 999999;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($from,$to,$emp_code,$jwt_code,$name,$division_name,$institution_id_arr=null)
    {
        //
        $this->from = $from;
        $this->to = $to;
        $this->emp_code = $emp_code;
        $this->jwt_code = $jwt_code;

        $this->name = $name;
        $this->division_name = $division_name;
        $this->institution_id_arr = $institution_id_arr;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
	//dd("old");

	    $code_execution_start_time = microtime(true);
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        
        $name = Session::get("emp_name");
        $div = Session::get("division_name");
        $activity_str = Session::get("emp_name");
        if($div != ''){
	    	$activity_str = Session::get("emp_name").'/'.Session::get("division_name");   
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
          
        }';
        
        $r = $client->request('POST', env('API_URL').'/API/Products', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        $data = json_decode($response);
       
        
        $r = $client->request('POST', env('API_URL').'/API/InstitutionOnly', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();

        $ignoredInstitutions = IgnoredInstitutions::get();
        $ignoredInstitutions = array_map(function($inst){return $inst->institution_id;}, $ignoredInstitutions->all());
        
        $response = json_decode($response);
        $resp_collection = collect($response);
        if($this->institution_id_arr != null){
            $resp_collection = $resp_collection->whereIn('INST_ID',$this->institution_id_arr);
        }
        
        $institutions = $resp_collection->whereNotIn('INST_ID',$ignoredInstitutions)->toArray();
        // dd($institutions);

       
        $time_minus = strtotime("-1 year", time());
        $date_minus = date("Y-m-d", $time_minus);
        // $last_year = $vq_listing_controller->getFinancialYear($date_minus,"Y");
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $year_arr = explode('-' , $year);
        $last_year = $year_arr[0]-1 . '-' . $year_arr[1]-1; // last financial year
        $phpdate1 = strtotime( $this->from );
        $start = date( 'Y-m-d H:i:s', $phpdate1 );
        $phpdate2 = strtotime( $this->to );
        $end = date( 'Y-m-d H:i:s', $phpdate2 );
        $vq_created = date('Y-m-d H:i:s');
        $ceiling_data_main = CeilingMaster::select('sku_id','discount_percent')->get();
        foreach($institutions as $institution){
            $vq_checker = VoluntaryQuotation::where('year',$year)->where('institution_id',$institution->INST_ID)->where('parent_vq_id',0)->where('is_deleted', 0)->exists();
            if(!$vq_checker){
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
                // $data = json_decode($res->getBody());
                
                $inst = VoluntaryQuotation::Create([
                    'hospital_name' => $institution->INST_NAME,
                    'institution_id' => $institution->INST_ID,
                    'institution_key_account' => $institution->KEY_ACC_NAME,
                    'city' => $institution->CITY,
                    'addr1'=>$institution->ADDR1,
                    'addr2'=>$institution->ADDR2,
                    'addr3'=>$institution->ADDR3,

                    'stan_code'=>$institution->STAN_CODE,
                    'pincode'=>$institution->PINCODE,
                    'state_name'=>$institution->STATE_NAME,
                    'current_level_start_date' => $vq_created,
                    'address' => $institution->ADDRESS,
                    'zone' => $institution->ZONE,
                    'institution_zone' => data_get($institution, 'LSTZONEMAPPING.0.ZSM_ZONE'),
                    'institution_region' => data_get($institution, 'LSTZONEMAPPING.0.RSM_REGION'),
                    'cfa_code' => $institution->CFA_CODE,
                    'contract_start_date' => $start,
                    'contract_end_date' => $end,
                    'year' => $year,
                    'sap_code' => $institution->SAP_CODE,
                    'institution_zone' => isset($institution->LSTZONEMAPPING[0]->ZSM_ZONE) ? $institution->LSTZONEMAPPING[0]->ZSM_ZONE : '',
                    'institution_region' => isset($institution->LSTZONEMAPPING[0]->RSM_REGION) ? $institution->LSTZONEMAPPING[0]->RSM_REGION : '',
                    'created_at' => $vq_created,
                    'updated_at' => $vq_created,
                    'rev_no' =>0//added on 05042024 to add rev no for create vq
                ]);
                // $vq_listing_controller->activityTracker($inst->id,$this->emp_code,'VQ Initiated by'.$activity_str, 'initiate');
                if($institution->LSTZONEMAPPING != null){
                    $mapListing = [];
                    foreach($institution->LSTZONEMAPPING as $institutionMap){
                        $mapListing[] = [
                            'vq_id' => $inst->id,
                            'institution_id' => $institution->INST_ID,
                            'division_id' => $institutionMap->DIV_CODE,
                            'zone' => $institutionMap->ZSM_ZONE,
                            'region' => NULL,
                            'employee_code' => $institutionMap->ZSM_CODE,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        $mapListing[] = [
                            'vq_id' => $inst->id,
                            'institution_id' => $institution->INST_ID,
                            'division_id' => $institutionMap->DIV_CODE,
                            'zone' => NULL,
                            'region' => $institutionMap->RSM_REGION,
                            'employee_code' => $institutionMap->RSM_CODE,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                    }
                    foreach (array_chunk($mapListing,100) as $t)  
                    {
                        DB::table('institution_division_mapping')->insert($t); 
                    }
                }
                if($this->institution_id_arr != null){
                    $vq_listing_controller->activityTracker($inst->id,$this->emp_code,'VQ Reinitiated (Add counter) by / '.$this->name.'/'.$this->division_name.'/'.$activity_str, 'reinitiate');
                }
                else
                {
                    $vq_listing_controller->activityTracker($inst->id,$this->emp_code,'VQ Initiated by / '.$this->name.'/'.$this->division_name.'/'.$activity_str, 'initiate');
                }
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
                $cnt=0;
                print_r($last_year);
                $last_year_main = LastYearPrice::select('sku_id','division_id','discount_percent','mrp','ptr')->where('institution_id',$institution->INST_ID)->where('year',$last_year)->get();
                foreach($data as $single_data){
                    $last_year_data = $last_year_main->where('sku_id',$single_data->ITEM_CODE)->where('division_id',$single_data->DIVISION_CODE)->first();
                    
                    if(!is_null($last_year_data)){
                        $last_year_percent = $ceiling_percent = $last_year_data['discount_percent'];
                        
                        //$mid = ($single_data->PTR / 100) * $last_year_percent;
                        //$last_year_rate = $single_data->PTR - $mid;
                        $last_year_mrp = $last_year_data['mrp'];
                        $last_year_ptr = $last_year_data['ptr'];

                        if($single_data->PTR != $last_year_data['ptr']):
                            $last_year_rate = $ceiling_rate = ($single_data->PTR - ($single_data->PTR * ($last_year_percent/100)));
                        else:
                            $last_year_rate = $ceiling_rate = ($last_year_data['ptr'] - ($last_year_data['ptr'] * ($last_year_percent/100)));
                        endif;
                    }else{
                        $last_year_percent = NULL;
                        $last_year_rate = NULL;
                        $ceiling_percent = 0;
                        $ceiling_rate = $single_data->PTR;
                        $last_year_mrp = NULL;
                        $last_year_ptr = NULL;
                    }
                    $filtered_pdms = $pdms_data->filter(function ($item) use($single_data) {
                        return $item->ITEM_CODE == $single_data->ITEM_CODE;
                    });
                    // print_r($filtered_pdms);
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
                        'last_year_ptr' => $last_year_ptr,
                        'last_year_percent' => $last_year_percent,
                        'last_year_rate' => $last_year_rate,
                        'pdms_discount' => $filtered_pdms->isNotEmpty() ? $filtered_pdms->pluck('MAX_DISCOUNT')->first() : null,
                        'discount_percent' => $ceiling_percent,

                        'discount_rate' => $ceiling_rate,
                        'sap_itemcode' => $single_data->SAP_ITEMCODE,
                        'mrp' => $single_data->MRP,
                        'last_year_mrp' => $last_year_mrp,
                        'mrp_margin'=>((($single_data->MRP - $ceiling_rate)/$single_data->MRP)*100 ),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    
                }
                foreach (array_chunk($listing_data,100) as $t)  
                {
                    DB::table('voluntary_quotation_sku_listing')->insert($t); 
                }
            }   
        }
		
		$code_execution_end_time = microtime(true);
        $execution_time = ($code_execution_end_time - $code_execution_start_time)/60;
        $execution_msg = 'Total Execution Time: '.$execution_time.' Mins';

        $data = array();
        $emp_email = Employee::where('emp_level','L1')->pluck('emp_email')->toArray();
	    $data["email"]=  $emp_email;
        $count_vq = VoluntaryQuotation::where('year',$year)->where('is_deleted', 0)->count();
        if($this->institution_id_arr != null){
           $data["subject"]="IDAP VQ Process initiated.";
        }else{
            $data["subject"]="IDAP VQ Process initiated for ".$year."  for ".$count_vq." Institutions";
        }
        $data['year']=$year;
        $data['link'] = env('APP_URL').'/login';
        $data['execution_msg'] = $execution_msg;

        try{
            if(env('APP_URL') == 'https://idap.noesis.dev'){
                Mail::send('admin.emails.auto_approval', $data, function($message)use($data) {
                    $message->to('ashok@noesis.tech')
                    ->cc('vijaya@noesis.tech')
                    ->replyTo('idap.support@sunpharma.com')
                    ->subject($data["subject"]);
                    });
            }
            elseif(env('APP_URL') == 'http://172.16.8.192' || env('APP_URL') == 'https://172.16.8.192'){
                Mail::send('admin.emails.createvq', $data, function($message)use($data) {
                    // $message->to($data["email"])
                    $message->to('BhagyeshVijay.Joshi@sunpharma.com')
                    ->cc('ImranKhan.IT@sunpharma.com')
                    ->subject($data["subject"]);
                    });
            }
            else{
                Mail::send('admin.emails.createvq', $data, function($message)use($data) {
                    // $message->to($data["email"])
                    $message->to($data["email"])
                    ->cc('IDAP.INSTRA@sunpharma.com')
                    ->replyTo('idap.instra@sunpharma.com')
                    ->subject($data["subject"])
                    ->cc('sunil.v@sunpharma.com')
                    ->cc('Supriya.Palkar@Sunpharma.Com')
                    ->cc('Kranti.Mohite@sunpharma.com');
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
        return 0;
    }

public function handle_email()
{
	dd("new");
	$data = array();
	$year = '2022-2023';	
	$emp_email = Employee::where('emp_level','L1')->pluck('emp_email')->toArray();
	$data["email"]=  $emp_email;

        // dd($data["email"]);
            $count_vq = VoluntaryQuotation::where('year',$year)->where('is_deleted', 0)->count();
            $data["subject"]="IDAP VQ Process initiated for ".$year."  for ".$count_vq." Institutions";
            $data['year']=$year;
            $data['link'] = env('APP_URL').'/login';
            //$data['execution_msg'] = $execution_msg;
    
            try{
                if(env('APP_URL') == 'https://idap.noesis.dev'){
                    Mail::send('admin.emails.auto_approval', $data, function($message)use($data) {
                        $message->to('ashok@noesis.tech')
                        ->cc('vijaya@noesis.tech')
                        ->replyTo('idap.support@sunpharma.com')
                        ->subject($data["subject"]);
                    });
                }
                elseif(env('APP_URL') == 'http://172.16.8.192' || env('APP_URL') == 'https://172.16.8.192'){
                    Mail::send('admin.emails.createvq', $data, function($message)use($data) {
                        // $message->to($data["email"])
                        $message->to('BhagyeshVijay.Joshi@sunpharma.com')
                        ->cc('ImranKhan.IT@sunpharma.com')
                        ->subject($data["subject"]);
                    });
                }
                else{
                    Mail::send('admin.emails.createvq', $data, function($message)use($data) {
                        // $message->to($data["email"])
                        $message->to($data["email"])
                        ->cc('IDAP.INSTRA@sunpharma.com')
                        ->replyTo('idap.instra@sunpharma.com')
                        ->subject($data["subject"])
                        ->cc('bhagyeshvijay.joshi@Sunpharma.Com')
                        ->cc('devendra.yede@Sunpharma.Com')
                        ->cc('sunil.v@sunpharma.com')
                        ->cc('Supriya.Palkar@Sunpharma.Com')
                        ->cc('Kranti.Mohite@sunpharma.com');
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
}

}