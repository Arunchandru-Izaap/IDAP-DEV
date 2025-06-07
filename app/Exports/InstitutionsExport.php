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
class InstitutionsExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths, IValueBinder
{
    use Exportable;

    private $id;
    private $type;

    public function __construct($selected_institutions, $year)
    {
        $this->selected_institutions = json_decode($selected_institutions, TRUE);
        $this->year = $year;
    }

    public function headings(): array
    {
        return [
            'INSTITUTION ID',
            'HOSPITAL NAME',
            'REV No'
        ];
    }

    public function collection()
    {

        $data = DB::table('voluntary_quotation as vq')
        ->select(
            'vq.institution_id',
            'vq.hospital_name',
            'vq.rev_no'
        )
        ->whereIn('vq.institution_id', $this->selected_institutions)
        ->where('vq.year', $this->year)
        ->where('vq.vq_status', 0)
        ->where('vq.is_deleted', 0)
        ->orderBy('vq.hospital_name')
        ->orderBy('vq.rev_no')
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
            'B' => 30,
            'C' => 10
        ];
    }

    
}
