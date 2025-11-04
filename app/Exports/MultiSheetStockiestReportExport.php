<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetStockiestReportExport implements WithMultipleSheets
{
    private $id;
    private $stockist;

    public function __construct($id, $stockist)
    {
        $this->id = $id;
        $this->stockist = $stockist;
    }
    public function sheets(): array
    {
        $sheets = [];

        foreach($this->stockist as $stockist):
            //dd($stockist);
            if($stockist['stockist_type_flag'] == 1):
                $stockist_id = $stockist['id'];
                $institution_code = $stockist['institution_code'];
                print('-');
                print_r($institution_code);
                print('-');
                $stockist_name = $stockist['stockist_name'];
                $stockist_code = $stockist['stockist_code'];
                // You can add as many sheets as you want here
                $sheets[] = new StockiestWiseCumulativeReportExport($this->id, $stockist_id, $stockist_name, $stockist_code);
            endif;
        endforeach;

        return $sheets;
    }
}
