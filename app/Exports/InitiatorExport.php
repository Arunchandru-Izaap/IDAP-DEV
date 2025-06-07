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
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use DateTime;
class InitiatorExport implements FromCollection,WithDrawings,WithHeadings,WithStyles,WithColumnWidths,WithEvents
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    /**
     * @return int
     */
    private $id;
    private $type;

    public function __construct($id,$type)
    {
        $this->id = $id;
        $this->type = $type;

    }
    
    public function columnWidths(): array
    {
        return [
            'A'=>15,
            'B'=>15,
            'C'=>40,
            'D'=>15,
            'E'=>15,
            'F'=>40,
            'G'=>20,
            'H'=>20,
            'I'=>15,
            'J'=>20,
            'K'=>20,
            'L'=>20           
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
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(15);

                $event->sheet->getDelegate()->getRowDimension('5')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(15);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(37);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(12);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(10);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(42);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(16);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(10);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('I')->setWidth(10);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('J')->setWidth(12);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('K')->setWidth(12);

                $event->sheet->getDelegate()->getRowDimension('6')->setRowHeight(40);
                $event->sheet->getDelegate()->getColumnDimension('L')->setWidth(12);

                $event->sheet->styleCells(
                    'E2:F2',
                    [
                        'borders' => [
                            'outline' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '#000000'],
                            ],
                        ]
                    ]
                );
                
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
                    'E3:F3',
                    [
                        'borders' => [
                            'outline' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '#000000'],
                            ],
                        ]
                    ],
                );
                $event->sheet->styleCells(
                    'E4:F4',
                    [
                        'borders' => [
                            'outline' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => '#000000'],
                            ],
                        ]
                    ],
                );
                $event->sheet->styleCells(
                    'F4',
                    [
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        ],
                    ],
                );

                $event->sheet->styleCells(
                    'A5:L5',
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
                    'A6:L6',
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

        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A5')->getFont()->setBold(true);
        $sheet->getStyle('A6:L6')->getFont()->setBold(true);

        $sheet->getStyle('A1')->getFont()->setSize(18);
        $sheet->getStyle('A2')->getFont()->setSize(10);
        $sheet->getStyle('A3')->getFont()->setSize(10);
        $sheet->getStyle('A4')->getFont()->setSize(10);
        $sheet->getStyle('A5')->getFont()->setSize(12);

        $sheet->getStyle('A4')->getFont()->setBold(true);

        $sheet->getStyle('H2')->getFont()->setSize(16);

        $sheet->getStyle('A6:L6')->getFont()->setSize(14);
        $sheet->getStyle('A6:L6')->getFont()->setSize(10);


        $sheet->getStyle('H2')->getFont()->setSize(12);
        $sheet->getStyle('I2')->getFont()->setSize(12);
        $sheet->getStyle('H2')->getFont()->setBold(true);
        $sheet->getStyle('I2')->getFont()->setBold(true);

        $sheet->getStyle('H3')->getFont()->setSize(12);
        $sheet->getStyle('I3')->getFont()->setSize(12);
        $sheet->getStyle('H3')->getFont()->setBold(true);
        $sheet->getStyle('I3')->getFont()->setBold(true);

        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A5:L5');
        
        // $sheet->mergeCells('G2:H2'); 
        // $sheet->mergeCells('G3:H3');
    }
    
    public static function beforeWriting(BeforeWriting $event) 
    {
        //
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

        return[ 
        [$company_name],
        ['SUN HOUSE, PLOT NO.201 B/1 , WESTERN EXPRESS HIGHWAY','','','','Date',$start_date->format('d/m/Y')],
        ['GOREGAON (E) - MUMBAI - 400063','','','','Valid Upto',$end_date->format('d/m/Y')],
        ['Phone: 022-43244324 Fax: 022-43244343', '', '', '', 'Revision number', $revision_count],
        ['QUOTATION TO '.$vq['hospital_name'].', '.$vq['address']],
        [
            'SAP CODE',
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
        ],];
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
        $drawing->setCoordinates('L1');
        return $drawing;
    }
   
    public function collection()
    {

        // $data = Institution::all();
        // return $data;
        return VoluntaryQuotationSkuListing::leftJoin('employee_master','employee_master.div_code','=','voluntary_quotation_sku_listing.div_id')->select(
        'voluntary_quotation_sku_listing.sap_itemcode',
        'voluntary_quotation_sku_listing.item_code',
        'voluntary_quotation_sku_listing.brand_name',
        'voluntary_quotation_sku_listing.hsn_code',
        'voluntary_quotation_sku_listing.applicable_gst',
        'voluntary_quotation_sku_listing.composition',
        'voluntary_quotation_sku_listing.type',
        'voluntary_quotation_sku_listing.div_name',
        'voluntary_quotation_sku_listing.pack',
        'voluntary_quotation_sku_listing.discount_rate',
        'voluntary_quotation_sku_listing.mrp',
        'voluntary_quotation_sku_listing.mrp_margin')
        // ->selectRaw('ROUND((voluntary_quotation_sku_listing.mrp - voluntary_quotation_sku_listing.discount_rate )* 100.0 / voluntary_quotation_sku_listing.mrp,2) as percentt')
        ->where('vq_id',$this->id)
        ->where('voluntary_quotation_sku_listing.is_deleted',0)
        ->where('employee_master.div_type',$this->type)
        ->distinct()
        ->get();
    }
    
}
