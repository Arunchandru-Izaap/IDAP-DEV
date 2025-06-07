<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendEmailCEOApproval;//aaded on 31012025
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Mail;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use App\Models\VqInitiateDates;


class CeoApprovalMailRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'ceoapprovalmail_request:daily';
    protected $signature = 'ceoapprovalmail_request {type} {fromdate?} {todate?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs daily for email the failed request details.';

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
        $type = $this->argument('type'); 
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        // $today = Carbon::today();
        // $lastMonth = Carbon::today()->subMonth(); 
        // $prevMonthDate = now()->subMonth()->toDateString();

        // actual concept flow date and months
        $yesterday = now()->subDay()->toDateString(); 
        $prevMonthStart = now()->subMonth()->startOfMonth()->toDateString(); // 1st day of the previous month
        $prevMonthEnd = now()->subMonth()->endOfMonth()->toDateString(); // Last day of the previous month

        // customize the data and month if want check exact date month report
        $previous_date = ($type == 'daily' && $this->argument('fromdate') != '')? $this->argument('fromdate') : $yesterday;
        $custom_form_month_start = ($this->argument('fromdate'))? $this->argument('fromdate') : $prevMonthStart;
        $custom_to_month_end = ($this->argument('todate'))? $this->argument('todate') : $prevMonthEnd;
        if($type == 'daily'):
            $sql = "select `item_code`, `div_id`, `vqsl`.`id` as `vqslid`, `vq`.`id` as `vqid` 
            from `voluntary_quotation` as `vq` 
            left join `voluntary_quotation_sku_listing` as `vqsl` on `vqsl`.`vq_id` = `vq`.`id` 
            where `vqsl`.`discount_percent` >= 30 
            and `vqsl`.`is_deleted` = 0 
            and (`vqsl`.`l8_status` = 1 or `vq`.`current_level` = 8) 
            and `vq`.`vq_status` = 0
            and `vq`.`year` = '$year'
            and `vq`.`is_deleted` = 0 
            and date(`vq`.`updated_at`) = '$previous_date'";
            // Execute the final SQL query
            $checkExceptionsItems = DB::select($sql);
            
        elseif($type == 'monthly'):
            $sql = "select `item_code`, `div_id`, `vqsl`.`id` as `vqslid`, `vq`.`id` as `vqid` 
            from `voluntary_quotation` as `vq` 
            left join `voluntary_quotation_sku_listing` as `vqsl` on `vqsl`.`vq_id` = `vq`.`id` 
            where `vqsl`.`discount_percent` >= 30 
            and `vqsl`.`is_deleted` = 0 
            and (`vqsl`.`l8_status` = 1 or `vq`.`current_level` = 8)
            and `vq`.`year` = '$year'
            and `vq`.`vq_status` = 0
            and `vq`.`is_deleted` = 0 
            and date(`vq`.`updated_at`) >= '$custom_form_month_start'
            and date(`vq`.`updated_at`) <= '$custom_to_month_end'";
            // and `vq`.`updated_at` between '$fromDate' AND '$toDate'";
            // Execute the final SQL query
            $checkExceptionsItems = DB::select($sql);

        endif;
        $vq_ids = array_column($checkExceptionsItems, 'vqid');
        $vqsl_ids = array_column($checkExceptionsItems, 'vqslid');
        if(!empty($vq_ids) && !empty($vqsl_ids)):
            SendEmailCEOApproval::dispatch(8, 'CEO-Approval-level', $type, $previous_date, $custom_form_month_start, $custom_to_month_end);
        endif;
        return 0;
    }
    public function getFinancialYear($inputDate,$format="Y" , $isApril=false){
        $currentDate = date('Y-m-d'); // Get today's date
        $currentMonth = date('m');   // Get the current month (01-12)
        $currentYear = date('Y');    // Get the current year
        // Fetch the VqInitiateDate
        $vqInitiateDate = VqInitiateDates::first();
        $vqInitiateDate = $vqInitiateDate ? $vqInitiateDate->date : null;
        // If financial year is based on April
        if ($isApril) {
            $date = date_create($inputDate);
            if (date_format($date, "m") >= 4) {
                // On or After April (FY is current year - next year)
                $financialYear = date_format($date, $format) . '-' . (date_format($date, $format) + 1);
            } else {
                // On or Before March (FY is previous year - current year)
                $financialYear = (date_format($date, $format) - 1) . '-' . date_format($date, $format);
            }
            return $financialYear;
        }
        // Logic for November - March financial year
        if ($currentMonth >= 11) { // November or later
            if ($vqInitiateDate && $vqInitiateDate <= $currentDate ) {
                // Current date is after or equal to VqInitiateDate, use next financial year
                $financialYear = ($currentYear + 1) . '-' . ($currentYear + 2);
            } else {
                // Current date is before VqInitiateDate, use current financial year
                $financialYear = $currentYear . '-' . ($currentYear + 1);
            }
        } else if($currentMonth >= 01 && $currentMonth < 04){ // January to March
            if ($vqInitiateDate && $vqInitiateDate <= $currentDate) {
                // Current date is before or equal to VqInitiateDate
                $financialYear = $currentYear . '-' . ($currentYear + 1);
            } else {
                // Current date is after VqInitiateDate
                $financialYear = ($currentYear - 1) . '-' . $currentYear;
            }
        }else {
            $financialYear = $currentYear . '-' . ($currentYear + 1);
        }
        return $financialYear;
    }

}
