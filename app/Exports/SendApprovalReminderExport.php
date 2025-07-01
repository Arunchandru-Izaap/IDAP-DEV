<?php

namespace App\Exports;

use App\Http\Controllers\Api\VqListingController;
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
class SendApprovalReminderExport implements FromCollection,WithHeadings,WithEvents,WithColumnWidths
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $year;
    protected $vqids;
    protected $level;
    protected $frequency_days;

    public function __construct($year, $vqids, $level, $frequency_days)
    {
        $this->year = $year;
        $this->vqids = $vqids;
        $this->level = $level;
        $this->frequency_days = $frequency_days;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // added to get the year from vq controller starts
        $vq_controller = new VoluntaryQuotationController;
        $year = $vq_controller->getFinancialYear(date('Y-m-d'),"Y");
        // added to get the year from vq controller ends
        $emp = Employee::where('emp_code', Session::get('emp_code'))->first();

        $data = VoluntaryQuotation::select(
            'voluntary_quotation.institution_id',
            'voluntary_quotation.sap_code',
            'voluntary_quotation.hospital_name',
            'voluntary_quotation.city',
            'voluntary_quotation.state_name',
            'voluntary_quotation.rev_no',
            'voluntary_quotation.contract_start_date',
            'voluntary_quotation.contract_end_date',
            'voluntary_quotation.year',
        )
        ->where('voluntary_quotation.is_deleted', 0)
        ->where('voluntary_quotation.year', $year)
        ->whereIn('voluntary_quotation.id', $this->vqids)
        ->get();//added year condition 26042024 and added parent_vq_id = 0 condition on 08052024

        $data = $data->map(function ($item) use($emp) {
            $item->rev_no =  ($item->rev_no == 0) ? "0": $item->rev_no;
            $itemArray = $item->toArray();
            return $itemArray;
        });
        return $data;
    }
    public function headings(): array
    {
        return[ 
        [
            'INST_ID', 'SAP_CODE', 'INST_NAME', 'CITY',	'STATE_NAME', 'REVISION_NO', 'CONTRACT_START_DATE',	'CONTRACT_END_DATE', 'VQ_YEAR'
        ],];
    }
    public function columnWidths(): array
    {
        return [
            'A' =>10,
            'B' =>15,
            'C' =>40,
            'D' =>15,
            'E'=>15,
            'F'=>15,
            'G'=>25,
            'H'=>25,
            'I'=>15,          
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:I1')
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFFF00');
            },
        ];
    
    }
}
