<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\VoluntaryQuotation;
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

use App\Http\Controllers\StaticPages\VoluntaryQuotationController;//added to get the financial year 
class VqExport implements FromCollection,WithHeadings,WithEvents,WithColumnWidths
{
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
        if($emp->emp_category == 'distribution'){
            return VoluntaryQuotation::select('institution_id','hospital_name','sap_code','city','addr1','addr2','addr3','pincode','stan_code','state_name','zone')->where('vq_status',1)->whereIn('cfa_code',explode(',',Session::get("division_id")))->where('is_deleted', 0)->where('year', $year)->where('parent_vq_id' , 0)->get();//added year condition 26042024 and added parent_vq_id = 0 condition on 08052024
        }
        elseif($emp->emp_category == 'poc'){
            return VoluntaryQuotation::select('voluntary_quotation.institution_id', 'voluntary_quotation.hospital_name', 'voluntary_quotation.sap_code','voluntary_quotation.city','voluntary_quotation.addr1','voluntary_quotation.addr2','voluntary_quotation.addr3','voluntary_quotation.pincode','voluntary_quotation.stan_code','voluntary_quotation.state_name','voluntary_quotation.zone')->where('vq_status',1)->leftJoin('poc_master','poc_master.institution_id','voluntary_quotation.institution_id')->where('poc_master.'.strtolower(Session::get("emp_type")).'_code',Session::get("emp_code"))->where('voluntary_quotation.is_deleted', 0)->where('voluntary_quotation.year', $year)->where('voluntary_quotation.parent_vq_id' , 0)->get();//added year condition 26042024 and added parent_vq_id = 0 condition on 08052024
        }elseif($emp->emp_category == 'approver' && preg_replace('/[^0-9.]+/', '', Session::get("level"))<=2){
           return VoluntaryQuotation::select('voluntary_quotation.institution_id','voluntary_quotation.hospital_name', 'voluntary_quotation.sap_code', 'voluntary_quotation.city','voluntary_quotation.addr1','voluntary_quotation.addr2','voluntary_quotation.addr3','voluntary_quotation.pincode','voluntary_quotation.stan_code','voluntary_quotation.state_name','voluntary_quotation.zone')
            ->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','voluntary_quotation.id')
            ->where('voluntary_quotation.is_deleted', 0)
            ->where('voluntary_quotation.year', $year)->where('voluntary_quotation.parent_vq_id' , 0)
            ->where('institution_division_mapping.employee_code',Session::get("emp_code"))
            ->distinct()->get();//added year condition 26042024 and added parent_vq_id = 0 condition on 08052024
        }
        elseif($emp->emp_category == 'approver' && preg_replace('/[^0-9.]+/', '', Session::get("level"))>=2){//added for approver vq show based on user login 30042024 and added parent_vq_id = 0 condition on 08052024
            $level =  (int) preg_replace('/[^0-9.]+/', '', Session::get("level"));
           return VoluntaryQuotation::select('voluntary_quotation.institution_id','voluntary_quotation.hospital_name', 'voluntary_quotation.sap_code', 'voluntary_quotation.city','voluntary_quotation.addr1','voluntary_quotation.addr2','voluntary_quotation.addr3','voluntary_quotation.pincode','voluntary_quotation.stan_code','voluntary_quotation.state_name','voluntary_quotation.zone')
            ->where('current_level','>=',$level)->where('year',$year)->where('voluntary_quotation.parent_vq_id' , 0)
            ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            ->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))
            ->where('voluntary_quotation_sku_listing.is_deleted','!=',1)
            ->where('voluntary_quotation.is_deleted', 0)->groupBy('voluntary_quotation.id')->get();
        }

        return VoluntaryQuotation::select('institution_id','hospital_name', 'sap_code', 'city','addr1','addr2','addr3','pincode','stan_code','state_name','zone')->where('is_deleted', 0)->where('parent_vq_id' , 0)->where('year', $year)->get();//added year condition 26042024 and added parent_vq_id = 0 condition on 08052024
    }
    public function headings(): array
    {
        return[ 
        [
            'INST_ID','INST_NAME','SAP_CODE', 'CITY','ADDR1','ADDR2','ADDR3','PIN','STAN_CODE','STATE_NAME','ZONE'
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
