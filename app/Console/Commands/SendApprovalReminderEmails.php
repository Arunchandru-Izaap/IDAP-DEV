<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use App\Models\VoluntaryQuotation;
use App\Models\ApprovalEmailScheduleMaster;
use App\Models\Employee;
use App\Models\ApprovalPeriod;
use App\Http\Controllers\Api\VqListingController;
use App\Exports\SendApprovalReminderExport;
use Maatwebsite\Excel\Excel as BaseExcel;
use Excel;

class SendApprovalReminderEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approvalremainderemails:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs daily for email to approvers in the VQ details.';

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

        
        echo 'Initiate VQ'; echo "\n";
        // Parent VQ approver email notification
        $schedules_data_parent = ApprovalEmailScheduleMaster::where('type', 'vq')->get();
        foreach ($schedules_data_parent as $schedule) {
            $approval_period = ApprovalPeriod::where('level', $schedule->level)->where('type', 'vq')->get()->toArray();
            // print_r($approval_period[0]['start_date']);
            $vq_ids = [];
            $pendingVQs = VoluntaryQuotation::where('current_level', $schedule->level)
                ->where('parent_vq_id',0)
                ->where('year', $year)
                ->where('vq_status', 0)
                ->where('is_deleted', 0)
                ->get();
            echo "level : ". $schedule->level; echo "\n";
            // echo "start_days : ". $schedule->start_days; echo "\n";
            if(!empty($pendingVQs)):
                foreach ($pendingVQs as $vq) {

                    $daysPending = now()->diffInDays(Carbon::parse($approval_period[0]['start_date']));
                    $totalHours = now()->diffInHours(Carbon::parse($approval_period[0]['start_date']));

                    // echo "Difference: {$totalHours} hour(s)"; echo "\n";
                    // echo "created_at : ".$approval_period[0]['start_date']; echo "\n";
                    // echo "daysPending : ". $daysPending; echo "\n";
                    
                    // Check if days passed is more than start_days
                    if ($daysPending >= $schedule->start_days) {
                        $daysAfterStart = $daysPending - $schedule->start_days;
                        // echo "daysAfterStart : ". $daysAfterStart; echo "\n";
                        // echo "Cal : ". ($daysAfterStart % $schedule->frequency_days); echo "\n";
                        // Now check frequency:
                        if ($schedule->frequency_days == 1 || $daysAfterStart % $schedule->frequency_days == 0) {
                            $vq_ids[] = $vq->id;
                        }
                    }
                }
            endif;

            if(!empty($vq_ids)):
                $data1['year'] = $year;
                $data1['count_institution'] = count($vq_ids);

                if($data1['count_institution'] == 1):
                    $single_vq = VoluntaryQuotation::where('current_level', $schedule->level)
                        ->whereIn('id', $vq_ids)
                        ->where('year', $year)
                        ->where('is_deleted', 0)
                        ->first();
                    $data1["subject"] = "Action Required: iDAP VQ Process Initiated for ".$year."  for ".$single_vq->hospital_name."  (".$single_vq->institution_id.")";
                else:
                    $data1["subject"] = "Action Required: iDAP VQ Process Initiated for ".$year." for attached institutions (".$data1['count_institution']." institutions)";
                endif;

                $data1['re_initiate'] = "initiated";
            
                $emp_email = Employee::where('emp_level','L'.$schedule->level)->get()->toArray();
                
                $data1['email'] =  array_column($emp_email, 'emp_email');
                
                $data1['manager_email']= array_column($emp_email, 'manager_email');

                $data1['link'] = env('APP_URL').'/login';

                $data1['SendApprovalRemainderExport'] = Excel::raw(new SendApprovalReminderExport($year, $vq_ids, $schedule->level, $schedule->frequency_days), BaseExcel::XLSX);
                try{
                    $level = $schedule->level;
                    Mail::send('admin.emails.approval_reminder', $data1, function($message)use($data1, $level) {
                        $message->to($data1["email"]);
                        if(!empty($data1["manager_email"])  && !empty(array_filter($data1['manager_email']))):
                            $manager_cc = array_values(array_filter($data1['manager_email']));
                            $message->cc($manager_cc);
                        endif;
                        $message->subject($data1["subject"]);
                        if($data1['count_institution'] > 0){
                            $message->attachData($data1["SendApprovalRemainderExport"], 'pending-institutions-list.xlsx');
                        }
                    });

                }catch(JWTException $exception){
                    $this->serverstatuscode = "0";
                    $this->serverstatusdes = $exception->getMessage();
                }
                if (Mail::failures()) {
                    $this->statusdesc  =   "Error sending mail";
                    $this->statuscode  =   "0";
        
                }else{
                echo "mail sent "; echo "\n";
                // print_r('mail sent');
                Log::debug('Send approver parent vq for L'.$schedule->level);
                    $this->statusdesc  =   "Message sent Succesfully";
                    $this->statuscode  =   "1";
                }
            endif;
        }
        
        

        echo 'Re-Initiate VQ Fast'; echo "\n";
        //reinitiate Vq Fast Approval Process
        $approval_period_reinitvq_fast = ApprovalPeriod::where('type', 'reinitvq_fast')->get();
        foreach($approval_period_reinitvq_fast as $reinitvq_fast){

            $pendingVQs = VoluntaryQuotation::where('year', $year)
                ->where('current_level', $reinitvq_fast->level)
                ->where('parent_vq_id', '!=', 0)
                ->where('is_deleted', 0)
                ->whereNotNull('fastforward_levels')
                ->get();

            echo "Level : ". $reinitvq_fast->level; echo "\n";
            // echo "Days : ". $reinitvq_fast->days; echo "\n";
            $reinitvq_fast_vq_ids = [];
            if(!empty($pendingVQs)):
                foreach ($pendingVQs as $vq) {
                    $daysPending = now()->diffInDays(Carbon::parse($vq->created_at));
                    $totalHours = now()->diffInHours(Carbon::parse($vq->created_at));

                    // echo "Difference: {$totalHours} hour(s)"; echo "\n";
                    // echo "created_at : ".$vq->created_at; echo "\n";
                    // echo "daysPending : ". $daysPending; echo "\n";
                    
                    // Check if days passed is more than start_days
                    if ($daysPending <= $reinitvq_fast->days) {
                        $reinitvq_fast_vq_ids[] = $vq->id;
                    }
                }

            endif;

            if(!empty($reinitvq_fast_vq_ids)):
                $data1['year'] = $year;
                $data1['count_institution'] = count($reinitvq_fast_vq_ids);

                if($data1['count_institution'] == 1):
                    $single_vq = VoluntaryQuotation::where('current_level', $reinitvq_fast->level)
                        ->whereIn('id', $reinitvq_fast_vq_ids)
                        ->where('year', $year)
                        ->where('is_deleted', 0)
                        ->first();
                    $data1["subject"] = "Action Required: iDAP VQ Process Re-Initiated for ".$year."  for ".$single_vq->hospital_name."  (".$single_vq->institution_id.")";
                else:
                    $data1["subject"] = "Action Required: iDAP VQ Process Re-Initiated for ".$year." for attached institutions (".$data1['count_institution']." institutions)";
                endif;

                $data1['re_initiate'] = "Re-initiated";

                $emp_email = Employee::where('emp_level','L'.$reinitvq_fast->level)->get()->toArray();
                
                $data1['email'] =  array_column($emp_email, 'emp_email');
                
                $data1['manager_email']= array_column($emp_email, 'manager_email');

                $data1['link'] = env('APP_URL').'/login';

                $data1['SendApprovalRemainderExport'] = Excel::raw(new SendApprovalReminderExport($year, $reinitvq_fast_vq_ids, $reinitvq_fast->level, 1), BaseExcel::XLSX);
                try{
                    $level = $reinitvq_fast->level;
                    Mail::send('admin.emails.approval_reminder', $data1, function($message)use($data1, $level) {
                        $message->to($data1["email"]);
                        if(!empty($data1["manager_email"])  && !empty(array_filter($data1['manager_email']))):
                            $manager_cc = array_values(array_filter($data1['manager_email']));
                            $message->cc($manager_cc);
                        endif;
                        $message->subject($data1["subject"]);
                        if($data1['count_institution'] > 0){
                            $message->attachData($data1["SendApprovalRemainderExport"], 'pending-institutions-list.xlsx');
                        }
                    });

                }catch(JWTException $exception){
                    $this->serverstatuscode = "0";
                    $this->serverstatusdes = $exception->getMessage();
                }
                if (Mail::failures()) {
                    $this->statusdesc  =   "Error sending mail";
                    $this->statuscode  =   "0";
        
                }else{
                echo "mail sent "; echo "\n";
                // print_r('mail sent');
                Log::debug('Send approver parent vq for L'.$reinitvq_fast->level);
                    $this->statusdesc  =   "Message sent Succesfully";
                    $this->statuscode  =   "1";
                }
            endif;
        }
    
        
        echo 'Re-Initiate VQ Normal'; echo "\n";
        //reinitiate Vq Normal Approval Process
        $approval_period_reinitvq_normal = ApprovalPeriod::where('type', 'reinitvq_normal')->get();
        foreach($approval_period_reinitvq_normal as $reinitvq_normal){

            $pendingVQs = VoluntaryQuotation::where('year', $year)
                ->where('current_level', $reinitvq_normal->level)
                ->where('parent_vq_id', '!=', 0)
                ->where('is_deleted', 0)
                ->whereNull('fastforward_levels')
                ->get();

            echo "Level : ". $reinitvq_normal->level; echo "\n";
            // echo "Days : ". $reinitvq_normal->days; echo "\n";
            $reinitvq_normal_vq_ids = [];
            if(!empty($pendingVQs)):
                foreach ($pendingVQs as $vq) {
                    $daysPending = now()->diffInDays(Carbon::parse($vq->created_at));
                    $totalHours = now()->diffInHours(Carbon::parse($vq->created_at));

                    // echo "Difference: {$totalHours} hour(s)"; echo "\n";
                    // echo "created_at : ".$vq->created_at; echo "\n";
                    // echo "daysPending : ". $daysPending; echo "\n";
                    
                    // Check if days passed is more than start_days
                    if ($daysPending <= $reinitvq_normal->days) {
                        $reinitvq_normal_vq_ids[] = $vq->id;
                    }
                }
            endif;

            if(!empty($reinitvq_normal_vq_ids)):
                $data1['year'] = $year;
                $data1['count_institution'] = count($reinitvq_normal_vq_ids);

                if($data1['count_institution'] == 1):
                    $single_vq = VoluntaryQuotation::where('current_level', $reinitvq_normal->level)
                        ->whereIn('id', $reinitvq_normal_vq_ids)
                        ->where('year', $year)
                        ->where('is_deleted', 0)
                        ->first();
                    $data1["subject"] = "Action Required: iDAP VQ Process Re-Initiated for ".$year."  for ".$single_vq->hospital_name."  (".$single_vq->institution_id.")";
                else:
                    $data1["subject"] = "Action Required: iDAP VQ Process Re-Initiated for ".$year." for attached institutions (".$data1['count_institution']." institutions)";
                endif;

                $data1['re_initiate'] = "Re-initiated";
                $data1['link'] = env('APP_URL').'/login';

                $emp_email = Employee::where('emp_level','L'.$reinitvq_normal->level)->get()->toArray();
                
                $data1['email'] =  array_column($emp_email, 'emp_email');
                
                $data1['manager_email'] = array_column($emp_email, 'manager_email');

                $data1['SendApprovalRemainderExport'] = Excel::raw(new SendApprovalReminderExport($year, $reinitvq_normal_vq_ids, $reinitvq_normal->level, 1), BaseExcel::XLSX);
                try{
                    $level = $reinitvq_normal->level;
                    Mail::send('admin.emails.approval_reminder', $data1, function($message)use($data1, $level) {
                        $message->to($data1["email"]);
                        if(!empty($data1["manager_email"]) && !empty(array_filter($data1['manager_email']))):
                            $manager_cc = array_values(array_filter($data1['manager_email']));
                            $message->cc($manager_cc);
                        endif;
                        $message->subject($data1["subject"]);
                        if($data1['count_institution'] > 0){
                            $message->attachData($data1["SendApprovalRemainderExport"], 'pending-institutions-list.xlsx');
                        }
                    });

                }catch(JWTException $exception){
                    $this->serverstatuscode = "0";
                    $this->serverstatusdes = $exception->getMessage();
                }
                if (Mail::failures()) {
                    $this->statusdesc  =   "Error sending mail";
                    $this->statuscode  =   "0";
        
                }else{
                echo "mail sent "; echo "\n";
                // print_r('mail sent');
                Log::debug('Send approver parent vq for L'.$reinitvq_normal->level);
                    $this->statusdesc  =   "Message sent Succesfully";
                    $this->statuscode  =   "1";
                }
            endif;
        }
        
        

        return 0;
    }
    
}
