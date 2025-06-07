<?php

namespace App\Jobs;
use App\Models\Institution;
use App\Models\CeilingMaster;
use App\Models\LastYearPrice;
use App\Models\VoluntaryQuotation;
use App\Models\ActivityTracker;
use App\Http\Controllers\Api\VqListingController;
use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\Stockist_master;
use App\Models\Employee;
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
    public function __construct($from,$to,$emp_code,$jwt_code,$name,$division_name)
    {
        //
        $this->from = $from;
        $this->to = $to;
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
        
        $r = $client->request('POST', env('API_URL').'/api/Products', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        $data = json_decode($response);
       
        
        $r = $client->request('POST', env('API_URL').'/API/InstitutionOnly', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        $institutions = json_decode($response);
        // dd($institutions);

       
        $time_minus = strtotime("-1 year", time());
        $date_minus = date("Y-m-d", $time_minus);
        $last_year = $vq_listing_controller->getFinancialYear($date_minus,"Y");
        $phpdate1 = strtotime( $this->from );
        $start = date( 'Y-m-d H:i:s', $phpdate1 );
        $phpdate2 = strtotime( $this->to );
        $end = date( 'Y-m-d H:i:s', $phpdate2 );
        $vq_created = date('Y-m-d H:i:s');
        $ceiling_data_main = CeilingMaster::select('sku_id','discount_percent')->get();
        foreach($institutions as $institution){
            $vq_checker = VoluntaryQuotation::where('year',$year)->where('institution_id',$institution->INST_ID)->where('parent_vq_id',0)->exists();
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
                
                // $data = json_decode($res->getBody());
                $resp = json_decode($response);
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

                    'address' => $institution->ADDRESS,
                    'zone' => $institution->ZONE,
                    'contract_start_date' => $start,
                    'contract_end_date' => $end,
                    'year' => $year,
                    'created_at' => $vq_created,
                    'updated_at' => $vq_created,
                ]);
                // $vq_listing_controller->activityTracker($inst->id,$this->emp_code,'VQ Initiated by'.$activity_str, 'initiate');
                
                $vq_listing_controller->activityTracker($inst->id,$this->emp_code,'VQ Initiated by / '.$this->name.'/'.$this->division_name.'/'.$activity_str, 'initiate');
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

                        'mrp' => $single_data->MRP,
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
        $emp_email = Employee::where('emp_level','L1')->select('emp_email','emp_name')->get();
        // dd($emp_email);
        $email = '';
        foreach ($emp_email as $key => $emp_mail_id) {
            $email .=$emp_mail_id['emp_mail'].', ';
            // $data["email"]=array("mansoor@noesis.tech","abhishek@noesis.tech","ashok@noesis.tech", "vijaya@noesis.tech", "rahul@noesis.tech");
            // $data["email"]= array($emp_mail_id['emp_email']);
        }
        $data["email"]= $email;
        // dd($data["email"]);
            $count_vq = VoluntaryQuotation::where('year',$year)->count();
            $data["subject"]="IDAP VQ Process initiated for ".$year."  for ".$count_vq." Institutions";
            $data['year']=$year;
            $data['link'] = env('APP_URL').'/login';
            $data['execution_msg'] = $execution_msg;
    
            try{
                if(env('APP_URL') == 'https://idap.noesis.dev'){
                    Mail::send('admin.emails.auto_approval', $data1, function($message)use($data1) {
                        $message->to('ashok@noesis.tech')
                        ->cc('vijaya@noesis.tech')
                        ->replyTo('idap.support@sunpharma.com')
                        ->subject($data1["subject"]);
                        });
                }else{
                    Mail::send('admin.emails.createvq', $data, function($message)use($data) {
                        // $message->to($data["email"])
                        $message->to($data["email"])
                        ->cc('IDAP.INSTRA@sunpharma.com')
                        ->replyTo('idap.support@sunpharma.com')
                        ->subject($data["subject"])
                        ->cc('sunil.v@sunpharma.com');
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

}
