<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\VqInitiateDates;

class max_rev extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'max_rev:hourly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs every hour to update the max_rev table and z_latest_rev_ptr';

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
        // Empty the table
        DB::table('z_max_rev')->truncate();
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        DB::statement("
            INSERT INTO z_max_rev (max_rev_no, item_code, institution_id)
            SELECT 
                MAX(v2.rev_no) AS max_rev_no,
                s.item_code,
                v2.institution_id
            FROM 
                voluntary_quotation_sku_listing AS s 
            LEFT JOIN 
                voluntary_quotation AS v2 ON v2.id = s.vq_id 
            WHERE 
                v2.year = '".$year."' 
                AND s.is_deleted = 0 
                AND v2.is_deleted = 0
                AND v2.vq_status = 1 
            GROUP BY 
                s.item_code, v2.institution_id
        ");
        // Empty the table
        /*DB::table('z_latest_rev_ptr')->truncate();
        DB::statement("INSERT INTO `z_latest_rev_ptr` (vq_id,discount_percent, discount_rate, mrp_margin, institution_id, item_code)
            select voluntary_quotation.id,`discount_percent`, `discount_rate`, `mrp_margin` ,voluntary_quotation.institution_id ,voluntary_quotation_sku_listing.item_code
            from `voluntary_quotation_sku_listing` 
            left join `voluntary_quotation` on `voluntary_quotation_sku_listing`.`vq_id` = `voluntary_quotation`.`id` 
            inner JOIN z_max_rev AS max_rev 
            ON max_rev.item_code = voluntary_quotation_sku_listing.item_code 
            AND max_rev.institution_id = voluntary_quotation.institution_id 
            AND max_rev.max_rev_no = voluntary_quotation.rev_no where year = '".$year."'
        ");*///commented not used 14052024
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
