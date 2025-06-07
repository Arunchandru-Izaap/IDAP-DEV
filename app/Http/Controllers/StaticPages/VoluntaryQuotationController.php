<?php

namespace App\Http\Controllers\StaticPages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use DB;
use App\Models\DiscountMarginMaster;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\ActivityTracker;
use App\Models\LastYearPrice;
use App\Models\ceilingMaster;
use App\Models\Stockist_master;
use App\Models\PocMaster;
use App\Models\IdapDiscTran;
use App\Models\VqInitiateDates;
use App\Models\JwtToken;
use Maatwebsite\Excel\Excel as BaseExcel;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Mail;
use App\Models\Signature;
use PDF;
use App\Exports\InitiatorExport;
use App\Exports\MissingDetailsExport;
use App\Jobs\CreateVq;
use App\Jobs\DeleteVq;
use App\Jobs\AddCounterRateTransfer;//added on 20072024
use App\Jobs\SendEmailCEOApproval;//aaded on 31012025
use App\Models\VoluntaryQuotationSkuListingStockist;//added on 22072024
use App\Models\IgnoredInstitutions;
use Excel;
use App\Http\Controllers\StaticPages\VoluntaryQuotationSkuListingController;
use App\Http\Controllers\Api\VqListingController;
use DateTime; 
use Storage;
use Log;
set_time_limit(0);
class VoluntaryQuotationController extends Controller
{
    public function approver(){
        if (file_exists( public_path() . '/latestreport' . Session::get('emp_code') . '.zip')) {
            $latest_file_link ='latestreport' . Session::get('emp_code') . '.zip';
            $latest_file_creation_date = date("j F Y H:i:s", filectime(public_path().'/'.$latest_file_link));
        }else{
            $latest_file_link = null;
            $latest_file_creation_date = null;
        }
        if (file_exists( public_path() . '/historicalreport' . Session::get('emp_code') . '.zip')) {
            $historical_file_link ='historicalreport' . Session::get('emp_code') . '.zip';
            $historical_file_creation_date = date("j F Y H:i:s", filectime(public_path().'/'.$historical_file_link));
        }else{
            $historical_file_link = null;
            $historical_file_creation_date = null;
        }
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $level =  (int) preg_replace('/[^0-9.]+/', '', Session::get("level"));
        //removed the data and added in getApproverVQlistData function for serverside datatable
        if($level>2){
           /* $data1 = VoluntaryQuotation::select('voluntary_quotation.id','voluntary_quotation.current_level','voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status as status_vq', 'voluntary_quotation.is_deleted')
            ->where('current_level','>=',$level)->where('year',$year)
            ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            ->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))
            ->where('voluntary_quotation_sku_listing.is_deleted','!=',1)
            ->where('voluntary_quotation.is_deleted', 0)->distinct()->get();
            
            //$ids = VoluntaryQuotation::select('voluntary_quotation.id')->where('current_level','>=',preg_replace('/[^0-9.]+/', '', Session::get("level")))->where('year',$year)->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation_sku_listing.div_id',Session::get("division_id"))->where('voluntary_quotation_sku_listing.is_deleted','==',0)->distinct()->get();
            
            $data2 = VoluntaryQuotation::select('voluntary_quotation.id','voluntary_quotation.current_level',DB::raw('3 as status_vq'),'voluntary_quotation.is_deleted')
            ->where('current_level','>=',$level)
            ->where('year',$year)
            ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            ->where('voluntary_quotation_sku_listing.is_deleted','=',1)
            ->where('voluntary_quotation.is_deleted', 0)
            ->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))
            ->distinct()->get(); */ // hide by Govind

            $divisionString = Session::get('division_id');

            $divisionArray = array_map(function ($div) {

                return "'" . trim($div) . "'";

            }, explode(',', $divisionString));

            $divisionInClause = implode(',', $divisionArray);

            $query = 'SELECT * FROM (

                SELECT 

                    vq.id,vq.hospital_name,

                    vq.institution_id,

                    vq.city,

                    vq.state_name,

                    vq.institution_zone,

                    vq.institution_region,

                    vq.cfa_code,

                    vq.sap_code,

                    vq.contract_start_date,

                    vq.contract_end_date,

                    vq.current_level,vq.vq_status,vq.year, vq.rev_no AS revision_count, 

                    CASE 

                        WHEN EXISTS (

                            SELECT 1 FROM voluntary_quotation_sku_listing as vqsl 

                            WHERE vqsl.vq_id = vq.id 

                              AND vqsl.' . strtolower(Session::get("level")) . '_status = 0 

                              AND vq.current_level = '.$level.'

                              and vq.`year` = "'.$year.'"

                              and `vqsl`.`div_id` in ('.$divisionInClause.')

                            and `vqsl`.`is_deleted` = 0 

                            and `vq`.`is_deleted` = 0

                        ) THEN 0 

                        WHEN EXISTS (

                            SELECT 1 FROM voluntary_quotation_sku_listing as vqsl 

                            WHERE vqsl.vq_id = vq.id 

                              AND vqsl.' . strtolower(Session::get("level")) . '_status = 1 

                              AND vq.current_level = '.$level.'

                              and vq.`year` = "'.$year.'"

                              and `vqsl`.`div_id` in ('.$divisionInClause.')

                            and `vqsl`.`is_deleted` = 0 

                            and `vq`.`is_deleted` = 0

                        ) THEN 1

                        WHEN EXISTS (

                            SELECT 1 FROM voluntary_quotation_sku_listing as vqsl 

                            WHERE vqsl.vq_id = vq.id 

                              AND vq.current_level > '.$level.'

                              AND vqsl.div_id in ('.$divisionInClause.')

                              and vq.`year` = "'.$year.'"

                            and `vqsl`.`is_deleted` = 0 

                            and `vq`.`is_deleted` = 0

                        ) THEN 1

                         WHEN EXISTS (

                            SELECT 1 FROM voluntary_quotation_sku_listing as vqsl 

                            WHERE vqsl.vq_id = vq.id  

                              AND vq.current_level >= '.$level.'

                              and vq.`year` = "'.$year.'" 

                              and `vqsl`.`div_id` in ('.$divisionInClause.')

                            and `vqsl`.`is_deleted` = 1 

                            and `vq`.`is_deleted` = 0

                        ) THEN 3

                        ELSE 4

                    END AS status_vq

                FROM voluntary_quotation vq

                WHERE EXISTS (

                    SELECT 1 FROM voluntary_quotation_sku_listing as vqsl 

                    WHERE vqsl.vq_id = vq.id 

                    AND (

                        (vqsl.' . strtolower(Session::get("level")) . '_status = 0 AND vq.current_level = '.$level.')

                       OR (vqsl.' . strtolower(Session::get("level")) . '_status = 1 AND vq.current_level = '.$level.')

                       OR (vq.current_level > '.$level.' AND vqsl.div_id in ('.$divisionInClause.') )

                    )

                    and vq.`year` = "'.$year.'"

                    and `vqsl`.`div_id` in ('.$divisionInClause.')

                    and `vq`.`is_deleted` = 0

                )

            ) AS sub Where 1=1';

            $data = collect(DB::select($query));
 
        }
        else
        {
            /** Added on 07012025 */
            $institution_division_mapping = DB::table('institution_division_mapping')
            ->where('employee_code', Session::get("emp_code"))
            ->selectRaw('vq_id')
            // ->selectRaw('GROUP_CONCAT(vq_id) as vq_ids')
            ->distinct('vq_id')
            ->get();
            /** Added on 07012025 */
            $institution_division_mapping_vq_id = $institution_division_mapping->pluck('vq_id')->unique()->toArray(); //array_column($institution_division_mapping, 'vq_id');

            // $data1 = VoluntaryQuotation::select('voluntary_quotation.id',
            // 'voluntary_quotation.current_level','voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status as status_vq', 'voluntary_quotation.is_deleted')
            // ->where('current_level','>=',$level)->where('year',$year)
            // ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            // //->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','voluntary_quotation.id')//commented on 15062024
            // ->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))
            // ->where('voluntary_quotation_sku_listing.is_deleted','!=',1)
            // ->where('voluntary_quotation.is_deleted', 0)
            // //->whereIn('institution_division_mapping.employee_code',[Session::get("emp_code")])//commented on 15062024
            // ->whereIn('voluntary_quotation.id', function ($query) {//added on 15062024
            //     $query->select('vq_id')
            //           ->from('institution_division_mapping')
            //           ->where('employee_code', Session::get("emp_code"));
            // })
            // ->distinct()->get(); // hide by arunchandru 15012025

            $data1 = VoluntaryQuotation::select(
                'voluntary_quotation.id',
                'voluntary_quotation.current_level',
                'voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status as status_vq', 
                'voluntary_quotation.is_deleted')
                ->where('current_level','>=',$level)
                ->where('year',$year)
                ->where('voluntary_quotation.is_deleted', 0)
                //->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','voluntary_quotation.id')//commented on 15062024
                ->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))
                ->where('voluntary_quotation_sku_listing.is_deleted','!=',1)
                ->whereIn('voluntary_quotation.id', $institution_division_mapping_vq_id) /** Added on 07012025 */
                //->whereIn('institution_division_mapping.employee_code',[Session::get("emp_code")])//commented on 15062024
                // ->whereIn('voluntary_quotation.id', function ($query) {//added on 15062024
                //     $query->select('vq_id')
                //           ->from('institution_division_mapping')
                //           ->where('employee_code', Session::get("emp_code"));
                // }) //commented on 07012025
                ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
                ->distinct()
                ->get();
            
            //$ids = VoluntaryQuotation::select('voluntary_quotation.id')->where('current_level','>=',preg_replace('/[^0-9.]+/', '', Session::get("level")))->where('year',$year)->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation_sku_listing.div_id',Session::get("division_id"))->where('voluntary_quotation_sku_listing.is_deleted','==',0)->distinct()->get();
            
            // $data2 = VoluntaryQuotation::select('voluntary_quotation.id','voluntary_quotation.current_level','voluntary_quotation.year',DB::raw('3 as status_vq'),'voluntary_quotation.is_deleted')
            // ->where('current_level','>=',$level)
            // ->where('year',$year)
            // ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            // //->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','voluntary_quotation.id')//commented on 15062024
            // ->where('voluntary_quotation_sku_listing.is_deleted','=',1)
            // ->where('voluntary_quotation.is_deleted', 0)
            // ->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))
            // // ->where('institution_division_mapping.vq_id','voluntary_quotation.id')
            // //->whereIn('institution_division_mapping.employee_code',[Session::get("emp_code")])//commented on 15062024
            // ->whereIn('voluntary_quotation.id', function ($query) {//added on 15062024
            //     $query->select('vq_id')
            //           ->from('institution_division_mapping')
            //           ->where('employee_code', Session::get("emp_code"));
            // })
            // ->distinct()->get(); //hide by arunchandru 15012025

            $data2 = VoluntaryQuotation::select(
                'voluntary_quotation.id',
                'voluntary_quotation.current_level',
                'voluntary_quotation.year',
                DB::raw('3 as status_vq'),
                'voluntary_quotation.is_deleted')
                ->where('current_level','>=',$level)
                ->where('year',$year)
                //->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','voluntary_quotation.id')//commented on 15062024
                ->where('voluntary_quotation_sku_listing.is_deleted','=',1)
                ->where('voluntary_quotation.is_deleted', 0)
                ->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))
                ->whereIn('voluntary_quotation.id', $institution_division_mapping_vq_id) // explode(',', $institution_division_mapping[0]->vq_ids)
                // ->where('institution_division_mapping.vq_id','voluntary_quotation.id')
                //->whereIn('institution_division_mapping.employee_code',[Session::get("emp_code")])//commented on 15062024
                // ->whereIn('voluntary_quotation.id', function ($query) {//added on 15062024
                //     $query->select('vq_id')
                //           ->from('institution_division_mapping')
                //           ->where('employee_code', Session::get("emp_code"));
                // }) //commented on 07012025
                ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
                ->distinct()
                ->get();
            
            $data = $data1->merge($data2);
        }

       
        if($level == 5 || $level == 6){//added by govind on 170425 start
            $pendingDivisions = VoluntaryQuotation::select('voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.div_name')
            ->where('current_level','=',$level)->where('year',$year)
            ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            ->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))
            ->where('voluntary_quotation_sku_listing.is_deleted','!=',1)
            ->where('voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status', 0)
            ->where('voluntary_quotation.is_deleted', 0)->groupBy('div_id')->get();
        }
        else
        {
            $pendingDivisions = collect();
        }
        $pendingVqExists = VoluntaryQuotation::leftJoin('voluntary_quotation_sku_listing', 'voluntary_quotation_sku_listing.vq_id', '=', 'voluntary_quotation.id')
        ->where('voluntary_quotation.current_level', $level)
        ->where('voluntary_quotation.year', $year)
        ->whereIn('voluntary_quotation_sku_listing.div_id', explode(',',Session::get("division_id")))
        ->where('voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status', 0)
        ->where('voluntary_quotation_sku_listing.is_deleted', '!=', 1)
        ->where('voluntary_quotation.is_deleted', 0)
        ->exists();//added by govind on 170425 end
        // print_r(count($data));die;
        return view('frontend.Approver.list',compact('data', 'historical_file_link', 'historical_file_creation_date', 'latest_file_link', 'latest_file_creation_date','pendingDivisions','pendingVqExists'));
    }
    public function normalUser(){
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $data = VoluntaryQuotation::select("*", \DB::raw('(CASE  WHEN voluntary_quotation.parent_vq_id != "0" THEN (SELECT COUNT(*) FROM voluntary_quotation as vq1 where vq1.parent_vq_id = voluntary_quotation.parent_vq_id AND vq1.id <= voluntary_quotation.id) ELSE "0"  END) AS revision_count'))->where('year',$year)->where('vq_status',1)->where('is_deleted', 0)->get();
        return view('frontend.User.list',compact('data'));
    }

    public function pocUser(){
        if (file_exists( public_path() . '/latestreport' . Session::get('emp_code') . '.zip')) {
            $latest_file_link ='latestreport' . Session::get('emp_code') . '.zip';
            $latest_file_creation_date = date("j F Y H:i:s", filectime(public_path().'/'.$latest_file_link));
         }else{
            $latest_file_link = null;
            $latest_file_creation_date = null;
         }
        if (file_exists( public_path() . '/historicalreport' . Session::get('emp_code') . '.zip')) {
            $historical_file_link ='historicalreport' . Session::get('emp_code') . '.zip';
            $historical_file_creation_date = date("j F Y H:i:s", filectime(public_path().'/'.$historical_file_link));
         }else{
            $historical_file_link = null;
            $historical_file_creation_date = null;
         }
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $data = VoluntaryQuotation::select('voluntary_quotation.*' , 'voluntary_quotation.rev_no AS revision_count')->where('year',$year)->where('vq_status',1)->leftJoin('poc_master','poc_master.institution_id','voluntary_quotation.institution_id')->where('poc_master.'.strtolower(Session::get("emp_type")).'_code',Session::get("emp_code"))->where('voluntary_quotation.is_deleted', 0)->get();
        $division_name = DB::table('brands')->select('div_name','div_id')->groupBy('div_name','div_id')->orderBy('div_name')->pluck('div_name','div_id')->toArray();
        return view('frontend.Poc.list',compact('data', 'historical_file_link', 'historical_file_creation_date', 'latest_file_link', 'latest_file_creation_date', 'division_name'));
    }

    public function distributionUser(){
        if (file_exists( public_path() . '/latestreport' . Session::get('emp_code') . '.zip')) {
            $latest_file_link ='latestreport' . Session::get('emp_code') . '.zip';
            $latest_file_creation_date = date("j F Y H:i:s", filectime(public_path().'/'.$latest_file_link));
         }else{
            $latest_file_link = null;
            $latest_file_creation_date = null;
         }
        if (file_exists( public_path() . '/historicalreport' . Session::get('emp_code') . '.zip')) {
            $historical_file_link ='historicalreport' . Session::get('emp_code') . '.zip';
            $historical_file_creation_date = date("j F Y H:i:s", filectime(public_path().'/'.$historical_file_link));
         }else{
            $historical_file_link = null;
            $historical_file_creation_date = null;
         }
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $division_name = DB::table('brands')->select('div_name','div_id')->groupBy('div_name','div_id')->orderBy('div_name')->pluck('div_name','div_id')->toArray();
        //$data = VoluntaryQuotation::select("*", \DB::raw('(CASE  WHEN voluntary_quotation.parent_vq_id != "0" THEN (SELECT COUNT(*) FROM voluntary_quotation as vq1 where vq1.parent_vq_id = voluntary_quotation.parent_vq_id AND vq1.id <= voluntary_quotation.id) ELSE "0"  END) AS revision_count'))->where('year',$year)->where('vq_status',1)->whereIn('cfa_code',explode(',',Session::get("division_id")))->where('is_deleted', 0)->get();
        //return view('frontend.Distribution.list',compact('data', 'historical_file_link', 'latest_file_link'));
        //commented on 19032024 regarding serverside dt
        return view('frontend.Distribution.list',compact('historical_file_link', 'historical_file_creation_date', 'latest_file_link', 'latest_file_creation_date', 'division_name'));
    }

    public function hoUser(){
        if (file_exists( public_path() . '/latestreport' . Session::get('emp_code') . '.zip')) {
           $latest_file_link ='latestreport' . Session::get('emp_code') . '.zip';
           $latest_file_creation_date = date("j F Y H:i:s", filectime(public_path().'/'.$latest_file_link));
        }else{
           $latest_file_link = null;
           $latest_file_creation_date = null;
        }
        if (file_exists( public_path() . '/historicalreport' . Session::get('emp_code') . '.zip')) {
            $historical_file_link ='historicalreport' . Session::get('emp_code') . '.zip';
            $historical_file_creation_date = date("j F Y H:i:s", filectime(public_path().'/'.$historical_file_link));
         }else{
            $historical_file_link = null;
            $historical_file_creation_date = null;
         }
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        //$data = VoluntaryQuotation::select("*", \DB::raw('(CASE  WHEN voluntary_quotation.parent_vq_id != "0" THEN (SELECT COUNT(*) FROM voluntary_quotation as vq1 where vq1.parent_vq_id = voluntary_quotation.parent_vq_id AND vq1.id <= voluntary_quotation.id) ELSE "0"  END) AS revision_count'))->where('year',$year)->where('is_deleted', 0)->get();
        //return view('frontend.Ho.list',compact('data','latest_file_link','historical_file_link'));
        return view('frontend.Ho.list',compact('latest_file_link', 'historical_file_creation_date','historical_file_link', 'latest_file_creation_date'));
    }

    public function initiator(){
        if (file_exists( public_path() . '/latestreport' . Session::get('emp_code') . '.zip')) {
            $latest_file_link ='latestreport' . Session::get('emp_code') . '.zip';
            $latest_file_creation_date = date("j F Y H:i:s", filectime(public_path().'/'.$latest_file_link));
        }else{
            $latest_file_link = null;
            $latest_file_creation_date = null;
        }
        if (file_exists( public_path() . '/historicalreport' . Session::get('emp_code') . '.zip')) {
            $historical_file_link ='historicalreport' . Session::get('emp_code') . '.zip';
            $historical_file_creation_date = date("j F Y H:i:s", filectime(public_path().'/'.$historical_file_link));
        }else{
            $historical_file_link = null;
            $historical_file_creation_date = null;
        }

        /** This for just session datas there. remove it session will execute this condition */
        if(Session::get('paymode_vq_ids')):
            Session::forget('paymode_vq_ids');
        endif;
        if(Session::get('edit_paymode_vq_id_listing')):
            Session::forget('edit_paymode_vq_id_listing');
        endif;

	    $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $vq_sku_listing_controller = new VoluntaryQuotationSkuListingController;
        //$financialYears = $vq_sku_listing_controller->getLastFinancialYears(5);
        $financialYears = VoluntaryQuotation::select('year')
        ->where('is_deleted',0)->groupBy('year')->orderBy('year','DESC')->pluck('year')->toArray();
        // if($vq->parent_vq_id !=0){
        //     $revision_count = VoluntaryQuotation::where('parent_vq_id',$vq->parent_vq_id)->where('id','<=',$this->id)->count();

        // }else{
        //     $revision_count="0";
        // }
      //  $data = VoluntaryQuotation::select("*", \DB::raw('(CASE  WHEN voluntary_quotation.parent_vq_id != "0" THEN (SELECT COUNT(*) FROM voluntary_quotation as vq1 where vq1.parent_vq_id = voluntary_quotation.parent_vq_id AND vq1.id <= voluntary_quotation.id) ELSE "0"  END) AS revision_count'))->where('year',$year)->where('is_deleted', 0)->get();
	//$data = VoluntaryQuotation::select("*", \DB::raw('voluntary_quotation.rev_no as revision_count' ))->where('year',$year)->where('is_deleted', 0)->get();
	    // $data = VoluntaryQuotation::where('year',$year)->whereNotIn('institution_id',['AM0951','AR0963','AZ0076','BA1237','BH0504','BO0320','BR0347','CA1665','CH1915','CR0197','DE1422','DM0138','DM0152','DM0939','DM1080','DM1081','DM1082','DM1084','DM1092','DM1097','DM1101','DM1111','DM1115','DM1127','DM1131','DM1132','DM1133','DM1316','DM1319','DM1322','DM1326','DM1338','DM1488','DM1498','DM1623','DM1770','DM1829','DM2017','DM2198','DM2285','DM2365','DM2371','DM2376','DM2383','DM2395','DM2525','DM2995','DM3056','DM3130','DM3137','DM3144','DM3436','DM3467','DM3538','DM3623','DM3626','DM3627','DM3843','DM3862','DM3896','DM3938','DM4120','DM4400','DM4406','DM4666','DM4684','DM4806','DM4886','DM4942','DM5120','DM5151','DM5258','DM5287','DM5881','DM6194','DM6516','DM6528','DM6563','DM6569','DM6578','DM6620','DM6621','DM6622','DM6849','DM6859','DM6860','DM6893','DM6894','DM6905','DM6975','DM6977','DM7619','DM7674','DM7926','DM8166','DM8167','DM8171','DM8173','DM8178','DM8181','DM8182','DM8202','DM8204','DM8205','DM9145','DM9205','DM9213','DM9214','DM9272','DM9323','DM9432','DM9649','DM9650','DM9951','DR11254','DR4156','DR8850','FN0357','FN0360','FN0423','FN0425','FN0426','FN0427','FN0428','FN0429','FN0430','FN0431','FN0432','FN0436','FN0437','FN0439','FN0440','FN1350','FN1778','FN1781','FN1782','FN1783','FN1791','HI0467','IDP0004','IDP0005','IDP0006','IDP0008','IDP0009','IDP0013','IDP0014','IDP0015','IDP0016','IDP0033','IDP0034','IDP0039','IDP0053','IDP0054','IDP0058','IDP0063','IDP0065','IDP0066','IDP0067','IDP0094','IDP0095','IDP0156','IDP0165','IDP0166','IDP0167','IDP0168','IDP0169','IDP0170','IDP0171','JA1170','JU0058','JU0061','JU0092','JU0103','KE0410','MA1121','MA2188','ME0792','ME1215','ME1572','MS0003','PD0002','PR1678','PS0106','RE7246','RS10301','RS1258','RS1459','RS1662','RS1812','RS1933','RS1934','RS2253','RS2359','RS2485','RS2518','RS2670','RS2737','RS2822','RS2824','RS2839','RS2916','RS2935','RS2970','RS3123','RS3258','RS3350','RS3428','RS3430','RS4276','RS6025','RS6473','RS6819','RS6878','RS7100','RS7318','RS7440','RS7565','RS7732','RS7735','RS7916','RS7917','RS8021','RS8144','RS8283','RS8484','RS8557','RS8621','RS8799','RS8801','RS8804','RS8825','RS8828','RS8830','RS8839','RS8840','RS8870','RS8990','RS8996','RS9096','RS9493','RS9562','RS9571','RS9585','RU0204','SA2546','SR8870','SU2964','WE0241','WO0171','WO0172','YA0136'])->get();
        $division_name = DB::table('brands')->select('div_name','div_id')->groupBy('div_name','div_id')->orderBy('div_name')->pluck('div_name','div_id')->toArray();
        return view('frontend.Initiator.list',compact('historical_file_link', 'latest_file_link','financialYears', 'historical_file_creation_date','latest_file_creation_date', 'division_name'));
    }

    public function activityDetails(Request $request){
        $currentYear = date("Y");
        $nextYear = date("Y", strtotime("+1 year"));
        $data = ActivityTracker::select('activity_trackers.*' , 'voluntary_quotation.hospital_name' , 'voluntary_quotation.city')->orderBy('activity_trackers.id','DESC')
        ->join('voluntary_quotation', function($j) use($currentYear , $nextYear){
            $j->on('activity_trackers.vq_id', '=', 'voluntary_quotation.id')
            ->where('voluntary_quotation.year' , $currentYear.'-'.$nextYear);
        })
        ->when($request->vq_id, function($q) use ($request) {
            $q->whereIn('voluntary_quotation.institution_id', $request->vq_id);
        })
        ->when($request->type , function($q) use ($request) {
            $q->whereIn('activity_trackers.type', $request->type);
        })
        ->get();
       
        
        $types = ActivityTracker::select('type')->distinct()->whereNotNull('type')->get();
        $formattedTypes = $types->map(function ($type) {
            return [
                'db_value' => $type->type,
                'display_value' => ucwords(str_replace('_', ' ', $type->type))
            ];
        });
        $institution_names = VoluntaryQuotation::where('year' , $currentYear.'-'.$nextYear)->where('parent_vq_id' , 0)->select('institution_id', 'id', 'hospital_name', 'city' , 'institution_id')->groupBy('institution_id')->get();
        foreach ($data as $activity) {
            $activity->json_data = $this->extractJson($activity->activity);
            $activity->activity_text = $this->stripJson($activity->activity);
        }
       

        return view('frontend.Initiator.activity-main',compact('data','formattedTypes' , 'institution_names'));
    }
    public function activityDetailsJson(Request $request){
        $currentYear = date("Y");
        $nextYear = date("Y", strtotime("+1 year"));
        $data = ActivityTracker::select('activity_trackers.*' , 'voluntary_quotation.hospital_name' , 'voluntary_quotation.city')->orderBy('activity_trackers.id','DESC')
        ->join('voluntary_quotation', function($j) use($currentYear , $nextYear){
            $j->on('activity_trackers.vq_id', '=', 'voluntary_quotation.id')
            ->where('voluntary_quotation.year' , $currentYear.'-'.$nextYear);
        })
        ->when($request->vq_id, function($q) use ($request) {
            $q->whereIn('voluntary_quotation.institution_id', $request->vq_id);
        })
        ->when($request->type , function($q) use ($request) {
            $q->whereIn('activity_trackers.type', $request->type);
        })
        ->get();
        foreach ($data as $activity) {
            $activity->json_data = $this->extractJson($activity->activity);
            $activity->activity_text = $this->stripJson($activity->activity);
        }
        $result  = response()->json(['status' => true , 'data' => $data]);
       

        return $result;
    }

    public function activityDetailsInitiator($id){
        // $data = ActivityTracker::join('employee_master','employee_master.emp_code','=','activity_trackers.emp_code')
        // ->join('voluntary_quotation','voluntary_quotation.id','=','activity_trackers.vq_id')
        // ->where('activity_trackers.vq_id',$id)
        // ->select('employee_master.emp_name','employee_master.emp_level','voluntary_quotation.comments','activity_trackers.vq_id')->get();
        // dd($data);
        $data = ActivityTracker::where('vq_id',$id)->orderBy('id','DESC')->get();;
        $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $url_id = $id;

        // echo '<pre>';
        $response_failed_filtered = array_filter($data->toArray(), function($entry) {
            return $entry['type'] === 'Idap_disc_tran_api_response_failed';
        });
        $insert_failed_filtered = array_filter($data->toArray(), function($entry) {
            return $entry['type'] === 'Idap_disc_tran_insert_failed';
        });
        $Idap_disc_tran_failed = array_merge($response_failed_filtered, $insert_failed_filtered);
        // print_r($Idap_disc_tran_failed);die;
        /*$acitvity_map = [
            'initiate' => 'Initiate',
            'update'=>'Update',
            'bulkapprove'=>'Bulk approve',
            'vq_metis_object'=>'Send Quotation Request JSON',
            'vq_metis_response'=>'Send Quotation Response JSON',
            'approve'=>'Approve',
            'bulkupdate'=>'Bulk Update',
            'reinitiate'=>'Reinitiate',
            'poc_feedback'=>'POC Feedback',
            'delete_vq_metis_object'=>'Delete VQ Request JSON',
            'delete_vq_metis_response'=>'Delete VQ Response JSON',
            'vq_metis_child_request'=>'Send Quotation Child Hospital Request JSON',
        ];*/
        $types = ActivityTracker::select('type')->distinct()->whereNotNull('type')->where('vq_id', '!=', 1)->orderBy('type')->get();
        $formattedTypes = $types->map(function ($type) {
            return [
                'db_value' => $type->type,
                'display_value' => ucwords(str_replace('_', ' ', $type->type))
            ];
        });
        foreach ($data as $activity) {
            $activity->json_data = $this->extractJson($activity->activity);
            $activity->activity_text = $this->stripJson($activity->activity);
        }
        // Handle JSON download for specific activity
        if (request()->has('download') && request()->has('activity_id')) {
            $activity = ActivityTracker::find(request('activity_id'));

            if ($activity) {
                $json = $this->extractJson($activity->activity);
                if ($json) {
                    $jsonData = json_encode(json_decode($json), JSON_PRETTY_PRINT);
                    $fileName = 'activity_' . request('activity_id') . '.txt';

                    // Return the JSON file as a download response
                    return response()->streamDownload(function () use ($jsonData) {
                        echo $jsonData;
                    }, $fileName, ['Content-Type' => 'application/json']);
                }
            }
        }

        return view('frontend.Initiator.activity',compact('data','url_id','vq','formattedTypes'));
    }

    public function activityDetailsUser($id){
        // $data = ActivityTracker::join('employee_master','employee_master.emp_code','=','activity_trackers.emp_code')
        // ->join('voluntary_quotation','voluntary_quotation.id','=','activity_trackers.vq_id')
        // ->where('activity_trackers.vq_id',$id)
        // ->select('employee_master.emp_name','employee_master.emp_level','voluntary_quotation.comments','activity_trackers.vq_id')->get();
        // dd($data);

        $data = ActivityTracker::where('vq_id',$id)->orderBy('id','DESC')->get();
        $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $url_id = $id;
        return view('frontend.User.activity',compact('data','url_id','vq'));
    }
    public function activityDetailsApprover($id){

        $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $data = ActivityTracker::where('vq_id',$id)->orderBy('id','DESC')->get();
        // dd($data);
        $url_id = $id;

        $types = ActivityTracker::select('type')->distinct()->whereNotNull('type')->where('vq_id', '!=', 1)->orderBy('type')->get();
        $formattedTypes = $types->map(function ($type) {
            return [
                'db_value' => $type->type,
                'display_value' => ucwords(str_replace('_', ' ', $type->type))
            ];
        });
        foreach ($data as $activity) {
            $activity->json_data = $this->extractJson($activity->activity);
            $activity->activity_text = $this->stripJson($activity->activity);
        }
        // Handle JSON download for specific activity
        if (request()->has('download') && request()->has('activity_id')) {
            $activity = ActivityTracker::find(request('activity_id'));

            if ($activity) {
                $json = $this->extractJson($activity->activity);
                if ($json) {
                    $jsonData = json_encode(json_decode($json), JSON_PRETTY_PRINT);
                    $fileName = 'activity_' . request('activity_id') . '.txt';

                    // Return the JSON file as a download response
                    return response()->streamDownload(function () use ($jsonData) {
                        echo $jsonData;
                    }, $fileName, ['Content-Type' => 'application/json']);
                }
            }
        }

        return view('frontend.Approver.activity',compact('data','url_id','vq','formattedTypes'));
    }
 
    public function activityDetailsPoc($id){
        $data = ActivityTracker::where('vq_id',$id)->orderBy('id','DESC')->get();;
        $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $url_id = $id;
        $types = ActivityTracker::select('type')->distinct()->whereNotNull('type')->where('vq_id', '!=', 1)->orderBy('type')->get();
        $formattedTypes = $types->map(function ($type) {
            return [
                'db_value' => $type->type,
                'display_value' => ucwords(str_replace('_', ' ', $type->type))
            ];
        });
        foreach ($data as $activity) {
            $activity->json_data = $this->extractJson($activity->activity);
            $activity->activity_text = $this->stripJson($activity->activity);
        }
        // Handle JSON download for specific activity
        if (request()->has('download') && request()->has('activity_id')) {
            $activity = ActivityTracker::find(request('activity_id'));

            if ($activity) {
                $json = $this->extractJson($activity->activity);
                if ($json) {
                    $jsonData = json_encode(json_decode($json), JSON_PRETTY_PRINT);
                    $fileName = 'activity_' . request('activity_id') . '.txt';

                    // Return the JSON file as a download response
                    return response()->streamDownload(function () use ($jsonData) {
                        echo $jsonData;
                    }, $fileName, ['Content-Type' => 'application/json']);
                }
            }
        }
        return view('frontend.Poc.activity',compact('data','url_id','vq','formattedTypes'));
    }

    public function activityDetailsDistribution($id){
        $data = ActivityTracker::where('vq_id',$id)->orderBy('id','DESC')->get();;
        $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $url_id = $id;
        $types = ActivityTracker::select('type')->distinct()->whereNotNull('type')->where('vq_id', '!=', 1)->orderBy('type')->get();
        $formattedTypes = $types->map(function ($type) {
            return [
                'db_value' => $type->type,
                'display_value' => ucwords(str_replace('_', ' ', $type->type))
            ];
        });
        foreach ($data as $activity) {
            $activity->json_data = $this->extractJson($activity->activity);
            $activity->activity_text = $this->stripJson($activity->activity);
        }
        // Handle JSON download for specific activity
        if (request()->has('download') && request()->has('activity_id')) {
            $activity = ActivityTracker::find(request('activity_id'));

            if ($activity) {
                $json = $this->extractJson($activity->activity);
                if ($json) {
                    $jsonData = json_encode(json_decode($json), JSON_PRETTY_PRINT);
                    $fileName = 'activity_' . request('activity_id') . '.txt';

                    // Return the JSON file as a download response
                    return response()->streamDownload(function () use ($jsonData) {
                        echo $jsonData;
                    }, $fileName, ['Content-Type' => 'application/json']);
                }
            }
        }
        return view('frontend.Distribution.activity',compact('data','url_id','vq','formattedTypes'));
    }

    public function activityDetailsHo($id){
        $data = ActivityTracker::where('vq_id',$id)->orderBy('id','DESC')->get();;
        $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $url_id = $id;
        $types = ActivityTracker::select('type')->distinct()->whereNotNull('type')->where('vq_id', '!=', 1)->orderBy('type')->get();
        $formattedTypes = $types->map(function ($type) {
            return [
                'db_value' => $type->type,
                'display_value' => ucwords(str_replace('_', ' ', $type->type))
            ];
        });
        foreach ($data as $activity) {
            $activity->json_data = $this->extractJson($activity->activity);
            $activity->activity_text = $this->stripJson($activity->activity);
        }
        // Handle JSON download for specific activity
        if (request()->has('download') && request()->has('activity_id')) {
            $activity = ActivityTracker::find(request('activity_id'));

            if ($activity) {
                $json = $this->extractJson($activity->activity);
                if ($json) {
                    $jsonData = json_encode(json_decode($json), JSON_PRETTY_PRINT);
                    $fileName = 'activity_' . request('activity_id') . '.txt';

                    // Return the JSON file as a download response
                    return response()->streamDownload(function () use ($jsonData) {
                        echo $jsonData;
                    }, $fileName, ['Content-Type' => 'application/json']);
                }
            }
        }
        return view('frontend.Ho.activity',compact('data','url_id','vq','formattedTypes'));
    }

    public function create_request(){
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        $ignoredInstitutions = IgnoredInstitutions::get();
        $ignoredInstitutions = array_map(function($inst){return $inst->institution_id;}, $ignoredInstitutions->all());
        $headers = [
            'Content-Type' => 'application/json',
            'AccessToken' => 'key',
            'Authorization' => 'Bearer '.$jwt['jwt_token'],
        ];
          
        $client = new GuzzleClient([
            'headers' => $headers,
            'verify' => false
        ]);
          
        $body = '{}';
          
        $r = $client->request('POST', env('API_URL').'/API/Institutions', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        
        // $data = json_decode($res->getBody());
        $resp = json_decode($response);
        $resp_collection =collect($resp);

        // Code to remove the institutions whiich are present in ignored_institutions table
        $resp = collect($resp);
        $resp = $resp->whereNotIn('INST_ID',$ignoredInstitutions)->toArray();
    
        usort($resp, function ($item1, $item2) {
            return $item1->INST_NAME <=> $item2->INST_NAME;
        });

        $created_date = VoluntaryQuotation::select('created_at')->where('is_deleted', 0)->first();

        // Get Products from DB to Old Pack
        $oldProductsApi = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.*', 'voluntary_quotation.contract_start_date','voluntary_quotation.contract_end_date')
        ->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
        ->groupBy('voluntary_quotation_sku_listing.item_code')
        ->where('voluntary_quotation.year', $year)
        ->get()->toArray();
        $data['old_product_data'] = $oldProductsApi;
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        // $currentCycleInstitutes = VoluntaryQuotation::where('year', $year)->where('parent_vq_id', 0)->where('is_deleted', 0)->get(); //hide by arunchandru 15012025
        $currentCycleInstitutes = VoluntaryQuotation::where('year', $year)->where('parent_vq_id', 0)->whereNotIn('institution_id',$ignoredInstitutions)->where('is_deleted', 0)->get(); // added by arunchadnru 15012025

        // Get new hospitals which are not initiated
        $currentCycleInstituteIdArr = $currentCycleInstitutes->map(function ($user) {
            return $user->institution_id;
        });
        $newInstitutesArr = $resp_collection->whereNotIn('INST_ID',$currentCycleInstituteIdArr)->whereNotIn('INST_ID',$ignoredInstitutions)->toArray();

        // Get Products from API to New Pack
        $newProductsApi = $client->request('POST', env('API_URL').'/api/Products', [
            'body' => $body
        ]);
        $newProductResponse = $newProductsApi->getBody()->getContents();
        $newProducts = json_decode($newProductResponse);
        $data['new_product_data'] = $newProducts;
        
        $reinitate_show = 'no';
        $initiator_button_hide ='no';
        if(isset($created_date['created_at'])){
	      	if(strtotime($created_date['created_at']) < strtotime('-45 days')) {
				$reinitate_show = 'yes'; 
 			}  
        }
        $data['is_reinitiate'] = VoluntaryQuotation::where('year',$year)->where('is_deleted', 0)->exists();
        if($data['is_reinitiate']==true){
            $initiator_button_hide ='yes';
        }else{
            $initiator_button_hide ='no'; 
        }
        // $currentCycleInstitutesNewCounterRt = VoluntaryQuotation::where('year', $year)->where('parent_vq_id', 0)->where('is_deleted', 0)->where('vq_status',1)->get(); //hide by arunchandru 15012025
        $currentCycleInstitutesNewCounterRt = VoluntaryQuotation::where('year', $year)->where('parent_vq_id', 0)->whereNotIn('institution_id',$ignoredInstitutions)->where('is_deleted', 0)->where('vq_status',1)->get(); // added by arunchadnru 15012025
        $checkLockingEnabled = DB::table('check_discounted')->select('is_enabled')->where('year',$year)->where('is_enabled','Y')->exists();

        $data['reinit_data'] = $resp;
        $data['reinitate_show'] = $reinitate_show;
        $data['intiator_button_status'] =  $initiator_button_hide;
        $data['current_cycle_institutes'] = $currentCycleInstitutes;
        $data['new_institutes'] = $newInstitutesArr;
        $data['currentCycleInstitutesNewCounterRt'] = $currentCycleInstitutesNewCounterRt;
        $data['lockingEnabled'] = $checkLockingEnabled;
        $year_arr = explode('-', $year);
        $start_date = '01/04/'.$year_arr[0];
        $end_date = '31/03/'.$year_arr[1];
        return view('frontend.Initiator.createRequest',compact('data' , 'start_date' , 'end_date'));
    }

    public function viewStockist($id){
      $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
    //   dd($jwt);
      $headers = [
        'Content-Type' => 'application/json',
        'AccessToken' => 'key',
        'Authorization' => 'Bearer '.$jwt['jwt_token'],
            ];
            
         $client = new GuzzleClient([
              'headers' => $headers,
              'verify' => false
          ]);
        
        $body = '{
        "INST_ID": "'.$id.'"
        }';
        
        $r = $client->request('POST', env('API_URL').'/api/Stockists', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
    
        // $data = json_decode($res->getBody());
        $resp = json_decode($response);
        // dd($resp);  
        $cnt = 0;
        if(count($resp)>0)
        {
            //$stockist_data = Stockist_master::where('institution_code',$id)->update(['stockist_type_flag'=>0,'updated_at'=> now()]);
        }
        foreach($resp as $item){
            /*$upd = Stockist_master::updateOrCreate(['institution_code' => $id,'stockist_code' => $item->STOCKIST_CODE ], [ 
                'stockist_name' => $item->STOCKIST_NAME,
                'stockist_address' => $item->STOCKIST_ADDRESS,
                'email_id'=> $item->STOCKIST_EMAIL,
                'stockist_type_flag' => 1
            ]);*/
        }
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $vq = VoluntaryQuotation::where('year',$year)->where('institution_id',$id)->where('is_deleted', 0)->first();
        $data = Stockist_master::where('institution_code',$id)->get();
        return view('frontend.Initiator.viewStockist',compact('data','vq'));
    
    }

    public function viewPoc($id){
        /** PAY MODE Session Data's */
        $vq_id_Session_data = Session::get("paymode_vq_ids");
        if(!empty($vq_id_Session_data)):
            if (($key = array_search($id, $vq_id_Session_data)) !== false) {
                unset($vq_id_Session_data[$key]); // Remove the specific value
            }
            Session::put('paymode_vq_ids', $vq_id_Session_data);
        endif;
        /** EDIT PAY MODE Session Data's */
        $edit_paymode_vq_id_listing = Session::get("edit_paymode_vq_id_listing");
        if(!empty($edit_paymode_vq_id_listing)):
            if (($key = array_search($id, $edit_paymode_vq_id_listing)) !== false) {
                unset($edit_paymode_vq_id_listing[$key]); // Remove the specific value
            }
            Session::put('edit_paymode_vq_id_listing', $edit_paymode_vq_id_listing);
        endif;


        // echo '<pre>';

        $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $send_json = array();
        $skuIdArr = Session::get('idArr');
        // print_r($skuIdArr);die;
        $listing_data = VoluntaryQuotationSkuListingStockist::join('voluntary_quotation_sku_listing', 'voluntary_quotation_sku_listing.id', '=', 'voluntary_quotation_sku_listing_stockist.sku_id')
                ->select('voluntary_quotation_sku_listing_stockist.payment_mode', 'voluntary_quotation_sku_listing_stockist.net_discount_percent',  'voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.item_code',  'voluntary_quotation_sku_listing.discount_percent', 'voluntary_quotation_sku_listing.discount_rate', 'voluntary_quotation_sku_listing.mrp','voluntary_quotation_sku_listing.ptr', 'stockist_master.stockist_code', 'stockist_master.stockist_name')
                ->join('stockist_master', 'stockist_master.id', '=', 'voluntary_quotation_sku_listing_stockist.stockist_id')
                ->where('voluntary_quotation_sku_listing_stockist.vq_id', $vq->id)
                ->where('voluntary_quotation_sku_listing_stockist.is_deleted', 0)
                ->where('voluntary_quotation_sku_listing.is_deleted', 0)
                ->where('stockist_master.stockist_type_flag', 1);
        if($skuIdArr == null){
            $listing_data = $listing_data->get();
            // print_r(count($listing_data));die;
            $validate_empty_datas = array();
            $exist_data = array();
            $send_json = array();
            $date=date_create($vq->created_at);
            if (date_format($date,"m") >= 4) {//On or After April (FY is current year - next year)
                $financial_year = (date_format($date,'Y')) . '-' . (date_format($date,'y')+1);
            } else {//On or Before March (FY is previous year - current year)
                $financial_year = (date_format($date,'Y')-1) . '-' . date_format($date,'y');
            }

            $is_IdapDiscTran = IdapDiscTran::where('IDAP_PRNT_Q_ID', $vq->id)->exists();
            if($is_IdapDiscTran == true):
                $base_idap_prnt_q_id = $vq->id; // Base vq_id
                // Fetch all IDs matching the base pattern
                $max_idap_prnt_q_id = IdapDiscTran::where('IDAP_PRNT_Q_ID', 'LIKE', $base_idap_prnt_q_id . "%")->max('IDAP_PRNT_Q_ID');
                // $idap_prnt_q_id = $max_idap_prnt_q_id;
                if ($max_idap_prnt_q_id) {
                    // Extract the decimal part of max_idap_prnt_q_id
                    $parts = explode('_', $max_idap_prnt_q_id);
                    
                    if (isset($parts[1])) {
                        // $max_idap_prnt_q_id = IdapDiscTran::where('idap_prnt_q_id', 'LIKE', $base_idap_prnt_q_id . "_%")->max('idap_prnt_q_id');
                        $sql = "SELECT IDAP_PRNT_Q_ID FROM IDAP_DISC_TRAN WHERE IDAP_PRNT_Q_ID LIKE '{$base_idap_prnt_q_id}_%' ORDER BY CAST(SUBSTRING_INDEX(IDAP_PRNT_Q_ID, '_', -1) AS DECIMAL(10,2)) DESC LIMIT 1";
                        $get_max_idap_prnt_q_id = DB::select($sql);
                        $max_idap_prnt_q_id = $get_max_idap_prnt_q_id[0]->IDAP_PRNT_Q_ID;
                        $parts1 = explode('_', $max_idap_prnt_q_id);
                        $numeric_parts = (string) $parts1[1];
                        $new_numeric_part = bcadd($numeric_parts, '0.01', 2); // Increment by 0.1
                        // Generate the new unique ID
                        $idap_prnt_q_id =  $base_idap_prnt_q_id . '_' . $new_numeric_part;
                    } else {
                        $idap_prnt_q_id = $base_idap_prnt_q_id . '_0.01'; // First increment
                    }
                } else {
                    $idap_prnt_q_id = $base_idap_prnt_q_id; // No existing record, use base ID
                }
            else:
                $idap_prnt_q_id = $vq->id;
            endif;

            $send_json['fin_year'] = $financial_year;
            $send_json['institute_code'] = $vq->institution_id;
            $send_json['vq_id'] = $vq->id;
            $send_json['revision_number'] = $vq->rev_no;
            //Update last year data with current year
            foreach($listing_data as $single_data){
                $sku_arr['item_code'] = $single_data['item_code'];
                $sku_arr['div_code'] = $single_data['div_id'];
                $sku_arr['discount_percent'] = $single_data['discount_percent'];
                $sku_arr['discount_rate'] = $single_data['discount_rate'];
                $sku_arr['stockist_code'] = $single_data['stockist_code'];
                $sku_arr['stockist_name'] = $single_data['stockist_name'];
                $sku_arr['payment_mode'] = $single_data['payment_mode'];
                $sku_arr['net_discount_percent'] = $single_data['net_discount_percent'];

                if($single_data['net_discount_percent'] === null || $single_data['net_discount_percent'] === 0):
                    $validate_empty_datas[] = $sku_arr;
                endif;
                if($single_data['payment_mode'] === null || $single_data['payment_mode'] === 0):
                    $validate_empty_datas[] = $sku_arr;
                endif;
                $send_json['sku'][]=$sku_arr;
            }
           
            foreach ($send_json['sku'] as $json_data) {
                $exist_data[] = [
                    'FIN_YEAR' => $send_json['fin_year'],
                    'INST_ID' => $send_json['institute_code'],
                    'DIV_CODE' => $json_data['div_code'],
                    'ITEM_CODE' => $json_data['item_code'],
                    'IDAP_PRNT_Q_ID' => $idap_prnt_q_id,
                    'REV_NO' => $send_json['revision_number'],
                    'STOCKIST_CODE' => $json_data['stockist_code'],
                    'STOCKIST_NAME' => $json_data['stockist_name'],
                    'DISCOUNT_MODE' => $json_data['payment_mode'],
                ];
            }

            // print_r($exist_data);
            // $encoded_data = array_map('json_encode', $exist_data);
            // $duplicates = array_unique(array_diff_assoc($encoded_data, array_unique($encoded_data)));
            // print_r($duplicates);die;
            // $duplicate_arrays = array_map('json_decode', $duplicates, true);

            $serialized_data = array_map('serialize', $exist_data);
            $duplicates = array_unique(array_diff_assoc($serialized_data, array_unique($serialized_data)));
            $duplicate_arrays = array_map('unserialize', $duplicates);
            
            $duplicate_array_datas[] = $duplicate_arrays; 

            $validate_empty_array_datas[] = $validate_empty_datas; 

            // echo 'Duplicates';
            // print_r($duplicate_array_datas);
            // echo 'empty';
            // print_r($validate_empty_array_datas);
                    
        }else{
            /*added on 15052024 for revision wise activity log and send quotation api send starts*/
            $all_vq = VoluntaryQuotationSkuListingStockist::select('voluntary_quotation.id','voluntary_quotation.rev_no','voluntary_quotation.institution_id','voluntary_quotation.rev_no','voluntary_quotation.contract_start_date','voluntary_quotation.contract_end_date','voluntary_quotation.created_at')->join('voluntary_quotation', 'voluntary_quotation.id','=','voluntary_quotation_sku_listing_stockist.vq_id')
                ->whereIn('voluntary_quotation_sku_listing_stockist.id', $skuIdArr)->where('voluntary_quotation.is_deleted', 0)->distinct()->get();
            // print_r(count($all_vq));die;
            
            foreach ($all_vq as $vq_data_final) {
                
                $listing_data = VoluntaryQuotationSkuListingStockist::join('voluntary_quotation_sku_listing', 'voluntary_quotation_sku_listing.id', '=', 'voluntary_quotation_sku_listing_stockist.sku_id')
                ->select('voluntary_quotation_sku_listing_stockist.payment_mode', 'voluntary_quotation_sku_listing_stockist.net_discount_percent',  'voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.item_code',  'voluntary_quotation_sku_listing.discount_percent', 'voluntary_quotation_sku_listing.discount_rate', 'voluntary_quotation_sku_listing.mrp','voluntary_quotation_sku_listing.ptr', 'stockist_master.stockist_code', 'stockist_master.stockist_name')
                ->join('stockist_master', 'stockist_master.id', '=', 'voluntary_quotation_sku_listing_stockist.stockist_id')
                ->where('voluntary_quotation_sku_listing_stockist.vq_id', $vq_data_final->id)
                ->where('voluntary_quotation_sku_listing_stockist.is_deleted', 0)
                ->whereIn('voluntary_quotation_sku_listing_stockist.id', $skuIdArr)
                ->where('voluntary_quotation_sku_listing.is_deleted', 0)->where('stockist_master.stockist_type_flag', 1)->get();
                // print_r(count($listing_data));

                $vq_listing_controller = new VqListingController;
                $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
                $send_json = array();
                $exist_data = array();
                $validate_empty_datas = array();
                $date=date_create($vq_data_final->created_at);
                if (date_format($date,"m") >= 4) {//On or After April (FY is current year - next year)
                    $financial_year = (date_format($date,'Y')) . '-' . (date_format($date,'y')+1);
                } else {//On or Before March (FY is previous year - current year)
                    $financial_year = (date_format($date,'Y')-1) . '-' . date_format($date,'y');
                }

                $is_IdapDiscTran = IdapDiscTran::where('IDAP_PRNT_Q_ID', $vq_data_final->id)->exists();
                if($is_IdapDiscTran == true):
                    $base_idap_prnt_q_id = $vq_data_final->id; // Base vq_id
                    // Fetch all IDs matching the base pattern
                    $max_idap_prnt_q_id = IdapDiscTran::where('IDAP_PRNT_Q_ID', 'LIKE', $base_idap_prnt_q_id . "%")->max('IDAP_PRNT_Q_ID');
                    
                    if ($max_idap_prnt_q_id) {
                        // Extract the decimal part of max_idap_prnt_q_id
                        $parts = explode('_', $max_idap_prnt_q_id);
                        
                        if (isset($parts[1])) {
                            // $max_idap_prnt_q_id = IdapDiscTran::where('idap_prnt_q_id', 'LIKE', $base_idap_prnt_q_id . "_%")->max('idap_prnt_q_id');
                            $sql = "SELECT IDAP_PRNT_Q_ID FROM IDAP_DISC_TRAN WHERE IDAP_PRNT_Q_ID LIKE '{$base_idap_prnt_q_id}_%' ORDER BY CAST(SUBSTRING_INDEX(IDAP_PRNT_Q_ID, '_', -1) AS DECIMAL(10,2)) DESC LIMIT 1";
                            $get_max_idap_prnt_q_id = DB::select($sql);
                            $max_idap_prnt_q_id = $get_max_idap_prnt_q_id[0]->IDAP_PRNT_Q_ID;
                            $parts1 = explode('_', $max_idap_prnt_q_id);
                            $numeric_parts = (string) $parts1[1];
                            $new_numeric_part = bcadd($numeric_parts, '0.01', 2); // Increment by 0.1
                            // Generate the new unique ID
                            $idap_prnt_q_id =  $base_idap_prnt_q_id . '_' . $new_numeric_part;
                        } else {
                            $idap_prnt_q_id = $base_idap_prnt_q_id . '_0.01'; // First increment
                        }
                    } else {
                        $idap_prnt_q_id = $base_idap_prnt_q_id; // No existing record, use base ID
                    }
                else:
                    $idap_prnt_q_id = $vq_data_final->id;
                endif;

                $send_json['fin_year'] = $financial_year;
                $send_json['institute_code'] = $vq_data_final->institution_id;
                $send_json['vq_id'] = $vq_data_final->id;
                $send_json['revision_number'] = $vq_data_final->rev_no;
                foreach($listing_data as $single_data){
                    $sku_arr['item_code'] = $single_data['item_code'];
                    $sku_arr['div_code'] = $single_data['div_id'];
                    $sku_arr['discount_percent'] = $single_data['discount_percent'];
                    $sku_arr['discount_rate'] = $single_data['discount_rate'];
                    $sku_arr['stockist_code'] = $single_data['stockist_code'];
                    $sku_arr['stockist_name'] = $single_data['stockist_name'];
                    $sku_arr['payment_mode'] = $single_data['payment_mode'];
                    $sku_arr['net_discount_percent'] = $single_data['net_discount_percent'];
    
                    if($single_data['net_discount_percent'] === null || $single_data['net_discount_percent'] === 0):
                        $validate_empty_datas[] = $sku_arr;
                    endif;
                    if($single_data['payment_mode'] === null || $single_data['payment_mode'] === 0):
                        $validate_empty_datas[] = $sku_arr;
                    endif;
                    
                    $send_json['sku'][]=$sku_arr;

                }
                
                foreach ($send_json['sku'] as $json_data) {
                    $exist_data[] = [
                        'FIN_YEAR' => $send_json['fin_year'],
                        'INST_ID' => $send_json['institute_code'],
                        'DIV_CODE' => $json_data['div_code'],
                        'ITEM_CODE' => $json_data['item_code'],
                        'IDAP_PRNT_Q_ID' => $idap_prnt_q_id,
                        'REV_NO' => $send_json['revision_number'],
                        'STOCKIST_CODE' => $json_data['stockist_code'],
                        'STOCKIST_NAME' => $json_data['stockist_name'],
                        'DISCOUNT_MODE' => $json_data['payment_mode'],
                    ];
                }
                
                // echo '<pre>';
                // print_r(count($exist_data));
                // $encoded_data = array_map('json_encode', $exist_data);
                // $duplicates = array_unique(array_diff_assoc($encoded_data, array_unique($encoded_data)));
                // $duplicate_arrays = array_map('json_decode', $duplicates, true);

                
                $serialized_data = array_map('serialize', $exist_data);
                $duplicates = array_unique(array_diff_assoc($serialized_data, array_unique($serialized_data)));
                $duplicate_arrays = array_map('unserialize', $duplicates);
                
                $duplicate_array_datas[] = $duplicate_arrays; 
                $validate_empty_array_datas[] = $validate_empty_datas;

                
            }
        }

        // echo '<pre>';
        // echo 'Duplicate';
        // print_r($duplicate_array_datas);
        // echo 'empty';
        // print_r($validate_empty_array_datas);

        $html = $html2 ='';
        if(!empty(array_filter($duplicate_array_datas))):
            foreach(array_filter($duplicate_array_datas) as $first_loop):
                foreach($first_loop as $second_loop):
                    $html .= '<li>'.$second_loop['ITEM_CODE'].' in '.$vq->hospital_name.' '.$second_loop['STOCKIST_CODE'].' '.$second_loop['STOCKIST_NAME'].'</li>';
                endforeach;
            endforeach;
        endif;

        if(!empty(array_filter($validate_empty_array_datas))):
            if($vq->vq_status != 0):
                foreach(array_filter($validate_empty_array_datas) as $empty_data_first_loop):
                    $empty_data_first_loop = array_map("unserialize", array_unique(array_map("serialize", $empty_data_first_loop)));
                    foreach($empty_data_first_loop as $empty_data_second_loop):
                        $payment_mode = ($empty_data_second_loop['payment_mode'] === null || $empty_data_second_loop['payment_mode'] === 0)? 'is Pay mode value is null' : '' ;
                        $net_discount_percent = ($empty_data_second_loop['net_discount_percent'] === null || $empty_data_second_loop['net_discount_percent'] === 0)? 'is net_discount_percent value is null' : '' ;
                        $html2 .= '<li>'.$empty_data_second_loop['item_code'].' in '.$vq->hospital_name.' '.$empty_data_second_loop['stockist_code'].' '.$empty_data_second_loop['stockist_name'].' '.$payment_mode.', '.$net_discount_percent.'</li>';
                    endforeach;
                endforeach;
                $idap_disc_tran_exist['html2'] = $html2;
                $idap_disc_tran_exist['validate_empty_datas'] = array_filter($validate_empty_datas);
            endif;
        endif;

        // echo '$html';
        // print_r($html);
        // echo '$html2';
        // print_r($html2);
        $idap_disc_tran_exist['exists_flag'] = 0;
        $idap_disc_tran_exist['html'] = '';
        $idap_disc_tran_exist['html2'] = '';
        if($html != '' || $html2 != ''):
            $idap_disc_tran_exist['exists_flag'] = 1;
            $idap_disc_tran_exist['html'] = $html;
            $idap_disc_tran_exist['html2'] = $html2;
        endif;

        

        $data = PocMaster::where('institution_id',$vq['institution_id'])->get();
        return view('frontend.Initiator.pocListing',compact('data', 'idap_disc_tran_exist'));
    
    }
    public function sendQuotation(Request $request){
        $id = 1;
        $data = array();
        $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $data['vq_data']= $vq;
        $data['stockist_data'] = VoluntaryQuotation::join('stockist_master','stockist_master.institution_code','=','voluntary_quotation.institution_id')
        ->where('voluntary_quotation.id',$id)
        ->where('stockist_master.stockist_type_flag',1)
        ->where('voluntary_quotation.is_deleted', 0)
        ->select('stockist_master.*')->get();
        
        $data['poc_data'] = VoluntaryQuotation::join('poc_master','poc_master.institution_id','=','voluntary_quotation.institution_id')
        ->where('voluntary_quotation.id',$id)
        ->where('voluntary_quotation.is_deleted', 0)
        ->select('poc_master.*')->first();

        $data['spll_signature']=Signature::first();
        $data['spil_signature']=Signature::first();
        $data["email"]="mansoor@noesis.tech";
        // $data["client_name"]=$request->get("client_name");
        $data["subject"]="IDAP Quotation Mail";
 
        $spllPdf = PDF::loadView('admin.pdf.spilpdf', compact('data'));
        $spilPdf = PDF::loadView('admin.pdf.spilpdf', compact('data'));

        $spllExcel = Excel::raw(new InitiatorExport($id,'SPLL'), BaseExcel::XLSX);
        $spilExcel = Excel::raw(new InitiatorExport($id,'SPIL'), BaseExcel::XLSX);
        
        try{
            Mail::send('admin.emails.send_quotation', $data, function($message)use($data,$spllPdf,$spilPdf,$spllExcel,$spilExcel) {
            $message->to($data["email"])
            ->subject($data["subject"])
            // ->cc('abhishek@noesis.tech', 'Mr. Abhishek')
            ->cc('IDAP.INSTRA@sunpharma.com')
            ->bcc('venkitaraman@noesis.tech', 'Mr. Venkitaraman')
            ->replyTo('ashokkumarkes@gmail.com', 'Mr. Example')
            ->attachData($spllPdf->output(), "Spll.pdf")
            ->attachData($spilPdf->output(), "Spil.pdf")
            ->attachData($spllExcel, "Spll.xlsx")
            ->attachData($spilExcel, "Spil.xlsx");
            });
        }catch(JWTException $exception){
            $this->serverstatuscode = "0";
            $this->serverstatusdes = $exception->getMessage();
        }
        if (Mail::failures()) {
             $this->statusdesc  =   "Error sending mail";
             $this->statuscode  =   "0";
 
        }else{
 
           $this->statusdesc  =   "Message sent Succesfully";
           $this->statuscode  =   "1";
        }
        return response()->json(compact('this'));
 }
    
    public function test_mail(){
	    echo 'Inside test mail <br/>';
	    $data = array('name'=>'Abhishek Chavan', 'site_url'=>env('APP_URL'));
        Mail::send('mail', $data, function($message)use($data) {
                    $message->to('abhishek@noesis.tech')
                    ->cc(['IDAP.INSTRA@sunpharma.com'])
                    ->replyTo('idap.support@sunpharma.com')
                    ->subject('Test mail');
                }); 
                   
      echo "<br/>Basic Email Sent. Check your inbox.";
    }
    
    public function import_address(){
	    
        $ignoredInstitutions = IgnoredInstitutions::get();
        $ignoredInstitutions = array_map(function($inst){return $inst->institution_id;}, $ignoredInstitutions->all());

	    $headers = [
            'Content-Type' => 'application/json',
            'AccessToken' => 'key',
            'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJQcm9qZWN0IjoiSURBUF9VQVQiLCJVc2VySUQiOiJFMDAyNTEiLCJuYmYiOjE2NTQ2MDI5NTMsImV4cCI6MTY1NDY4OTM1MywiaWF0IjoxNjU0NjAyOTUzLCJpc3MiOiJodHRwczovL3ByYWdhdGkuc3VucGhhcm1hLmNvbSIsImF1ZCI6Imh0dHBzOi8vbm9lc2lzLnRlY2gifQ.Y-3RjMrC788VD6pGSf0IZH_XB1VmD0Vo8A1WYcsY7Kw',
        ];
        
        $client = new GuzzleClient([
            'headers' => $headers,
            'verify' => false
        ]);
        
        $body = '{
          
        }';
        
        $r = $client->request('POST', env('API_URL').'/API/Institutions', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        $response = json_decode($response);
        $resp_collection = collect($response);
        $institutions = $resp_collection->whereNotIn('INST_ID',$ignoredInstitutions)->toArray();
        
        foreach($institutions as $institution){
	        echo $institution->INST_ID .'<br/>';
	        DB::table('voluntary_quotation')
                ->where('institution_id', $institution->INST_ID)->update(['addr1'=>$institution->ADDR1,'addr2'=>$institution->ADDR2, 'addr3'=>$institution->ADDR3, 'stan_code'=>$institution->STAN_CODE, 'pincode'=>$institution->PINCODE, 'state_name'=>$institution->STATE_NAME]);

	    }
    } 
    
    public function addNewCounter(Request $request){

        $name = Session::get("emp_name");
        $division_name = Session::get("division_name");
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        if($request->rateTransfer == "false")
        {
            $this->dispatch(new CreateVq($request->from,$request->to,Session::get("emp_code"),$jwt->jwt_token,$name,$division_name, $request->institution_id_arr));
        }
        else
        {
            if($request->rateTransferInstitution == -1)
            {
                return response()->json([
                    'success' => false,
                    'message' => 'Rate transfer institution not selected'
                ]);
            }
            else
            {
                $this->dispatch(new AddCounterRateTransfer($request->from,$request->to,Session::get("emp_code"),$jwt->jwt_token,$name,$division_name, $request->institution_id_arr, $request->rateTransferInstitution));
                return response()->json([
                    'success' => true,
                    'message' => 'New counter has been added!'
                ]);
            }
        }

            return response()->json([
                'success' => true,
                'message' => 'New counter has been added!'
            ]);
    }

    public function pocFeedbackForm(){
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $data = VoluntaryQuotation::where('year',$year)->where(['vq_status' => 1, 'poc_status' => 0])->where('is_deleted', 0)->get();
        return view('frontend.Poc.feedback',compact('data'));
    }

    public function savePocFeedback(Request $request){
        $institutesIdArr = data_get($request, 'institutes');
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        if (in_array('all', $institutesIdArr)) {
            $institutesIdArr = VoluntaryQuotation::where('year',$year)->where(['vq_status' => 1, 'poc_status' => 0])->where('is_deleted', 0)->pluck('id')->toArray();
        }
        foreach($institutesIdArr as $id){

            $vq_data = VoluntaryQuotation::where('id', $id)->where('is_deleted', 0)->first();
            
            $emailDataArr = VoluntaryQuotationSkuListing::join('employee_master', 'employee_master.div_code', '=', 'voluntary_quotation_sku_listing.div_id')->select('employee_master.emp_email as email')->where('voluntary_quotation_sku_listing.vq_id', (int)$id)
            //->orWhere('employee_master.emp_category', 'initiator')
            ->get();


            $emailDataArr = array_unique(array_map(function($v){ return $v->email;}, $emailDataArr->all()));

            $updation = ActivityTracker::Create([
                'vq_id' => $id,
                'emp_code' => Session::get("emp_code"),
                'activity' => 'VQ Feedback submitted by '.Session::get("emp_name").' of level - '.Session::get("emp_type"),
                'type'=> 'poc_feedback',
                'meta_data'=> data_get($request, 'comment'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            
            if(VoluntaryQuotation::where('id', $id)->where('is_deleted', 0)->update(['poc_status' => 1])){
                $data = [];
                $data["email_id"] = $emailDataArr;
                $data["subject"] =  $vq_data->hospital_name;
                $data["hospital_name"] = $vq_data->hospital_name;
                $data["hospital_code"] = $vq_data->institution_id;
                $data["emp_name"] = Session::get("emp_name");
                $data["emp_type"] = Session::get("emp_type");

                if(env('APP_URL') == 'https://idap.noesis.dev'){
                    Mail::send('frontend.Poc.feedbackEmail', $data, function($message)use($data) {
                        $message->to('rahulsharma852369741@yopmail.com')
                        ->cc('vijaya@noesis.tech')
                        // ->replyTo('idap.support@sunpharma.com')
                        ->subject($data["subject"]);
                        });
                }
                elseif(env('APP_URL') == 'http://172.16.8.192' || env('APP_URL') == 'https://172.16.8.192'){
                    Mail::send('frontend.Poc.feedbackEmail', $data, function($message)use($data) {
                        $message->to('BhagyeshVijay.Joshi@sunpharma.com')
                        ->cc('ImranKhan.IT@sunpharma.com')
                        ->subject($data["subject"]);
                        });
                }
                else{
                    Mail::send('frontend.Poc.feedbackEmail', $data, function($message)use($data) {
                        $message->to($data['email_id'])
                        // ->cc('vijaya@noesis.tech')
                        ->replyTo('idap.support@sunpharma.com')
                        ->subject($data["subject"]);
                        });
                }

                
            }
        }

        return redirect()->back()->with([
            'status' => true,
            'message' => 'Feedback has been submitted successfully!'
        ]);
    }

    public function deleteCounter($id){
        $data = VoluntaryQuotation::where('id',$id)->first();
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $ignoredInstitutionsCheck = IgnoredInstitutions::where('parent_institution_id', $data->institution_id)->exists();
        if($ignoredInstitutionsCheck)
        {
            $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
            $headers = [
                'Content-Type' => 'application/json',
                'AccessToken' => 'key',
                'Authorization' => 'Bearer '.$jwt['jwt_token'],
            ];
              
            $client = new GuzzleClient([
                'headers' => $headers,
                'verify' => false
            ]);
              
            $body = '{}';
              
            $r = $client->request('POST', env('API_URL').'/API/Institutions', [
                'body' => $body
            ]);
            $response = $r->getBody()->getContents();
            
            // $data = json_decode($res->getBody());
            $resp = json_decode($response);
            $resp_collection =collect($resp);

            $ignoredInstitutionsAll = IgnoredInstitutions::where('parent_institution_id', $data->institution_id)->get();
            $ignoredInstitutions = [];
            foreach($ignoredInstitutionsAll as $singleIgnoredinstitution) 
            {
                $child_institutionCheckVQ = VoluntaryQuotation::where('year', $year)->where('institution_id', $singleIgnoredinstitution['institution_id'])->exists();
                if(!$child_institutionCheckVQ)
                {
                    $child_institutionVQ = $resp_collection->where('INST_ID', $singleIgnoredinstitution['institution_id'])->first();
                    $ignoredInstitutions[] = [
                        'institution_id' => $child_institutionVQ->INST_ID,
                        'institution_name' => $child_institutionVQ->INST_NAME, 
                    ];
                }
                else
                {
                    $child_institutionVQ = VoluntaryQuotation::where('year', $year)->where('parent_vq_id', 0)->where('institution_id', $singleIgnoredinstitution['institution_id'])->first();
                    /*foreach($child_institutionVQ as $child_institutionVQsingle)
                    {
                        $ignoredInstitutions[] = [
                            'institution_id' => $child_institutionVQsingle['institution_id'],
                            'institution_name' => $child_institutionVQsingle['hospital_name'], 
                        ];
                    }*/
                    $ignoredInstitutions[] = [
                        'institution_id' => $child_institutionVQ['institution_id'],
                        'institution_name' => $child_institutionVQ['hospital_name'], 
                    ];
                }
            }
            if(count($ignoredInstitutions) == 1)
            {
                return redirect()->back()->with([
                    'ignored_institution' => true,
                    'child_institutions' =>$ignoredInstitutions,
                    'message' => "Selected Counter is parent institution",
                    'parent'=> $data['hospital_name'].'-'.$data['institution_id'],
                    'selected_vq_delete' => $id
                ]);
            }
            else
            {
                return redirect()->back()->with([
                    'ignored_institution' => true,
                    'child_institutions' =>$ignoredInstitutions,
                    'message' => "Selected Counter is parent institution",
                    'parent'=> $data['hospital_name'].'-'.$data['institution_id'],
                    'selected_vq_delete' => $id
                ]);
            }

        }
        else
        {
            if($data->parent_vq_id == 0){
                $result = VoluntaryQuotation::find($id)->update(['is_deleted' => 1]);
                $child = VoluntaryQuotation::where('parent_vq_id',$id)->update(['is_deleted' => 1]);
            }else{
                $parent = VoluntaryQuotation::find($data->parent_vq_id)->update(['is_deleted' => 1]);
                $result = VoluntaryQuotation::where('parent_vq_id',$data->parent_vq_id)->update(['is_deleted' => 1]);
            }
            $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
            DeleteVq::dispatch($id, $jwt->jwt_token);

            if($result){
                return redirect()->back()->with([
                    'status' => true,
                    'message' => 'Counter has been deleted successfully!'
                ]);
            }else{
                return redirect()->back()->withErrors([
                    'message' => "Counter not deleted. Please try again later!"
                ]);
            }
        }
        
    }

    public function getVQlistData(Request $request)
    {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        // Process DataTables' request parameters
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $common_search = $request->input('search.value');
        $searchValue1 = $request->input('columns.0.search.value');//hospital_name
        $searchValue2 = $request->input('columns.1.search.value');//institution_id
        $searchValue3 = $request->input('columns.2.search.value');//city
        $orderColumnIndex = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir');
        $columns = ['hospital_name', 'institution_id', 'city', 'state_name', 'institution_zone', 'institution_region', 'revision_count', 'cfa_code' , 'sap_code', 'contract_start_date', 'contract_end_date', 'year', 'current_level','vq_status'];


        // Query to fetch data from database based on DataTables' parameters
         $query = DB::table('voluntary_quotation_view')
            ->where('year', $year)
            ->where('is_deleted', 0);
        // Apply search filters
        $query->where(function ($q) use ($searchValue1, $searchValue2, $searchValue3) {
            if (!empty($searchValue1)) {
                $q->where('hospital_name', 'like', "%$searchValue1%");
            }
            if (!empty($searchValue2)) {
                $q->Where('institution_id', 'like', "%$searchValue2%");
            }
            if (!empty($searchValue3)) {
                $q->Where('city', 'like', "%$searchValue3%");
            }
        });
        // Apply common search filter
        if (!empty($common_search)) {
            $query->where(function ($q) use ($common_search) {
                $q->where('hospital_name', 'like', "%$common_search%")
                  ->orWhere('institution_id', 'like', "%$common_search%")
                  ->orWhere('city', 'like', "%$common_search%")
                  ->orWhere('state_name', 'like', "%$common_search%")
                  ->orWhere('institution_zone', 'like', "%$common_search%")
                  ->orWhere('institution_region', 'like', "%$common_search%")
                  ->orWhere('revision_count', 'like', "%$common_search%")
                  ->orWhere('cfa_code', 'like', "%$common_search%")
                  ->orWhere('sap_code', 'like', "%$common_search%")
                  ->orWhere('contract_start_date', 'like', "%$common_search%")
                  ->orWhere('contract_end_date', 'like', "%$common_search%")
                  ->orWhere('year', 'like', "%$common_search%")
                  ->orWhere(DB::raw('current_level COLLATE utf8mb4_unicode_ci'), 'like', '%' .$common_search . '%')
                  ->orWhere(DB::raw('vq_status COLLATE utf8mb4_unicode_ci'), 'like', '%' .$common_search . '%');
            });
        }
        $recordsFiltered = $query->count();
        // Apply sorting
        if(isset($orderColumnIndex)&&array_key_exists($orderColumnIndex, $columns))
        {
            $orderColumnName = $columns[$orderColumnIndex];
            $query->orderBy($orderColumnName, $orderDirection);
        }
        // Fetch data for the current page
        $data = $query->offset($start)->limit($length)->get();
        // Get total records count (without pagination)
        $recordsTotal = VoluntaryQuotation::where('year', $year)->where('is_deleted', 0)->count();
        // Get filtered records count (after applying search filters)
        
        
        // Prepare JSON response for DataTables
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
    public function getApproverVQlistData(Request $request)
    {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $common_search = strtolower($request->input('search.value'));
        $searchValue1 = $request->input('columns.0.search.value');//hospital_name
        $searchValue2 = $request->input('columns.1.search.value');//institution_id
        $searchValue3 = $request->input('columns.2.search.value');//city
        $orderColumnIndex = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir');
        $totalRows = $request->total_row;
        $columns = ['hospital_name', 'institution_id', 'city', 'state_name', 'institution_zone', 'institution_region', 'revision_count', 'cfa_code' , 'sap_code', 'contract_start_date', 'contract_end_date', 'year', 'current_level','vq_status'];
        $level =  (int) preg_replace('/[^0-9.]+/', '', Session::get("level"));
        if($level>2){
            $data1 = VoluntaryQuotation::select('voluntary_quotation.id','voluntary_quotation.hospital_name',
            'voluntary_quotation.institution_id',
            'voluntary_quotation.city',
            'voluntary_quotation.state_name',
            'voluntary_quotation.institution_zone',
            'voluntary_quotation.institution_region',
            'voluntary_quotation.cfa_code',
            'voluntary_quotation.sap_code',
            'voluntary_quotation.contract_start_date',
            'voluntary_quotation.contract_end_date',
            'voluntary_quotation.current_level','voluntary_quotation.vq_status','voluntary_quotation.year','voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status as status_vq', 'voluntary_quotation_sku_listing.deleted_by as deleted_by', 'voluntary_quotation.rev_no as revision_count')
            // ->where('current_level','>=',$level)
            ->where('year',$year)
            ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            ->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))
            ->where('voluntary_quotation_sku_listing.is_deleted','!=',1)
            ->where('voluntary_quotation.is_deleted', 0)->distinct();
            
            //$ids = VoluntaryQuotation::select('voluntary_quotation.id')->where('current_level','>=',preg_replace('/[^0-9.]+/', '', Session::get("level")))->where('year',$year)->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation_sku_listing.div_id',Session::get("division_id"))->where('voluntary_quotation_sku_listing.is_deleted','==',0)->distinct()->get();
            
            $data2 = VoluntaryQuotation::select('voluntary_quotation.id','voluntary_quotation.hospital_name',
            'voluntary_quotation.institution_id',
            'voluntary_quotation.city',
            'voluntary_quotation.state_name',
            'voluntary_quotation.institution_zone',
            'voluntary_quotation.institution_region',
            'voluntary_quotation.cfa_code',
            'voluntary_quotation.sap_code',
            'voluntary_quotation.contract_start_date',
            'voluntary_quotation.contract_end_date',
            'voluntary_quotation.current_level','voluntary_quotation.vq_status','voluntary_quotation.year',DB::raw('3 as status_vq'),'voluntary_quotation_sku_listing.deleted_by as deleted_by', 'voluntary_quotation.rev_no as revision_count')
            ->where('current_level','>=',$level)
            ->where('year',$year)
            ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            ->where('voluntary_quotation_sku_listing.is_deleted','=',1)
            ->where('voluntary_quotation.is_deleted', 0)
            ->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))
            ->distinct();
        }
        else
        {
            /** Added on 07012025 */
            $institution_division_mapping = DB::table('institution_division_mapping')
            ->where('employee_code', Session::get("emp_code"))
            ->selectRaw('vq_id')
            // ->selectRaw('GROUP_CONCAT(vq_id) as vq_ids')
            ->distinct('vq_id')
            ->get();
            /** Added on 07012025 */
            $institution_division_mapping_vq_id = $institution_division_mapping->pluck('vq_id')->unique()->toArray(); //array_column($institution_division_mapping, 'vq_id');

            $data1 = VoluntaryQuotation::select('voluntary_quotation.id','voluntary_quotation.hospital_name',
            'voluntary_quotation.institution_id',
            'voluntary_quotation.city',
            'voluntary_quotation.state_name',
            'voluntary_quotation.institution_zone',
            'voluntary_quotation.institution_region',
            'voluntary_quotation.cfa_code',
            'voluntary_quotation.sap_code',
            'voluntary_quotation.contract_start_date',
            'voluntary_quotation.contract_end_date',
            'voluntary_quotation.current_level','voluntary_quotation.vq_status','voluntary_quotation.year','voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status as status_vq', 'voluntary_quotation_sku_listing.deleted_by as deleted_by', 'voluntary_quotation.rev_no AS revision_count')
            ->where('current_level','>=',$level)->where('year',$year)
            ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            //->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','voluntary_quotation.id')//commented on 15062024
            ->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))
            ->where('voluntary_quotation_sku_listing.is_deleted','!=',1)
            ->where('voluntary_quotation.is_deleted', 0)
            ->whereIn('voluntary_quotation.id',$institution_division_mapping_vq_id)
            //->whereIn('institution_division_mapping.employee_code',[Session::get("emp_code")])//commented on 15062024
            // ->whereIn('voluntary_quotation.id', function ($query) {//added on 15062024
            //     $query->select('vq_id')
            //           ->from('institution_division_mapping')
            //           ->where('employee_code', Session::get("emp_code"));
            // }) // hide by arunchandru 15012025
            ->distinct();
            
            //$ids = VoluntaryQuotation::select('voluntary_quotation.id')->where('current_level','>=',preg_replace('/[^0-9.]+/', '', Session::get("level")))->where('year',$year)->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation_sku_listing.div_id',Session::get("division_id"))->where('voluntary_quotation_sku_listing.is_deleted','==',0)->distinct()->get();
            
            $data2 = VoluntaryQuotation::select('voluntary_quotation.id','voluntary_quotation.hospital_name',
            'voluntary_quotation.institution_id',
            'voluntary_quotation.city',
            'voluntary_quotation.state_name',
            'voluntary_quotation.institution_zone',
            'voluntary_quotation.institution_region',
            'voluntary_quotation.cfa_code',
            'voluntary_quotation.sap_code',
            'voluntary_quotation.contract_start_date',
            'voluntary_quotation.contract_end_date',
            'voluntary_quotation.current_level','voluntary_quotation.vq_status','voluntary_quotation.year',DB::raw('3 as status_vq'),'voluntary_quotation_sku_listing.deleted_by as deleted_by', 'voluntary_quotation.rev_no AS revision_count')
            ->where('current_level','>=',$level)
            ->where('year',$year)
            ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            //->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','voluntary_quotation.id')//commented on 15062024
            ->where('voluntary_quotation_sku_listing.is_deleted','=',1)
            ->where('voluntary_quotation.is_deleted', 0)
            ->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))
            ->whereIn('voluntary_quotation.id',$institution_division_mapping_vq_id)
            // ->where('institution_division_mapping.vq_id','voluntary_quotation.id')
            //->whereIn('institution_division_mapping.employee_code',[Session::get("emp_code")])//commented on 15062024
            // ->whereIn('voluntary_quotation.id', function ($query) {//added on 15062024
            //     $query->select('vq_id')
            //           ->from('institution_division_mapping')
            //           ->where('employee_code', Session::get("emp_code"));
            // }) // hide by arunchandru 15012025
            ->distinct();
        }
        $recordsTotal = $totalRows;
        $data1->where(function ($q) use ($searchValue1, $searchValue2, $searchValue3) {
            if (!empty($searchValue1)) {
                $q->where('hospital_name', 'like', "%$searchValue1%");
            }
            if (!empty($searchValue2)) {
                $q->Where('voluntary_quotation.institution_id', 'like', "%$searchValue2%");
            }
            if (!empty($searchValue3)) {
                $q->Where('city', 'like', "%$searchValue3%");
            }
        });
        // Apply common search filter
        if (!empty($common_search)) {
            if($common_search == 'approved')
            {
                $data1->where(function ($q) use ($common_search, $level) {
                    $q->where('current_level', '>', $level);
                });
                $data2->where(function ($q) use ($common_search) {
                    $q->where('voluntary_quotation_sku_listing.is_deleted',0);
                });
            }
            else if($common_search == 'pending')
            {
                $data1->where(function ($q) use ($common_search, $level) {
                    $commonsearchColumn = 'voluntary_quotation_sku_listing.' . strtolower(Session::get("level")) . '_status';
                    $q->where($commonsearchColumn, 0);
                    $q->where('current_level', $level);
                });
                $data2->where(function ($q) use ($common_search) {
                    $q->where('voluntary_quotation_sku_listing.is_deleted',0);
                });
            }
            else if($common_search == 'cancel')
            {
                $data1->where(function ($q) use ($common_search, $level) {
                   $q->where('voluntary_quotation_sku_listing.is_deleted',1);
                });
                $data2->where(function ($q) use ($common_search) {
                    $q->where('voluntary_quotation_sku_listing.is_deleted',1);
                });
            }
            else if($common_search == 'cancelled')
            {
                $data1->where(function ($q) use ($common_search, $level) {
                   $q->where('voluntary_quotation_sku_listing.is_deleted',1);
                });
                $data2->where(function ($q) use ($common_search) {
                    $q->where('voluntary_quotation_sku_listing.is_deleted',1);
                });
            }
            else
            {
                if($common_search == 'rsm')
                {
                    $common_search = 1;
                }
                else if($common_search == 'zsm')
                {
                    $common_search = 2;
                }
                else if($common_search == 'nsm')
                {
                    $common_search = 3;
                }
                else if($common_search == 'sbu')
                {
                    $common_search = 4;
                }
                else if($common_search == 'semi cluster')
                {
                    $common_search = 5;
                }
                else if($common_search == 'cluster')
                {
                    $common_search = 6;
                }
                $data1->where(function ($q) use ($common_search) {
                    $q->where('hospital_name', 'like', "%$common_search%")
                      ->orWhere('voluntary_quotation.institution_id', 'like', "%$common_search%")
                      ->orWhere('city', 'like', "%$common_search%")
                      ->orWhere('state_name', 'like', "%$common_search%")
                      ->orWhere('institution_zone', 'like', "%$common_search%")
                      ->orWhere('institution_region', 'like', "%$common_search%")
                      ->orWhere('rev_no', 'like', "%$common_search%")
                      ->orWhere('cfa_code', 'like', "%$common_search%")
                      ->orWhere('sap_code', 'like', "%$common_search%")
                      ->orWhere('contract_start_date', 'like', "%$common_search%")
                      ->orWhere('contract_end_date', 'like', "%$common_search%")
                      ->orWhere('year', 'like', "%$common_search%")
                      ->orWhere(DB::raw('current_level COLLATE utf8mb4_unicode_ci'), 'like', '%' .$common_search . '%')
                      ->orWhere(DB::raw('vq_status COLLATE utf8mb4_unicode_ci'), 'like', '%' .$common_search . '%');
                });
                $data2->where(function ($q) use ($common_search) {
                    $q->where('hospital_name', 'like', "%$common_search%")
                      ->orWhere('voluntary_quotation.institution_id', 'like', "%$common_search%")
                      ->orWhere('city', 'like', "%$common_search%")
                      ->orWhere('state_name', 'like', "%$common_search%")
                      ->orWhere('institution_zone', 'like', "%$common_search%")
                      ->orWhere('institution_region', 'like', "%$common_search%")
                      ->orWhere('rev_no', 'like', "%$common_search%")
                      ->orWhere('cfa_code', 'like', "%$common_search%")
                      ->orWhere('sap_code', 'like', "%$common_search%")
                      ->orWhere('contract_start_date', 'like', "%$common_search%")
                      ->orWhere('contract_end_date', 'like', "%$common_search%")
                      ->orWhere('year', 'like', "%$common_search%")
                      ->orWhere(DB::raw('current_level COLLATE utf8mb4_unicode_ci'), 'like', '%' .$common_search . '%')
                      ->orWhere(DB::raw('vq_status COLLATE utf8mb4_unicode_ci'), 'like', '%' .$common_search . '%');
                });
            }
            //$recordsFiltered = $data1->get()->count() + $data2->get()->count();
        }
        else
        {
            /*if (!empty($searchValue1) || !empty($searchValue2) || !empty($searchValue3)) {
                $recordsFiltered = $data1->get()->count() + $data2->get()->count();
            }
            else
            {
                $recordsFiltered = $totalRows;
            }*/
        }
       // Apply sorting
        if(isset($orderColumnIndex)&&array_key_exists($orderColumnIndex, $columns))
        {
            $orderColumnName = $columns[$orderColumnIndex];
            $data1->orderBy($orderColumnName, $orderDirection);
        }
        // Get total records count (without pagination)
        if($level > 2)
        {
            $divisionString = Session::get('division_id');

            $divisionArray = array_map(function ($div) {

                return "'" . trim($div) . "'";

            }, explode(',', $divisionString));

            $divisionInClause = implode(',', $divisionArray);

            $query = 'SELECT * FROM (

                SELECT 

                    vq.id,vq.hospital_name,

                    vq.institution_id,

                    vq.city,

                    vq.state_name,

                    vq.institution_zone,

                    vq.institution_region,

                    vq.cfa_code,

                    vq.sap_code,

                    vq.contract_start_date,

                    vq.contract_end_date,

                    vq.current_level,vq.vq_status,vq.year, vq.rev_no AS revision_count, 

                    CASE 

                        WHEN EXISTS (

                            SELECT 1 FROM voluntary_quotation_sku_listing as vqsl 

                            WHERE vqsl.vq_id = vq.id 

                              AND vqsl.' . strtolower(Session::get("level")) . '_status = 0 

                              AND vq.current_level = '.$level.'

                              and vq.`year` = "'.$year.'"

                              and `vqsl`.`div_id` in ('.$divisionInClause.')

                            and `vqsl`.`is_deleted` = 0 

                            and `vq`.`is_deleted` = 0

                        ) THEN 0 

                        WHEN EXISTS (

                            SELECT 1 FROM voluntary_quotation_sku_listing as vqsl 

                            WHERE vqsl.vq_id = vq.id 

                              AND vqsl.' . strtolower(Session::get("level")) . '_status = 1 

                              AND vq.current_level = '.$level.'

                              and vq.`year` = "'.$year.'"

                              and `vqsl`.`div_id` in ('.$divisionInClause.')

                            and `vqsl`.`is_deleted` = 0 

                            and `vq`.`is_deleted` = 0

                        ) THEN 1

                        WHEN EXISTS (

                            SELECT 1 FROM voluntary_quotation_sku_listing as vqsl 

                            WHERE vqsl.vq_id = vq.id 

                              AND vq.current_level > '.$level.'

                              AND vqsl.div_id in ('.$divisionInClause.')

                              and vq.`year` = "'.$year.'"

                            and `vqsl`.`is_deleted` = 0 

                            and `vq`.`is_deleted` = 0

                        ) THEN 1

                         WHEN EXISTS (

                            SELECT 1 FROM voluntary_quotation_sku_listing as vqsl 

                            WHERE vqsl.vq_id = vq.id  

                              AND vq.current_level >= '.$level.'

                              and vq.`year` = "'.$year.'" 

                              and `vqsl`.`div_id` in ('.$divisionInClause.')

                            and `vqsl`.`is_deleted` = 1 

                            and `vq`.`is_deleted` = 0

                        ) THEN 3

                        ELSE 4

                    END AS status_vq

                FROM voluntary_quotation vq

                WHERE EXISTS (

                    SELECT 1 FROM voluntary_quotation_sku_listing as vqsl 

                    WHERE vqsl.vq_id = vq.id 

                    AND (

                        (vqsl.' . strtolower(Session::get("level")) . '_status = 0 AND vq.current_level = '.$level.')

                       OR (vqsl.' . strtolower(Session::get("level")) . '_status = 1 AND vq.current_level = '.$level.')

                       OR (vq.current_level > '.$level.' AND vqsl.div_id in ('.$divisionInClause.') )

                    )

                    and vq.`year` = "'.$year.'"

                    and `vqsl`.`div_id` in ('.$divisionInClause.')

                    and `vq`.`is_deleted` = 0

                )

            ) AS sub Where 1=1';
 
            if (!empty($searchValue1)) {

                $searchValue1 = str_replace('-', '%', $searchValue1);

                $query .= " AND hospital_name LIKE '%$searchValue1%'";

            }

            if (!empty($searchValue2)) {

                $query .= " AND institution_id LIKE '%$searchValue2%'";

            }

            if (!empty($searchValue3)) {

                $query .= " AND city LIKE '%$searchValue3%'";

            }

            if (!empty($common_search)) {

                $common_search = str_replace('-', '%', $common_search);

                if($common_search == 'approved')

                {

                    $query .= ' AND status_vq = 1';

                }

                else if($common_search == 'pending')

                {

                    $query .= ' AND status_vq = 0';

                }

                else if($common_search == 'cancel')

                {

                    $query .= ' AND status_vq = 3';

                }

                else if($common_search == 'cancelled')

                {

                    $query .= ' AND status_vq = 3';

                }

                else

                {

                    $query .= " AND (

                        hospital_name LIKE '%$common_search%' OR

                        institution_id LIKE '%$common_search%' OR

                        city LIKE '%$common_search%' OR

                        sap_code LIKE '%$common_search%' OR

                        state_name LIKE '%$common_search%' OR

                        institution_zone LIKE '%$common_search%' OR

                        institution_region LIKE '%$common_search%' OR

                        revision_count LIKE '%$common_search%' OR

                        cfa_code LIKE '%$common_search%' OR

                        contract_start_date LIKE '%$common_search%' OR

                        contract_end_date LIKE '%$common_search%' OR

                        year LIKE '%$common_search%'

                    )";

                }

            }

            $totalFilteredCountSql = "SELECT COUNT(*) as total FROM ($query) AS subquery";

            $totalFilteredCountResult = DB::select($totalFilteredCountSql);

            $recordsFiltered = $totalFilteredCountResult[0]->total; 

            if (isset($orderColumnIndex) && array_key_exists($orderColumnIndex, $columns)) {

                $orderColumnName = $columns[$orderColumnIndex];

                $query .= " ORDER BY $orderColumnName $orderDirection";

            }

            $query .= " LIMIT $length OFFSET $start";

            $data = collect(DB::select($query));
        }
        else
        {
            $recordsFiltered = $data1->get()->count() + $data2->get()->count();
            // Fetch data for the current page
            $data1_withPagination = $data1->offset($start)->limit($length)->get();
            $data2_withPagination = $data2->offset($start)->limit($length)->get();
            
            $data = $data1_withPagination->merge($data2_withPagination);
        }
        // Prepare JSON response for DataTables
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
    public function getHoVQlistData(Request $request)
    {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        // Process DataTables' request parameters
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $common_search = strtolower($request->input('search.value'));
        $searchValue1 = $request->input('columns.0.search.value');//hospital_name
        $searchValue2 = $request->input('columns.1.search.value');//institution_id
        $searchValue3 = $request->input('columns.2.search.value');//city
        $orderColumnIndex = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir');
        $columns = ['hospital_name', 'institution_id', 'city', 'state_name', 'institution_zone', 'institution_region', 'revision_count', 'cfa_code' , 'sap_code', 'contract_start_date', 'contract_end_date', 'year', 'current_level','vq_status'];


        // Query to fetch data from database based on DataTables' parameters
         $query = VoluntaryQuotation::select("*", \DB::raw('(CASE  WHEN voluntary_quotation.parent_vq_id != "0" THEN (SELECT COUNT(*) FROM voluntary_quotation as vq1 where vq1.parent_vq_id = voluntary_quotation.parent_vq_id AND vq1.id <= voluntary_quotation.id) ELSE "0"  END) AS revision_count'))->where('year',$year)->where('is_deleted', 0);
        // Apply search filters
        $query->where(function ($q) use ($searchValue1, $searchValue2, $searchValue3) {
            if (!empty($searchValue1)) {
                $q->where('hospital_name', 'like', "%$searchValue1%");
            }
            if (!empty($searchValue2)) {
                $q->Where('institution_id', 'like', "%$searchValue2%");
            }
            if (!empty($searchValue3)) {
                $q->Where('city', 'like', "%$searchValue3%");
            }
        });
        // Apply common search filter
        if (!empty($common_search)) {

            if($common_search == 'rsm')
            {
                $common_search = 1;
                $query->where(function ($q) use ($common_search) {
                    $q->where('current_level', 'like', "%$common_search%");
                });
            }
            else if($common_search == 'zsm')
            {
                $common_search = 2;
                $query->where(function ($q) use ($common_search) {
                    $q->where('current_level', 'like', "%$common_search%");
                });
            }
            else if($common_search == 'nsm')
            {
                $common_search = 3;
                $query->where(function ($q) use ($common_search) {
                    $q->where('current_level', 'like', "%$common_search%");
                });
            }
            else if($common_search == 'sbu')
            {
                $common_search = 4;
                $query->where(function ($q) use ($common_search) {
                    $q->where('current_level', 'like', "%$common_search%");
                });
            }
            else if($common_search == 'semi cluster')
            {
                $common_search = 5;
                $query->where(function ($q) use ($common_search) {
                    $q->where('current_level', 'like', "%$common_search%");
                });
            }
            else if($common_search == 'cluster')
            {
                $common_search = 6;
                $query->where(function ($q) use ($common_search) {
                    $q->where('current_level', 'like', "%$common_search%");
                });
            }
            else if($common_search == 'sent')
            {
                $common_search = 1;
                $query->where(function ($q) use ($common_search) {
                    $q->where('vq_status', 'like', "%$common_search%");
                });
            }
            else if($common_search == 'pending')
            {
                $common_search = 0;
                $query->where(function ($q) use ($common_search) {
                    $q->where('vq_status', 'like', "%$common_search%");
                });
            }
            else if($common_search == 'approved')
            {
               $common_search = 7;
                $query->where(function ($q) use ($common_search) {
                    $q->where('current_level', 'like', "%$common_search%");
                });
            }
            else
            {
                $query->where(function ($q) use ($common_search) {
                $q->where('hospital_name', 'like', "%$common_search%")
                  ->orWhere('institution_id', 'like', "%$common_search%")
                  ->orWhere('city', 'like', "%$common_search%")
                  ->orWhere('state_name', 'like', "%$common_search%")
                  ->orWhere('institution_zone', 'like', "%$common_search%")
                  ->orWhere('institution_region', 'like', "%$common_search%")
                  ->orWhere('rev_no', 'like', "%$common_search%")
                  ->orWhere('cfa_code', 'like', "%$common_search%")
                  ->orWhere('sap_code', 'like', "%$common_search%")
                  ->orWhere('contract_start_date', 'like', "%$common_search%")
                  ->orWhere('contract_end_date', 'like', "%$common_search%")
                  ->orWhere('year', 'like', "%$common_search%");
                });
            }
            
        }
        $recordsFiltered = $query->count();
        // Apply sorting
        if(isset($orderColumnIndex)&&array_key_exists($orderColumnIndex, $columns))
        {
            $orderColumnName = $columns[$orderColumnIndex];
            $query->orderBy($orderColumnName, $orderDirection);
        }
        // Fetch data for the current page
        $data = $query->offset($start)->limit($length)->get();
        // Get total records count (without pagination)
        $recordsTotal = VoluntaryQuotation::select("*", \DB::raw('(CASE  WHEN voluntary_quotation.parent_vq_id != "0" THEN (SELECT COUNT(*) FROM voluntary_quotation as vq1 where vq1.parent_vq_id = voluntary_quotation.parent_vq_id AND vq1.id <= voluntary_quotation.id) ELSE "0"  END) AS revision_count'))->where('year',$year)->where('is_deleted', 0)->count();
        // Get filtered records count (after applying search filters)
        
        
        // Prepare JSON response for DataTables
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
    public function getDistributionVQlistData(Request $request)
    {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        // Process DataTables' request parameters
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $common_search = strtolower($request->input('search.value'));
        $searchValue1 = $request->input('columns.0.search.value');//hospital_name
        $searchValue2 = $request->input('columns.1.search.value');//institution_id
        $searchValue3 = $request->input('columns.2.search.value');//city
        $orderColumnIndex = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir');
        $columns = ['hospital_name', 'institution_id', 'city', 'state_name', 'institution_zone', 'institution_region', 'revision_count', 'cfa_code' , 'sap_code', 'contract_start_date', 'contract_end_date', 'year', 'current_level','vq_status'];


        // Query to fetch data from database based on DataTables' parameters
         $query = VoluntaryQuotation::select("*", \DB::raw('(CASE  WHEN voluntary_quotation.parent_vq_id != "0" THEN (SELECT COUNT(*) FROM voluntary_quotation as vq1 where vq1.parent_vq_id = voluntary_quotation.parent_vq_id AND vq1.id <= voluntary_quotation.id) ELSE "0"  END) AS revision_count'))->where('year',$year)->where('vq_status',1)->whereIn('cfa_code',explode(',',Session::get("division_id")))->where('is_deleted', 0);
        // Apply search filters
        $query->where(function ($q) use ($searchValue1, $searchValue2, $searchValue3) {
            if (!empty($searchValue1)) {
                $q->where('hospital_name', 'like', "%$searchValue1%");
            }
            if (!empty($searchValue2)) {
                $q->Where('institution_id', 'like', "%$searchValue2%");
            }
            if (!empty($searchValue3)) {
                $q->Where('city', 'like', "%$searchValue3%");
            }
        });
        // Apply common search filter
        if (!empty($common_search)) {

            if($common_search == 'rsm')
            {
                $common_search = 1;
                $query->where(function ($q) use ($common_search) {
                    $q->where('current_level', 'like', "%$common_search%");
                });
            }
            else if($common_search == 'zsm')
            {
                $common_search = 2;
                $query->where(function ($q) use ($common_search) {
                    $q->where('current_level', 'like', "%$common_search%");
                });
            }
            else if($common_search == 'nsm')
            {
                $common_search = 3;
                $query->where(function ($q) use ($common_search) {
                    $q->where('current_level', 'like', "%$common_search%");
                });
            }
            else if($common_search == 'sbu')
            {
                $common_search = 4;
                $query->where(function ($q) use ($common_search) {
                    $q->where('current_level', 'like', "%$common_search%");
                });
            }
            else if($common_search == 'semi cluster')
            {
                $common_search = 5;
                $query->where(function ($q) use ($common_search) {
                    $q->where('current_level', 'like', "%$common_search%");
                });
            }
            else if($common_search == 'cluster')
            {
                $common_search = 6;
                $query->where(function ($q) use ($common_search) {
                    $q->where('current_level', 'like', "%$common_search%");
                });
            }
            else if($common_search == 'sent')
            {
                $common_search = 1;
                $query->where(function ($q) use ($common_search) {
                    $q->where('vq_status', 'like', "%$common_search%");
                });
            }
            else if($common_search == 'pending')
            {
                $common_search = 0;
                $query->where(function ($q) use ($common_search) {
                    $q->where('vq_status', 'like', "%$common_search%");
                });
            }
            else if($common_search == 'approved')
            {
               $common_search = 7;
                $query->where(function ($q) use ($common_search) {
                    $q->where('current_level', 'like', "%$common_search%");
                });
            }
            else
            {
                $query->where(function ($q) use ($common_search) {
                $q->where('hospital_name', 'like', "%$common_search%")
                  ->orWhere('institution_id', 'like', "%$common_search%")
                  ->orWhere('city', 'like', "%$common_search%")
                  ->orWhere('state_name', 'like', "%$common_search%")
                  ->orWhere('institution_zone', 'like', "%$common_search%")
                  ->orWhere('institution_region', 'like', "%$common_search%")
                  ->orWhere('rev_no', 'like', "%$common_search%")
                  ->orWhere('cfa_code', 'like', "%$common_search%")
                  ->orWhere('sap_code', 'like', "%$common_search%")
                  ->orWhere('contract_start_date', 'like', "%$common_search%")
                  ->orWhere('contract_end_date', 'like', "%$common_search%")
                  ->orWhere('year', 'like', "%$common_search%");
                });
            }
            
        }
        $recordsFiltered = $query->count();
        // Apply sorting
        if(isset($orderColumnIndex)&&array_key_exists($orderColumnIndex, $columns))
        {
            $orderColumnName = $columns[$orderColumnIndex];
            $query->orderBy($orderColumnName, $orderDirection);
        }
        // Fetch data for the current page
        $data = $query->offset($start)->limit($length)->get();
        // Get total records count (without pagination)
        $recordsTotal = VoluntaryQuotation::select("*", \DB::raw('(CASE  WHEN voluntary_quotation.parent_vq_id != "0" THEN (SELECT COUNT(*) FROM voluntary_quotation as vq1 where vq1.parent_vq_id = voluntary_quotation.parent_vq_id AND vq1.id <= voluntary_quotation.id) ELSE "0"  END) AS revision_count'))->where('year',$year)->where('vq_status',1)->whereIn('cfa_code',explode(',',Session::get("division_id")))->where('is_deleted', 0)->count();
        // Get filtered records count (after applying search filters)
        
        
        // Prepare JSON response for DataTables
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
    public function editStockist($id){
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $vq = VoluntaryQuotation::where('year',$year)->where('id',$id)->first();
        $institution_id = $vq->institution_id;
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        //   dd($jwt);
        $headers = [
            'Content-Type' => 'application/json',
            'AccessToken' => 'key',
            'Authorization' => 'Bearer '.$jwt['jwt_token'],
        ];
            
        $client = new GuzzleClient([
              'headers' => $headers,
              'verify' => false
        ]);
        
        $body = '{
            "INST_ID": "'.$institution_id.'"
        }';
        
        $r = $client->request('POST', env('API_URL').'/api/Stockists', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
    
        // $data = json_decode($res->getBody());
        $resp = json_decode($response);
        // dd($resp);  
        $cnt = 0;
        if(count($resp)>0)
        {
            //$stockist_data = Stockist_master::where('institution_code',$institution_id)->update(['stockist_type_flag'=>0,'updated_at'=> now()]);
        }
        /*foreach($resp as $item){
            $upd = Stockist_master::updateOrCreate(['institution_code' => $institution_id,'stockist_code' => $item->STOCKIST_CODE ], [ 
                'stockist_name' => $item->STOCKIST_NAME,
                'stockist_address' => $item->STOCKIST_ADDRESS,
                'email_id'=> $item->STOCKIST_EMAIL,
                'stockist_type_flag' => 1
            ]);
        }*/
        $data = Stockist_master::where('institution_code',$institution_id)->get();
        return view('frontend.Initiator.editStockist',compact('data','vq'));
    
    }

    public function genereate_request(){
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");

        $item_code = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.item_code','voluntary_quotation_sku_listing.sap_itemcode','voluntary_quotation_sku_listing.brand_name')
        ->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
        ->groupBy('voluntary_quotation_sku_listing.item_code')
        ->where('voluntary_quotation.year', $year)
        ->where('voluntary_quotation_sku_listing.product_type','new')
        ->where('voluntary_quotation.current_level', 7)
        ->where('voluntary_quotation.vq_status', 0)
        ->get()->toArray();
        if (request()->has('download') && request()->has('item_code')) {
            $downloadType = request('download');
            $item_code = request('item_code');
            if($downloadType == 'missingPaymode')
            {
                $selection = Session::get('selected_item_generate_vq_paymode');
                $response = Excel::download(new MissingDetailsExport($downloadType, $item_code, $selection, $year), 'missing_paymode.xlsx');
            }
            else if($downloadType == 'missingStockist')
            {
                $selection = Session::get('selected_item_generate_vq_stockist'); 
                $response = Excel::download(new MissingDetailsExport($downloadType, $item_code, $selection, $year), 'missing_stockist.xlsx');
            }
            else if($downloadType == 'missingPoc')
            {
                $selection = Session::get('selected_item_generate_vq_poc'); 
                $response = Excel::download(new MissingDetailsExport($downloadType, $item_code, $selection, $year), 'missing_poc.xlsx');
            }
            $response->headers->remove('Server');
            $response->headers->remove('X-Powered-By');

            return $response;
        }
        $DiscountMargin = DiscountMarginMaster::pluck('discount_margin', 'item_code')->toArray();
        $data['DiscountMargin_datas'] = $DiscountMargin;
        $data['old_product_data'] = $item_code;
        return view('frontend.Initiator.generate',compact('data'));
    }

    public function generate_vq_data(Request $request)
    {
        ini_set('memory_limit', '5120M');
        $vq_listing_controller = new VqListingController;
        try 
        {
            $item_code = $request->item_code;
            $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
            $year = $this->getFinancialYear(date('Y-m-d'),"Y");
            $headers = [
            'Content-Type' => 'application/json',
            'AccessToken' => 'key',
            'Authorization' => 'Bearer '.$jwt['jwt_token'],
                ];
                
            $client = new GuzzleClient([
                'headers' => $headers,
                'verify' => false
            ]);
            // $data = VoluntaryQuotationSkuListing::select('voluntary_quotation.*','voluntary_quotation_sku_listing.id as sku_id','stockist_master.*','stockist_master.id as stockist_id') // hide by arunchandru 15012025
            $data = VoluntaryQuotationSkuListing::select('voluntary_quotation.*','voluntary_quotation_sku_listing.id as sku_id', 'voluntary_quotation_sku_listing.vq_id as vq_id','stockist_master.*','stockist_master.id as stockist_id')
            ->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            ->leftJoin('stockist_master','voluntary_quotation.institution_id','=','stockist_master.institution_code')
            ->where('voluntary_quotation_sku_listing.item_code',$item_code)
            ->where('voluntary_quotation.year', $year)
            ->where('voluntary_quotation.current_level', 7)
            ->where('voluntary_quotation.vq_status', 0)
            ->where('voluntary_quotation.is_deleted', 0)
            ->where('voluntary_quotation_sku_listing.is_deleted', 0)
            ->where(function($query) {
                $query->where('stockist_master.stockist_type_flag', 1)
                ->orWhereNull('stockist_master.stockist_type_flag');
            })
            ->where('voluntary_quotation_sku_listing.product_type','new')
            ->get();
            $noStockistData = $data->filter(function($row) {
                return empty($row->stockist_id);
            });
            $uniqueInstitutionIds = $noStockistData->pluck('institution_id')->unique()->toArray();

            if(count($uniqueInstitutionIds)>0)
            {
                foreach ($uniqueInstitutionIds as $institutionId) {
                   $body = '{
                    "INST_ID": "'.$institutionId.'"
                    }';
                    
                    $r = $client->request('POST', env('API_URL').'/api/Stockists', [
                        'body' => $body
                    ]);
                    $response = $r->getBody()->getContents();
                
                    // $data = json_decode($res->getBody());
                    $resp = json_decode($response);
                    $stockist_data = Stockist_master::where('institution_code',$institutionId)->exists();
                    if(!$stockist_data){
                        $stock_cnt = 0;
                        foreach($resp as $itm){
                            /*if($stock_cnt<3){
                                $stock_flag = 1;
                            }else{
                                $stock_flag = 0;
                            }
                            $stock_cnt++;*/
                            $stock_flag = 0;
                            $stock = Stockist_master::Create([
                                'institution_code' => $institutionId,
                                'stockist_name' => $itm->STOCKIST_NAME,
                                'stockist_address' => $itm->STOCKIST_ADDRESS,
                                'email_id' => $itm->STOCKIST_EMAIL,
                                'stockist_code' => $itm->STOCKIST_CODE,
                                'stockist_type_flag' => $stock_flag,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }else{
                        foreach($resp as $item){
                            $upd = Stockist_master::updateOrCreate(['institution_code' => $institutionId,'stockist_code' => $item->STOCKIST_CODE ], [ 
                                'stockist_name' => $item->STOCKIST_NAME,
                                'stockist_address' => $item->STOCKIST_ADDRESS,
                                'email_id'=> $item->STOCKIST_EMAIL,
                                'stockist_type_flag' => 1,
                                'updated_at'=> now()
                            ]);
                        }
                    }
                }
            }

            /** Check Duplication Start*/
            $vqskulistingExists = IgnoredInstitutions::select('ignored_institutions.*','voluntary_quotation.*', 'voluntary_quotation.id as vqid')
            ->leftJoin('voluntary_quotation', 'voluntary_quotation.institution_id', '=', 'ignored_institutions.institution_id')
            ->where('voluntary_quotation.year', $year)
            ->where('voluntary_quotation.current_level', 7)
            ->where('voluntary_quotation.vq_status', 0)
            ->where('voluntary_quotation.is_deleted', 0)
            ->get();
            $vqids = [];
            foreach($vqskulistingExists as $vqskulisting){
                $vqids[] = $vqskulisting->vqid;
            }
            
            $check_duplication_data = VoluntaryQuotationSkuListing::where('voluntary_quotation_sku_listing.item_code', $item_code)
            ->leftJoin('voluntary_quotation', 'voluntary_quotation.id', '=', 'voluntary_quotation_sku_listing.vq_id')
            ->whereIn('voluntary_quotation.id', $vqids)
            ->whereIn('voluntary_quotation_sku_listing.vq_id', $vqids)
            ->where('voluntary_quotation_sku_listing.product_type', 'new')
            ->where('voluntary_quotation.is_deleted', 0)
            ->get()
            ->toArray();
            /** Check Dulication End*/
            $generate_vq_new_products = Session::get('generate_vq_new_products');
            if(empty($check_duplication_data)): // if data's empty not execute if condition
                $vq_created = date('Y-m-d H:i:s');
                $phpdate1 = strtotime( $vq_created );
                $start = date( 'Y-m-d H:i:s', $phpdate1 ); //contract_start_date
                $finddayyear = date("Y") + 1;
                $finddaymonth = date("3");
                $days = cal_days_in_month(CAL_GREGORIAN, $finddaymonth, $finddayyear);
                $enddateyear = strtotime( $finddayyear.'-'.$finddaymonth.'-'.$days );
                $end = date('Y-m-d H:i:s', $enddateyear);  //contract_end_date
                foreach ($data as $index => $row) {
                    $row->unique_id = $index + 1; 
                }
                $data_vq_id = [];
                foreach($data as $row){
                    $data_vq_id[] = $row['vq_id'];
                }
                $vq_id_unique_datas = array_unique($data_vq_id);
        
                if(!empty($vq_id_unique_datas)){
                    DB::beginTransaction();
                    try {
                        $institution_vq = [];
                        $listing_data = [];
                        foreach($vq_id_unique_datas as $vq_id){
                            $vq = VoluntaryQuotation::where('id', $vq_id)->where('is_deleted', 0)->first();
                            $ignoredinstitutions = IgnoredInstitutions::where('parent_institution_id', data_get($vq, 'institution_id'))->select('parent_institution_id','institution_id')->get();
                            if(!empty($ignoredinstitutions)){
                                foreach($ignoredinstitutions as $ig_inst):
                                    $ignoreinstitution_vq = VoluntaryQuotation::where('institution_id', $ig_inst->institution_id)->where('parent_vq_id', 0)->where('year', $year)->where('is_deleted', 0)->first();
                                    // print_r($ignoreinstitution_vq);
                                    if(!empty($ignoreinstitution_vq)):
                                        /** Get VoluntaryQuotation last rev_no query */
                                        $newestClient = VoluntaryQuotation::where('institution_id', $ig_inst->institution_id)->where('year', $year)->where('is_deleted', 0)->orderBy('rev_no', 'desc')->first(); // gets the one row
                                        $maxValue = $newestClient->rev_no;
                                        $rev_no = (!empty($newestClient->toArray()))? $newestClient->rev_no+1 : '0';
                                        /** Insert VoluntaryQuotation Table */
                                        $institution_vq = VoluntaryQuotation::Create([
                                            'hospital_name' => $ignoreinstitution_vq->hospital_name,
                                            'institution_id' => $ignoreinstitution_vq->institution_id,
                                            'institution_key_account' => $ignoreinstitution_vq->institution_key_account,
                                            'city' => $ignoreinstitution_vq->city,
                                            'addr1'=>$ignoreinstitution_vq->addr1,
                                            'addr2'=>$ignoreinstitution_vq->addr2,
                                            'addr3'=>$ignoreinstitution_vq->addr3,
                                            'stan_code'=>$ignoreinstitution_vq->stan_code,
                                            'pincode'=>$ignoreinstitution_vq->pincode,
                                            'state_name'=>$ignoreinstitution_vq->state_name,
                                            'current_level_start_date' => $vq_created,
                                            'current_level' => "7",
                                            'address' => $ignoreinstitution_vq->address,
                                            'zone' => $ignoreinstitution_vq->zone,
                                            'institution_zone' => $ignoreinstitution_vq->institution_zone,
                                            'institution_region' => $ignoreinstitution_vq->institution_region,
                                            'cfa_code' => $ignoreinstitution_vq->cfa_code,
                                            'contract_start_date' => $start,
                                            'contract_end_date' => $end,
                                            'year' => $year,
                                            'sap_code' => $ignoreinstitution_vq->sap_code,
                                            'created_at' => $vq_created,
                                            'updated_at' => $vq_created,
                                            'vq_status' => 0,
                                            'parent_vq_id' => $ignoreinstitution_vq->id,
                                            'rev_no' => $rev_no
                                        ]);
                                        $get_inst_id[] = $institution_vq->id;
                                        $vq_listing_controller->activityTracker($institution_vq->id, Session::get("emp_code"), 'VQ Reinitiated / '.Session::get("emp_name").'/'.Session::get("emp_code"), 'reinitiate');
                                        $get_vq_sku_listing = VoluntaryQuotationSkuListing::where('vq_id', $vq_id)->where('is_deleted', 0)->get();
                                        foreach($get_vq_sku_listing as $single_data):
                                            $listing_data[] = [
                                                'vq_id' => $institution_vq->id, // Last insert VoluntaryQuotation ID
                                                'item_code' => $single_data->item_code,
                                                'brand_name' => $single_data->brand_name,
                                                'mother_brand_name' => $single_data->mother_brand_name,
                                                'hsn_code' => $single_data->hsn_code,
                                                'applicable_gst' => $single_data->applicable_gst,
                                                'composition' => $single_data->composition,
                                                'type' => $single_data->type,
                                                'div_name' => $single_data->div_name,
                                                'div_id' => $single_data->div_id,
                                                'pack' => $single_data->pack,
                                                'ptr' => $single_data->ptr,
                                                'last_year_ptr' => $single_data->last_year_ptr,
                                                'last_year_percent' => $single_data->last_year_percent,
                                                'last_year_rate' => $single_data->last_year_rate,
                                                'pdms_discount' => $single_data->pdms_discount,
                                                'discount_percent' => $single_data->discount_percent,
                                                'discount_rate' => $single_data->discount_rate,
                                                'sap_itemcode' => $single_data->sap_itemcode,
                                                'mrp' => $single_data->mrp,
                                                'last_year_mrp' => $single_data->last_year_mrp,
                                                'mrp_margin'=>$single_data->mrp_margin,
                                                'created_at' => date('Y-m-d H:i:s'),
                                                'updated_at' => date('Y-m-d H:i:s'),
                                                'product_type' => 'new'
                                            ];
                                        endforeach;
                                    else:
                                        /** Insert VoluntaryQuotation Table */
                                        $headers = [
                                            'Content-Type' => 'application/json',
                                            'AccessToken' => 'key',
                                            'Authorization' => 'Bearer '.$jwt['jwt_token'],
                                        ];
                                        $client = new GuzzleClient([
                                            'headers' => $headers,
                                            'verify' => false
                                        ]);
                                        $body = '{}';
                                        $r = $client->request('POST', env('API_URL').'/API/InstitutionOnly', [
                                            'body' => $body
                                        ]);
                                        $response = $r->getBody()->getContents();
                                        
                                        $response = json_decode($response, true);
                                        $resp_collection = collect($response);
                                        $chain_hospital_institution = $resp_collection->where('INST_ID', $ig_inst->institution_id)->first();
                                        /** Get VQ id by IG-INT parent insutition id */
                                        $parent_vq = VoluntaryQuotation::where('institution_id', $ig_inst->parent_institution_id)->where('year', $year)->where('is_deleted', 0)->orderBy('id', 'desc')->first();
                                        /** API Insert Data */
                                        $institution_vq = VoluntaryQuotation::Create([
                                            'hospital_name' => $chain_hospital_institution['INST_NAME'],
                                            'institution_id' => $chain_hospital_institution['INST_ID'],
                                            'institution_key_account' => $chain_hospital_institution['KEY_ACC_NAME'],
                                            'city' => $chain_hospital_institution['CITY'],
                                            'addr1'=>$chain_hospital_institution['ADDR1'],
                                            'addr2'=>$chain_hospital_institution['ADDR2'],
                                            'addr3'=>$chain_hospital_institution['ADDR3'],
                                            'stan_code'=>$chain_hospital_institution['STAN_CODE'],
                                            'pincode'=>$chain_hospital_institution['PINCODE'],
                                            'state_name'=>$chain_hospital_institution['STATE_NAME'],
                                            'current_level_start_date' => $vq_created,
                                            'current_level' => "7",
                                            'address' => $chain_hospital_institution['ADDRESS'],
                                            'zone' => $chain_hospital_institution['ZONE'],
                                            'institution_zone' => data_get($chain_hospital_institution, 'LSTZONEMAPPING.0.ZSM_ZONE'),
                                            'institution_region' => data_get($chain_hospital_institution, 'LSTZONEMAPPING.0.RSM_REGION'),
                                            'cfa_code' => $chain_hospital_institution['CFA_CODE'],
                                            'contract_start_date' => $start,
                                            'contract_end_date' => $end,
                                            'year' => $year,
                                            'sap_code' => $chain_hospital_institution['SAP_CODE'],
                                            // 'institution_zone' => isset($chain_hospital_institution['LSTZONEMAPPING'][0]->ZSM_ZONE) ? $chain_hospital_institution['LSTZONEMAPPING'][0]->ZSM_ZONE : '',
                                            // 'institution_region' => isset($chain_hospital_institution['LSTZONEMAPPING'][0]->RSM_REGION) ? $chain_hospital_institution['LSTZONEMAPPING'][0]->RSM_REGION : '',
                                            'created_at' => $vq_created,
                                            'updated_at' => $vq_created,
                                            'vq_status' => 0,
                                            'parent_vq_id' => 0,
                                            'rev_no' =>0//added on 05042024 to add rev no for create vq
                                        ]);
                                        $get_inst_id[] = $institution_vq->id;
                                        $vq_listing_controller->activityTracker($institution_vq->id, Session::get("emp_code"),'VQ Initiated by / '.Session::get("emp_name").'/'.Session::get("emp_code"), 'initiate');
                                        
                                        /** hide by arunchandru 03122024 */
                                        $rateTransferInstitution = $parent_vq->institution_id;
                                        /** Insert VoluntaryQuotation Table */
                                        $maxRevSubquery = DB::table('voluntary_quotation_sku_listing as s')
                                        ->select(DB::raw('MAX(v2.rev_no) AS max_rev_no'), 's.item_code', 'v2.institution_id')
                                        ->leftJoin('voluntary_quotation as v2', 'v2.id', '=', 's.vq_id')
                                        ->where('v2.year', $year)
                                        ->where('s.is_deleted', 0)
                                        ->where('v2.vq_status', 1)
                                        ->where('v2.is_deleted', 0)
                                        ->where('v2.institution_id', $rateTransferInstitution)
                                        ->groupBy('s.item_code');
                    
                                        $vq_sku_listing_datas = DB::table('voluntary_quotation_sku_listing as vqsl')
                                        ->select('vqsl.*', 'vq.*')
                                        ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
                                        ->joinSub($maxRevSubquery, 'max_rev', function ($join) use ($rateTransferInstitution) {
                                            $join->on('vqsl.item_code', '=', 'max_rev.item_code')
                                                ->where('vq.institution_id', $rateTransferInstitution)
                                                ->on('vq.rev_no', '=', 'max_rev.max_rev_no');
                                        })
                                        ->where('vq.institution_id', $rateTransferInstitution)
                                        ->where('vq.year', $year)
                                        ->where('vq.vq_status', 1)
                                        ->where('vq.is_deleted', 0)
                                        ->where('vqsl.is_deleted', 0)
                                        ->where('vqsl.item_code', $item_code)
                                        ->get();
                                        
                                        // $get_vq_sku_listing = VoluntaryQuotationSkuListing::where('vq_id', $vq_id)->where('is_deleted', 0)->get();
                                        foreach($vq_sku_listing_datas as $single_data){
                                            $listing_data[]=[
                                                'vq_id' => $institution_vq->id, // last insert vq id
                                                'item_code' => $single_data->item_code, 
                                                'brand_name' => $single_data->brand_name,
                                                'mother_brand_name' => $single_data->mother_brand_name,
                                                'hsn_code' => $single_data->hsn_code,
                                                'applicable_gst' => $single_data->applicable_gst,
                                                'composition' => $single_data->composition,
                                                'type' => $single_data->type,
                                                'div_name' => $single_data->div_name,
                                                'div_id' => $single_data->div_id,
                                                'pack' => $single_data->pack,
                                                'ptr' => $single_data->ptr,
                                                'last_year_ptr' => $single_data->last_year_ptr,
                                                'last_year_percent' => $single_data->last_year_percent,
                                                'last_year_rate' => $single_data->last_year_rate,
                                                'pdms_discount' => $single_data->pdms_discount,
                                                'discount_percent' => $single_data->discount_percent,
                                                'discount_rate' => $single_data->discount_rate,
                                                'sap_itemcode' => $single_data->sap_itemcode,
                                                'mrp' => $single_data->mrp,
                                                'last_year_mrp' => $single_data->last_year_mrp,
                                                'mrp_margin'=>$single_data->mrp_margin,
                                                'created_at' => date('Y-m-d H:i:s'),
                                                'updated_at' => date('Y-m-d H:i:s'),
                                                'product_type' => 'new'
                                            ];
                                        }
                                    endif;
                                endforeach;
                            }
                        }
                        Session::put('generate_vq_new_products', $get_inst_id);
                        // added to optimise the skulisting table insert starts
                        foreach (array_chunk($listing_data, 100) as $t)  
                        {
                            DB::table('voluntary_quotation_sku_listing')->insert($t); 
                        }
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Batch insert failed: " . $e->getMessage());
                    }
                }
            endif;

            $data = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.*','voluntary_quotation.*','voluntary_quotation_sku_listing.id as sku_id','stockist_master.*','stockist_master.id as stockist_id')
            ->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            ->leftJoin('stockist_master','voluntary_quotation.institution_id','=','stockist_master.institution_code')
            ->where('voluntary_quotation_sku_listing.item_code',$item_code)
            ->where('voluntary_quotation.year', $year)
            ->where('voluntary_quotation.current_level', 7)
            ->where('voluntary_quotation.is_deleted', 0)
            ->where('voluntary_quotation_sku_listing.is_deleted', 0)
            ->where('voluntary_quotation.vq_status', 0)
            //->where('stockist_master.stockist_type_flag',1)
            ->where(function($query) {
                $query->where('stockist_master.stockist_type_flag', 1)
                ->orWhereNull('stockist_master.stockist_type_flag');
            })
            ->where('voluntary_quotation_sku_listing.product_type','new')
            ->get();
            foreach ($data as $index => $row) {
                $row->unique_id = $index + 1; 
            }
            $sku_stockist_data = [];
            foreach($data as $row){
                if($row['stockist_id']!='')
                {
                    $vqslStockistExists = VoluntaryQuotationSkuListingStockist::
                    where('vq_id', $row['vq_id'])
                    ->where('sku_id',$row['sku_id'])
                    ->where('stockist_id',$row['stockist_id'])
                    ->where('item_code',$row['item_code'])
                    ->exists();
                    if(!$vqslStockistExists){
                        $sku_stockist_data[] = [
                            'vq_id' => $row['vq_id'],
                            'sku_id' => $row['sku_id'],
                            'item_code' => $row['item_code'],
                            'stockist_id' => $row['stockist_id'],
                            'parent_vq_id' => $row['parent_vq_id'],
                            'revision_count' => $row['rev_no'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    else
                    {

                    }
                }
            }
            if(count($sku_stockist_data)>0)
            {
                $chunkSize = 100;
                foreach (array_chunk($sku_stockist_data, $chunkSize) as $chunk) {
                    VoluntaryQuotationSkuListingStockist::insert($chunk);
                }
            }
            return response()->json([
                'data' => $data,
            ]);
        }
        catch (\Exception $e) {
            \Log::error('Error in getVqgenerationData: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while processing your request.'.$e->getMessage()
            ], 500);
        }
    }
    public function adjust_workflow(){
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");

        $institutions = VoluntaryQuotation::select('institution_id','hospital_name')
        ->where('voluntary_quotation.year', $year)
        ->where('voluntary_quotation.vq_status', 0)
        ->where('is_deleted',0)
        ->groupBy('institution_id','hospital_name')
        ->get()->toArray();
        $data['institutions'] = $institutions;
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.workflowChange',compact('data'));
        }
        else
        {
            return view('admin.workflowChange',compact('data'));
        }
    }
    public function get_pending_vq_data_workflow(Request $request)
    {
        try 
        {
            $institution_id = $request->institution_id;
            
            $year = $this->getFinancialYear(date('Y-m-d'),"Y");
            
            $data = VoluntaryQuotation::where('voluntary_quotation.year', $year)
            ->where('voluntary_quotation.vq_status', 0)
            ->whereIn('institution_id',$institution_id)
            ->where('is_deleted',0)
            ->get();
            return response()->json([
                'data' => $data,
            ]);
            
            
            
        }
        catch (\Exception $e) {
            \Log::error('Error in getVqgenerationData: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while processing your request.'.$e->getMessage()
            ], 500);
        }
    }
    public function enable_locking()
    {
        try 
        {
            
            
            $year = $this->getFinancialYear(date('Y-m-d'),"Y");
            
            $data = DB::table('check_discounted')->select('is_enabled')->first();

            $log = ActivityTracker::select('activity_trackers.*','employee_master.emp_code','employee_master.emp_name')->leftJoin('employee_master','employee_master.emp_code','=','activity_trackers.emp_code')->where('type','change_locking_period')
            ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.fin_year")) = ?', [$year])
            //->where('activity_trackers.emp_code', Session::get("emp_code"))
            ->orderBy('activity_trackers.created_at', 'DESC')->get();
            
            if(Session::get("type") == 'initiator')
            {
                return view('frontend.Initiator.enableLocking',compact('data','log'));
            }
            else
            {
                return view('admin.enableLocking',compact('data','log'));
            }
            
            
            
        }
        catch (\Exception $e) {
            \Log::error('Error in getVqgenerationData: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while processing your request.'.$e->getMessage()
            ], 500);
        }
    }
    public function genereate_request_existing()
    {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");

        $item_codes = DB::table('voluntary_quotation_sku_listing as vqsl')
        ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
        ->select('vqsl.vq_id', 'vqsl.item_code', 'vqsl.sap_itemcode', 'vqsl.brand_name')
        ->where('vq.year', $year)
        ->where('vq.vq_status', 0)
        ->where('vq.current_level', 7)
        ->where('vqsl.product_type', 'old')
        ->where('vq.is_deleted', 0)
        ->groupBy('vqsl.vq_id')
        ->havingRaw('COUNT(vqsl.vq_id) = 1')
        ->get()
        ->toArray();
        $unique_items = [];
        foreach ($item_codes as $item) {
            $key = $item->item_code . '|' . $item->sap_itemcode . '|' . $item->brand_name;
            if (!isset($unique_items[$key])) {
                $unique_items[$key] = $item;
            }
        }
        if (request()->has('download') && request()->has('item_code')) {
            $downloadType = request('download');
            $item_code = request('item_code');
            if($downloadType == 'missingPaymode')
            {
                $selection = Session::get('selected_item_generate_vq_paymode');
                $response = Excel::download(new MissingDetailsExport($downloadType, $item_code, $selection, $year), 'missing_paymode.xlsx');
            }
            else if($downloadType == 'missingStockist')
            {
                $selection = Session::get('selected_item_generate_vq_stockist'); 
                $response = Excel::download(new MissingDetailsExport($downloadType, $item_code, $selection, $year), 'missing_stockist.xlsx');
            }
            else if($downloadType == 'missingPoc')
            {
                $selection = Session::get('selected_item_generate_vq_poc'); 
                $response = Excel::download(new MissingDetailsExport($downloadType, $item_code, $selection, $year), 'missing_poc.xlsx');
            }
            $response->headers->remove('Server');
            $response->headers->remove('X-Powered-By');

            return $response;
        }
        $DiscountMargin = DiscountMarginMaster::pluck('discount_margin', 'item_code')->toArray();
        $data['DiscountMargin_datas'] = $DiscountMargin;
        $data['old_product_data'] = array_values($unique_items);
        return view('frontend.Initiator.generateExisting',compact('data'));
    }
    public function generate_vq_data_existing(Request $request)
    {
        $vq_listing_controller = new VqListingController;
        try 
        {
            $item_code = $request->item_code;
            $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
            $year = $this->getFinancialYear(date('Y-m-d'),"Y");
            $headers = [
            'Content-Type' => 'application/json',
            'AccessToken' => 'key',
            'Authorization' => 'Bearer '.$jwt['jwt_token'],
                ];
                
             $client = new GuzzleClient([
                'headers' => $headers,
                'verify' => false
            ]);
            $all_vq_ids = DB::table('voluntary_quotation_sku_listing as vqsl')
            ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
            ->select('vqsl.vq_id', 'vqsl.item_code')
            ->where('vq.year', $year)
            ->where('vq.vq_status', 0)
            ->where('vq.current_level', 7)
            ->where('vq.is_deleted', 0)
            ->where('vqsl.is_deleted', 0)
            ->where('vqsl.product_type', 'old')
            ->groupBy('vqsl.vq_id')
            ->havingRaw('COUNT(vqsl.vq_id) = 1')
            ->get();
            $vq_ids = $all_vq_ids->filter(function ($item) use ($item_code) {
                return $item->item_code == $item_code;
            })->pluck('vq_id')->toArray();
            
            $data = VoluntaryQuotationSkuListing::select('voluntary_quotation.*', 'voluntary_quotation_sku_listing.id as sku_id', 'voluntary_quotation_sku_listing.vq_id as vq_id',  'stockist_master.*', 'stockist_master.id as stockist_id')
            ->leftJoin('voluntary_quotation', 'voluntary_quotation_sku_listing.vq_id', '=', 'voluntary_quotation.id')
            ->leftJoin('stockist_master', 'voluntary_quotation.institution_id', '=', 'stockist_master.institution_code')
            ->whereIn('voluntary_quotation.id', $vq_ids)
            ->where(function($query) {
                $query->where('stockist_master.stockist_type_flag', 1)
                      ->orWhereNull('stockist_master.stockist_type_flag');
            })
            ->get();
            $noStockistData = $data->filter(function($row) {
                return empty($row->stockist_id);
            });
            $uniqueInstitutionIds = $noStockistData->pluck('institution_id')->unique()->toArray();
            if(count($uniqueInstitutionIds)>0)
            {
                foreach ($uniqueInstitutionIds as $institutionId) {
                   $body = '{
                    "INST_ID": "'.$institutionId.'"
                    }';
                    
                    $r = $client->request('POST', env('API_URL').'/api/Stockists', [
                        'body' => $body
                    ]);
                    $response = $r->getBody()->getContents();
                
                    // $data = json_decode($res->getBody());
                    $resp = json_decode($response);
                    $stockist_data = Stockist_master::where('institution_code',$institutionId)->exists();
                    if(!$stockist_data){
                        $stock_cnt = 0;
                        foreach($resp as $itm){
                            /*if($stock_cnt<3){
                                $stock_flag = 1;
                            }else{
                                $stock_flag = 0;
                            }
                            $stock_cnt++;*/
                            $stock_flag = 0;
                            $stock = Stockist_master::Create([
                                'institution_code' => $institutionId,
                                'stockist_name' => $itm->STOCKIST_NAME,
                                'stockist_address' => $itm->STOCKIST_ADDRESS,
                                'email_id' => $itm->STOCKIST_EMAIL,
                                'stockist_code' => $itm->STOCKIST_CODE,
                                'stockist_type_flag' => $stock_flag,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                        }
                    }else{
                        foreach($resp as $item){
                            $upd = Stockist_master::updateOrCreate(['institution_code' => $institutionId,'stockist_code' => $item->STOCKIST_CODE ], [ 
                                'stockist_name' => $item->STOCKIST_NAME,
                                'stockist_address' => $item->STOCKIST_ADDRESS,
                                'email_id'=> $item->STOCKIST_EMAIL,
                                'stockist_type_flag' => 1,
                                'updated_at'=> now()
                            ]);
                        }
                    }
                }
            }

              
            $check_duplication_data = VoluntaryQuotationSkuListing::where('voluntary_quotation_sku_listing.item_code', $item_code)
            ->whereIn('voluntary_quotation_sku_listing.vq_id', $vq_ids)
            ->where('voluntary_quotation_sku_listing.product_type', 'old')
            ->get()
            ->toArray();
            
            /** Check Dulication End*/
            $generate_vq_existing_products = Session::get('generate_vq_existing_products');
            /** Create for child Items from parent  */
            if(count($check_duplication_data) == 1):
                $vq_created = date('Y-m-d H:i:s');
                $phpdate1 = strtotime( $vq_created );
                $start = date( 'Y-m-d H:i:s', $phpdate1 ); //contract_start_date
                $finddayyear = date("Y") + 1;
                $finddaymonth = date("3");
                $days = cal_days_in_month(CAL_GREGORIAN, $finddaymonth, $finddayyear);
                $enddateyear = strtotime( $finddayyear.'-'.$finddaymonth.'-'.$days );
                $end = date('Y-m-d H:i:s', $enddateyear);  //contract_end_date
                foreach ($data as $index => $row) {
                    $row->unique_id = $index + 1; 
                }
                $data_vq_id = [];
                foreach($data as $row){
                    $data_vq_id[] = $row['vq_id'];
                }
                $vq_id_unique_datas = array_unique($data_vq_id);
                // print_r($vq_id_unique_datas);die;
                
                if(!empty($vq_id_unique_datas)){
                    DB::beginTransaction();
                    try {
                        $institution_vq = [];
                        $listing_data = [];
                        $c = 1;
                        foreach($vq_id_unique_datas as $vq_id){
                            $vq = VoluntaryQuotation::where('id', $vq_id)->where('is_deleted', 0)->first();
                            $ignoredinstitutions = IgnoredInstitutions::where('parent_institution_id', data_get($vq, 'institution_id'))->select('parent_institution_id','institution_id')->get();
                            if(!empty($ignoredinstitutions)){
                                foreach($ignoredinstitutions as $ig_inst):
                                    $ignoreinstitution_vq = VoluntaryQuotation::where('institution_id', $ig_inst->institution_id)->where('parent_vq_id', 0)->where('year', $year)->where('is_deleted', 0)->first();
                                    // print_r($ignoreinstitution_vq);
                                    if(!empty($ignoreinstitution_vq)):
                                        // echo 'if';die;
                                        /** Get VoluntaryQuotation last rev_no query */
                                        $newestClient = VoluntaryQuotation::where('institution_id', $ig_inst->institution_id)->where('year', $year)->where('is_deleted', 0)->orderBy('rev_no', 'desc')->first(); // gets the one row
                                        // $maxValue = $newestClient->rev_no;
                                        // $rev_no = ($maxValue == NULL)? $maxValue+0 : $maxValue+1;
                                        $rev_no = (!empty($newestClient->toArray()))? $newestClient->rev_no+1 : '0';
                                        /** Insert VoluntaryQuotation Table */
                                        $institution_vq = VoluntaryQuotation::Create([
                                            'hospital_name' => $ignoreinstitution_vq->hospital_name,
                                            'institution_id' => $ignoreinstitution_vq->institution_id,
                                            'institution_key_account' => $ignoreinstitution_vq->institution_key_account,
                                            'city' => $ignoreinstitution_vq->city,
                                            'addr1'=>$ignoreinstitution_vq->addr1,
                                            'addr2'=>$ignoreinstitution_vq->addr2,
                                            'addr3'=>$ignoreinstitution_vq->addr3,
                                            'stan_code'=>$ignoreinstitution_vq->stan_code,
                                            'pincode'=>$ignoreinstitution_vq->pincode,
                                            'state_name'=>$ignoreinstitution_vq->state_name,
                                            'current_level_start_date' => $vq_created,
                                            'current_level' => "7",
                                            'address' => $ignoreinstitution_vq->address,
                                            'zone' => $ignoreinstitution_vq->zone,
                                            'institution_zone' => $ignoreinstitution_vq->institution_zone,
                                            'institution_region' => $ignoreinstitution_vq->institution_region,
                                            'cfa_code' => $ignoreinstitution_vq->cfa_code,
                                            'contract_start_date' => $start,
                                            'contract_end_date' => $end,
                                            'year' => $year,
                                            'sap_code' => $ignoreinstitution_vq->sap_code,
                                            'created_at' => $vq_created,
                                            'updated_at' => $vq_created,
                                            'vq_status' => 0,
                                            'parent_vq_id' => $ignoreinstitution_vq->id,
                                            'rev_no' => $rev_no
                                        ]);
                                        $get_inst_id[]  = $institution_vq->id;
                                        $vq_listing_controller->activityTracker($institution_vq->id, Session::get("emp_code"),'VQ ReInitiated by / '.Session::get("emp_name").'/'.Session::get("emp_code"), 'reinitiate');
                                        $get_vq_sku_listing = VoluntaryQuotationSkuListing::where('vq_id', $vq_id)->where('is_deleted', 0)->get();
                                        foreach($get_vq_sku_listing as $single_data):
                                            $listing_data[] = [
                                                'vq_id' => $institution_vq->id, // Last insert VoluntaryQuotation ID
                                                'item_code' => $single_data->item_code,
                                                'brand_name' => $single_data->brand_name,
                                                'mother_brand_name' => $single_data->mother_brand_name,
                                                'hsn_code' => $single_data->hsn_code,
                                                'applicable_gst' => $single_data->applicable_gst,
                                                'composition' => $single_data->composition,
                                                'type' => $single_data->type,
                                                'div_name' => $single_data->div_name,
                                                'div_id' => $single_data->div_id,
                                                'pack' => $single_data->pack,
                                                'ptr' => $single_data->ptr,
                                                'last_year_ptr' => $single_data->last_year_ptr,
                                                'last_year_percent' => $single_data->last_year_percent,
                                                'last_year_rate' => $single_data->last_year_rate,
                                                'pdms_discount' => $single_data->pdms_discount,
                                                'discount_percent' => $single_data->discount_percent,
                                                'discount_rate' => $single_data->discount_rate,
                                                'sap_itemcode' => $single_data->sap_itemcode,
                                                'mrp' => $single_data->mrp,
                                                'last_year_mrp' => $single_data->last_year_mrp,
                                                'mrp_margin'=>$single_data->mrp_margin,
                                                'created_at' => date('Y-m-d H:i:s'),
                                                'updated_at' => date('Y-m-d H:i:s'),
                                                'product_type' => 'old'
                                            ];
                                        endforeach;
                                    else:
                                        // echo 'else';die;
                                        /** Insert VoluntaryQuotation Table */
                                        $headers = [
                                            'Content-Type' => 'application/json',
                                            'AccessToken' => 'key',
                                            'Authorization' => 'Bearer '.$jwt['jwt_token'],
                                        ];
                                        $client = new GuzzleClient([
                                            'headers' => $headers,
                                            'verify' => false
                                        ]);
                                        $body = '{}';
                                        $r = $client->request('POST', env('API_URL').'/API/InstitutionOnly', [
                                            'body' => $body
                                        ]);
                                        $response = $r->getBody()->getContents();
                                        
                                        $response = json_decode($response, true);
                                        $resp_collection = collect($response);
                                        $chain_hospital_institution = $resp_collection->where('INST_ID', $ig_inst->institution_id)->first();
                                        /** Get VQ id by IG-INT parent insutition id */
                                        $parent_vq = VoluntaryQuotation::where('institution_id', $ig_inst->parent_institution_id)->where('year', $year)->where('is_deleted', 0)->orderBy('id', 'desc')->first();
                                        /** API Insert Data */
                                        $institution_vq = VoluntaryQuotation::Create([
                                            'hospital_name' => $chain_hospital_institution['INST_NAME'],
                                            'institution_id' => $chain_hospital_institution['INST_ID'],
                                            'institution_key_account' => $chain_hospital_institution['KEY_ACC_NAME'],
                                            'city' => $chain_hospital_institution['CITY'],
                                            'addr1'=>$chain_hospital_institution['ADDR1'],
                                            'addr2'=>$chain_hospital_institution['ADDR2'],
                                            'addr3'=>$chain_hospital_institution['ADDR3'],
                                            'stan_code'=>$chain_hospital_institution['STAN_CODE'],
                                            'pincode'=>$chain_hospital_institution['PINCODE'],
                                            'state_name'=>$chain_hospital_institution['STATE_NAME'],
                                            'current_level_start_date' => $vq_created,
                                            'current_level' => "7",
                                            'address' => $chain_hospital_institution['ADDRESS'],
                                            'zone' => $chain_hospital_institution['ZONE'],
                                            'institution_zone' => data_get($chain_hospital_institution, 'LSTZONEMAPPING.0.ZSM_ZONE'),
                                            'institution_region' => data_get($chain_hospital_institution, 'LSTZONEMAPPING.0.RSM_REGION'),
                                            'cfa_code' => $chain_hospital_institution['CFA_CODE'],
                                            'contract_start_date' => $start,
                                            'contract_end_date' => $end,
                                            'year' => $year,
                                            'sap_code' => $chain_hospital_institution['SAP_CODE'],
                                            // 'institution_zone' => isset($chain_hospital_institution['LSTZONEMAPPING'][0]->ZSM_ZONE) ? $chain_hospital_institution['LSTZONEMAPPING'][0]->ZSM_ZONE : '',
                                            // 'institution_region' => isset($chain_hospital_institution['LSTZONEMAPPING'][0]->RSM_REGION) ? $chain_hospital_institution['LSTZONEMAPPING'][0]->RSM_REGION : '',
                                            'created_at' => $vq_created,
                                            'updated_at' => $vq_created,
                                            'vq_status' => 0,
                                            'parent_vq_id' => 0,
                                            'rev_no' =>0//added on 05042024 to add rev no for create vq
                                        ]);
                                        $get_inst_id[]  = $institution_vq->id;
                                        $vq_listing_controller->activityTracker($institution_vq->id, Session::get("emp_code"),'VQ Initiated by / '.Session::get("emp_name").'/'.Session::get("emp_code"), 'initiate');
                                        $rateTransferInstitution = $parent_vq->institution_id;
                                        /** Insert VoluntaryQuotation Table */
                                        $maxRevSubquery = DB::table('voluntary_quotation_sku_listing as s')
                                        ->select(DB::raw('MAX(v2.rev_no) AS max_rev_no'), 's.item_code', 'v2.institution_id')
                                        ->leftJoin('voluntary_quotation as v2', 'v2.id', '=', 's.vq_id')
                                        ->where('v2.year', $year)
                                        ->where('s.is_deleted', 0)
                                        ->where('v2.vq_status', 1)
                                        ->where('v2.is_deleted', 0)
                                        ->where('v2.institution_id', $rateTransferInstitution)
                                        ->groupBy('s.item_code');
                                        $vq_sku_listing_datas = DB::table('voluntary_quotation_sku_listing as vqsl')
                                        ->select('vqsl.*', 'vq.*')
                                        ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
                                        ->joinSub($maxRevSubquery, 'max_rev', function ($join) use ($rateTransferInstitution) {
                                            $join->on('vqsl.item_code', '=', 'max_rev.item_code')
                                                ->where('vq.institution_id', $rateTransferInstitution)
                                                ->on('vq.rev_no', '=', 'max_rev.max_rev_no');
                                        })
                                        ->where('vq.institution_id', $rateTransferInstitution)
                                        ->where('vq.year', $year)
                                        ->where('vq.vq_status', 1)
                                        ->where('vq.is_deleted', 0)
                                        ->where('vqsl.is_deleted', 0)
                                        ->where('vqsl.item_code', $item_code)
                                        ->get();
                                        // $get_vq_sku_listing = VoluntaryQuotationSkuListing::where('vq_id', $vq_id)->where('is_deleted', 0)->get();
                                        foreach($vq_sku_listing_datas as $single_data){
                                            $listing_data[]=[
                                                'vq_id' => $institution_vq->id, // last insert vq id
                                                'item_code' => $single_data->item_code,
                                                'brand_name' => $single_data->brand_name,
                                                'mother_brand_name' => $single_data->mother_brand_name,
                                                'hsn_code' => $single_data->hsn_code,
                                                'applicable_gst' => $single_data->applicable_gst,
                                                'composition' => $single_data->composition,
                                                'type' => $single_data->type,
                                                'div_name' => $single_data->div_name,
                                                'div_id' => $single_data->div_id,
                                                'pack' => $single_data->pack,
                                                'ptr' => $single_data->ptr,
                                                'last_year_ptr' => $single_data->last_year_ptr,
                                                'last_year_percent' => $single_data->last_year_percent,
                                                'last_year_rate' => $single_data->last_year_rate,
                                                'pdms_discount' => $single_data->pdms_discount,
                                                'discount_percent' => $single_data->discount_percent,
                                                'discount_rate' => $single_data->discount_rate,
                                                'sap_itemcode' => $single_data->sap_itemcode,
                                                'mrp' => $single_data->mrp,
                                                'last_year_mrp' => $single_data->last_year_mrp,
                                                'mrp_margin'=>$single_data->mrp_margin,
                                                'created_at' => date('Y-m-d H:i:s'),
                                                'updated_at' => date('Y-m-d H:i:s'),
                                                'product_type' => 'old'
                                            ];
                                        }
                                    endif;
                                endforeach;
                            }
                        $c++;
                        }
                        Session::put('generate_vq_existing_products', $get_inst_id);
                        
                        // added to optimise the skulisting table insert starts
                        foreach (array_chunk($listing_data, 100) as $t)  
                        {
                            DB::table('voluntary_quotation_sku_listing')->insert($t); 
                        }
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Batch insert failed: " . $e->getMessage());
                    }
                }
            endif;
            /** Inserted all Vq Item Fetching Query added on 07122024 */
            $latest_all_vq_ids = DB::table('voluntary_quotation_sku_listing as vqsl')
            ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
            ->select('vqsl.vq_id', 'vqsl.item_code')
            ->where('vq.year', $year)
            ->where('vq.vq_status', 0)
            ->where('vq.current_level', 7)
            ->where('vq.is_deleted', 0)
            ->where('vqsl.is_deleted', 0)
            ->where('vqsl.product_type', 'old')
            ->groupBy('vqsl.vq_id')
            ->havingRaw('COUNT(vqsl.vq_id) = 1')
            ->get();
            $latest_vq_ids = $latest_all_vq_ids->filter(function ($item) use ($item_code) {
                return $item->item_code == $item_code;
            })->pluck('vq_id')->toArray();


            $data = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.*','voluntary_quotation.*', 'voluntary_quotation_sku_listing.id as sku_id','voluntary_quotation_sku_listing.vq_id','voluntary_quotation_sku_listing.item_code', 'stockist_master.*', 'stockist_master.id as stockist_id')
            ->leftJoin('voluntary_quotation', 'voluntary_quotation_sku_listing.vq_id', '=', 'voluntary_quotation.id')
            ->leftJoin('stockist_master', 'voluntary_quotation.institution_id', '=', 'stockist_master.institution_code')
            // ->whereIn('voluntary_quotation.id', $vq_ids) // hide by arunchandru 15012024
            ->whereIn('voluntary_quotation.id', $latest_vq_ids) // added by arunchandru 15012024
            ->where(function($query) {
                $query->where('stockist_master.stockist_type_flag', 1)
                ->orWhereNull('stockist_master.stockist_type_flag');
            })
            ->get();
            // change above variable $vq_ids to $latest_vq_ids on 07122024


            foreach ($data as $index => $row) {
                $row->unique_id = $index + 1; 
            }
            $sku_stockist_data = [];
            foreach($data as $row){
                if($row['stockist_id']!='')
                {
                    $vqslStockistExists = VoluntaryQuotationSkuListingStockist::
                    where('vq_id', $row['vq_id'])
                    ->where('sku_id',$row['sku_id'])
                    ->where('stockist_id',$row['stockist_id'])
                    ->where('item_code',$row['item_code'])
                    ->exists();
                    if(!$vqslStockistExists){
                        $sku_stockist_data[] = [
                            'vq_id' => $row['vq_id'],
                            'sku_id' => $row['sku_id'],
                            'item_code' => $row['item_code'],
                            'stockist_id' => $row['stockist_id'],
                            'parent_vq_id' => $row['parent_vq_id'],
                            'revision_count' => $row['rev_no'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    else
                    {

                    }
                }
            }
            if(count($sku_stockist_data)>0)
            {
                $chunkSize = 100;
                foreach (array_chunk($sku_stockist_data, $chunkSize) as $chunk) {
                    VoluntaryQuotationSkuListingStockist::insert($chunk);
                }
            }
            return response()->json([
                'data' => $data,
            ]);
        }
        catch (\Exception $e) {
            \Log::error('Error in getVqgenerationData: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while processing your request.'.$e->getMessage()
            ], 500);
        }
    }
    // Helper method to extract JSON from activity column
    private function extractJson($string) {
        $jsonStart = strpos($string, '{'); // Look for the start of the JSON object
        if ($jsonStart !== false) {
            $json = substr($string, $jsonStart);
            json_decode($json);
            // Check if it's valid JSON
            if (json_last_error() == JSON_ERROR_NONE) {
                return $json;
            }
        }
        return null;
    }

    // Helper method to strip JSON from activity column
    private function stripJson($string) {
        $jsonStart = strpos($string, '{'); // Look for the start of the JSON object
        if ($jsonStart !== false) {
            return substr($string, 0, $jsonStart); // Return everything before the JSON
        }
        return $string; // Return original if no JSON
    }
    public function create_stock(){
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        // $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        $jwt = JwtToken::where('jwt_token', '!=', '')->whereNotNull('jwt_token')->orderBy('updated_at', 'desc')->first();
        // $ignoredInstitutions = IgnoredInstitutions::get(); // hide by arunchandu 29-01-2025
        // $ignoredInstitutions = array_map(function($inst){return $inst->institution_id;}, $ignoredInstitutions->all()); // hide by arunchandu 29-01-2025
        $headers = [
            'Content-Type' => 'application/json',
            'AccessToken' => 'key',
            'Authorization' => 'Bearer '.$jwt['jwt_token'],
        ];
          
        $client = new GuzzleClient([
            'headers' => $headers,
            'verify' => false
        ]);
          
        $body = '{}';
          
        $r = $client->request('POST', env('API_URL').'/API/Institutions', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        
        // $data = json_decode($res->getBody());
        $resp = json_decode($response);
        $resp_collection =collect($resp);

        // Code to remove the institutions whiich are present in ignored_institutions table
        $resp = collect($resp);
        // $resp = $resp->whereNotIn('INST_ID',$ignoredInstitutions)->toArray(); // hide by arunchandu 29-01-2025
        $resp = $resp->toArray();
    
        usort($resp, function ($item1, $item2) {
            return $item1->INST_NAME <=> $item2->INST_NAME;
        });

       
        $data['current_cycle_institutes'] = $resp;


        $currentCycleInstitutesNewCounterRts = Stockist_master::select('stockist_code','stockist_name')->where('stockist_type_flag', 1)->groupBy('stockist_code')->get();
       
       
        $test['currentCycleInstitutesNewCounterRts'] = $currentCycleInstitutesNewCounterRts;
        
        $log = ActivityTracker::select('activity_trackers.*','employee_master.emp_code','employee_master.emp_name')->leftJoin('employee_master','employee_master.emp_code','=','activity_trackers.emp_code')-> whereIn('type',['stockist_wise','institution_wise'])
        ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity_trackers.activity, "$.fin_year")) = ?', [$year])
        //->where('activity_trackers.emp_code', Session::get("emp_code"))
        ->orderBy('activity_trackers.created_at', 'DESC')->get();
        
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.stockist', compact('data','test','log'));
        }
        else
        {
            return view('admin.stockist', compact('data','test','log'));
        }
    }


    
    public function downloadPDF1($id) {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $vqData = VoluntaryQuotation::where('institution_id', $id)->where('is_deleted', 0)->where('year', $year)->first();
        $zip_file = $vqData->hospital_name.'_cover_letter.zip';
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        
        // Fetch stockist master data
        $revisionAll = VoluntaryQuotation::select('voluntary_quotation.id as vq_id','voluntary_quotation.*','stockist_master.*')
        ->join('stockist_master', 'stockist_master.institution_code', '=', 'voluntary_quotation.institution_id')
        ->where('stockist_master.stockist_type_flag', 1)
        ->where('voluntary_quotation.institution_id', $id)
        ->where('voluntary_quotation.year', $year)
        ->where('voluntary_quotation.is_deleted', 0)
        ->get();

        // Collect voluntary quotations for each stockist
        foreach ($revisionAll as $revisionsingle) {

            $data = array();
            $vq = VoluntaryQuotation::where('id',$revisionsingle['vq_id'])->where('is_deleted', 0)->first();
            $vq_date = explode("-",$vq['year']);
            $vq_year = $vq_date[0].substr($vq_date[1], 2);
            // $date = new DateTime($vq['created_at']);
            $data['vq_data']= $vq;
            $data['stockist_data'] = VoluntaryQuotation::join('stockist_master','stockist_master.institution_code','=','voluntary_quotation.institution_id')
            ->where('voluntary_quotation.id',$revisionsingle['vq_id'])
            ->where('stockist_master.stockist_type_flag',1)
            ->where('voluntary_quotation.is_deleted', 0)
            ->select('stockist_master.*')->get();

            $data['poc_data'] = VoluntaryQuotation::join('poc_master','poc_master.institution_id','=','voluntary_quotation.institution_id')
            ->where('voluntary_quotation.id',$revisionsingle['vq_id'])
            ->where('voluntary_quotation.is_deleted', 0)
            ->select('poc_master.*')->first();
            if (empty($data['poc_data'])) {
                return redirect()->back()->withErrors(['message' => 'POC data is empty for '.$revisionsingle['institution_id']]);
            }

            $revision_count = VoluntaryQuotation::select('rev_no')->where('id',$revisionsingle['vq_id'])->where('is_deleted', 0)->first();
            $data['revision_count'] = $revision_count->rev_no;

            
            $data['signature'] = Signature::first();
            $type1 = pathinfo(base_path() . '/public/images/' . $data['signature']->spll_sign, PATHINFO_EXTENSION);
            $type2 = pathinfo(base_path() . '/public/images/' . $data['signature']->spil_sign, PATHINFO_EXTENSION);
               
            $data['signature']->spll_sign = 'data:image/' . $type1 . ';base64,' . base64_encode(file_get_contents(base_path() . '/public/images/' . $data['signature']->spll_sign));
            $data['signature']->spil_sign = 'data:image/' . $type2 . ';base64,' . base64_encode(file_get_contents(base_path() . '/public/images/' . $data['signature']->spil_sign));

            
            if ($revisionsingle['stockist_type'] == 'SPIL') {
                $file_prefix = ucwords(strtolower($revisionsingle['hospital_name'])) . '_SPIL_VQ' . $vq_year . ' - '. $revisionsingle['vq_id'];
                $pdf2 = PDF::loadView('admin.pdf.spilpdf', compact('data'))->output();
                /*Storage::put('spil_cover_' . $revisionsingle['vq_id'] . '.pdf', $pdf2->output());
                $zip->addFile(storage_path('app/spil_cover_' . $revisionsingle['vq_id'] . '.pdf'), $file_prefix . '.pdf');*/
                $zip->addFromString($file_prefix.'.pdf', $pdf2);
            } elseif ($revisionsingle['stockist_type'] == 'SPLL') {
                $file_prefix = ucwords(strtolower($revisionsingle['hospital_name'])) . '_SPLL_VQ' . $vq_year . ' - '. $revisionsingle['vq_id'];
                $pdf1 = PDF::loadView('admin.pdf.spllpdf', compact('data'))->output();
                /*Storage::put('spll_cover_' . $revisionsingle['vq_id'] . '.pdf', $pdf1->output());
                $zip->addFile(storage_path('app/spll_cover_' . $revisionsingle['vq_id'] . '.pdf'), $file_prefix . '.pdf');*/
                $zip->addFromString($file_prefix.'.pdf', $pdf1);
            } else {
                $file_prefix_spll = ucwords(strtolower($revisionsingle['hospital_name'])) . '_SPLL_VQ' . $vq_year . ' - '. $revisionsingle['vq_id'];
                $pdf1 = PDF::loadView('admin.pdf.spllpdf', compact('data'))->output();
                //Storage::put('spll_cover_' . $revisionsingle['vq_id'] . '.pdf', $pdf1->output());

                $file_prefix_spil = ucwords(strtolower($revisionsingle['hospital_name'])) . '_SPIL_VQ' . $vq_year . ' - '. $revisionsingle['vq_id'];
                $pdf2 = PDF::loadView('admin.pdf.spilpdf', compact('data'))->output();
                //Storage::put('spil_cover_' . $revisionsingle['vq_id'] . '.pdf', $pdf2->output());

                /*$zip->addFile(storage_path('app/spll_cover_' . $revisionsingle['vq_id'] . '.pdf'), $file_prefix_spll . '.pdf');
                $zip->addFile(storage_path('app/spil_cover_' . $revisionsingle['vq_id'] . '.pdf'), $file_prefix_spil . '.pdf');*/
                $zip->addFromString($file_prefix_spll.'.pdf', $pdf1);
                $zip->addFromString($file_prefix_spil.'.pdf', $pdf2);
            } 
        }
        $zip->close();
        return response()->download($zip_file);

        

       
    }
    public function downloadPDF($id) {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        //$vqData = VoluntaryQuotation::where('institution_id', $id)->where('is_deleted', 0)->where('year', $year)->first();
        $stockist = Stockist_master::where('id', $id)->where('stockist_type_flag', 1)->first();

        if (!$stockist) {
            return redirect()->back()->withErrors(['message' => 'Stockist inactive.']);
        }
        $vqData = VoluntaryQuotation::join('stockist_master', 'stockist_master.institution_code', '=', 'voluntary_quotation.institution_id')->select('stockist_master.*','voluntary_quotation.*','stockist_master.id as stockist_master_id')
        ->where('stockist_master.id', $id)->where('is_deleted', 0)->where('year', $year)->first();
        if (!$vqData) {
            return redirect()->back()->withErrors(['message' => 'No voluntary quotation found for the given stockist and year.']);
        }
        $zip_file = $vqData->hospital_name.'_cover_letter.zip';
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        
        // Fetch stockist master data
        $revisionAll = VoluntaryQuotation::select('voluntary_quotation.id as vq_id','voluntary_quotation.*')
        ->where('voluntary_quotation.institution_id', $stockist->institution_code)
        ->where('voluntary_quotation.year', $year)
        ->where('voluntary_quotation.is_deleted', 0)
        ->whereRaw('voluntary_quotation.rev_no = (
            SELECT MAX(vq2.rev_no)
            FROM voluntary_quotation vq2
            WHERE vq2.institution_id = voluntary_quotation.institution_id
            AND vq2.year = ?
            AND vq2.is_deleted = 0
        )', [$year])->get();
        if($revisionAll->isEmpty())
        {
            return redirect()->back()->withErrors(['message' => 'No Revisions found for Institution '.$vqData->hospital_name.'-'.$vqData->institution_id]);
        }
        foreach ($revisionAll as $singleRevision) {
            $data = array();
            $vq = VoluntaryQuotation::where('id',$singleRevision['vq_id'])->where('is_deleted', 0)->first();
            $vq_date = explode("-",$vq['year']);
            $vq_year = $vq_date[0].substr($vq_date[1], 2);
            // $date = new DateTime($vq['created_at']);
            $data['vq_data']= $vq;
            $spll_stockist_data = VoluntaryQuotation::join('stockist_master','stockist_master.institution_code','=','voluntary_quotation.institution_id')
            ->where('voluntary_quotation.id',$singleRevision['vq_id'])
            ->where('stockist_master.stockist_type_flag',1)
            ->where('voluntary_quotation.is_deleted', 0)
            ->where(function($query) {
                $query->whereNull('stockist_master.stockist_type')
                      ->orWhere('stockist_master.stockist_type', 'SPLL');
            })
            ->select('stockist_master.*')->get();
            $spil_stockist_data = VoluntaryQuotation::join('stockist_master','stockist_master.institution_code','=','voluntary_quotation.institution_id')
            ->where('voluntary_quotation.id',$singleRevision['vq_id'])
            ->where('stockist_master.stockist_type_flag',1)
            ->where('voluntary_quotation.is_deleted', 0)
            ->where(function($query) {
                $query->whereNull('stockist_master.stockist_type')
                      ->orWhere('stockist_master.stockist_type', 'SPIL');
            })
            ->select('stockist_master.*')->get();

            /*$data['stockist_data'] = VoluntaryQuotation::join('stockist_master','stockist_master.institution_code','=','voluntary_quotation.institution_id')
            ->where('voluntary_quotation.id',$singleRevision['vq_id'])
            ->where('stockist_master.stockist_type_flag',1)
            ->where('voluntary_quotation.is_deleted', 0)
            //->where('stockist_master.id', $id)
            ->select('stockist_master.*')->get();*/

            $data['poc_data'] = VoluntaryQuotation::join('poc_master','poc_master.institution_id','=','voluntary_quotation.institution_id')
            ->where('voluntary_quotation.id',$singleRevision['vq_id'])
            ->where('voluntary_quotation.is_deleted', 0)
            ->select('poc_master.*')->first();
            if (empty($data['poc_data'])) {
                return redirect()->back()->withErrors(['message' => 'POC data is empty for '.$singleRevision['institution_id']]);
            }

            $revision_count = VoluntaryQuotation::select('rev_no')->where('id',$singleRevision['vq_id'])->where('is_deleted', 0)->first();
            $data['revision_count'] = $revision_count->rev_no;

            
            $data['signature'] = Signature::first();
            $type1 = pathinfo(base_path() . '/public/images/' . $data['signature']->spll_sign, PATHINFO_EXTENSION);
            $type2 = pathinfo(base_path() . '/public/images/' . $data['signature']->spil_sign, PATHINFO_EXTENSION);
               
            $data['signature']->spll_sign = 'data:image/' . $type1 . ';base64,' . base64_encode(file_get_contents(base_path() . '/public/images/' . $data['signature']->spll_sign));
            $data['signature']->spil_sign = 'data:image/' . $type2 . ';base64,' . base64_encode(file_get_contents(base_path() . '/public/images/' . $data['signature']->spil_sign));

            
            if(count($spil_stockist_data) > 0)
            {
                $data['stockist_data'] = $spil_stockist_data;
                $file_prefix = ucwords(strtolower($singleRevision['hospital_name'])) . '_SPIL_VQ' . $vq_year . ' - '. $singleRevision['vq_id'];
                $pdf2 = PDF::loadView('admin.pdf.spilpdf', compact('data'))->output();
                /*Storage::put('spil_cover_' . $singleRevision['vq_id'] . '.pdf', $pdf2->output());
                $zip->addFile(storage_path('app/spil_cover_' . $singleRevision['vq_id'] . '.pdf'), $file_prefix . '.pdf');*/
                $zip->addFromString($file_prefix.'.pdf', $pdf2);
            } 
            if(count($spll_stockist_data) > 0)
            {
                $data['stockist_data'] = $spll_stockist_data;
                $file_prefix = ucwords(strtolower($singleRevision['hospital_name'])) . '_SPLL_VQ' . $vq_year . ' - '. $singleRevision['vq_id'];
                $pdf1 = PDF::loadView('admin.pdf.spllpdf', compact('data'))->output();
                /*Storage::put('spll_cover_' . $singleRevision['vq_id'] . '.pdf', $pdf1->output());
                $zip->addFile(storage_path('app/spll_cover_' . $singleRevision['vq_id'] . '.pdf'), $file_prefix . '.pdf');*/
                $zip->addFromString($file_prefix.'.pdf', $pdf1);
            } 
        } 
        $zip->close();
        return response()->download($zip_file);
        
    }
    public function stockist_downloadPDF($id) {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $stockistData = Stockist_master::where('stockist_code', $id)->where('stockist_type_flag', 1)->first();
        $zip_file = $stockistData->stockist_name.'_cover_letter.zip';

        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        
        // Fetch stockist master data
       $revisionAll = VoluntaryQuotation::select('voluntary_quotation.id as vq_id', 'voluntary_quotation.*', 'stockist_master.*', 'stockist_master.id as stockist_master_id')
        ->join('stockist_master', 'stockist_master.institution_code', '=', 'voluntary_quotation.institution_id')
        ->where('stockist_master.stockist_type_flag', 1)
        ->where('stockist_master.stockist_code', $id)
        ->where('voluntary_quotation.year', $year)
        ->where('voluntary_quotation.is_deleted', 0)
        ->whereRaw('voluntary_quotation.rev_no = (
            SELECT MAX(vq2.rev_no)
            FROM voluntary_quotation vq2
            WHERE vq2.institution_id = voluntary_quotation.institution_id
            AND vq2.year = ?
            AND vq2.is_deleted = 0
        )', [$year])
        ->get();
        if($revisionAll->isEmpty())
        {
            return redirect()->back()->withErrors(['message' => 'No Revisions found for Stockist '.$stockistData->stockist_name.'-'.$stockistData->stockist_code]);
        }

        $allStockistData = VoluntaryQuotation::join('stockist_master','stockist_master.institution_code','=','voluntary_quotation.institution_id')
            ->where('stockist_master.stockist_code',$id)
            ->where('stockist_master.stockist_type_flag',1)
            ->where('voluntary_quotation.year', $year)
            ->where('voluntary_quotation.is_deleted', 0)
            ->select('stockist_master.*','voluntary_quotation.id as vq_id')->get();
        $allStockistData = collect($allStockistData);

        $allPocData = VoluntaryQuotation::join('poc_master','poc_master.institution_id','=','voluntary_quotation.institution_id')
            ->where('voluntary_quotation.year', $year)
            ->where('voluntary_quotation.is_deleted', 0)
            ->select('poc_master.*','voluntary_quotation.id as vq_id')->get();
        $allPocData = collect($allPocData);

        $signature = Signature::first();
        $type1 = pathinfo(base_path() . '/public/images/' . $signature->spll_sign, PATHINFO_EXTENSION);
        $type2 = pathinfo(base_path() . '/public/images/' . $signature->spil_sign, PATHINFO_EXTENSION);
        $signature->spll_sign = 'data:image/' . $type1 . ';base64,' . base64_encode(file_get_contents(base_path() . '/public/images/' . $signature->spll_sign));
        $signature->spil_sign = 'data:image/' . $type2 . ';base64,' . base64_encode(file_get_contents(base_path() . '/public/images/' . $signature->spil_sign));

        // Collect voluntary quotations for each stockist
        foreach ($revisionAll as $revisionsingle) {

            $data = array();
            //$vq = VoluntaryQuotation::where('id',$revisionsingle['vq_id'])->where('is_deleted', 0)->first();
            $vq = $revisionsingle;
            $vq_date = explode("-",$vq['year']);
            $vq_year = $vq_date[0].substr($vq_date[1], 2);
            // $date = new DateTime($vq['created_at']);
            $data['vq_data']= $vq;

            /*$data['stockist_data'] = VoluntaryQuotation::join('stockist_master','stockist_master.institution_code','=','voluntary_quotation.institution_id')
            ->where('voluntary_quotation.id',$revisionsingle['vq_id'])
            ->where('stockist_master.stockist_code',$id)
            ->where('stockist_master.stockist_type_flag',1)
            ->where('voluntary_quotation.is_deleted', 0)
            ->select('stockist_master.*')->get();*/
            $data['stockist_data'] = $allStockistData->where('vq_id', $revisionsingle['vq_id']);

            /*$data['poc_data'] = VoluntaryQuotation::join('poc_master','poc_master.institution_id','=','voluntary_quotation.institution_id')
            ->where('voluntary_quotation.id',$revisionsingle['vq_id'])
            ->where('voluntary_quotation.is_deleted', 0)
            ->select('poc_master.*')->first();*/
            $data['poc_data'] = $allPocData->where('vq_id', $revisionsingle['vq_id'])->first();


            if (empty($data['poc_data'])) {
                return redirect()->back()->withErrors(['message' => 'POC data is empty for '.$revisionsingle['institution_id']]);
            }

            //$revision_count = VoluntaryQuotation::select('rev_no')->where('id',$revisionsingle['vq_id'])->where('is_deleted', 0)->first();
            $data['revision_count'] = $vq->rev_no;

            
            /*$data['signature'] = Signature::first();
            $type1 = pathinfo(base_path() . '/public/images/' . $data['signature']->spll_sign, PATHINFO_EXTENSION);
            $type2 = pathinfo(base_path() . '/public/images/' . $data['signature']->spil_sign, PATHINFO_EXTENSION);
               
            $data['signature']->spll_sign = 'data:image/' . $type1 . ';base64,' . base64_encode(file_get_contents(base_path() . '/public/images/' . $data['signature']->spll_sign));
            $data['signature']->spil_sign = 'data:image/' . $type2 . ';base64,' . base64_encode(file_get_contents(base_path() . '/public/images/' . $data['signature']->spil_sign));*/
            $data['signature'] = $signature;
            
            if ($revisionsingle['stockist_type'] == 'SPIL') {
                $file_prefix = ucwords(strtolower($revisionsingle['hospital_name'])) . '_SPIL_VQ' . $vq_year . ' - '. $revisionsingle['vq_id'];
                $pdf2 = PDF::loadView('admin.pdf.spilpdf', compact('data'))->stream();
                /*Storage::put('spil_cover_' . $revisionsingle['vq_id'] . '.pdf', $pdf2->output());
                $zip->addFile(storage_path('app/spil_cover_' . $revisionsingle['vq_id'] . '.pdf'), $file_prefix . '.pdf');*/
                $zip->addFromString($file_prefix.'.pdf', $pdf2);
            } elseif ($revisionsingle['stockist_type'] == 'SPLL') {
                $file_prefix = ucwords(strtolower($revisionsingle['hospital_name'])) . '_SPLL_VQ' . $vq_year . ' - '. $revisionsingle['vq_id'];
                $pdf1 = PDF::loadView('admin.pdf.spllpdf', compact('data'))->stream();
                /*Storage::put('spll_cover_' . $revisionsingle['vq_id'] . '.pdf', $pdf1->output());
                $zip->addFile(storage_path('app/spll_cover_' . $revisionsingle['vq_id'] . '.pdf'), $file_prefix . '.pdf');*/
                $zip->addFromString($file_prefix.'.pdf', $pdf1);
            } else {
                $file_prefix_spll = ucwords(strtolower($revisionsingle['hospital_name'])) . '_SPLL_VQ' . $vq_year . ' - '. $revisionsingle['vq_id'];
                $pdf1 = PDF::loadView('admin.pdf.spllpdf', compact('data'))->stream();
                //Storage::put('spll_cover_' . $revisionsingle['vq_id'] . '.pdf', $pdf1->output());

                $file_prefix_spil = ucwords(strtolower($revisionsingle['hospital_name'])) . '_SPIL_VQ' . $vq_year . ' - '. $revisionsingle['vq_id'];
                $pdf2 = PDF::loadView('admin.pdf.spilpdf', compact('data'))->stream();
                //Storage::put('spil_cover_' . $revisionsingle['vq_id'] . '.pdf', $pdf2->output());

                /*$zip->addFile(storage_path('app/spll_cover_' . $revisionsingle['vq_id'] . '.pdf'), $file_prefix_spll . '.pdf');
                $zip->addFile(storage_path('app/spil_cover_' . $revisionsingle['vq_id'] . '.pdf'), $file_prefix_spil . '.pdf');*/
                $zip->addFromString($file_prefix_spll.'.pdf', $pdf1);
                $zip->addFromString($file_prefix_spil.'.pdf', $pdf2);
            } 
        }
        $zip->close();
        return response()->download($zip_file);
        
    }
    public function activity_tracker_new(){
        
        //$data = ActivityTracker::whereNotNull('type')->where('vq_id', '!=', 1)->orderBy('id','DESC')->get();
        $url_id = 1;

        $types = ActivityTracker::select('type')->distinct()->whereNotNull('type')->where('vq_id', '!=', 1)->orderBy('type')->get();
        $formattedTypes = $types->map(function ($type) {
            return [
                'db_value' => $type->type,
                'display_value' => ucwords(str_replace('_', ' ', $type->type))
            ];
        });
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $institutions = VoluntaryQuotation::select('hospital_name', 'institution_id')->where('year', $year)->where('parent_vq_id', 0)->groupBy('hospital_name','institution_id')->get();
        
        /*foreach ($data as $activity) {
            $activity->json_data = $this->extractJson($activity->activity);
            $activity->activity_text = $this->stripJson($activity->activity);
        }*/
        // Handle JSON download for specific activity
        if (request()->has('download') && request()->has('activity_id')) {
            $activity = ActivityTracker::find(request('activity_id'));

            if ($activity) {
                $json = $this->extractJson($activity->activity);
                if ($json) {
                    $jsonData = json_encode(json_decode($json), JSON_PRETTY_PRINT);
                    $fileName = 'activity_' . request('activity_id') . '.txt';

                    // Return the JSON file as a download response
                    return response()->streamDownload(function () use ($jsonData) {
                        echo $jsonData;
                    }, $fileName, ['Content-Type' => 'application/json']);
                }
            }
        }
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.activityNew',compact('formattedTypes', 'institutions'));
        }
        else
        {
            return view('admin.activityNew',compact('formattedTypes','institutions'));
        }
    }
    public function initiateDate() {
        $vq_ini_dates = VqInitiateDates::all();
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $vq_checker = VoluntaryQuotation::where('year',$year)->where('parent_vq_id',0)->where('is_deleted', 0)->exists();
        $year_arr = explode('-',$year);
        return view('admin.initiate_date',compact('vq_ini_dates' , 'vq_checker' , 'year_arr'));
    }
    public function initiateDateAdd(Request $request) {
        $vq_ini_dates = VqInitiateDates::first();
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $vq_checker = VoluntaryQuotation::where('year',$year)->where('parent_vq_id',0)->where('is_deleted', 0)->exists();
        $year_arr = explode('-',$year);
        return view('admin.initiate_date_add',compact('vq_ini_dates' , 'vq_checker' , 'year_arr'));
    }
    public function initiateDateUpdate(Request $request) {
        if($request->id){
            $vq_ini_dates = VqInitiateDates::where('id' , $request->id)->update(['date' => $request->date]);
        }else{
            $vq_ini_dates = VqInitiateDates::create(['date' => $request->date]);
        }
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $ip_address = request()->ip();
        $userAgent = request()->header('User-Agent');
        $params = array();
        $params['fin_year'] = $year;
        $params['ip_address'] = $ip_address;
        $params['changed_at'] = date('Y-m-d H:i:s');
        $params['user_agent'] = $userAgent;
        $params['changed_to'] = $request->date;
        $updation = ActivityTracker::Create([
            'vq_id' => 1,
            'emp_code' => Session::get("emp_code"),
            'activity' => json_encode($params),
            'type'=>'change_vq_init_date',
            'meta_data'=>null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->route('initiate_date');
    }
    /* added by arunchandru at 24-01-2025 */
    public function productwiseCumulativeReport(){
        $emp_code = Session::get("emp_code");
        $division_id = explode(',',Session::get("division_id"));
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        
        if(Session::get("type") == 'initiator' || Session::get("type") == 'ceo' || Session::get("type") == 'poc' || Session::get("type") == 'distribution'){
            $division_Names = DB::table('brands')->select('id','div_name','div_id')->groupBy('div_name','div_id')->orderBy('div_name', 'ASC')->get()->toArray();
        }else{
            $division_Names = DB::table('brands')->select('id', 'div_name','div_id')->whereIn('div_id', $division_id)->groupBy('div_name','div_id')->orderBy('div_name', 'ASC')->get()->toArray();
        }
        
        $emp_category = (Session::get("type") == 'ceo')? 'approver' : Session::get("type");
        $brand_Names = DB::table('brands')->select('id','brand_name')->groupBy('brand_name')->orderBy('brand_name', 'ASC')->get()->toArray();
       
        // if(Session::get("type") == 'initiator'){} 
        // elseif(Session::get("type") == 'approver'){}
        // elseif(Session::get("type") == 'distribution'){}
        // elseif(Session::get("type") == 'ho'){}
        // elseif(Session::get("type") == 'poc'){}
        // elseif(Session::get("type") == 'ceo'){}
        // elseif(Session::get("type") == 'admin'){}

        if(Session::get("type") == 'admin')
        {
            return view('admin.product_cumulative_reportNew',compact('division_Names', 'brand_Names', 'emp_category'));
        }
        else
        {
            return view('frontend.Initiator.product_cumulative_report',compact('division_Names', 'brand_Names', 'emp_category'));
        }
        
    }

    /* added by arunchandru at 13-05-2025 */
    public function initiator_bulk_counter_update(){
        $emp_code = Session::get("emp_code");
        $division_id = explode(',',Session::get("division_id"));
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");

        $vqInstitutions = VoluntaryQuotation::select('*')->where('parent_vq_id', 0)->where('year',$year)->where('is_deleted', 0)->get();
        
        $brandNameswithItemcode = DB::table('brands')->select('id','item_code', 'brand_name')->groupBy('brand_name')->orderBy('brand_name', 'ASC')->get();
        
        $DiscountMargin = DiscountMarginMaster::pluck('discount_margin', 'item_code')->toArray();
        $data['DiscountMargin_datas'] = $DiscountMargin;
        if(Session::get("type") == 'admin')
        {
            return view('admin.bulk_update_counter',compact('vqInstitutions', 'brandNameswithItemcode', 'data'));
        }
        else
        {
            return view('frontend.Initiator.bulk_update_counter',compact('vqInstitutions', 'brandNameswithItemcode', 'data'));
        }
    }


    public function productwise_discard_data()
    {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $brand_Names = DB::table('brands')->select('brand_name','item_code','sap_itemcode')->orderBy('brand_name','ASC')->get()->toArray();
 
        if(Session::get("type") == 'admin')
        {
            return view('admin.productwise_discard',compact('brand_Names'));
        }
        else
        {
            return view('frontend.Initiator.productwise_discard',compact('brand_Names'));
        }
    }



    
}