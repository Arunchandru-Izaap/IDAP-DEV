<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\VoluntaryQuotation;
use App\Models\Employee;
use App\Models\VoluntaryQuotationSkuListing;
use Carbon\Carbon;
use DB;
use App\Http\Controllers\Api\VqListingController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Exports\CeoApprovalExport;
use Maatwebsite\Excel\Excel as BaseExcel;
use Excel;

class SendEmailCEOApproval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $mail_levels;
    protected $action;
    protected $report_type;
    protected $previous_date;
    protected $custom_month_start;
    protected $custom_month_end;
    public $timeout = 999999;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mail_levels, $action, $report_type, $previous_date, $custom_month_start, $custom_month_end)
    {
        $this->mail_levels = $mail_levels;
        $this->action = $action;
        $this->report_type = $report_type;
        $this->previous_date = $previous_date;
        $this->custom_month_start = $custom_month_start;
        $this->custom_month_end = $custom_month_end;
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

        $level_names = [
            1 => 'RSM',
            2 => 'ZSM',
            3 => 'NSM',
            4 => 'SBU',
            5 => 'Semi Cluster',
            6 => 'Cluster',
            7 => 'Initiator',
            8 => 'CEO'
        ];
        if (isset($level_names[$this->mail_levels])) {
            $level_name = $level_names[$this->mail_levels];
        } else {
            $level_name = $this->mail_levels;
        }
        print_r($this->report_type);
        $data['report_type'] = $this->report_type;
        // $data['prevMonthStart'] = now()->subMonth()->startOfMonth()->toDateString(); // 1st day of the previous month
        // $data['prevMonthEnd'] = now()->subMonth()->endOfMonth()->toDateString(); // Last day of the previous month
        $data['yesterday_date'] = $this->previous_date;
        $data['prevMonthStart'] = $this->custom_month_start;
        $data['prevMonthEnd'] = $this->custom_month_end;
        
        if($this->report_type == 'daily'):
            $yesterday = $data['yesterday_date'];
            $sql = "select `item_code`, `div_id`, `vqsl`.`id` as `vqslid`, `vq`.`id` as `vqid` 
            from `voluntary_quotation` as `vq` 
            left join `voluntary_quotation_sku_listing` as `vqsl` on `vqsl`.`vq_id` = `vq`.`id` 
            where `vqsl`.`discount_percent` >= 30 
            and `vqsl`.`is_deleted` = 0 
            and (`vqsl`.`l8_status` = 1 or `vq`.`current_level` = 8) 
            and `vq`.`year` = '$year'
            and `vq`.`vq_status` = 0
            and `vq`.`is_deleted` = 0 
            and date(`vq`.`updated_at`) = '$yesterday'"; //and `vq`.`current_level` = 8 
            // Execute the final SQL query
            $checkExceptionsItems = DB::select($sql);
            
        elseif($this->report_type == 'monthly'):
            $prevMonthStart = $data['prevMonthStart'];
            $prevMonthEnd = $data['prevMonthEnd'];
            $sql = "select `item_code`, `div_id`, `vqsl`.`id` as `vqslid`, `vq`.`id` as `vqid` 
            from `voluntary_quotation` as `vq` 
            left join `voluntary_quotation_sku_listing` as `vqsl` on `vqsl`.`vq_id` = `vq`.`id` 
            where `vqsl`.`discount_percent` >= 30 
            and `vqsl`.`is_deleted` = 0 
            and (`vqsl`.`l8_status` = 1 or `vq`.`current_level` = 8)
            and `vq`.`year` = '$year'
            and `vq`.`vq_status` = 0
            and `vq`.`is_deleted` = 0 
            and date(`vq`.`updated_at`) >= '$prevMonthStart'
            and date(`vq`.`updated_at`) <= '$prevMonthEnd'";
            // and `vq`.`updated_at` between '$prevMonthStart' AND '$prevMonthEnd'";
            // Execute the final SQL query
            $checkExceptionsItems = DB::select($sql);
        endif;
        $vq_ids = array_column($checkExceptionsItems, 'vqid');
        $vqsl_ids = array_column($checkExceptionsItems, 'vqslid');

        print_r(count($vq_ids));

        $data['email']=array();
        $data['email_cc']=array();
       
        $toEmails = DB::table('email_configurations')
            ->where('email_type', 'TO')
            ->where('status', 'ACTIVE')
            ->where('used_for', 'ceo_approval')
            ->pluck('email_address')
            ->toArray();

        $ccEmails = DB::table('email_configurations')
            ->where('email_type', 'CC')
            ->where('status', 'ACTIVE')
            ->where('used_for', 'ceo_approval')
            ->pluck('email_address')
            ->toArray();
 
        // mail id's
        $data['email'] = $toEmails;
        $data['email_cc'] = $ccEmails;


        if($this->report_type == 'daily'):
            $data['yesterday_date_formated'] =  date("d-M-Y", strtotime($data['yesterday_date']));
            $excel_file_name = 'CEO-Approval-Level-Report-'.$data['yesterday_date_formated'].'-'.date('His').'.xlsx'; // filename for excel
            $data['subject'] = 'Daily Institution report (iDAP) send for CEO Approval as on '.$data['yesterday_date_formated'];  // Mail subject
        elseif($this->report_type == 'monthly'):
            $data['prevMonthStart_formated'] = date("d-m-Y", strtotime($data['prevMonthStart']));
            $data['prevMonthEnd_formated'] = date("d-m-Y", strtotime($data['prevMonthEnd']));
            // $data['lastMonthYear'] = Carbon::now()->subMonth()->format('MMM-Y');
            $data['lastMonthYear'] = date("M-Y", strtotime($data['prevMonthStart']));
            $excel_file_name = 'CEO-Approval-Level-Report-'.$data['lastMonthYear'].'-'.date('His').'.xlsx'; // filename for excel
            $data['subject'] = 'Monthly Institution report (iDAP) send for CEO Approval as on '.$data['lastMonthYear'];  // Mail subject
        else: // this condition it's rare case 
            $excel_file_name = 'CEO-Approval-Level-Report.xlsx'; // filename for excel
            $data["subject"]= "Institution report (iDAP) send for CEO Approval"; // Mail subject
        endif;
        
        $ceolevelExport = Excel::raw(new CeoApprovalExport($year, $vq_ids, $vqsl_ids, $this->action, $level_name, $this->report_type), BaseExcel::XLSX);
        print_r("Export Data");
        try{
            if(count($vq_ids) > 0):
                Mail::send('admin.emails.ceo_level_reports', $data, function($message)use($data, $ceolevelExport, $excel_file_name, $vq_ids) {
                    $message->to($data['email'])
                    ->subject($data["subject"])
                    ->cc($data['email_cc']);
                    if(count($vq_ids) > 0){
                        $message->attachData($ceolevelExport, $excel_file_name);
                    }
                });
                print_r("Mail Ready to sent");
            endif;
        }catch(JWTException $exception){
            $this->serverstatuscode = "0";
            $this->serverstatusdes = $exception->getMessage();
        }
        if (Mail::failures()) {
            $this->statusdesc  =   "Error sending mail";
            $this->statuscode  =   "0";
        }else{
            print_r("mail sent");
            $this->statusdesc  =   "Message sent Succesfully";
            $this->statuscode  =   "1";
        }
        
    }
}
