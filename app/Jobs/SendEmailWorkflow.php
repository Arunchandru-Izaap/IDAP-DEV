<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\VoluntaryQuotation;
use DB;
use App\Http\Controllers\Api\VqListingController;
use App\Models\Employee;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendEmailWorkflow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $mail_levels;
    protected $action;
    public $timeout = 999999;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mail_levels,$action)
    {
        $this->mail_levels = $mail_levels;
        $this->action = $action;
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
        $mail_vqs = VoluntaryQuotation::select('current_level', DB::raw('count(current_level) as count'))
        ->where('is_deleted', 0)
        ->where('year', $year)
        ->where('vq_status', 0)
        ->whereIn('current_level', $this->mail_levels)
        ->where('parent_vq_id', 0)
        ->groupBy('current_level')
        ->get();
        foreach ($mail_vqs as $single_mail_vq) {
            $mail_level = $single_mail_vq['current_level'];
            if($mail_level<=6){
                $data1['year']=$year;
                $count_institution = $single_mail_vq['count'];
                $data1["subject"]="iDAP VQ Process initiated for ".$year."  for ".$count_institution." Institutions";
        
                $emp_email = Employee::where('emp_level','L'.$mail_level)->pluck('emp_email')->toArray();
                $data1['email']=$emp_email;
                $data1['link'] = env('APP_URL').'/login';
                $data1['email_cc'] = 'IDAP.INSTRA@sunpharma.com';
            }elseif($mail_level==7){
                $data1['email_cc']=array('mansoor@noesis.tech', 'venkitaraman@noesis.tech','vijaya@noesis.tech');

                $data1['year']=$year;
                $data1["subject"]="IDAP VQ Process apporval  for ".$year." has been done and ready to send";
        
                $emp_email = Employee::where('emp_category','initiator')->pluck('emp_email')->toArray();
                $data1['email']=$emp_email;

                $data1['email_cc'] = array();
                // array_push($data1['email_cc'],'thomas.edakalathoor@sunpharma.com'); //hide by arunchandru at 07042025
                // array_push($data1['email_cc'],'Achyut.Redkar@Sunpharma.Com'); //hide by arunchandru 07042025
                array_push($data1['email_cc'],'IDAP.INSTRA@sunpharma.com');
                $data1['link'] = env('APP_URL').'/login';
            }
            elseif($mail_level ==8){
                $data1['year']=$year;
                $count_institution = $single_mail_vq['count'];
                $data1["subject"]="iDAP VQ Process initiated for ".$year."  for ".$count_institution." Institutions";
        
                $emp_email = Employee::where('emp_level','L'.$mail_level)->pluck('emp_email')->toArray();
                $data1['email']=$emp_email;
                $data1['email_cc'] = array();
                $emailAddresses = DB::table('email_cc')->where('is_active',1)->where('cc_email_level',$mail_level)->pluck('cc_email');
                foreach ($emailAddresses as $email) {
                    array_push($data1['email_cc'], $email);
                }
                $data1['link'] = env('APP_URL').'/login';
            }
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
                    ->cc($data1['email_cc'])
                    ->replyTo('idap.support@sunpharma.com')
                    ->subject($data1["subject"]);
                    });
            }
            if (Mail::failures()) {
                Log::debug('error sending mail');
    
            }else{
                Log::debug('message sent');
            }
        }
        $mail_vqs = VoluntaryQuotation::select('current_level', DB::raw('count(current_level) as count'))
        ->where('is_deleted', 0)
        ->where('year', $year)
        ->where('vq_status', 0)
        ->whereIn('current_level', $this->mail_levels)
        ->where('parent_vq_id', '!=', 0)
        ->groupBy('current_level')
        ->get();
        foreach ($mail_vqs as $single_mail_vq) {
            $mail_level = $single_mail_vq['current_level'];
            if($mail_level<=6){
                $data1['year']=$year;
                $count_institution = $single_mail_vq['count'];
                $data1["subject"]="iDAP VQ Process Reinitiated for ".$year."  for ".$count_institution." Institutions";
        
                $emp_email = Employee::where('emp_level','L'.$mail_level)->pluck('emp_email')->toArray();
                $data1['email']=$emp_email;
                $data1['link'] = env('APP_URL').'/login';
                $data1['email_cc'] = 'IDAP.INSTRA@sunpharma.com';
            }elseif($mail_level==7){
                $data1['email_cc']=array('mansoor@noesis.tech', 'venkitaraman@noesis.tech','vijaya@noesis.tech');

                $data1['year']=$year;
                $data1["subject"]="IDAP VQ Process apporval  for ".$year." has been done and ready to send";
        
                $emp_email = Employee::where('emp_category','initiator')->pluck('emp_email')->toArray();
                $data1['email']=$emp_email;

                $data1['email_cc'] = array();
                // array_push($data1['email_cc'],'thomas.edakalathoor@sunpharma.com');  //hide by arunchandru at 07042025
                // array_push($data1['email_cc'],'Achyut.Redkar@Sunpharma.Com');  //hide by arunchandru at 07042025
                array_push($data1['email_cc'],'IDAP.INSTRA@sunpharma.com'); 
                $data1['link'] = env('APP_URL').'/login';
            }
            elseif($mail_level ==8){
                $data1['year']=$year;
                $count_institution = $single_mail_vq['count'];
                $data1["subject"]="iDAP VQ Process Reinitiated for ".$year."  for ".$count_institution." Institutions";
        
                $emp_email = Employee::where('emp_level','L'.$mail_level)->pluck('emp_email')->toArray();
                $data1['email']=$emp_email;
                $data1['email_cc'] = array();
                $emailAddresses = DB::table('email_cc')->where('is_active',1)->where('cc_email_level',$mail_level)->pluck('cc_email');
                foreach ($emailAddresses as $email) {
                    array_push($data1['email_cc'], $email);
                }
                $data1['link'] = env('APP_URL').'/login';
            }
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
                    ->cc($data1['email_cc'])
                    ->replyTo('idap.support@sunpharma.com')
                    ->subject($data1["subject"]);
                    });
            }
            if (Mail::failures()) {
                Log::debug('error sending mail');
    
            }else{
                Log::debug('message sent');
            }
        }
    }
}
