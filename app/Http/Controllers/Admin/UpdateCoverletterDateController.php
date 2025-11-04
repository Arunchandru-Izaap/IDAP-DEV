<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityTracker;
use App\Models\ApprovalEmailScheduleMaster;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\VoluntaryQuotationSkuListingStockist;
use App\Models\JwtToken;
use App\Models\ApprovalPeriod;
use App\Http\Controllers\Api\VqListingController;
use Illuminate\Support\Facades\DB;
use Session;
use DateTime; 
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Mail;

class UpdateCoverletterDateController extends Controller
{
    public function view_update_date()
    {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        
        $institutions = VoluntaryQuotation::select('institution_id','hospital_name')
        ->where('voluntary_quotation.year', $year)
        ->where('voluntary_quotation.parent_vq_id', 0)
        ->orderby('voluntary_quotation.institution_id', 'asc')
        ->where('is_deleted', 0)
        ->groupBy('institution_id','hospital_name')
        ->get()->toArray();
        $data['institutions'] = $institutions;

        $log = ActivityTracker::select('activity_trackers.*','employee_master.emp_code','employee_master.emp_name')
            ->leftJoin('employee_master','employee_master.emp_code','=','activity_trackers.emp_code')
            ->where('type','update_cover_letter_date')
            ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.fin_year")) = ?', [$year])
            ->orderBy('activity_trackers.created_at', 'DESC')->get();
        $data['logs'] = $log;
        
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.coverletter.update_cover_letter_date',compact('data'));
        }
        else
        {
            return view('admin.coverletter.update_cover_letter_date', compact('data'));
        }
    }

    public function GetInstutitionRevisions(Request $request)
    {
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $institution_ids = $request->data;
        $revision_no = [];
        if ($institution_ids != null) {
            foreach($institution_ids as $institution){
                $check_institutions = VoluntaryQuotation::select('institution_id','hospital_name')
                    ->where('year', $year)
                    ->where('institution_id', $institution)
                    ->where('is_deleted', 0)
                    ->get()->toArray(); 
                $revision_no[$institution] = (!empty($check_institutions))? count($check_institutions) : '0';
            }
        }
        $data['maxValue'] = max($revision_no); 
        $data['revision_no'] = $revision_no;
        //print_r($data);die;
        return response()->json([
            'success' => true, 
            'message' => "Get Max revisions",
            'data' => $data
        ]);
    }

    public function update_coverletter_date(Request $request)
    {
        $vq_listing_controller = new VqListingController;
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $ip_address = request()->ip();
        $userAgent = request()->header('User-Agent');

        $validatedData = $request->validate([
            'institution_id' => 'required'
        ]);

        $institution_ids = $request->institution_id;
        $revision_no = $request->rev_no;
        $update_date = (!empty($request->coverletter_date))? $request->coverletter_date : null ;
        if(!empty($institution_ids)):
            foreach($institution_ids as $institution):
                $get_before_update_details = VoluntaryQuotation::select('institution_id','hospital_name', 'rev_no',  'cover_letter_date')
                ->where('institution_id', $institution)
                ->where('year', $year)
                ->where('is_deleted', 0);
                if ($revision_no != 'empty') {
                    $get_before_update_details->where('rev_no', $revision_no);
                }
                $get_before_update_details = $get_before_update_details->get();

                // activity logs store in the table 
                $addl_params = array();
                $addl_params['fin_year'] = $year;
                $addl_params['ip_address'] = $ip_address;
                $addl_params['changed_at'] = date('Y-m-d H:i:s');
                $addl_params['user_agent'] = $userAgent;
                foreach($get_before_update_details as $update_details):
                    $addl_params['institution_id'] = $update_details->institution_id;
                    $addl_params['hospital_name'] = $update_details->hospital_name;
                    $addl_params['revision_no'] = $update_details->rev_no;
                    $addl_params['pervious_cover_letter_date'] = $update_details->cover_letter_date;
                    $addl_params['changed_cover_letter_date'] = $request->coverletter_date;
                    //print_r($addl_params);die;
                    $vq_listing_controller->activityTracker(1, Session::get("emp_code"), json_encode($addl_params), 'update_cover_letter_date');
                endforeach;

                // Update Date into the table
                $institution_data['cover_letter_date'] = $update_date;
                $query = VoluntaryQuotation::where('institution_id', $institution)
                    ->where('year', $year)
                    ->where('is_deleted', 0);
                if ($revision_no != 'empty') {
                    $query->where('rev_no', $revision_no);
                }
                $query->update($institution_data);
            endforeach;
        endif;

        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/view_update_date')->with('message', 'Cover Letter Date is successfully validated and Date has been Updated');
        }
        else
        {
            return redirect('/admin/view_update_date')->with('message', 'Cover Letter Date is successfully validated and Date has been Updated');
        }
    }

    public function GetCoverLetterUpdatedViewLogs(Request $request)
    {
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $vq_id = $request->vq_id;
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $common_search = $request->input('search.value');
        $orderColumnIndex = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir');
        $columns = ['id', 'id', 'created_at', 'institution_id', 'revision_no', 'id'];
       
        $details = ActivityTracker::select(
            'activity_trackers.*',
            'employee_master.emp_code',
            'employee_master.emp_name',
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.fin_year")) as fin_year'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.ip_address")) as ip_address'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.user_agent")) as user_agent'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.changed_at")) as changed_at'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.institution_id")) as institution_id'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.hospital_name")) as hospital_name'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.revision_no")) as revision_no'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.pervious_cover_letter_date")) as pervious_cover_letter_date'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.changed_cover_letter_date")) as changed_cover_letter_date')
        )
        ->leftJoin('employee_master', 'employee_master.emp_code', '=', 'activity_trackers.emp_code')
        ->where('type', 'update_cover_letter_date')
        ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.fin_year")) = ?', [$year]);
        
        // Apply common search filter
        if (!empty($common_search)) {
            $details->where(function ($q) use ($common_search) {
                $q->where('employee_master.emp_code', 'like', "%$common_search%")
                ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.fin_year")) LIKE ?', ["%$common_search%"])
                ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.changed_at")) LIKE ?', ["%$common_search%"])
                ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.institution_id")) LIKE ?', ["%$common_search%"])
                ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.hospital_name")) LIKE ?', ["%$common_search%"])
                ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.revision_no")) LIKE ?', ["%$common_search%"]);
            });
        }

        $recordsFiltered = $details->count();
        // Apply sorting
        if(isset($orderColumnIndex)&&array_key_exists($orderColumnIndex, $columns))
        {
            $orderColumnName = $columns[$orderColumnIndex];
            $details->orderBy($orderColumnName, $orderDirection);
        }

        // Fetch data for the current page
        $data = $details->offset($start)->limit($length)->get();

        // $sql = $details->toSql();
        // $bindings = $details->getBindings();
        // dd($sql, $bindings);

        $queryTotal1 = ActivityTracker::leftJoin('employee_master', 'employee_master.emp_code', '=', 'activity_trackers.emp_code')
        ->where('activity_trackers.type', 'update_cover_letter_date')
        ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.fin_year")) = ?', [$year])
        ->count();

        $recordsTotal = $queryTotal1;
        // Prepare JSON response for DataTables
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
}
?>