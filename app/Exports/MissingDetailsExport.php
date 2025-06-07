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
use App\Models\Stockist_master;
use App\Models\PocMaster;
use DB;
use Session;
class MissingDetailsExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths, IValueBinder
{
    use Exportable;

    private $id;
    private $type;

    public function __construct($downloadType, $item_code, $selection, $year)
    {
        $this->downloadType = $downloadType;
        $this->item_code = $item_code;
        $this->selected_institutions = $selection;
        $this->year = $year;
    }

    public function headings(): array
    {
        if($this->downloadType == 'missingPaymode')
        {
            return [
                'STOCKIST ID',
                'INSTITUTION ID',
                'HOSPITAL NAME',
                'CITY',
                'STATE',
                'STOCKIST NAME',
                'STOCKIST CODE',
                'PAYMENT MODE',
                'STATUS'
            ];
        }  
        else if($this->downloadType == 'missingStockist')
        {
            return [
                'INSTITUTION ID',
                'HOSPITAL NAME',
                'CITY',
                'STATE',
                'STOCKIST NAME',
                'STOCKIST CODE',
                'PAYMENT MODE',
                'STATUS'
            ];
        }    
        else if($this->downloadType == 'missingPoc')
        {
            return [
                'INSTITUTION ID',
                'HOSPITAL NAME',
            ];
        }     
    }

    public function collection()
    {
        if($this->downloadType == 'missingPaymode')
        {
            $data = Stockist_master::select('stockist_master.id','institution_id','hospital_name','city','state_name', 'stockist_name','stockist_code','payment_mode','stockist_type_flag')->leftJoin('voluntary_quotation','voluntary_quotation.institution_id','=', 'stockist_master.institution_code')->where('year', $this->year)->where('parent_vq_id', 0)->where('is_deleted', 0)->whereIn('institution_code', $this->selected_institutions)->where('stockist_type_flag', 1)
            ->where(function($query) {
                $query->whereNull('payment_mode')
                      ->orWhere('payment_mode', '');
            })
            ->get();
            $data = $data->map(function ($item) {
                return [
                    'ID' => $item->id,
                    'Institution ID' => $item->institution_id,
                    'Hospital Name' => $item->hospital_name,
                    'City'          => $item->city,
                    'State'         => $item->state_name,
                    'Stockist Name' => $item->stockist_name,
                    'Stockist Code' => $item->stockist_code,
                    'Payment Mode' => $item->payment_mode,
                    'Status' => $item->stockist_type_flag == 0 ? "Inactive" : "Active",
                ];
            });
        }
        else if($this->downloadType == 'missingStockist')
        {
            $data = VoluntaryQuotation::select('institution_id','hospital_name','city','state_name', 'stockist_name','stockist_code','payment_mode','stockist_type_flag')->leftJoin('stockist_master','voluntary_quotation.institution_id','=', 'stockist_master.institution_code')->where('year', $this->year)->where('parent_vq_id', 0)->where('is_deleted', 0)->whereIn('institution_id', $this->selected_institutions)
            ->where(function($query) {
                $query->whereNull('stockist_code')
                      ->orWhere('stockist_code', '');
            })
            ->get();
            $data = $data->map(function ($item) {
                return [
                    'Institution ID' => $item->institution_id,
                    'Hospital Name' => $item->hospital_name,
                    'City'          => $item->city,
                    'State'         => $item->state_name,
                    'Stockist Name' => $item->stockist_name,
                    'Stockist Code' => $item->stockist_code,
                    'Payment Mode' => $item->payment_mode,
                    'Status' => $item->stockist_type_flag,
                ];
            });
        }
        else if($this->downloadType == 'missingPoc')
        {
            $data = VoluntaryQuotation::select('voluntary_quotation.institution_id','hospital_name','fsm_code')->leftJoin('poc_master','voluntary_quotation.institution_id','=', 'poc_master.institution_id')->where('year', $this->year)->where('parent_vq_id', 0)->where('is_deleted', 0)->whereIn('voluntary_quotation.institution_id', $this->selected_institutions)
            ->where(function($query) {
                $query->whereNull('fsm_code')
                      ->orWhere('fsm_code', '');
            })
            ->get();
            $data = $data->map(function ($item) {
                return [
                    'Institution ID' => $item->institution_id,
                    'Hospital Name' => $item->hospital_name,
                ];
            });
        }

        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'C' => 40,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 20
        ];
    }

    
}
