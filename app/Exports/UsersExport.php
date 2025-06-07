<?php

namespace App\Exports;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\WithColumnFormatting;
// use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Style\Color;

class UsersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    
    public function collection()
    {
        return User::all();
    }
    public function headings(): array
    {
        $vq = VoluntaryQuotation::where('id',1)->first();
        return[ 
        ['SUN PHARMA LABORATORIES LTD.'],
        ['SUN HOUSE, PLOT NO.201 B/1 , WESTERN EXPRESS HIGHWAY','','','','','','Date','7/17/2021'],
        ['GOREGAON (E) - MUMBAI - 400063','','','','','','Valid Upto','7/31/2022'],
        ['Phone: 022-43244324 Fax: 022-43244343'],
        ['QUOTATION TO '.$vq['hospital_name'].', MALA MEDICAL STORE TEERTHANKER MAHAVEER UNIVERSITY TMU, TEERTHANKER MAHAVEER UNIVERSITY (TMU), '.$vq['address']],
        // [
        //     'METIS CODE',
        //     'BRAND NAME',
        //     'HSN CODE',
        //     'APPLICABLE GST',
        //     'COMPOSITION',
        //     'TYPE',
        //     'DIVNAME',
        //     'PACK',
        //     'RATE TO HOSPITAL (EXCL. OF GST)',
        //     'MRP (Including GST)',
        //     '% MARGIN ON MRP',
        // ],
    ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->getFont()->setSize(18);
        $sheet->getStyle('A2')->getFont()->setSize(9);
        $sheet->getStyle('A4')->getFont()->setSize(16);
        $sheet->getStyle('G2')->getFont()->setSize(16);

        $sheet->getStyle('A5')->getFont()->setSize(18);


        $sheet->getStyle('G2')->getFont()->setSize(16);
        $sheet->getStyle('H2')->getFont()->setSize(16);

        $sheet->getStyle('G3')->getFont()->setSize(16);
        $sheet->getStyle('H3')->getFont()->setSize(16);
    }
    
}
