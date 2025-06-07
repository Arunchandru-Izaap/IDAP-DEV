<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\VoluntaryQuotationSkuListingStockist;
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
class CumulativeReport implements FromCollection,WithHeadings,WithEvents,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $year;
    protected $reporttype;
    protected $brandName;
    protected $divisionName;

    public function __construct($year, $reporttype, $brandName, $divisionName)
    {
        $this->year = $year;
        $this->reporttype = $reporttype;
        $this->brandName = $brandName;
        $this->divisionName = $divisionName;
    }
    public function collection()
    {
        $year = $this->year;
        $brandName = $this->brandName;
        $divisionName = $this->divisionName;

        $divisionNameimplode = implode('","', $this->divisionName);
        $brandNameimplode = implode('","', $this->brandName);
        // print_r($brandNameimplode);die;
        
        // $subquery = "SELECT 
        // MAX(vq.rev_no) as max_rev_no, 
        // vq.institution_id
        // FROM voluntary_quotation as vq
        // LEFT JOIN voluntary_quotation_sku_listing as vqsl 
        // ON vqsl.vq_id = vq.id
        // WHERE 
        // vqsl.div_id IN ('" . implode("','", $this->divisionName) . "')
        // AND vqsl.brand_name IN ('" . implode("','", $this->brandName) . "')
        // AND vqsl.is_deleted = 0
        // AND vq.is_deleted = 0
        // AND vq.year = '".$this->year."'
        // AND vq.vq_status = 1
        // GROUP BY vq.institution_id";

        $sql = 'SELECT 
            vq.hospital_name, 
            vq.institution_id, 
            vq.city, 
            vq.state_name, 
            vq.rev_no, 
            vqsl.div_name, 
            vqsl.mother_brand_name, 
            vqsl.brand_name, 
            vqsl.item_code, 
            vqsl.sap_itemcode,
            vq.id as vq_id, 
            vqsl.id as vqsl_id, 
            -- voluntary_quotation.sap_code, 
            vqsl.discount_percent, 
            vqsl.discount_rate, 
            vqsl.applicable_gst, 
            vqsl.pack, 
            cfa_code, 
            vqsl.mrp, 
            vqsl.ptr, 
            vqsl.mrp_margin, 
            vqsl.product_type, 
            vqsl.hsn_code, 
            vqsl.composition, 
            vq.contract_start_date, 
            vq.contract_end_date, 
            vq.year
        FROM voluntary_quotation as vq
        LEFT JOIN voluntary_quotation_sku_listing as vqsl 
            ON vqsl.vq_id = vq.id
        INNER JOIN (
            SELECT vqsl2.brand_name, vq2.institution_id, MAX(vq2.rev_no) AS max_rev_no
            FROM voluntary_quotation AS vq2
            LEFT JOIN voluntary_quotation_sku_listing AS vqsl2 
                ON vqsl2.vq_id = vq2.id
            WHERE 
                vqsl2.div_id IN ("' . $divisionNameimplode . '")
                ';
                if(!in_array('all', $brandName)):
                    $sql .= ' AND vqsl2.brand_name IN ("' . $brandNameimplode . '")';
                endif;
                $sql .= '
                AND vqsl2.is_deleted = 0
                AND vq2.is_deleted = 0
                AND vq2.year = "'.$year.'"
                AND vq2.vq_status = 1
            GROUP BY vq2.institution_id, vqsl2.brand_name
        ) AS latest_rev
        ON vq.institution_id = latest_rev.institution_id
        AND vq.rev_no = latest_rev.max_rev_no
        AND vqsl.brand_name = latest_rev.brand_name
        WHERE 
            vqsl.div_id IN ("' . $divisionNameimplode . '")';
            if(!in_array('all', $brandName)):
                $sql .= ' AND vqsl.brand_name IN ("' . $brandNameimplode . '")';
            endif;
        $sql .= '
            AND vqsl.is_deleted = 0
            AND vq.is_deleted = 0
            AND vq.year = "'.$year.'"
            AND vq.vq_status = 1';

        // $sql = 'SELECT 
        //     vq.hospital_name, 
        //     vq.institution_id, 
        //     vq.city, 
        //     vq.state_name, 
        //     -- vq.rev_no, 
        //     vqsl.div_name, 
        //     vqsl.mother_brand_name, 
        //     vqsl.brand_name, 
        //     vqsl.item_code, 
        //     vqsl.sap_itemcode,
        //     vq.id as vq_id, 
        //     vqsl.id as vqsl_id, 
        //     vqsl.discount_percent, 
        //     vqsl.discount_rate, 
        //     vqsl.applicable_gst, 
        //     vqsl.pack, 
        //     cfa_code, 
        //     vqsl.mrp, 
        //     vqsl.ptr, 
        //     vqsl.mrp_margin, 
        //     vqsl.product_type, 
        //     vqsl.hsn_code, 
        //     vqsl.composition, 
        //     vq.contract_start_date, 
        //     vq.contract_end_date, 
        //     vq.year,
        //     MAX(vq.rev_no) AS rev_no
        //     FROM voluntary_quotation AS vq
        //     LEFT JOIN voluntary_quotation_sku_listing AS vqsl
        //         ON vqsl.vq_id = vq.id
        //     WHERE vqsl.is_deleted = 0';
        //     if(!in_array('all', $divisionName)):
        //         $sql .= ' AND vqsl.div_id IN ("' . $divisionNameimplode . '")';
        //     endif;
        //     if(!in_array('all', $brandName)):
        //         $sql .= ' AND vqsl.brand_name IN ("' . $brandNameimplode . '")';
        //     endif;
        //     $sql .=' 
        //     AND vq.is_deleted = 0
        //     AND vq.year = "'.$year.'"
        //     AND vq.vq_status = 1
        //     GROUP BY vq.institution_id, vqsl.brand_name';

        if(Session::get("type") == 'poc'):
            $poc_master_institution_id = DB::table('poc_master')->select('institution_id')->where(strtolower(Session::get("emp_type")).'_code', Session::get("emp_code"))->groupBy('institution_id')->orderBy('institution_id', 'ASC')->pluck('institution_id')->toArray();
            $poc_master_institution_id = implode('","', $poc_master_institution_id);
            $sql .= ' AND vq.institution_id IN ("' . $poc_master_institution_id . '")';
        endif;
        if(Session::get("type") == 'distribution'):
            $expolde_session_div_id = implode('","', explode(',',Session::get("division_id")));
            $sql .= ' AND vq.cfa_code IN ("' . $expolde_session_div_id . '")';
        endif;
        // Execute the final SQL query
        $data = DB::select($sql);

        $stockist_margin = Config::select('meta_value')->where('meta_key', 'stockist_margin')->first();
        // Convert to array
        $data = collect($data);
        $data = $data->map(function ($item) use($stockist_margin) {
            $item->rev_no =  ($item->rev_no == 0) ? "0": $item->rev_no;
            $item->applicable_gst =  ($item->applicable_gst == 0) ? "0": $item->applicable_gst;
            $item->discount_percent =  ($item->discount_percent == 0) ? "0": $item->discount_percent;
            $item->discount_rate =  ($item->discount_rate == 0) ? "0": $item->discount_rate;
            $item->ptr =  ($item->ptr == 0) ? "0": $item->ptr;
            $item->mrp_margin =  ($item->mrp_margin == 0) ? "0": $item->mrp_margin;
            
            $stockists = VoluntaryQuotationSkuListingStockist::select(
                'stockist_master.stockist_code',
                'stockist_master.stockist_name',
                'voluntary_quotation_sku_listing_stockist.item_code',
                'voluntary_quotation_sku_listing_stockist.payment_mode',
                'voluntary_quotation_sku_listing_stockist.net_discount_percent')
            ->leftJoin('stockist_master','stockist_master.id','=','voluntary_quotation_sku_listing_stockist.stockist_id')
            ->where('voluntary_quotation_sku_listing_stockist.vq_id',$item->vq_id)
            ->where('voluntary_quotation_sku_listing_stockist.sku_id',$item->vqsl_id)
            ->where('voluntary_quotation_sku_listing_stockist.is_deleted', 0)->get();

            $item->stockist_codes = $stockists->pluck('stockist_code')->implode(', ');
            $item->stockist_names = $stockists->pluck('stockist_name')->implode(', ');
            $item->payment_modes = $stockists->pluck('payment_mode')->map(fn($mode) => $mode ?? '')->implode(', ');
            $item->net_discounts = $stockists->pluck('net_discount_percent')->implode(', ');

            $itemArray = (array) $item;
            unset($itemArray['vq_id']);
            unset($itemArray['vqsl_id']);
            unset($itemArray['max_rev_no']);
            $insertIndex = array_search('discount_rate', array_keys($itemArray)) + 1;

            $newColumns = [
                'stockist_codes'  => $item->stockist_codes,
                'stockist_names'  => $item->stockist_names,
                'payment_modes'   => $item->payment_modes,
                'net_discounts'   => $item->net_discounts,
            ];
            $itemArray = array_slice($itemArray, 0, $insertIndex, true) +
                $newColumns +
                array_slice($itemArray, $insertIndex, null, true);

            return $itemArray;
        });
        // print_r($data);die;
        return $data;
    }
    public function headings(): array
    {
        return[ 
            [
                'Hospital name', 'Institution Code', 'City', 'State', 'Revision no', 'Division Name', 'Mother Brand Name', 'Brand Name',  'Item Code', 'SAP Code', 'Disc. PTR (%)', 'RTH (Excl. GST)', 'Stockist Code', 'Stockist Name', 'Mode of discount', 'Net discount' , 'App. GST(%)',  'Pack', 'CFA Code', 'MRP', 'C. Y. PTR', 'MRP Margin (%)', 'Product Type', 'HSN Code',  'Composition', 'Contract Start Date', 'Contract End Date', 'VQ Year'
            ],
        ];//24
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
            'M'=>35,  
            'N'=>75,  
            'O'=>15,  
            'P'=>15,  
            'Q'=>15,  
            'R'=>15,  
            'S'=>15,  
            'T'=>15,  
            'U'=>15,  
            'V'=>25, 
            'W'=>25,
            'X'=>15,
            'Y'=>15,
            'Z'=>15,
            'AA'=>15,
            'AB'=>15
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:AB1')
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFFF00');
            },
        ];
    
    }
}
