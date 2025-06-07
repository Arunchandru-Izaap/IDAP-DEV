<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\IValueBinder;
use App\Http\Controllers\StaticPages\VoluntaryQuotationController;
use DB;
use Session;
class InitiatorExportNew extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths, IValueBinder
{
    use Exportable;

    private $id;
    private $type;

    public function __construct($id, $type)
    {
        $this->id = $id;
        $this->type = $type;
    }

    public function headings(): array
    {
        return [
            'REVISION NUMBER',
            'SAP CODE',
            'METIS CODE',
            'BRAND NAME',
            'HSN CODE',
            'APPLICABLE GST',
            'COMPOSITION',
            'TYPE',
            'DIVNAME',
            'PACK',
            'DISCOUNT %',
            'RATE TO HOSPITAL (EXCL. OF GST)',
            'MRP (Including GST)',
            '% MARGIN ON MRP'
        ];
    }

    public function collection()
    {
        $vq_controller = new VoluntaryQuotationController;
        $year = $vq_controller->getFinancialYear(date('Y-m-d'),"Y");
        $fetchVQ = VoluntaryQuotation::select('institution_id')->where('id',$this->id)->first();
        $divId = Employee::select('div_code')->where('div_type', $this->type)->whereNotNull('div_code')
        ->when(Session::get('type') == 'approver', function ($query) {
            return $query->whereIn('div_code', explode(',', Session::get('division_id')));
        })
        ->pluck('div_code')->toArray();
        $uniqueDivId = array_unique($divId);
        $maxRevSubquery = DB::table('voluntary_quotation_sku_listing as s')
        ->select(DB::raw('MAX(v2.rev_no) AS max_rev_no'), 's.item_code', 'v2.institution_id')
        ->leftJoin('voluntary_quotation as v2', 'v2.id', '=', 's.vq_id')
        ->where('v2.year', $year)
        ->where('s.is_deleted', 0)
        ->where('v2.vq_status', 1)
        ->where('v2.is_deleted', 0)
        ->where('v2.institution_id', $fetchVQ->institution_id)
        ->groupBy('s.item_code');

        $data = DB::table('voluntary_quotation_sku_listing as vqsl')
        ->select(
            'vq.rev_no',
            'vqsl.sap_itemcode',
            'vqsl.item_code',
            'vqsl.brand_name',
            'vqsl.hsn_code',
            'vqsl.applicable_gst',
            'vqsl.composition',
            'vqsl.type',
            'vqsl.div_name',
            'vqsl.pack',
            'vqsl.discount_percent',
            'vqsl.discount_rate',
            'vqsl.mrp'
        )
        ->selectRaw('ROUND((vqsl.mrp - vqsl.discount_rate) * 100.0 / vqsl.mrp, 2) as percentt')
        ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
        ->joinSub($maxRevSubquery, 'max_rev', function ($join) use($fetchVQ) {
            $join->on('vqsl.item_code', '=', 'max_rev.item_code')
                ->where('vq.institution_id', $fetchVQ->institution_id)
                ->on('vq.rev_no', '=', 'max_rev.max_rev_no');
        })
        ->where('vq.institution_id', $fetchVQ->institution_id)
        ->where('vq.year', $year)
        ->where('vq.vq_status', 1)
        ->where('vq.is_deleted', 0)
        ->where('vqsl.is_deleted', 0)
        ->whereIn('vqsl.div_id', $uniqueDivId)
        ->get();
        /*$data =  VoluntaryQuotationSkuListing::leftJoin('employee_master', 'employee_master.div_code', '=', 'voluntary_quotation_sku_listing.div_id')
            ->leftJoin('voluntary_quotation','voluntary_quotation.id','=','voluntary_quotation_sku_listing.vq_id')
            ->select(
                'voluntary_quotation.rev_no',
                'voluntary_quotation_sku_listing.sap_itemcode',
                'voluntary_quotation_sku_listing.item_code',
                'voluntary_quotation_sku_listing.brand_name',
                'voluntary_quotation_sku_listing.hsn_code',
                'voluntary_quotation_sku_listing.applicable_gst',
                'voluntary_quotation_sku_listing.composition',
                'voluntary_quotation_sku_listing.type',
                'voluntary_quotation_sku_listing.div_name',
                'voluntary_quotation_sku_listing.pack',
                'voluntary_quotation_sku_listing.discount_percent',
                'voluntary_quotation_sku_listing.discount_rate',
                'voluntary_quotation_sku_listing.mrp'
            )
            ->selectRaw('ROUND((voluntary_quotation_sku_listing.mrp - voluntary_quotation_sku_listing.discount_rate) * 100.0 / voluntary_quotation_sku_listing.mrp, 2) as percentt')
            ->where('vq_id', $this->id)
            ->where('voluntary_quotation_sku_listing.is_deleted', 0)
            ->where('employee_master.div_type', $this->type)
            ->distinct()
            ->get();*/
        $data = $data->map(function ($item) {
            $item->applicable_gst =  ($item->applicable_gst == "0") ? "0": $item->applicable_gst;
            $item->rev_no =  ($item->rev_no == "0") ? "0": $item->rev_no;
            $item->discount_rate =  ($item->discount_rate == "0") ? "0": $item->discount_rate;
            $item->discount_percent =  ($item->discount_percent == "0") ? "0": $item->discount_percent;
            /*$itemArray = $item->toArray();
            return $itemArray;*/
            return (array) $item; 
        });
        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 10,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 40,
            'H' => 15,
            'I' => 10,
            'J' => 10,
            'K' => 15,
            'L' => 30,
            'M' => 20,
            'N' => 20
        ];
    }

    
}
