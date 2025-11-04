<?php

namespace App\Exports;

use App\Models\Employee;
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
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\IValueBinder;
use App\Http\Controllers\StaticPages\VoluntaryQuotationController;
use DB;
use Session;
use DateTime;

class InitiatorExportNew implements FromCollection,WithDrawings,WithHeadings,WithStyles,WithColumnWidths,WithEvents
{
    use Exportable;

    private $id;
    private $type;

    public function __construct($id, $type)
    {
        $this->id = $id;
        $this->type = $type;
    }

    
    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 15, // add new columns for Start date
            'C' => 15, // add new columns for end date
            'D' => 10,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 50,
            'J' => 15,
            'K' => 10,
            'L' => 10,
            'M' => 15,
            'N' => 30,
            'O' => 20, 
            // 'P' => 20,  // modified at 10-10-2025 for user requirement
        ];
    }

     public function registerEvents(): array
    {
        return [
            
            AfterSheet::class    => function(AfterSheet $event) {
                $workSheet = $event->sheet->getDelegate();
                $workSheet->freezePane('A1');
                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(60);
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(15);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(60);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(20);

                $event->sheet->getDelegate()->getRowDimension('5')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(15);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(20);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(12);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(10);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(42);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(16);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(20);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(50);
                $event->sheet->getDelegate()->getColumnDimension('I')->setWidth(20);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(60);
                $event->sheet->getDelegate()->getColumnDimension('J')->setWidth(12);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('K')->setWidth(15);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('L')->setWidth(15);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('O')->setWidth(12);

                // modified at 10-10-2025 for user requirement
                /*
                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('P')->setWidth(12);
                */
                
                $event->sheet->styleCells(
                    'A',
                    [
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        ],
                    ]
                );
                $event->sheet->styleCells(
                    'B',
                    [
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        ],
                    ]
                );
                $event->sheet->styleCells(
                    'E',
                    [
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        ],
                    ]
                );
                
                $event->sheet->styleCells(
                    'A5:O5',
                    [
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                        ],
                        'borders' => [
                            'outline' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '#000000'],
                            ],
                        ]
                    ]
                );
                $event->sheet->styleCells(
                    'A6:O6',
                    [
                        'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                    ],
                        'borders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '#000000'],
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => 'd8d8d8',
                             ]           
                        ],
                    ]
                );
            },
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->fromArray([], null, 'A5', false, false)
        ->getStyle('A5')
        ->getAlignment()
        ->setWrapText(true);

        $sheet->fromArray([], null, 'E4', false, false)
        ->getStyle('E4')
        ->getAlignment()
        ->setWrapText(true);

        $sheet->fromArray([], null, 'E6', false, false)
        ->getStyle('E6')
        ->getAlignment()
        ->setWrapText(true);

        $sheet->fromArray([], null, 'J6', false, false)
        ->getStyle('J6')
        ->getAlignment()
        ->setWrapText(true);

        $sheet->fromArray([], null, 'K6', false, false)
        ->getStyle('K6')
        ->getAlignment()
        ->setWrapText(true);

        $sheet->fromArray([], null, 'L6', false, false)
        ->getStyle('L6')
        ->getAlignment()
        ->setWrapText(true);

        $sheet->fromArray([], null, 'O6', false, false)
        ->getStyle('O6')
        ->getAlignment()
        ->setWrapText(true);

        // modified at 10-10-2025 for user requirement
        /*
        $sheet->fromArray([], null, 'P6', false, false)
        ->getStyle('P6')
        ->getAlignment()
        ->setWrapText(true);
        */

        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A5')->getFont()->setBold(true);
        $sheet->getStyle('A6:O6')->getFont()->setBold(true);

        $sheet->getStyle('A1')->getFont()->setSize(18);
        $sheet->getStyle('A2')->getFont()->setSize(10);
        $sheet->getStyle('A3')->getFont()->setSize(10);
        $sheet->getStyle('A4')->getFont()->setSize(10);
        $sheet->getStyle('A5')->getFont()->setSize(12);

        $sheet->getStyle('A4')->getFont()->setBold(true);

        $sheet->getStyle('H2')->getFont()->setSize(16);

        $sheet->getStyle('A6:O6')->getFont()->setSize(14);
        $sheet->getStyle('A6:O6')->getFont()->setSize(10);


        $sheet->getStyle('H2')->getFont()->setSize(12);
        $sheet->getStyle('I2')->getFont()->setSize(12);
        $sheet->getStyle('H2')->getFont()->setBold(true);
        $sheet->getStyle('I2')->getFont()->setBold(true);

        $sheet->getStyle('H3')->getFont()->setSize(12);
        $sheet->getStyle('I3')->getFont()->setSize(12);
        $sheet->getStyle('H3')->getFont()->setBold(true);
        $sheet->getStyle('I3')->getFont()->setBold(true);

        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A5:O5');
        
        // $sheet->mergeCells('G2:H2'); 
        // $sheet->mergeCells('G3:H3');
    }

    public function headings(): array
    {
        $vq = VoluntaryQuotation::where('id',$this->id)->where('is_deleted', 0)->first();
        $start_date = new DateTime($vq['contract_start_date']);
        $end_date = new DateTime($vq['contract_end_date']);
	    // $start_date = new DateTime();

        // dd($vq['contract_start_date']);
        if($this->type == 'SPLL'){
            $company_name = 'SUN PHARMA LABORATORIES LTD';
        }elseif($this->type == 'SPIL'){
            $company_name = 'SUN PHARMACEUTICAL INDUSTRIES LTD';
        }

        /*if($vq->parent_vq_id !=0){
            $revision_count = VoluntaryQuotation::where('parent_vq_id',$vq->parent_vq_id)->where('id','<=',$this->id)->where('is_deleted', 0)->count();

        }else{
            $revision_count="0";
        }*/
        $revision_count = VoluntaryQuotation::select('rev_no')->where('id',$this->id)->where('is_deleted', 0)->first();
        $revision_count = ($revision_count->rev_no != 0)? $revision_count->rev_no : "0";

        return [ 
            [$company_name],
            ['SUN HOUSE, PLOT NO.201 B/1 , WESTERN EXPRESS HIGHWAYs','','','','',''],
            ['GOREGAON (E) - MUMBAI - 400063','','','','',''],
            ['Phone: 022-43244324 Fax: 022-43244343', '', '', '', '', ''],
            ['QUOTATION TO '.$vq['hospital_name'].', '.$vq['address']],
            [
                'REVISION NUMBER',
                'START DATE',
                'END DATE',
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
                // '% MARGIN ON MRP' // modified at 10-10-2025 for user requirement
            ]
        ];
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing2 = new Drawing();
        $drawing->setName('Demo');
        // $drawing->setDescription('This is my logo');
        $drawing->setPath(public_path('admin/images/Sun_Pharma_logo.png'));
        $drawing->setHeight(85);
        // $drawing->setWidth(100);
        $drawing->setCoordinates('O1'); // modified at 26-09-2025 for user requirement
        return $drawing;
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
            'vq.contract_start_date',
            'vq.contract_end_date',
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
        // ->selectRaw('ROUND((vqsl.mrp - vqsl.discount_rate) * 100.0 / vqsl.mrp, 2) as percentt') // modified at 10-10-2025 for user requirement
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


    
}
