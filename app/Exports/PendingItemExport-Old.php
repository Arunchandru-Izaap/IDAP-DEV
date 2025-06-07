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
class PendingItemExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths, IValueBinder
{
    use Exportable;

    private $id;
    private $type;

    public function __construct($pendingItem, $selected_institutions, $year)
    {
        $this->pendingItem = $pendingItem;
        $this->selected_institutions = json_decode($selected_institutions, TRUE);
        $this->year = $year;
    }

    public function headings(): array
    {
        return [
            'ITEM CODE',
            'BRAND NAME',
            'DIV NAME',
            'INSTITUTION ID',
            'HOSPITAL NAME',
            'REV No'
        ];
    }

    public function collection()
    {

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
        ->get();

        $data = $data->map(function ($item)  {
            $item->rev_no =  ($item->rev_no == 0) ? "0": $item->rev_no;

            return (array) $item; 
        });
        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 40,
            'C' => 20,
            'D' => 10,
            'E' => 30,
            'F' => 10
        ];
    }

    
}
