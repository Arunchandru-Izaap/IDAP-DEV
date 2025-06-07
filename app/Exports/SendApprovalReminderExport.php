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

        
        return VoluntaryQuotation::select(
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
            'B' => 40,
            'C' => 20,
            'D' =>40,
            'E'=>40,
            'F'=>40,
            'G'=>15,
            'H'=>15,
            'I'=>20,
            'J'=>15,           
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:J1')
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFFF00');
            },
        ];
    
    }
}
