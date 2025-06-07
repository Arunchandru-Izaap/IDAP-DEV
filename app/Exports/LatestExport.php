<?php

namespace App\Exports;

use App\Models\Institution;
use DB;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use DateTime;
class LatestExport implements FromCollection,WithHeadings
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    /**
     * @return int
     */
    private $year;
    private $type;

    public function __construct($year,$type)
    {
        $this->year = $year;
        $this->type = $type;

    }
    
    public function headings(): array
    {
       

        return[ 
        [
            'HOSPITAL NAME',
            'METIS CODE',
            'BRAND NAME',
            'HSN CODE',
            'APPLICABLE GST',
            'COMPOSITION',
            'TYPE',
            'DIVNAME',
            'PACK',
            'RATE TO HOSPITAL (EXCL. OF GST)',
            'MRP (Including GST)',
            '% MARGIN ON MRP',
        ]];
    }

   
    public function collection()
    {

        // $data = Institution::all();
        // return $data;
        // return VoluntaryQuotationSkuListing::leftJoin('voluntary_quotation','voluntary_quotation.id','=','voluntary_quotation_sku_listing.vq_id')->select('voluntary_quotation.hospital_name','voluntary_quotation_sku_listing.item_code',
        // 'voluntary_quotation_sku_listing.brand_name',
        // 'voluntary_quotation_sku_listing.hsn_code',
        // 'voluntary_quotation_sku_listing.applicable_gst',
        // 'voluntary_quotation_sku_listing.composition',
        // 'voluntary_quotation_sku_listing.type',
        // 'voluntary_quotation_sku_listing.div_name',
        // 'voluntary_quotation_sku_listing.pack',
        // 'voluntary_quotation_sku_listing.discount_rate',
        // 'voluntary_quotation_sku_listing.mrp',
        // 'voluntary_quotation_sku_listing.discount_rate')
        // ->selectRaw('ROUND((voluntary_quotation_sku_listing.mrp - voluntary_quotation_sku_listing.discount_rate )* 100.0 / voluntary_quotation_sku_listing.mrp,2) as percentt')
        // ->where('voluntary_quotation_sku_listing.is_deleted',0)
        // ->where('voluntary_quotation.parent_vq_id',0)
        // ->leftJoin('voluntary_quotation as vq2','voluntary_quotation_sku_listing.vq_id','=','vq2.parent_vq_id')
        // ->where()
        // ->distinct()
        // ->get();

        // $vq = VoluntaryQuotation::where('id',$this->year);
	    // // $start_date = new DateTime();

        // // dd($vq['contract_start_date']);
        // if($this->type == 'ho'){
        //     $vq = $vq->get();
        // }
        $data = [];
        $original_list = DB::table('voluntary_quotation_sku_listing_og AS parent_sku')
        ->select('MAX(parent_sku.id) AS id')
        ->select(
            'vq.hospital_name',
            'vq.institution_id',
            'parent_sku.item_code',
            'parent_sku.brand_name',
            'parent_sku.hsn_code',
            'parent_sku.applicable_gst',
            'parent_sku.composition',
            'parent_sku.type',
            'parent_sku.div_name',
            'parent_sku.pack',
            'parent_sku.discount_rate',
            'parent_sku.mrp'
        )
       ->leftJoin('voluntary_quotation_og AS vq', 'vq.id','parent_sku.vq_id')
       ->where('vq.year', $this->year)
       ->where('vq.zone','NORTH')
       ->where('vq.parent_vq_id',  0)
       ->orderBy('vq.hospital_name','ASC')
       ->chunk(200, function ($rows) use (&$data) {
        // Accumulate the data into the $data array
        foreach ($rows as $row) {
            $data[] = $row;
        }
    });
    // dd($data);
//         $child_list = DB::table('voluntary_quotation_sku_listing_og AS parent_sku')
//         ->select('MAX(parent_sku.id) AS id')
//         ->select(
//             'vq.hospital_name',
//             'vq.institution_id',
//             'parent_sku.item_code',
//             'parent_sku.brand_name',
//             'parent_sku.hsn_code',
//             'parent_sku.applicable_gst',
//             'parent_sku.composition',
//             'parent_sku.type',
//             'parent_sku.div_name',
//             'parent_sku.pack',
//             'parent_sku.discount_rate',
//             'parent_sku.mrp'
//         )
//        ->leftJoin('voluntary_quotation AS vq', 'vq.id','parent_sku.vq_id')
//        ->where('vq.parent_vq_id','!=',  0)
// ->where('year', $this->year)
//        ->groupBy('parent_sku.item_code','parent_sku.vq_id')
//        ->orderBy('parent_sku.id','DESC')->get();
    
//        $originalCollection = collect($original_list);
//        $childCollection = collect($child_list);
       
//       // Group collections by item_code to create associative arrays

// // Merge the two associative arrays, giving priority to the child elements
// // $mergedCollection = $childCollection->union($originalCollection);
// $mergedCollection = $originalCollection->map(function ($item) use ($childCollection) {
//     $childItem = $childCollection->where('institution_id',$item->institution_id)->firstWhere('item_code', $item->item_code);
//     return $childItem ?? $item;
// });
    return collect($data);
       
    }
    
}
