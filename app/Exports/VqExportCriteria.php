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
class VqExportCriteria implements FromCollection,WithHeadings,WithEvents,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $year;
    protected $status;
    protected $criteria;
    protected $institutionNames;
    protected $clusters;

    public function __construct($year, $status, $criteria, $institutionNames, $clusters)
    {
        $this->year = $year;
        $this->status = $status;
        $this->criteria = $criteria;
        $this->institutionNames = $institutionNames;
        $this->clusters = $clusters;
    }
    public function collection()
    {

        $query = VoluntaryQuotationSkuListing::select('hospital_name', 'sap_code', 'rev_no', 'city','div_name','mother_brand_name','sap_itemcode','brand_name','item_code', 'discount_percent', 'discount_rate','applicable_gst', 'last_year_percent', 'last_year_rate', 'last_year_mrp','mrp',  'ptr', 'mrp_margin','composition', 'last_year_ptr')
        ->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
        ->where('year', $this->year)->where('voluntary_quotation_sku_listing.is_deleted',0)->where('voluntary_quotation.is_deleted', 0);
        if($this->status == 'pending')
        {
            $query->where('current_level', preg_replace('/[^0-9.]+/', '', Session::get("level")))->where('voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status',0);
        }
        elseif($this->status == 'approved')
        {
            $query->where('voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status',1);
        }
        else
        {
            $query->whereIn('current_level', [7,preg_replace('/[^0-9.]+/', '', Session::get("level"))])->whereIn('voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status',[1,0]);
        }
        if (!empty($this->institutionNames) && !in_array('all', $this->institutionNames)) {
            $query->whereIn('institution_id', $this->institutionNames);
        }

        if (!empty($this->clusters) && !in_array('all', $this->clusters)) {
            $div_codes = DB::table('cluster')->select('div_code')->where('is_deleted',0)->whereIn('cluster',$this->clusters)->pluck('div_code');
            $query->whereIn('div_id', $div_codes);
        }
        if ($this->criteria && count($this->criteria) > 0) {
            $criteria = $this->criteria;
            $criteria = array_map('urldecode', $this->criteria);
            $criteria = array_map('trim', $criteria);
            $query->where(function ($details) use ($criteria) {
                foreach ($criteria as $condition) {
                    if (preg_match('/^>=(\d+)$/', $condition, $matches)) {
                        $details->orWhere('discount_percent', '>=', (int)$matches[1]);
                    } elseif (preg_match('/^>(\d+)$/', $condition, $matches)) {
                        $details->orWhere('discount_percent', '>', (int)$matches[1]);
                    } elseif (preg_match('/between (\d+) and (\d+)$/', $condition, $matches)) {
                        $details->orWhereBetween('discount_percent', [(int)$matches[1], (int)$matches[2]]);
                    } elseif (preg_match('/^<=(\d+)$/', $condition, $matches)) {
                        $details->orWhere('discount_percent', '<=', (int)$matches[1]);
                    } elseif (preg_match('/^<(\d+)$/', $condition, $matches)) {
                        $details->orWhere('discount_percent', '<', (int)$matches[1]);
                    } elseif (preg_match('/^=(\d+)$/', $condition, $matches)) {
                        $details->orWhere('discount_percent', '=', (int)$matches[1]);
                    } else {
                       
                    }
                }
            });
        }
        $stockist_margin = Config::select('meta_value')->where('meta_key', 'stockist_margin')->first();
        $data = $query->limit(2500)->get();

        $data = $data->map(function ($item) use($stockist_margin) {
            if ($item->discount_rate !== null  && $item->discount_rate !== '') {
                $calculated_data = $item->discount_rate - (($item->discount_rate*$stockist_margin->meta_value)/100);
                if(round($calculated_data,2) < 0.1) $item->billing_price_direct =  round($calculated_data,2);
                else $item->billing_price_direct = round($calculated_data,2);
            } else {
                $item->billing_price_direct =  "0";
            }
            $item->billing_price_credit = ($item->discount_rate == 0) ? "0": $item->discount_rate;

            $lastYearMrp = $item->last_year_mrp;
            $lastYearPtr = $item->last_year_ptr;
            $result = $lastYearMrp != null && $lastYearPtr != null ? round((($lastYearMrp - $lastYearPtr) / $lastYearMrp) * 100 ,2) : "-";
            $item->lastYearPercentMargin =  $result;
            $item->last_year_percent =  ($item->last_year_percent == null|| $item->last_year_percent == 0) ? "0": $item->last_year_percent;
            $item->last_year_rate =  ($item->last_year_rate == null || $item->last_year_rate == 0) ? "0": $item->last_year_rate;
            $item->last_year_mrp =  ($item->last_year_mrp == null || $item->last_year_mrp == 0) ? "0": $item->last_year_mrp;
            $item->rev_no =  ($item->rev_no == 0) ? "0": $item->rev_no;
            $item->applicable_gst =  ($item->applicable_gst == 0) ? "0": $item->applicable_gst;
            $item->discount_percent =  ($item->discount_percent == 0) ? "0": $item->discount_percent;
            $item->discount_rate =  ($item->discount_rate == 0) ? "0": $item->discount_rate;
            $item->ptr =  ($item->ptr == 0) ? "0": $item->ptr;
            $item->mrp_margin =  ($item->mrp_margin == 0) ? "0": $item->mrp_margin;
            $item->billing_price_direct =  ($item->billing_price_direct == 0.0) ? "0": $item->billing_price_direct;
            $itemArray = $item->toArray();
            $composition = $itemArray['composition'];
            unset($itemArray['composition']);
            unset($itemArray['last_year_ptr']);
            $itemArray['composition'] = $composition;

            return $itemArray;
        });
        return $data;
    }
    public function headings(): array
    {
        return[ 
        [
            'Hospital Name', 'SAP Code', 'Revision no', 'City','Division Name','Mother Brand Name','SAP Itemcode','Brand Name','Item Code', 'Discount Percent', 'Discount Rate','Applicable GST', 'Last Year Percent', 'Last Year Rate', 'Last Year MRP','MRP',  'PTR', 'MRP Margin','Billing price(Direct Master)','Billing price(Credit Note)','Last year % Margin on MRP','Composition'
        ],];
    }
    public function columnWidths(): array
    {
        return [
            'A' =>40,
            'B' => 20,
            'C' => 10,
            'D' =>20,
            'E'=>30,
            'F'=>30,
            'G'=>15,
            'H'=>45,
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
            'U'=>25,  
            'V'=>75,           
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:V1')
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFFF00');
            },
        ];
    
    }
}
