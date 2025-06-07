<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;

use App\Http\Controllers\StaticPages\VoluntaryQuotationController;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;

use DB;
use Session;
class PendingItemExport
{

    private $id;
    private $type;

    public function __construct($pendingItem, $selected_institutions, $year)
    {
        $this->pendingItem = $pendingItem;
        $this->selected_institutions = json_decode($selected_institutions, TRUE);
        $this->year = $year;
    }

    public function export($filePath)
    {
        // Define headings for the Excel file
        $headings = [
            'ITEM CODE',
            'BRAND NAME',
            'DIV NAME',
            'INSTITUTION ID',
            'HOSPITAL NAME',
            'REV No'
        ];

        // Create a writer instance
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($filePath);

        // Style for the header row
        $headerStyle = (new StyleBuilder())->setFontBold()->build();

        // Add the header row
        $headerRow = WriterEntityFactory::createRowFromArray($headings, $headerStyle);
        $writer->addRow($headerRow);

        // Fetch data from the database
        $data = DB::table('voluntary_quotation_sku_listing as vqsl')
            ->select(
                'vqsl.item_code',
                'vqsl.brand_name',
                'vqsl.div_name',
                'vq.institution_id',
                'vq.hospital_name',
                'vq.rev_no'
            )
            ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
            ->whereIn('vq.institution_id', $this->selected_institutions)
            ->whereIn('vqsl.item_code', $this->pendingItem)
            ->where('vq.year', $this->year)
            ->where('vq.vq_status', 0)
            ->where('vq.is_deleted', 0)
            ->where('vqsl.is_deleted', 0)
            ->orderBy('item_code')
            ->orderBy('hospital_name')
            ->orderBy('rev_no')
            ->get();

        // Process each row and add it to the file
        foreach ($data as $item) {
            $item->rev_no = ($item->rev_no == 0) ? "0" : $item->rev_no;

            $row = WriterEntityFactory::createRowFromArray([
                str_pad($item->item_code, 50), // Add padding to the item code
                str_pad($item->brand_name, 50),
                str_pad($item->div_name, 55),
                str_pad($item->institution_id, 50),
                str_pad($item->hospital_name, 55),
                str_pad($item->rev_no, 55)   
            ]);

            $writer->addRow($row);
        }

        // Close the writer
        $writer->close();
    }

    
}
