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
use App\Models\MaxDiscountCap;
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
class CreateVqForNew implements ShouldQueue
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
        $year_arr = explode('-' , $year);
        $prev_year = $year_arr[0]-1 . '-' . $year_arr[1]-1;
        
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
        
        $p = $client->request('POST', env('API_URL').'/API/Products', [
            'body' => $body
        ]);
        $products = $p->getBody()->getContents();
        $products = json_decode($products);
       
        
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
        $last_year = $vq_listing_controller->getFinancialYear($date_minus,"Y");
        $phpdate1 = strtotime( $this->from );
        $start = date('Y-m-d H:i:s', $phpdate1 );
        $phpdate2 = strtotime( $this->to );
        $end = date('Y-m-d H:i:s', $phpdate2);
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
                    "FIN_YEAR": "'.$prev_year.'",
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
                if($institution->LSTZONEMAPPING != null && count($institution->LSTZONEMAPPING)){
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
                        try {
                        DB::table('institution_division_mapping')->insert($t); 
                        } catch (\Exception $e) {
                            print('error in institution_division_mapping');
                            print_r($e->getMessage());
                            \Log::error("Batch insert failed: " . $e->getMessage());
                            // Optionally, handle the error (e.g., skip or retry failed batches)
                        }
                    }
                }
                // if(!$institution->LSTZONEMAPPING || !count($institution->LSTZONEMAPPING)) {
                //     $last_year_vq = VoluntaryQuotation::where('institution_id' , $institution->INST_ID)->where('voluntary_quotation.is_deleted' ,0)
                //     ->where('voluntary_quotation.vq_status' ,1)->where('parent_vq_id', 0)->where('year' , $last_year)
                //     ->first();
                //     $institutionDivMap = InstitutionDivisionMapping::where('vq_id', $last_year_vq->id)->distinct()->cursor();
                //     print_r("mappingfound");
                //     $institutionMappingData = [];
                //     $batchSize = 100;
                //     $counter = 0;
                //     foreach($institutionDivMap as $map){
                //         $institutionMappingData[] = [
                //             'vq_id' => $inst->id,
                //             'institution_id' => data_get($map, 'institution_id'),
                //             'division_id' => data_get($map, 'division_id'),
                //             'zone' => data_get($map, 'zone'),
                //             'region' => data_get($map, 'region'),
                //             'employee_code' => data_get($map, 'employee_code'),
                //             'created_at' => date('Y-m-d H:i:s'),
                //             'updated_at' => date('Y-m-d H:i:s')
                //         ];
                //         // Insert in batches
                //         if (++$counter % $batchSize == 0) {
                //             InstitutionDivisionMapping::insert($institutionMappingData);
                //             $institutionMappingData = []; // Reset array for next batch
                //         }
                //     }
                //     if (!empty($institutionMappingData)) {
                //         InstitutionDivisionMapping::insert($institutionMappingData);
                //     }
                //     print_r("institution mapped");
                // }
               
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
                $maxRevSubquery = DB::table('voluntary_quotation_sku_listing as s')
                ->select(DB::raw('MAX(v2.rev_no) AS max_rev_no'), 's.item_code', 'v2.institution_id')
                ->leftJoin('voluntary_quotation as v2', 'v2.id', '=', 's.vq_id')
                ->where('v2.year', $prev_year)
                ->where('s.is_deleted', 0)
                ->where('v2.vq_status', 1)
                ->where('v2.is_deleted', 0)
                ->where('v2.institution_id', $institution->INST_ID)
                ->groupBy('s.item_code');
                $lastyearData = DB::table('voluntary_quotation_sku_listing as vqsl')
                ->select('vqsl.*', 'vq.*')
                ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
                ->joinSub($maxRevSubquery, 'max_rev', function ($join) use($institution) {
                    $join->on('vqsl.item_code', '=', 'max_rev.item_code')
                        ->where('vq.institution_id', $institution->INST_ID)
                        ->on('vq.rev_no', '=', 'max_rev.max_rev_no');
                })
                ->where('vq.institution_id', $institution->INST_ID)
                ->where('vq.year', $prev_year)
                ->where('vq.vq_status', 1)
                ->where('vq.is_deleted', 0)
                ->where('vqsl.is_deleted', 0)
                ->orderByDesc('vqsl.created_at')
                ->get();
                foreach($products as $product){
                    $last_year_data  = $lastyearData->where('item_code' , $product->ITEM_CODE)->where('div_id' , $product->DIVISION_CODE)->first();
                    
                    if(!is_null($last_year_data)){
                        $last_year_percent = $ceiling_percent = $last_year_data->discount_percent;
                        $last_year_rate = $ceiling_rate = ($last_year_data->discount_rate);
                        $last_year_mrp = $last_year_data->mrp;
                        $last_year_ptr = $last_year_data->ptr;
    
                        if($product->PTR != $last_year_data->ptr):
                            $last_year_rate = $ceiling_rate = ($product->PTR - ($product->PTR * ($last_year_percent/100)));
                        else:
                            $last_year_rate = $ceiling_rate = ($last_year_data->ptr - ($last_year_data->ptr * ($last_year_percent/100)));
                        endif;
                    }else{
                        $last_year_percent = NULL;
                        $last_year_rate = NULL;
                        $ceiling_percent = 0;
                        $ceiling_rate = $product->PTR;
                        $last_year_mrp = NULL;
                        $last_year_ptr = NULL;
                    }
                    $filtered_pdms = $pdms_data->filter(function ($item) use($product) {
                        return $item->ITEM_CODE == $product->ITEM_CODE;
                    });
                    $listing_data[]=[
                        'vq_id' =>$inst->id,
                        'item_code' => $product->ITEM_CODE,
                        'brand_name' => $product->BRAND_NAME,
                        'mother_brand_name' => $product->MOTHER_BRAND_NAME,
                        'hsn_code' => $product->HSN_CODE,
                        'applicable_gst' => $product->APPLICABLE_GST,
                        'composition' => $product->COMPOSITION,
                        'type' => $product->ITEM_TYPE,
                        'div_name' => $product->DIVISION_NAME,
                        'div_id' => $product->DIVISION_CODE,
                        'pack' => $product->PACK_SIZE,
                        'ptr' => $product->PTR,
                        'last_year_ptr' => $last_year_ptr,
                        'last_year_percent' => $last_year_percent,
                        'last_year_rate' => $last_year_rate,
                        'pdms_discount' => $filtered_pdms->isNotEmpty() ? $filtered_pdms->pluck('MAX_DISCOUNT')->first() : null,
                        'discount_percent' => $ceiling_percent,
                        'discount_rate' => $ceiling_rate,
                        'sap_itemcode' => $product->SAP_ITEMCODE,
                        'mrp' => $product->MRP,
                        'last_year_mrp' => $last_year_mrp,
                        'mrp_margin'=> ((($product->MRP - $ceiling_rate)/$product->MRP)*100 ),
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

        //max dicount cap update previous financial year to current finanical year
        $max_discount_cap_data = DB::table('max_discount_cap')
        ->select('div_id', DB::raw('MAX(max_discount) as max_discount'))
        ->where('year', $prev_year)
        ->groupBy('div_id')
        // ->distinct()
        ->get();
        foreach($max_discount_cap_data as $max_discount_data):
            $MaxDiscountCap = MaxDiscountCap::Create([
                'div_id' => $max_discount_data->div_id,
                'max_discount' => $max_discount_data->max_discount,
                'year' => $year,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        endforeach;

        //added if report file is existing rename all report files
        $emp_master_code = Employee::select('emp_code')->whereIn('emp_category',['approver', 'initiator'])->pluck('emp_code')->toArray();
        foreach($emp_master_code as $emp_code):
            if (file_exists( public_path() . '/latestreport' . $emp_code . '.zip')) {
                $latest_file_existing =  public_path() . '/latestreport' . $emp_code . '.zip';
                $latest_file_new =  public_path() . '/old-latestreport' . $emp_code . '.zip';
                rename($latest_file_existing, $latest_file_new);
            }
            if (file_exists( public_path() . '/historicalreport' . $emp_code . '.zip')) {
                $historical_file_existing = public_path() . '/historicalreport' . $emp_code . '.zip';
                $historical_file_new = public_path() . '/old-historicalreport' . $emp_code . '.zip';
                rename($historical_file_existing, $historical_file_new);
            }
        endforeach;

        $financialYears = VoluntaryQuotation::select('year')
        ->where('is_deleted',0)->groupBy('year')->orderBy('year','DESC')->pluck('year')->toArray();
        foreach($financialYears as $financialYear):
            foreach($emp_master_code as $emp_code):
                if (file_exists(public_path() . '/financialyearhistoricalreport' . $emp_code . '-'.$financialYear.'.zip')) {
                    $financialyear_historical_file_existing = public_path() . '/financialyearhistoricalreport' . $emp_code . '-'.$financialYear. '.zip';
                    $financialyear_historical_file_new = public_path() . '/old-financialyearhistoricalreport' . $emp_code . '-'.$financialYear. '.zip';
                    rename($financialyear_historical_file_existing, $financialyear_historical_file_new);
                }
            endforeach;    
        endforeach;
		
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
            // if(env('APP_URL') == 'https://idap.noesis.dev'){
                Mail::send('admin.emails.auto_approval', $data, function($message)use($data) {
                    $message->to('elakyarni.of@gmail.com')
                    // ->cc('vijaya@noesis.tech')
                    ->replyTo('idap.support@sunpharma.com')
                    ->subject($data["subject"]);
                    });
            // }
            // elseif(env('APP_URL') == 'http://172.16.8.192' || env('APP_URL') == 'https://172.16.8.192'){
            //     Mail::send('admin.emails.createvq', $data, function($message)use($data) {
            //         // $message->to($data["email"])
            //         $message->to('BhagyeshVijay.Joshi@sunpharma.com')
            //         ->cc('ImranKhan.IT@sunpharma.com')
            //         ->subject($data["subject"]);
            //         });
            // }
            // else{
            //     Mail::send('admin.emails.createvq', $data, function($message)use($data) {
            //         // $message->to($data["email"])
            //         $message->to($data["email"])
            //         ->cc('IDAP.INSTRA@sunpharma.com')
            //         ->replyTo('idap.instra@sunpharma.com')
            //         ->subject($data["subject"])
            //         ->cc('sunil.v@sunpharma.com')
            //         ->cc('Supriya.Palkar@Sunpharma.Com')
            //         ->cc('Kranti.Mohite@sunpharma.com');
            //         });
            // }
            
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
	$data = array();
	$year = '2022-2023';	
	$emp_email = Employee::where('emp_level','L1')->pluck('emp_email')->toArray();
	$data["email"]=  $emp_email;
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