<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\Config;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\AfterSheet;

use Maatwebsite\Excel\Concerns\WithStyles;

use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Session;
use DB;
use App\Http\Controllers\StaticPages\VoluntaryQuotationController;//added to get the financial year 
class CeoApprovalExport implements FromCollection,WithHeadings,WithEvents,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $year;
    protected $vqids;
    protected $vqsl_ids;
    protected $button_action;
    protected $level_name;
    protected $report_type;

    public function __construct($year, $vqids, $vqsl_ids, $button_action, $level_name, $report_type)
    {
        $this->year = $year;
        $this->button_action = $button_action;
        $this->vqids = $vqids;
        $this->vqsl_ids = $vqsl_ids;
        $this->level_name = $level_name;
        $this->report_type = $report_type;
    }
    public function collection()
    {
        $query = VoluntaryQuotationSkuListing::select([
            'vq.hospital_name',
            'vq.institution_id',
            'vq_sku.sap_itemcode',
            'vq.city',
            'vq.state_name',
            'vq.zone',
            'vq.year',
            'vq_sku.item_code',
            'vq.sap_code',
            'vq_sku.brand_name',
            'vq_sku.mother_brand_name',
            'vq_sku.hsn_code',
            'vq_sku.div_name',
            'vq_sku.applicable_gst',
            'vq_sku.product_type',
            'vq_sku.div_id',
            'vq_sku.pack',
            'vq_sku.last_year_percent',
            'vq_sku.last_year_rate',
            'vq_sku.last_year_mrp',
            'vq_sku.mrp',
            'vq_sku.ptr',
            'vq_sku.discount_percent',
            'vq_sku.discount_rate',
            'vq_sku.mrp_margin',
            'vq.rev_no',
            'vq.vq_status',
            'vq.id as vq_id',
            'vq_sku.id as sku_id',
            'vq_sku.composition'
        ])
        ->from('voluntary_quotation_sku_listing as vq_sku')
        ->join('voluntary_quotation as vq', 'vq_sku.vq_id', '=', 'vq.id')
        // ->where('vq.current_level', 8)
        // ->orWhere('vq_sku.l8_status', 1)
        ->where('vq.year', $this->year)
        ->where('vq.vq_status', 0)
        ->where('vq.is_deleted', 0)
        ->where('vq_sku.discount_percent','>=',30)
        ->where('vq_sku.is_deleted', 0)
        ->whereIn('vq_sku.vq_id', $this->vqids)
        ->whereIn('vq_sku.id', $this->vqsl_ids);
        $stockist_margin = Config::select('meta_value')->where('meta_key', 'stockist_margin')->first();
        $data = $query->get();
        $data = $data->map(function ($item) use($stockist_margin) {
            $lastYearMrp = $item->last_year_mrp;
            $lastYearPtr = $item->last_year_ptr;
            $result = $lastYearMrp != null && $lastYearPtr != null ? round((($lastYearMrp - $lastYearPtr) / $lastYearMrp) * 100 ,2) : "-";
            $item->last_year_percent =  ($item->last_year_percent == null|| $item->last_year_percent == 0) ? "0": $item->last_year_percent;
            $item->last_year_rate =  ($item->last_year_rate == null || $item->last_year_rate == 0) ? "0": $item->last_year_rate;
            $item->last_year_mrp =  ($item->last_year_mrp == null || $item->last_year_mrp == 0) ? "0": $item->last_year_mrp;
            $item->rev_no =  ($item->rev_no == 0) ? "0": $item->rev_no;
            $item->vq_status =  ($item->vq_status == 0) ? "0": $item->vq_status;
            $item->applicable_gst =  ($item->applicable_gst == 0) ? "0": $item->applicable_gst;
            $item->discount_percent =  ($item->discount_percent == 0) ? "0": $item->discount_percent;
            $item->discount_rate =  ($item->discount_rate == 0) ? "0": $item->discount_rate;
            $item->ptr =  ($item->ptr == 0) ? "0": $item->ptr;
            $item->mrp_margin =  ($item->mrp_margin == 0) ? "0": $item->mrp_margin;
            $itemArray = $item->toArray();
            $composition = $itemArray['composition'];
            unset($itemArray['composition']);
            $itemArray['composition'] = $composition;

            return $itemArray;
        });
        return $data;
    }
    public function headings(): array
    {
        return[ 
            [
                'Hospital name', 'Institution Code', 'SAP Itemcode', 'City', 'State', 'Zone', 'YEAR', 'Item Code', 'SAP Code', 'Brand Name', 'Mother Brand Name', 'HSN Code', 'Division Name', 'Applicable GST', 'TYPE', 'Division ID',	'Pack', 'Last Year Percent', 'Last Year Rate',	'Last Year MRP', 'Current MRP', 'Current PTR', 'Discount Percent','Discount Rate', 'MRP Margin', 'Revision no', 'VQ status', 'VQ ID', 'SKU ID', 'Composition'
            ],
        ];//30
    }
    public function columnWidths(): array
    {
        return [
            'A'=>40,
            'B'=>10,
            'C'=>10,
            'D'=>20,
            'E'=>30,
            'F'=>30,
            'G'=>15,
            'H'=>15,
            'I'=>20,
            'J'=>15,  
            'K'=>15,  
            'L'=>15,  
            'M'=>15,  
            'N'=>15,  
            'O'=>15,  
            'P'=>15,  
            'Q'=>15,  
            'R'=>15,  
            'S'=>15,  
            'T'=>15,  
            'U'=>15,  
            'V'=>15, 
            'W'=>15,
            'X'=>15,         
            'Y'=>15, 
            'Z'=>15,
            'AA'=>15, 
            'AB'=>15, 
            'AC'=>15,
            'AD'=>75,  
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:AD1')
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFFF00');
            },
        ];
    
    }
}
