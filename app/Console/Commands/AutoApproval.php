<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Institution;
use App\Models\ceilingMaster;
use App\Models\LastYearPrice;
use App\Models\VoluntaryQuotation;
use App\Models\ActivityTracker;
use App\Models\ApprovalPeriod;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;
use App\Models\VoluntaryQuotationSkuListing;
use App\Http\Controllers\Api\VqListingController;
use Illuminate\Support\Facades\Mail;

use DB;
use GuzzleHttp\Client;
class AutoApproval extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approval:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto approval cron';

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
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $current_date = strtotime(date('Y-m-d H:i:s'));
        $vq_flag=0;
        $reinit_vq_flag=0;
        //Parent vq Auto Approval Process
        $approval_period = ApprovalPeriod::get();
        $data_parent = VoluntaryQuotation::where('year',$year)->where('current_level','<=',6)->where('parent_vq_id',0)->where('is_deleted', 0)->get();
        foreach($data_parent as $single_data){
            // $vq_date = strtotime($single_data['created_at']);
            // $datediff = $current_date - $vq_date;
            // $diff = round($datediff / (60 * 60 * 24));
            // $calc_level = 0;

            $vq_date = $single_data['created_at'] ? strtotime($single_data['created_at']) : strtotime(date("Y-m-d H:i:s", strtotime("-2 months"))) ;//changed to created_at from current_level_start_date 21052024
            $datediff = $current_date - $vq_date;
            $diff = ($datediff / (60 * 60 * 24));
            $calc_level = $single_data['current_level'];
            
            // added for idap-33 ceo approval 22052024 starts
            if($calc_level == 6)
            {
                // check for item more discount ptr more than equal to 30 added on 22052024 
                $checkItems = VoluntaryQuotation::select('item_code','voluntary_quotation.id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_data['id'])->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->exists();
                if($checkItems == true)
                {
                    $checkExceptionsItems = VoluntaryQuotation::select('item_code','div_id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_data['id'])->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->get();
                    $allItemsPresent = true;
                    foreach ($checkExceptionsItems as $item) {
                        $exists = DB::table('exception_sku_list')
                            ->where('item_code', $item['item_code'])
                            ->where('div_id', $item['div_id'])
                            ->where('year', $year)
                            ->exists();

                        if (!$exists) {
                            $allItemsPresent = false;
                            break;
                        }
                    }
                    if ($allItemsPresent) {
                        //All items are present in exception list
                        if($diff > $approval_period->where('type', '=', 'vq')->where('level',$calc_level)->first()['days']){
                            $calc_level++;
                        }
                    } else {
                        //Some items are missing in exception list
                        if($diff > $approval_period->where('type', '=', 'vq')->where('level',$calc_level)->first()['days']){
                            $calc_level = 8;//current level for ceo is 8
                        }
                    }
                    
                }
                else
                {
                    if($diff > $approval_period->where('type', '=', 'vq')->where('level',$calc_level)->first()['days']){
                        $calc_level++;
                    }
                }
            }
            else
            {
                if($diff > $approval_period->where('type', '=', 'vq')->where('level',$calc_level)->first()['days']){
                    $calc_level++;
                }
            }
            // added for idap-33 ceo approval 22052024 ends
            // if($diff <= $approval_period->where('type', '=', 'vq')->where('level',1)->first()['level']){
            //     $calc_level=1;
            // }elseif($diff <= $approval_period->where('type', '=', 'vq')->where('level',2)->first()['level'] && $diff > $approval_period->where('type', '=', 'vq')->where('level',1)->first()['level']){
            //     $calc_level=2;
            // }elseif($diff <= $approval_period->where('type', '=', 'vq')->where('level',3)->first()['level'] && $diff > $approval_period->where('type', '=', 'vq')->where('level',2)->first()['level']){
            //     $calc_level=3;
            // }elseif($diff <= $approval_period->where('type', '=', 'vq')->where('level',4)->first()['level'] && $diff > $approval_period->where('type', '=', 'vq')->where('level',3)->first()['level']){
            //     $calc_level=4;
            // }elseif($diff <= $approval_period->where('type', '=', 'vq')->where('level',5)->first()['level'] && $diff > $approval_period->where('type', '=', 'vq')->where('level',4)->first()['level']){
            //     $calc_level=5;
            // }elseif($diff <= $approval_period->where('type', '=', 'vq')->where('level',6)->first()['level'] && $diff > $approval_period->where('type', '=', 'vq')->where('level',5)->first()['level']){
            //     $calc_level=6;
            // }else{
            //     $calc_level=7;
            // }
            if($calc_level != $single_data['current_level']){
                $mail_level = $calc_level;
                $vq_flag = 1;
                $update = VoluntaryQuotation::where('id',$single_data['id'])->where('is_deleted', 0)->update(['current_level'=>$calc_level,'updated_at'=>date('Y-m-d H:i:s')]);
                if($calc_level == 8)
                {
                    $last_level = 6;
                }
                else
                {
                    $last_level = $calc_level-1 == 0 ? 1 : $calc_level-1;
                }
                $vq_details = VoluntaryQuotationSkuListing::select(
                    "div_name" ,
                    DB::raw("(sum(l".$last_level."_status)) as statuss")
                    )
                    ->where('vq_id',$single_data['id'])
                    ->where('is_deleted',0)
                    ->groupBy('div_name')
                    ->get();
                    foreach($vq_details as $vq_detail){
                        if($vq_detail->statuss < 1){
                            $vq_listing_controller->activityTracker($single_data['id'],'','VQ Auto Approved of division - '.$vq_detail->div_name.' at level - '.$last_level,'autoapprove');
                        }
                    }
            }
        }
        
        //send mail
        if($vq_flag==1){
            if($mail_level<=6){
                //L2 to L4 approval reminder mail
                // $data1["email"]=array("mansoor@noesis.tech",'vijaya@noesis.tech');
                $data1['year']=$year;
                $count_institution = VoluntaryQuotation::where('year',$year)->where('current_level',$mail_level)->where('parent_vq_id',0)->where('is_deleted', 0)->count();
                $data1["subject"]="iDAP VQ Process initiated for ".$year."  for ".$count_institution." Institutions";
        
                $emp_email = Employee::where('emp_level','L'.$mail_level)->pluck('emp_email')->toArray();
                $data1['email']=$emp_email;
                $data1['link'] = env('APP_URL').'/login';
                
                try{
                    if(env('APP_URL') == 'https://idap.noesis.dev'){
                        Mail::send('admin.emails.auto_approval', $data1, function($message)use($data1) {
                            $message->to('mansoor@noesis.tech')
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
                        Mail::send('admin.emails.auto_approval', $data1, function($message)use($data1) {
                            $message->to($data1["email"])
                            ->cc('IDAP.INSTRA@sunpharma.com')
                            ->replyTo('idap.support@sunpharma.com')
                            ->subject($data1["subject"]);
                            });
                    }//commented for email issue 22052024
                    
                }catch(JWTException $exception){
                    $this->serverstatuscode = "0";
                    $this->serverstatusdes = $exception->getMessage();
                }
                if (Mail::failures()) {
                    $this->statusdesc  =   "Error sending mail";
                    $this->statuscode  =   "0";
        
                }else{
                    Log::debug('auto approver parent vq for L2 to L4');
                    $this->statusdesc  =   "Message sent Succesfully";
                    $this->statuscode  =   "1";
                }
            }elseif($mail_level==7){

                //if current level is 5 final mail to initiator
                // $data["email_to"]="mansoor@noesis.tech";
                $data['email_cc']=array('mansoor@noesis.tech', 'venkitaraman@noesis.tech','vijaya@noesis.tech');

                $data['year']=$year;
                $data["subject"]="IDAP VQ Process apporval  for ".$year." has been done and ready to send";
        
                $emp_email = Employee::where('emp_category','initiator')->pluck('emp_email')->toArray();
                $data['email_to']=$emp_email;

                $data['email_cc'] = array();
                // array_push($data['email_cc'],'thomas.edakalathoor@sunpharma.com'); //hide by arunchandru at 07042025
                // array_push($data['email_cc'],'Achyut.Redkar@Sunpharma.Com'); //hide by arunchandru  at 07042025
                // array_push($data['email_cc'],'vijaya@noesis.com');
                // array_push($data['email_cc'],'Devendra.Yede@sunpharma.com');
                array_push($data['email_cc'],'IDAP.INSTRA@sunpharma.com');
                $data['link'] = env('APP_URL').'/login';

                try{
                    if(env('APP_URL') == 'https://idap.noesis.dev'){
                        Mail::send('admin.emails.approval_complete', $data, function($message)use($data) {
                            $message->to('mansoor@noesis.tech')
                            ->cc('vijaya@noesis.tech')
                            ->replyTo('idap.support@sunpharma.com')
                            ->subject($data["subject"]);
                            });
                    }
                    elseif(env('APP_URL') == 'http://172.16.8.192' || env('APP_URL') == 'https://172.16.8.192'){
                        Mail::send('admin.emails.approval_complete', $data, function($message)use($data) {
                            $message->to('BhagyeshVijay.Joshi@sunpharma.com')
                            ->cc('ImranKhan.IT@sunpharma.com')
                            ->subject($data["subject"]);
                            });
                    }
                    else{
                        Mail::send('admin.emails.approval_complete', $data, function($message)use($data) {
                            $message->to($data["email_to"])
                            ->replyTo('idap.support@sunpharma.com')
                            ->cc($data['email_cc'])
                            ->subject($data["subject"]);
                            });
                    }//commented for email issue 22052024
                    
                }catch(JWTException $exception){
                    $this->serverstatuscode = "0";
                    $this->serverstatusdes = $exception->getMessage();
                }
                if (Mail::failures()) {
                    $this->statusdesc  =   "Error sending mail";
                    $this->statuscode  =   "0";
        
                }else{
                    Log::debug('auto approver parent vq for L5');
                    $this->statusdesc  =   "Message sent Succesfully";
                    $this->statuscode  =   "1";
                }
            }
            elseif($mail_level ==8){
                //L8 approval reminder mail;
                $data1['year']=$year;
                $count_institution = VoluntaryQuotation::where('year',$year)->where('current_level',$mail_level)->where('parent_vq_id',0)->where('is_deleted', 0)->count();
                $data1["subject"]="iDAP VQ Process initiated for ".$year."  for ".$count_institution." Institutions";
        
                $emp_email = Employee::where('emp_level','L'.$mail_level)->pluck('emp_email')->toArray();
                $data1['email']=$emp_email;
                $data1['email_cc'] = array();
                $emailAddresses = DB::table('email_cc')->where('is_active',1)->where('cc_email_level',$mail_level)->pluck('cc_email');
                foreach ($emailAddresses as $email) {
                    array_push($data1['email_cc'], $email);
                }
                $data1['link'] = env('APP_URL').'/login';
                try{
                    if(env('APP_URL') == 'https://idap.noesis.dev'){
                        Mail::send('admin.emails.auto_approval', $data1, function($message)use($data1) {
                            $message->to($data1["email"])
                            ->cc($data1['email_cc'])
                            ->replyTo('idap.support@sunpharma.com')
                            ->subject($data1["subject"]);
                            });
                    }
                    elseif(env('APP_URL') == 'http://172.16.8.192' || env('APP_URL') == 'https://172.16.8.192'){
                        Mail::send('admin.emails.auto_approval', $data1, function($message)use($data1) {
                            $message->to($data1["email"])
                            ->cc($data1['email_cc'])
                            ->subject($data1["subject"]);
                            });
                    }
                    else{
                        Mail::send('admin.emails.auto_approval', $data1, function($message)use($data1) {
                            $message->to($data1["email"])
                            ->cc($data1['email_cc'])
                            ->replyTo('idap.support@sunpharma.com')
                            ->subject($data1["subject"]);
                            });
                    }//commented for email issue 22052024
                    
                }catch(JWTException $exception){
                    $this->serverstatuscode = "0";
                    $this->serverstatusdes = $exception->getMessage();
                }
                if (Mail::failures()) {
                    $this->statusdesc  =   "Error sending mail";
                    $this->statuscode  =   "0";
        
                }else{
                    Log::debug('auto approver parent vq to L8');
                    $this->statusdesc  =   "Message sent Succesfully";
                    $this->statuscode  =   "1";
                }
            }
        }

        //reinitiate Vq Approval Process
        $data_reinit = VoluntaryQuotation::where('year',$year)->where('current_level','<=',6)->where('parent_vq_id','!=',0)->where('is_deleted', 0)->get();
        foreach($data_reinit as $single_data){
            $vq_date = $single_data['created_at'] ? strtotime($single_data['created_at']) : strtotime(date("Y-m-d H:i:s", strtotime("-2 months"))) ;//changed to created_at from current_level_start_date 21052024
            if($single_data['fastforward_levels'] != NULL){
                $reinit_type = 'reinitvq_fast';
                $fastforward = collect(explode(',',$single_data['fastforward_levels']))->sort();
            }else{
                $reinit_type = 'reinitvq_normal';
                $fastforward = NULL;
            }
            $datediff = $current_date - $vq_date;
            $diff = ($datediff / (60 * 60 * 24));
            $calc_level = $single_data['current_level'];
            
            // added for idap-33 ceo approval 22052024 starts
            if($calc_level == 6)
            {
                // check for item more discount ptr more than equal to 30 added on 22052024 
                $checkItems = VoluntaryQuotation::select('item_code','voluntary_quotation.id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_data['id'])->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->exists();
                if($checkItems == true)
                {
                    $checkExceptionsItems = VoluntaryQuotation::select('item_code','div_id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_data['id'])->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->get();
                    $allItemsPresent = true;
                    foreach ($checkExceptionsItems as $item) {
                        $exists = DB::table('exception_sku_list')
                            ->where('item_code', $item['item_code'])
                            ->where('div_id', $item['div_id'])
                            ->where('year', $year)
                            ->exists();

                        if (!$exists) {
                            $allItemsPresent = false;
                            break;
                        }
                    }
                    if ($allItemsPresent) {
                       if($diff > $approval_period->where('type', '=', $reinit_type)->where('level',$calc_level)->first()['days']){
                            if($fastforward != NULL){
                                $firstHighest = $fastforward->filter(function ($number) use ($calc_level) {
                                    return $number > $calc_level;
                                })->first();
                                
                                $calc_level = $firstHighest ? $firstHighest : 7;
                                Log::debug(print_r("in",true));

                            }else{
                                $calc_level++;
                            }
                        }
                    }
                    else {
                        //Some items are missing in exception list
                        if($diff > $approval_period->where('type', '=', $reinit_type)->where('level',$calc_level)->first()['days']){
                            $calc_level = 8;//current level for ceo is 8
                        }
                    }
                }
                else
                {
                    if($diff > $approval_period->where('type', '=', $reinit_type)->where('level',$calc_level)->first()['days']){
                        if($fastforward != NULL){
                            $firstHighest = $fastforward->filter(function ($number) use ($calc_level) {
                                return $number > $calc_level;
                            })->first();
                            
                            $calc_level = $firstHighest ? $firstHighest : 7;
                            Log::debug(print_r("in",true));

                        }else{
                            $calc_level++;
                        }
                    }
                }
            }
            else
            {
                if($diff > $approval_period->where('type', '=', $reinit_type)->where('level',$calc_level)->first()['days']){
                    if($fastforward != NULL){
                        $firstHighest = $fastforward->filter(function ($number) use ($calc_level) {
                            return $number > $calc_level;
                        })->first();
                        
                        $calc_level = $firstHighest ? $firstHighest : 7;
                        if($calc_level == 7)//check for vq disc percent more than 30% if calc level is Initiator
                        {
                            $checkItems = VoluntaryQuotation::select('item_code','voluntary_quotation.id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_data['id'])->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->exists();
                            if($checkItems == true)
                            {
                                $checkExceptionsItems = VoluntaryQuotation::select('item_code','div_id')->leftJoin('voluntary_quotation_sku_listing as vqsl','vqsl.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.id',$single_data['id'])->where('vqsl.discount_percent','>=',30)->where('vqsl.is_deleted', 0)->get();
                                $allItemsPresent = true;
                                foreach ($checkExceptionsItems as $item) {
                                    $exists = DB::table('exception_sku_list')
                                        ->where('item_code', $item['item_code'])
                                        ->where('div_id', $item['div_id'])
                                        ->where('year', $year)
                                        ->exists();

                                    if (!$exists) {
                                        $allItemsPresent = false;
                                        break;
                                    }
                                }
                                if ($allItemsPresent) {
                                    
                                }
                                else
                                {
                                     $calc_level = 8;//current level for ceo is 8
                                }
                            }
                        }

                    }else{
                        $calc_level++;
                    }
                }
            }
            // added for idap-33 ceo approval 22052024 ends
            Log::debug(print_r($calc_level,true));
          

            if($calc_level != $single_data['current_level']){
                $reinit_vq_flag=1;
                $mail_level = $calc_level;
                $update = VoluntaryQuotation::where('id',$single_data['id'])->where('is_deleted', 0)->update(['current_level'=>$calc_level,'updated_at'=>date('Y-m-d H:i:s')]);//removed current_level_start_date 24052024

                if($calc_level == 8)
                {
                    if($fastforward != NULL){
                        $last_level = $single_data['current_level'];
                    }
                    else
                    {
                        $last_level = 6;
                    }
                }
                else
                {
                    if($fastforward != NULL){
                        $last_level = $single_data['current_level'];
                    }
                    else
                    {
                        $last_level = $calc_level-1 == 0 ? 1 : $calc_level-1;
                    }
                }
                $vq_details = VoluntaryQuotationSkuListing::select(
                    "div_name" ,
                    DB::raw("(sum('l".$last_level."_status')) as statuss")
                    )
                    ->where('vq_id',$single_data['id'])
                    ->where('is_deleted',0)
                    ->groupBy('div_name')
                    ->get();
                    foreach($vq_details as $vq_detail){
                        if($vq_detail->statuss < 1){
                            $vq_listing_controller->activityTracker($single_data['id'],'','VQ Auto Approved of division - '.$vq_detail->div_name.' at level - '.$last_level,'autoapprove');
                        }
                    }
            }
        }
        if($reinit_vq_flag==1){
            if($mail_level<=6){
                //L2 to L4 approval reminder mail
                // $data1["email"]=array("mansoor@noesis.tech",'vijaya@noesis.tech');
                $data1['year']=$year;
                $count_institution = VoluntaryQuotation::where('year',$year)->where('current_level',$mail_level)->where('parent_vq_id','!=',0)->where('is_deleted', 0)->count();
                $data1["subject"]="IDAP VQ Process Reinitiated for ".$year."  for ".$count_institution." Institutions";
        
                $emp_email = Employee::where('emp_level','L'.$mail_level)->pluck('emp_email')->toArray();
                $data1['email']=$emp_email;
                $data1['link'] = env('APP_URL').'/login';
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
                        Mail::send('admin.emails.auto_approval', $data1, function($message)use($data1) {
                            $message->to($data1["email"])
                            ->cc('IDAP.INSTRA@sunpharma.com')
                            ->replyTo('idap.support@sunpharma.com')
                            ->subject($data1["subject"]);
                            });
                    }//commented for email issue 24062024
                   
                }catch(JWTException $exception){
                    $this->serverstatuscode = "0";
                    $this->serverstatusdes = $exception->getMessage();
                }
                if (Mail::failures()) {
                    $this->statusdesc  =   "Error sending mail";
                    $this->statuscode  =   "0";
        
                }else{
                    Log::debug('auto approver child vq for L2 to L4');
                    $this->statusdesc  =   "Message sent Succesfully";
                    $this->statuscode  =   "1";
                }
            }elseif($mail_level==7){

                //if current level is 5 final mail to initiator
                
                $data["email_to"]=array('sunil.v@sunpharma.com', 'kranti.mohite@sunpharma.com', 'supriya.palkar@sunpharma.com');
                $data['email_cc']=array('abhishek@noesis.tech', 'venkitaraman@noesis.tech','vijaya@noesis.tech');

                $data['year']=$year;
                $data["subject"]="iDAP VQ Process apporval  for ".$year." has been done and ready to send";
        
                $emp_email = Employee::where('emp_category','initiator')->pluck('emp_email')->toArray();
                $data['actual_email_to']=$emp_email;

                $data['actual_email_cc'] = array();
                // array_push($data['email_cc'],'thomas.edakalathoor@sunpharma.com'); //hide by arunchandru 07042025
                // array_push($data['email_cc'],'Achyut.Redkar@Sunpharma.Com'); //hide by arunchandru 07042025
                // array_push($data['email_cc'],'vijaya@noesis.com');
                array_push($data['email_cc'],'IDAP.INSTRA@sunpharma.com');
                // array_push($data['email_cc'],'bhagyeshVijay.Joshi@sunpharma.com');
                 
                $data['link'] = env('APP_URL').'/login';

                try{
                    if(env('APP_URL') == 'https://idap.noesis.dev'){
                        Mail::send('admin.emails.approval_complete', $data, function($message)use($data) {
                            $message->to('mansoor@noesis.tech')
                            ->cc('vijaya@noesis.tech')
                            ->replyTo('idap.support@sunpharma.com')
                            ->subject($data["subject"]);
                            });
                    }
                    elseif(env('APP_URL') == 'http://172.16.8.192' || env('APP_URL') == 'https://172.16.8.192'){
                        Mail::send('admin.emails.approval_complete', $data, function($message)use($data) {
                            $message->to('BhagyeshVijay.Joshi@sunpharma.com')
                            ->cc('ImranKhan.IT@sunpharma.com')
                            ->subject($data["subject"]);
                            });
                    }
                    else{
                        Mail::send('admin.emails.approval_complete', $data, function($message)use($data) {
                            $message->to($data["email_to"])
                            ->replyTo('idap.support@sunpharma.com')
                            ->cc($data['email_cc'])
                            ->subject($data["subject"]);
                            });
                    }//commented for email issue 24052024
                    
                }catch(JWTException $exception){
                    $this->serverstatuscode = "0";
                    $this->serverstatusdes = $exception->getMessage();
                }
                if (Mail::failures()) {
                    $this->statusdesc  =   "Error sending mail";
                    $this->statuscode  =   "0";
        
                }else{
                    Log::debug('auto approver child vq for L5');
                    $this->statusdesc  =   "Message sent Succesfully";
                    $this->statuscode  =   "1";
                }
            }
            elseif($mail_level ==8){
                //L8 approval reminder mail;
                $data1['year']=$year;
                $count_institution = VoluntaryQuotation::where('year',$year)->where('current_level',$mail_level)->where('parent_vq_id','!=',0)->where('is_deleted', 0)->count();
                $data1["subject"]="iDAP VQ Process Reinitiated for ".$year."  for ".$count_institution." Institutions";
        
                $emp_email = Employee::where('emp_level','L'.$mail_level)->pluck('emp_email')->toArray();
                $data1['email']=$emp_email;
                $data1['email_cc'] = array();
                $emailAddresses = DB::table('email_cc')->where('is_active',1)->where('cc_email_level',$mail_level)->pluck('cc_email');
                foreach ($emailAddresses as $email) {
                    array_push($data1['email_cc'], $email);
                }
                $data1['link'] = env('APP_URL').'/login';
                try{
                    if(env('APP_URL') == 'https://idap.noesis.dev'){
                        Mail::send('admin.emails.auto_approval', $data1, function($message)use($data1) {
                            $message->to($data1["email"])
                            ->cc($data1['email_cc'])
                            ->replyTo('idap.support@sunpharma.com')
                            ->subject($data1["subject"]);
                            });
                    }
                    elseif(env('APP_URL') == 'http://172.16.8.192' || env('APP_URL') == 'https://172.16.8.192'){
                        Mail::send('admin.emails.auto_approval', $data1, function($message)use($data1) {
                            $message->to($data1["email"])
                            ->cc($data1['email_cc'])
                            ->subject($data1["subject"]);
                            });
                    }
                    else{
                        Mail::send('admin.emails.auto_approval', $data1, function($message)use($data1) {
                            $message->to($data1["email"])
                            ->cc($data1['email_cc'])
                            ->replyTo('idap.support@sunpharma.com')
                            ->subject($data1["subject"]);
                            });
                    }//commented for email issue 22052024
                    
                }catch(JWTException $exception){
                    $this->serverstatuscode = "0";
                    $this->serverstatusdes = $exception->getMessage();
                }
                if (Mail::failures()) {
                    $this->statusdesc  =   "Error sending mail";
                    $this->statuscode  =   "0";
        
                }else{
                    Log::debug('auto approver parent vq to L8');
                    $this->statusdesc  =   "Message sent Succesfully";
                    $this->statuscode  =   "1";
                }
            }
        }
        $this->info('Successfully sent daily quote to everyone.');
    }

}
