<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Institution;
use App\Models\ceilingMaster;
use App\Models\LastYearPrice;
use App\Models\VoluntaryQuotation;
use App\Models\ActivityTracker;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;

use App\Models\VoluntaryQuotationSkuListing;
use App\Http\Controllers\Api\VqListingController;
use Illuminate\Support\Facades\Mail;

use DB;
use GuzzleHttp\Client;
class CompletionMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'completion:cron';

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
        // Will be running daily but will be triggered after all the vq are sent to poc
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $current_date = strtotime(date('Y-m-d H:i:s'));
        $sent_vq = VoluntaryQuotation::where('year',$year)->where('current_level',7)->where('vq_status',1)->where('parent_vq_id',0)->where('is_deleted', 0)->count();
        $total_vq = VoluntaryQuotation::where('year',$year)->where('current_level',7)->where('parent_vq_id',0)->where('is_deleted', 0)->count();
        if($sent_vq == $total_vq && $sent_vq != 0 && $total_vq != 0){
            $checker = VoluntaryQuotation::where('year',$year)->where('current_level',7)->where('vq_status',1)->where('completion_mail',1)->where('parent_vq_id',0)->where('is_deleted', 0)->exists();
            if(!$checker){
                // $data1["email"]=array("mansoor@noesis.tech",'vijaya@noesis.tech');
                
                $data1['year']=$year;
                $data1["subject"]="iDAP VQ Process Completed for ".$year;
                
                $emp_email = Employee::where('emp_level','L1')->pluck('emp_email')->toArray();
                $data1['email']=$emp_email;
                $data1['email_cc']=array('IDAP.INSTRA@sunpharma.com');
                $data1['link'] = env('APP_URL').'/login';
                // $data1['email_cc']=array('abhishek@noesis.tech', 'venkitaraman@noesis.tech','vijaya@noesis.tech');
                // $dqta['email']
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
                        Mail::send('admin.emails.completion', $data1, function($message)use($data1) {
                            $message->to('BhagyeshVijay.Joshi@sunpharma.com')
                            ->cc('ImranKhan.IT@sunpharma.com')
                            ->subject($data1["subject"]);
                            });
                    }
                    else{
                        Mail::send('admin.emails.completion', $data1, function($message)use($data1) {
                            $message->to($data1["email"])
                            ->cc($data1['email_cc'])
                            ->replyTo('idap.support@sunpharma.com')
                            ->subject($data1["subject"]);
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
                    Log::debug('Full process completed and last completion mail');
                    $this->statusdesc  =   "Message sent Succesfully";
                    $this->statuscode  =   "1";
                }
                if($this->statuscode == 1){
                    $updater = VoluntaryQuotation::where('year',$year)->where('current_level',7)->where('vq_status',1)->where('parent_vq_id',0)->where('is_deleted', 0)->update(['completion_mail'=>1]);

                }
            }
        }
        return 0;
    }
}
