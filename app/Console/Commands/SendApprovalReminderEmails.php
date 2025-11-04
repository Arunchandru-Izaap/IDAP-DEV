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
                $vq_mail_sent_status = 0;
                $maildata['year'] = $year;
                $maildata['re_initiate'] = "initiated";
                $maildata['link'] = env('APP_URL').'/login';
                $Employee_email_details = Employee::where('emp_level','L'.$schedule->level)->where('emp_category','approver')->get()->toArray();
                foreach($Employee_email_details as $Employee_email):
                    $div_code =  $Employee_email['div_code'];
                    $get_div_code_vq_ids = VoluntaryQuotation::select('voluntary_quotation.id')
                        ->where('voluntary_quotation.current_level', $schedule->level)
                        ->where('voluntary_quotation.year',$year)
                        ->where('voluntary_quotation.parent_vq_id',0)
                        ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
                        ->whereIn('voluntary_quotation.id', $vq_ids)
                        ->where('voluntary_quotation_sku_listing.div_id', $div_code)
                        ->where('voluntary_quotation_sku_listing.is_deleted', 0)
                        ->where('voluntary_quotation.is_deleted', 0)->get()->toArray();

                    $div_code_vq_ids = (!empty($get_div_code_vq_ids)) ? array_values(array_unique(array_column($get_div_code_vq_ids, 'id'))) : array();
                    
                    if(!empty($div_code_vq_ids)):
                        $maildata['count_institution'] = count($div_code_vq_ids);
                        if($maildata['count_institution'] == 1):
                            $single_vq = VoluntaryQuotation::where('current_level', $schedule->level)
                                ->whereIn('id', $div_code_vq_ids)
                                ->where('year', $year)
                                ->where('is_deleted', 0)
                                ->first();
                            $maildata["subject"] = "Action Required: iDAP VQ Process Initiated for ".$year."  for ".$single_vq->hospital_name."  (".$single_vq->institution_id.")";
                        else:
                            $maildata["subject"] = "Action Required: iDAP VQ Process Initiated for ".$year." for attached institutions (".$maildata['count_institution']." institutions)";
                        endif;
                        // print_r(count($div_code_vq_ids)); echo "\n";
                        $maildata['emp_email'] =  $Employee_email['emp_email'];
                        $maildata['manager_email'] =  $Employee_email['manager_email'];
                        $maildata['SendApprovalRemainderExport'] = Excel::raw(new SendApprovalReminderExport($year, $div_code_vq_ids, $schedule->level, 1), BaseExcel::XLSX);
                        try{
                            $level = $schedule->level;
                            Mail::send('admin.emails.approval_reminder', $maildata, function($message)use($maildata, $level) {
                                $message->to($maildata["emp_email"]);
                                if(!empty($maildata["manager_email"])):
                                    $manager_cc = $maildata['manager_email'];
                                    $message->cc($manager_cc);
                                endif;
                                $message->subject($maildata["subject"]);
                                if($maildata['count_institution'] > 0){
                                    $message->attachData($maildata["SendApprovalRemainderExport"], 'pending-institutions-list.xlsx');
                                }
                            });
                        }catch(JWTException $exception){
                            $this->serverstatuscode = "0";
                            $this->serverstatusdes = $exception->getMessage();
                        }
                        if (!Mail::failures()) {
                            $vq_mail_sent_status++;
                            $this->statusdesc  =   "Message sent Succesfully";
                            $this->statuscode  =   "1";
                        }
                    endif;
                endforeach;
                if($vq_mail_sent_status > 0):
                    echo "mail sent "; echo "\n";
                    Log::debug('Send approver parent vq for L'.$schedule->level);
                endif;
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
                $vq_fast_mail_sent_status = 0;
                $maildata['year'] = $year;
                $maildata['re_initiate'] = "Re-initiated";
                $maildata['link'] = env('APP_URL').'/login';
                $Employee_email_details = Employee::where('emp_level','L'.$reinitvq_fast->level)->where('emp_category','approver')->get()->toArray();
                foreach($Employee_email_details as $Employee_email):
                    $div_code =  $Employee_email['div_code'];
                    $get_div_code_vq_ids = VoluntaryQuotation::select('voluntary_quotation.id')
                        ->where('current_level', $reinitvq_fast->level)
                        ->where('year',$year)
                        ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
                        ->whereIn('voluntary_quotation.id', $reinitvq_fast_vq_ids)
                        ->where('voluntary_quotation_sku_listing.div_id', $div_code)
                        ->where('voluntary_quotation_sku_listing.is_deleted', 0)
                        ->where('voluntary_quotation.is_deleted', 0)->get()->toArray();

                    $reinitvq_fast_div_code_vq_ids = (!empty($get_div_code_vq_ids)) ? array_values(array_unique(array_column($get_div_code_vq_ids, 'id'))) : array();
                    
                    // print_r(count($reinitvq_fast_div_code_vq_ids)); echo "\n";
                    if(!empty($reinitvq_fast_div_code_vq_ids)):
                        $maildata['count_institution'] = count($reinitvq_fast_div_code_vq_ids);
                        if($maildata['count_institution'] == 1):
                            $single_vq = VoluntaryQuotation::where('current_level', $reinitvq_fast->level)
                                ->whereIn('id', $reinitvq_fast_div_code_vq_ids)
                                ->where('year', $year)
                                ->where('is_deleted', 0)
                                ->first();
                            $maildata["subject"] = "Action Required: iDAP VQ Process Re-Initiated for ".$year."  for ".$single_vq->hospital_name."  (".$single_vq->institution_id.")";
                        else:
                            $maildata["subject"] = "Action Required: iDAP VQ Process Re-Initiated for ".$year." for attached institutions (".$maildata['count_institution']." institutions)";
                        endif;
                        $maildata['emp_email'] =  $Employee_email['emp_email'];
                        $maildata['manager_email'] =  $Employee_email['manager_email'];
                        $maildata['SendApprovalRemainderExport'] = Excel::raw(new SendApprovalReminderExport($year, $reinitvq_fast_div_code_vq_ids, $reinitvq_fast->level, 1), BaseExcel::XLSX);
                        try{
                            $level = $reinitvq_fast->level;
                            Mail::send('admin.emails.approval_reminder', $maildata, function($message)use($maildata, $level) {
                                $message->to($maildata["emp_email"]);
                                if(!empty($maildata["manager_email"])):
                                    $manager_cc = $maildata['manager_email'];
                                    $message->cc($manager_cc);
                                endif;
                                $message->subject($maildata["subject"]);
                                if($maildata['count_institution'] > 0){
                                    $message->attachData($maildata["SendApprovalRemainderExport"], 'pending-institutions-list.xlsx');
                                }
                            });
                        }catch(JWTException $exception){
                            $this->serverstatuscode = "0";
                            $this->serverstatusdes = $exception->getMessage();
                        }
                        if (!Mail::failures()) {
                            $vq_fast_mail_sent_status++;
                            $this->statusdesc  =   "Message sent Succesfully";
                            $this->statuscode  =   "1";
                        }
                    endif;
                endforeach;
                if($vq_fast_mail_sent_status > 0):
                    echo "mail sent "; echo "\n";
                    Log::debug('Send approver parent vq for L'.$reinitvq_fast->level);
                endif;
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
                    
                    // Check if days passed is more than start_days
                    if ($daysPending <= $reinitvq_normal->days) {
                        $reinitvq_normal_vq_ids[] = $vq->id;
                    }
                }
            endif;
            if(!empty($reinitvq_normal_vq_ids)):
                $vq_normal_mail_sent_status = 0;
                $maildata['year'] = $year;
                $maildata['re_initiate'] = "Re-initiated";
                $maildata['link'] = env('APP_URL').'/login';
                
                $Employee_email_details = Employee::where('emp_level','L'.$reinitvq_normal->level)->where('emp_category','approver')->get()->toArray();
                foreach($Employee_email_details as $Employee_email):
                    $div_code =  $Employee_email['div_code'];
                    $get_div_code_vq_ids = VoluntaryQuotation::select('voluntary_quotation.id')
                        ->where('current_level', $reinitvq_normal->level)
                        ->where('year',$year)
                        ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
                        ->whereIn('voluntary_quotation.id', $reinitvq_normal_vq_ids)
                        ->where('voluntary_quotation_sku_listing.div_id', $div_code)
                        ->where('voluntary_quotation_sku_listing.is_deleted', 0)
                        ->where('voluntary_quotation.is_deleted', 0)->get()->toArray();

                    $reinitvq_normal_div_code_vq_ids = (!empty($get_div_code_vq_ids)) ? array_values(array_unique(array_column($get_div_code_vq_ids, 'id'))) : array();
                    // print_r(count($reinitvq_normal_div_code_vq_ids)); echo "\n";
                    if(!empty($reinitvq_normal_div_code_vq_ids)):
                        $maildata['count_institution'] = count($reinitvq_normal_div_code_vq_ids);
                        if($maildata['count_institution'] == 1):
                            $single_vq = VoluntaryQuotation::where('current_level', $reinitvq_normal->level)
                                ->whereIn('id', $reinitvq_normal_div_code_vq_ids)
                                ->where('year', $year)
                                ->where('is_deleted', 0)
                                ->first();
                            $maildata["subject"] = "Action Required: iDAP VQ Process Re-Initiated for ".$year."  for ".$single_vq->hospital_name."  (".$single_vq->institution_id.")";
                        else:
                            $maildata["subject"] = "Action Required: iDAP VQ Process Re-Initiated for ".$year." for attached institutions (".$maildata['count_institution']." institutions)";
                        endif;
                        $maildata['emp_email'] =  $Employee_email['emp_email'];
                        $maildata['manager_email'] =  $Employee_email['manager_email'];
                        $maildata['SendApprovalRemainderExport'] = Excel::raw(new SendApprovalReminderExport($year, $reinitvq_normal_div_code_vq_ids, $reinitvq_normal->level, 1), BaseExcel::XLSX);
                        try{
                            $level = $reinitvq_normal->level;
                            Mail::send('admin.emails.approval_reminder', $maildata, function($message)use($maildata, $level) {
                                $message->to($maildata["emp_email"]);
                                if(!empty($maildata["manager_email"])):
                                    $manager_cc = $maildata['manager_email'];
                                    $message->cc($manager_cc);
                                endif;
                                $message->subject($maildata["subject"]);
                                if($maildata['count_institution'] > 0){
                                    $message->attachData($maildata["SendApprovalRemainderExport"], 'pending-institutions-list.xlsx');
                                }
                            });
                        }catch(JWTException $exception){
                            $this->serverstatuscode = "0";
                            $this->serverstatusdes = $exception->getMessage();
                        }
                        if (!Mail::failures()) {
                            $vq_normal_mail_sent_status++;
                            $this->statusdesc  =   "Message sent Succesfully";
                            $this->statuscode  =   "1";
                        }
                    endif;
                endforeach;
                if($vq_normal_mail_sent_status > 0):
                    echo "mail sent "; echo "\n";
                    Log::debug('Send approver parent vq for L'.$reinitvq_normal->level);
                endif;
            endif;
        }
        
        
        return 0;
    }
    
}
