<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\VqInitiateDates;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
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
