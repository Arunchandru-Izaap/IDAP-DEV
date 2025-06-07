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
class Reminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:cron';

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
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $current_date = strtotime(date('Y-m-d H:i:s'));
        $vq_flag=0;
        $reinit_vq_flag=0;
        $data_parent = VoluntaryQuotation::where('year',$year)->where('current_level',1)->where('parent_vq_id',0)->where('is_deleted', 0)->first();
        if(!is_null($data_parent)){
            $vq_date = strtotime($data_parent->created_at);
            $datediff = $current_date - $vq_date;
            $diff = round($datediff / (60 * 60 * 24));
            $calc_level = 0;
            //kept reminder day as 2 for L1, originally it will be 11
            if($diff == 11){
                // $data1["email"]=array("mansoor@noesis.tech",'vijaya@noesis.tech');
                $data1['year']=$year;
                $count_institution = VoluntaryQuotation::where('year',$year)->where('current_level',1)->where('parent_vq_id',0)->where('is_deleted', 0)->count();
                $data1["subject"]="Reminder  : iDAP VQ Process initiated for ".$year."  for ".$count_institution." Institutions";
                $data1["created_at"] = $data_parent->created_at;
                $emp_email = Employee::where('emp_level','L1')->pluck('emp_email')->toArray();
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
                        Mail::send('admin.emails.reminder', $data1, function($message)use($data1) {
                            $message->to('BhagyeshVijay.Joshi@sunpharma.com')
                            ->cc('ImranKhan.IT@sunpharma.com')
                            ->subject($data1["subject"]);
                            });
                    }
                    else{
                        Mail::send('admin.emails.reminder', $data1, function($message)use($data1) {
                            $message->to($data1["email"])
                            ->replyTo('idap.support@sunpharma.com')
                            ->cc('IDAP.INSTRA@sunpharma.com')
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
                    Log::debug('Reminder Mail for L1 for 11th day');
                    $this->statusdesc  =   "Message sent Succesfully";
                    $this->statuscode  =   "1";
                }
            }
        }
    }
}
