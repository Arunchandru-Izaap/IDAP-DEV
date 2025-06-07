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
use App\Models\CeilingMaster;
use App\Models\Config;
use App\Models\JwtToken;
use App\Models\Stockist_master;
use GuzzleHttp\Client as GuzzleClient;
use App\Http\Controllers\Api\VqListingController;
set_time_limit(0);
class VoluntaryQuotationSkuListingController extends Controller
{
    public function approverDetails($id){
        $vq=VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        $details_listing_data_deleted_check = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.*')->where('voluntary_quotation_sku_listing.vq_id',$id)->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.is_deleted',1)->where('deleted_by', preg_replace('/[^0-9.]+/', '', Session::get("level")))->exists();
        if($vq->current_level >= preg_replace('/[^0-9.]+/', '', Session::get("level"))){
            if(Session::get("level")!='L1' ){
                $detailsbig = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
            $query->where('is_deleted', 0)->with('getStockistDetails');
        }])->select('voluntary_quotation_sku_listing.*','voluntary_quotation.current_level','max_discount_cap.max_discount','ceiling_master.discount_percent as ceiling_percent')->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.vq_id',$id)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->leftJoin('max_discount_cap','voluntary_quotation_sku_listing.div_id','=','max_discount_cap.div_id')->leftJoin('ceiling_master','ceiling_master.sku_id','=','voluntary_quotation_sku_listing.item_code')->whereRaw('voluntary_quotation_sku_listing.discount_percent > ceiling_master.discount_percent');
                if(!$details_listing_data_deleted_check)
                {
                    $detailsbig = $detailsbig->where('voluntary_quotation_sku_listing.is_deleted', 0)->get();
                }
                else
                {
                    $detailsbig = $detailsbig->get();
                }
                $detailssmall = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
            $query->where('is_deleted', 0)->with('getStockistDetails');
        }])->select('voluntary_quotation_sku_listing.*','voluntary_quotation.current_level','max_discount_cap.max_discount','ceiling_master.discount_percent as ceiling_percent')->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.vq_id',$id)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->leftJoin('max_discount_cap','voluntary_quotation_sku_listing.div_id','=','max_discount_cap.div_id')->leftJoin('ceiling_master','ceiling_master.sku_id','=','voluntary_quotation_sku_listing.item_code')->whereRaw('voluntary_quotation_sku_listing.discount_percent < ceiling_master.discount_percent');
                if(!$details_listing_data_deleted_check)
                {
                    $detailssmall = $detailssmall->where('voluntary_quotation_sku_listing.is_deleted', 0)->get();
                }
                else
                {
                    $detailssmall = $detailssmall->get();
                }
               	$detailssame = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
            $query->where('is_deleted', 0)->with('getStockistDetails');
        }])->select('voluntary_quotation_sku_listing.*','voluntary_quotation.current_level','max_discount_cap.max_discount','ceiling_master.discount_percent as ceiling_percent')->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.vq_id',$id)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->leftJoin('max_discount_cap','voluntary_quotation_sku_listing.div_id','=','max_discount_cap.div_id')->leftJoin('ceiling_master','ceiling_master.sku_id','=','voluntary_quotation_sku_listing.item_code')->whereRaw('voluntary_quotation_sku_listing.discount_percent = ceiling_master.discount_percent');
                if(!$details_listing_data_deleted_check)
                {
                    $detailssame = $detailssame->where('voluntary_quotation_sku_listing.is_deleted', 0)->get();
                }
                else
                {
                    $detailssame = $detailssame->get();
                }
                $detailsmissing = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
            $query->where('is_deleted', 0)->with('getStockistDetails');
        }])->select('voluntary_quotation_sku_listing.*','voluntary_quotation.current_level','max_discount_cap.max_discount','ceiling_master.discount_percent as ceiling_percent')->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.vq_id',$id)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->leftJoin('max_discount_cap','voluntary_quotation_sku_listing.div_id','=','max_discount_cap.div_id')->leftJoin('ceiling_master','ceiling_master.sku_id','=','voluntary_quotation_sku_listing.item_code')->whereNull('ceiling_master.discount_percent');
                if(!$details_listing_data_deleted_check)
                {
                    $detailsmissing = $detailsmissing->where('voluntary_quotation_sku_listing.is_deleted', 0)->get();
                }
                else
                {
                    $detailsmissing = $detailsmissing->get();
                }
		$details = new \Illuminate\Database\Eloquent\Collection;
                $details =$details->merge($detailsbig);
                $details =$details->merge($detailssmall);
                $details =$details->merge($detailssame);
		$details =$details->merge($detailsmissing);
            }
            else{
                $details = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
            $query->where('is_deleted', 0)->with('getStockistDetails');
        }])->select('voluntary_quotation_sku_listing.*','voluntary_quotation.current_level','max_discount_cap.max_discount','ceiling_master.discount_percent as ceiling_percent')->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.vq_id',$id)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->leftJoin('max_discount_cap','voluntary_quotation_sku_listing.div_id','=','max_discount_cap.div_id')->leftJoin('ceiling_master','ceiling_master.sku_id','=','voluntary_quotation_sku_listing.item_code')->distinct()->get();
                if(!$details_listing_data_deleted_check)
                {
                    $details = $details->where('voluntary_quotation_sku_listing.is_deleted', 0);
                }
            }  
             
	//dd($details);

        }else{
            $details =[];
        }
        $stockists = Stockist_master::where('institution_code', data_get($id, 'institution_id'))->get();
        $stockist_margin = Config::where('meta_key', 'stockist_margin')->first();
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $exception_items = DB::table('exception_sku_list')->select('item_code','div_id')->where('year',$year)->get();
        $data['exception_items'] = $exception_items;
        $DiscountMargin = DiscountMarginMaster::pluck('discount_margin', 'item_code')->toArray();
        $data['DiscountMargin_datas'] = $DiscountMargin;
        $data['vq_data']=$vq;
        $data['jwt']=$jwt['jwt_token'];
        $data['details']=$details;
        $data['stockists']=$stockists;
        $data['stockist_margin']=$stockist_margin->meta_value;

        $level = (int) preg_replace('/[^0-9.]+/', '', Session::get("level"));
        if($level == 5 || $level == 6){//added by govind on 170425 start
            $data['pendingDivisions'] = VoluntaryQuotation::select('voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.div_name')
            ->where('current_level','=',$level)->where('year',$year)
            ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            ->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))
            ->where('voluntary_quotation_sku_listing.is_deleted','!=',1)
            ->where('voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status', 0)
            ->where('voluntary_quotation.is_deleted', 0)->where('voluntary_quotation.id', $id)->groupBy('div_id')->get();
        }
        else
        {
            $data['pendingDivisions'] = collect();
        }//added by govind on 170425 end
        return view('frontend.Approver.details',compact('data'));
    }

    public function getPdmsDiscount(Request $request){
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
        
        // $body = '{
        //     "FIN_YEAR": "2023-2024",
        //     "ITEM_CODE": "'.$request->item_code.'",
        //     "DIV_CODE": "'.$request->div_id.'",
        //     "INSTITUTE_CODE": "'.$request->institution_id.'"
        // }';
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $body = '{
            "FIN_YEAR": "'.$year.'",
            "ITEM_CODE": "",
            "DIV_CODE": "",
            "INSTITUTE_CODE": "'.$request->institution_id.'"
        }';
        $r = $client->request('GET', env('API_URL').'/API/PDMSData', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
    
        // $data = json_decode($res->getBody());
        $data = json_decode($response);
        $updateStatements = [];
        foreach ($data as $items) {
            $updateStatements[] = DB::raw("UPDATE voluntary_quotation_sku_listing SET pdms_discount = ".$items->MAX_DISCOUNT." WHERE item_code = ".$items->ITEM_CODE." and vq_id = ".$request->vq_id."");
        }
    
        // Execute the bulk update
        $sql = implode('; ', $updateStatements);
        DB::statement($sql);
        return $data;
    }
    public function initiatorDetails($id){
        $vq=VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        //$details = VoluntaryQuotationSkuListing::with('getSkuStockist.getStockistDetails')->where('vq_id',$id)->where('is_deleted',0)->get();
        
        $stockists = Stockist_master::where('institution_code', data_get($vq, 'institution_id'))->get();
        $stockist_margin = Config::where('meta_key', 'stockist_margin')->first();
        /*$division_name = VoluntaryQuotationSkuListing::select('div_name')->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('year',$year)->where('voluntary_quotation.is_deleted',0)->where('voluntary_quotation_sku_listing.is_deleted',0)->groupBy('div_name')->orderBy('div_name')->get();*/
        //$division_name = VoluntaryQuotationSkuListing::select('div_name')->groupBy('div_name')->orderBy('div_name')->get();
        $division_name = DB::table('brands')->select('div_name')->groupBy('div_name')->orderBy('div_name')->get();

        $DiscountMargin = DiscountMarginMaster::pluck('discount_margin', 'item_code')->toArray();
        $data['DiscountMargin_datas'] = $DiscountMargin;
        $data['vq_data']=$vq;
        //$data['details']=$details;
        $data['stockists']=$stockists;
        $data['stockist_margin']=$stockist_margin->meta_value;
        $data['division_name']=$division_name;
        Session::forget('stockist_id_arr');//added on 02072024 to unset the recently added stockist
        
        $allSkus = VoluntaryQuotationSkuListing::where('vq_id', $id)->get();
        $data['setFlagDiscard'] = $allSkus->every(function ($row) {
            return $row->is_deleted == 1 && $row->is_discarded == 1;
        });
        
        return view('frontend.Initiator.details',compact('data'));
    }

    public function pocDetails($id){
        $vq=VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $details = VoluntaryQuotationSkuListing::with('getSkuStockist.getStockistDetails')->where('vq_id',$id)
        ->select('voluntary_quotation_sku_listing.*', DB::raw('COALESCE(discount_margin_master.discount_margin, 10) as discount_margin'))
        ->leftJoin('discount_margin_master','discount_margin_master.item_code','=','voluntary_quotation_sku_listing.item_code')->where('is_deleted',0)->get();
        $stockists = Stockist_master::where('institution_code', data_get($id, 'institution_id'))->get();
        $stockist_margin = Config::where('meta_key', 'stockist_margin')->first();
        $DiscountMargin = DiscountMarginMaster::pluck('discount_margin', 'item_code')->toArray();
        $data['DiscountMargin_datas'] = $DiscountMargin;
        $data['vq_data']=$vq;
        $data['details']=$details;
        $data['stockists']=$stockists;
        $data['stockist_margin']=$stockist_margin->meta_value;
        return view('frontend.Poc.details',compact('data'));
    }

    public function distributionDetails($id){
        $vq=VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        //$details = VoluntaryQuotationSkuListing::with('getSkuStockist.getStockistDetails')->where('vq_id',$id)->where('is_deleted',0)->get();//commented on 19032024 for serverside dt
        $stockists = Stockist_master::where('institution_code', data_get($id, 'institution_id'))->get();
        $stockist_margin = Config::where('meta_key', 'stockist_margin')->first();
        $DiscountMargin = DiscountMarginMaster::pluck('discount_margin', 'item_code')->toArray();
        $data['DiscountMargin_datas'] = $DiscountMargin;
        $data['vq_data']=$vq;
        //$data['details']=$details;//commented on 19032024 for serverside dt
        $data['stockists']=$stockists;
        $data['stockist_margin']=$stockist_margin->meta_value;
        return view('frontend.Distribution.details',compact('data'));
    }

    public function hoDetails($id){
        $vq=VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        //$details = VoluntaryQuotationSkuListing::with('getSkuStockist.getStockistDetails')->where('vq_id',$id)->where('is_deleted',0)->get();//commented for serverside datatable 19032024
        $stockists = Stockist_master::where('institution_code', data_get($id, 'institution_id'))->get();
        $stockist_margin = Config::where('meta_key', 'stockist_margin')->first();
        $DiscountMargin = DiscountMarginMaster::pluck('discount_margin', 'item_code')->toArray();
        $data['DiscountMargin_datas'] = $DiscountMargin;
        $data['vq_data']=$vq;
        //$data['details']=$details;//commented for serverside datatable 19032024
        $data['stockists']=$stockists;
        $data['stockist_margin']=$stockist_margin->meta_value;
        return view('frontend.Ho.details',compact('data'));
    }

    public function userDetails($id){
        $vq=VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $details = VoluntaryQuotationSkuListing::with('getSkuStockist.getStockistDetails')->where('vq_id',$id)->where('is_deleted',0)->get();
        $stockists = Stockist_master::where('institution_code', data_get($id, 'institution_id'))->get();
        $stockist_margin = Config::where('meta_key', 'stockist_margin')->first();
        $DiscountMargin = DiscountMarginMaster::pluck('discount_margin', 'item_code')->toArray();
        $data['DiscountMargin_datas'] = $DiscountMargin;
        $data['vq_data']=$vq;
        $data['details']=$details;
        $data['stockists']=$stockists;
        $data['stockist_margin']=$stockist_margin->meta_value;
        return view('frontend.User.details',compact('data'));
    }


	public function reinitiate_listing($id,Request $request){
        $inputData = $request->all();
    	if(!array_key_exists('institutes', $inputData)){
    		return redirect()->back()->withErrors([
    			'message' => "Please select atleast one Institution."
    		]);
    	}

        $institute_count = count($inputData['institutes']);
        $inputData['institutes'] = json_encode($inputData['institutes']);
        $inputData['select_approval'] = data_get($inputData, 'select_approval', null) ? json_encode(data_get($inputData, 'select_approval', null)) : json_encode(array("7"));
        
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
        
        $body = '{
        
        }';
        
        $r = $client->request('POST', env('API_URL').'/api/Products', [
            'body' => $body
        ]);
        $response = $r->getBody()->getContents();
        
        // $data = json_decode($res->getBody());
        $data = json_decode($response);
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $divisionNames = array_column($data, 'DIVISION_NAME');
        $uniqueDivisionNames = array_unique($divisionNames);
        sort($uniqueDivisionNames);
        // added for checking discount item prevent reinitiate flag starts
        $checkDiscountFlagEnabled = DB::table('check_discounted')->select('is_enabled')->where('year',$year)->where('is_enabled','Y')->get();
        //if ($checkDiscountFlagEnabled->count() != 0) {
            $item_codes = [];
            $division_codes = [];
            foreach ($data as $single_data) {
                $item_codes[] = $single_data->ITEM_CODE;
                $division_codes[] = $single_data->DIVISION_CODE;
            }

            // Remove duplicates
            $item_codes = array_unique($item_codes);
            $division_codes = array_unique($division_codes);
        //}
        // added for checking discount item prevent reinitiate flag ends
        if($institute_count == 1){
            $id = json_decode($inputData['institutes'])[0];
            $voluntary_quotations = VoluntaryQuotationSkuListing::select('item_code', 'voluntary_quotation.id', 'voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.item_code as sku_item_code')
                ->leftJoin('voluntary_quotation', 'voluntary_quotation_sku_listing.vq_id', '=', 'voluntary_quotation.id')
                ->whereIn('voluntary_quotation_sku_listing.item_code', $item_codes)
                ->where('year', $year)
                ->where('voluntary_quotation.is_deleted', 0)
                ->where('voluntary_quotation.institution_id', $id)
                ->where('voluntary_quotation_sku_listing.is_deleted', 0)->groupBy('item_code')
                ->get();
            // Prepare the flag lookup
            $discounted_flag_lookup = [];
            foreach ($voluntary_quotations as $vq) {
                $key = $vq->sku_item_code;
                $discounted_flag_lookup[$key] = true;
            }
            //$vq_listing = VoluntaryQuotation::where('institution_id',$id)->where('year',$year)->where('parent_vq_id',0)->where('is_deleted', 0)->first();
            $vq_listing = VoluntaryQuotation::query()
                ->select('voluntary_quotation.*')
                ->leftJoin('voluntary_quotation_sku_listing AS s', 'voluntary_quotation.id', '=', 's.vq_id')
                ->where('voluntary_quotation.rev_no', function ($query) use ($year, $id){
                    $query->select(DB::raw('MAX(v.rev_no)'))
                          ->from('voluntary_quotation AS v')
                          ->where('v.year', $year)
                          ->where('v.vq_status', 1)
                          ->where('v.institution_id',$id)
                          ->where('v.is_deleted', 0);
                })
                ->where('institution_id',$id)->where('year', $year)->where('vq_status', 1)->where('voluntary_quotation.is_deleted', 0)->first();//added to fetch the revisied vq and added year,vqstatus,isdeleted on 08052024
            if (!$vq_listing) {//added on 17052024
               $vq_listing = VoluntaryQuotation::where('institution_id',$id)->where('year',$year)->where('parent_vq_id',0)->where('is_deleted', 0)->first();
            } 
            $sku_count = VoluntaryQuotationSkuListing::where('vq_id',$vq_listing->id)->count();
            // dd($data);
            if(count($data) != $sku_count){
                $listing_data = array();
                /*$checkers = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.div_id')
                ->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
                ->whereRaw('QUARTER(voluntary_quotation.created_at) = QUARTER(now())')
                ->where('voluntary_quotation.parent_vq_id',$vq_listing->id)->distinct()->get();*///commented on 22042024 notused 
                //$last_year_data_main = LastYearPrice::where('institution_id',$vq_listing->institution_id)->where('year',$year)->get();
                //$old_data_main = VoluntaryQuotationSkuListing::where('vq_id',$vq_listing->id)->get();//commented for issue
                //$ceiling_data_main = ceilingMaster::get();//commented for issue
                $maxRevSubquery = DB::table('voluntary_quotation_sku_listing as s')
                ->select(DB::raw('MAX(v2.rev_no) AS max_rev_no'), 's.item_code', 'v2.institution_id')
                ->leftJoin('voluntary_quotation as v2', 'v2.id', '=', 's.vq_id')
                ->where('v2.year', $year)
                ->where('s.is_deleted', 0)
                ->where('v2.vq_status', 1)
                ->where('v2.is_deleted', 0)
                ->where('v2.institution_id', $id)
                ->groupBy('s.item_code');

                $latestdata = DB::table('voluntary_quotation_sku_listing as vqsl')
                ->select('vqsl.*', 'vq.*')
                ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
                ->joinSub($maxRevSubquery, 'max_rev', function ($join) use($id) {
                    $join->on('vqsl.item_code', '=', 'max_rev.item_code')
                        ->where('vq.institution_id',  $id)
                        ->on('vq.rev_no', '=', 'max_rev.max_rev_no');
                })
                ->where('vq.institution_id',  $id)
                ->where('vq.year', $year)
                ->where('vq.vq_status', 1)
                ->where('vq.is_deleted', 0)
                ->where('vqsl.is_deleted', 0)
                ->get();

                $latestdata = collect($latestdata);
                foreach($data as $single_data){
                    $flag=0;
                    // foreach($checkers as $checker){
                    //     if($checker['div_id'] == $single_data->DIVISION_CODE){
                    //         $flag=1;
                    //         break;
                    //     }
                    // }
                    if($flag == 0){
                        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
                        $time_minus = strtotime("-1 year", time());
                        $date_minus = date("Y-m-d", $time_minus);
                        $last_year = $this->getFinancialYear($date_minus,"Y");
                        //$last_year_data = $last_year_data_main->where('division_id',$single_data->DIVISION_CODE)->first();
                        $last_year_data = LastYearPrice::where('institution_id',$vq_listing->institution_id)->where('division_id',$single_data->DIVISION_CODE)->where('year',$last_year)->where('sku_id',$single_data->ITEM_CODE)->first();
                        //$old_data = VoluntaryQuotationSkuListing::where('vq_id',$vq_listing->id)->where('item_code',$single_data->ITEM_CODE)->first();//commented on 19042024 for latest discout ptr issue
                        //added on 19042024 for latest discount ptr fetch start
                        $single_data_item_code = $single_data->ITEM_CODE;
                        /*$old_data = VoluntaryQuotationSkuListing::select('discount_percent','discount_rate','mrp_margin')->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation.institution_id',$id)->where('voluntary_quotation_sku_listing.item_code',$single_data->ITEM_CODE)->where('year',$year)*/
                       /* ->where('voluntary_quotation.rev_no', function ($query) use ($year, $id, $single_data_item_code){
                        $query->select(DB::raw('MAX(v.rev_no)'))
                              ->from('voluntary_quotation AS v')
                              ->leftJoin('voluntary_quotation_sku_listing AS s', 'v.id', '=', 's.vq_id')
                              ->where('v.year', $year)
                              ->where('v.vq_status', 1)
                              ->where('v.institution_id',$id)
                              ->where('s.item_code',$single_data_item_code)
                              ->where('v.is_deleted', 0);
                        })*/
                        /*->join('z_max_rev AS max_rev', function ($join) {
                            $join->on('max_rev.item_code', '=', 'voluntary_quotation_sku_listing.item_code')
                                 //->on('max_rev.institution_id', '=', 'voluntary_quotation.institution_id')
                                 ->on('max_rev.max_rev_no', '=', 'voluntary_quotation.rev_no');
                        })->where('max_rev.institution_id',   $id)
                        ->first();*/
                        $old_data =  $latestdata->firstWhere('item_code', $single_data_item_code);
                        //$old_data = DB::table('z_latest_rev_ptr')->select('discount_percent','discount_rate','mrp_margin')->where('institution_id',$id)->where('item_code',$single_data_item_code)->first();
                        //added on 19042024 for latest discount ptr fetch end
                        if(is_null($old_data)){
                            //$ceiling_data = ceilingMaster::where('sku_id',$single_data->ITEM_CODE)->first();
                            $ceiling_data = null;
                            if(!is_null($ceiling_data)){
                                $dis_per = $ceiling_data->discount_percent;
                                $mid = ($single_data->PTR / 100) * $dis_per;
                                $dis_rate = $single_data->PTR - $mid;
                            }else{
                                $dis_per = 0;
                                $dis_rate = $single_data->PTR;
                            }

                            $mrp_margin = ((($single_data->MRP -$single_data->PTR)/$single_data->MRP)*100 );

                        }else{
                            $dis_per = $old_data->discount_percent;
                            $dis_rate = $old_data->discount_rate;
                            $mrp_margin = $old_data->mrp_margin;
                        }
                        if(!is_null($last_year_data)){
                            $last_year_percent = $last_year_data->discount_percent;
                            $mid = ($single_data->PTR / 100) * $last_year_percent;
                            $last_year_rate = $single_data->PTR - $mid;
                            $last_year_mrp = $last_year_data->mrp;
                        }else{
                            $last_year_percent = NULL;
                            $last_year_rate = NULL;
                            $last_year_mrp = NULL;
                        }
                        $key = $single_data->ITEM_CODE;
                        /*$newProdCheck = VoluntaryQuotationSkuListing::leftJoin('voluntary_quotation','voluntary_quotation.id','=','voluntary_quotation_sku_listing.vq_id')->where('item_code',$key)->where('year',$year)->where('institution_id',$id)->where('voluntary_quotation_sku_listing.is_deleted',0)->where('voluntary_quotation.is_deleted',0)->exists();*/
                        if (isset($discounted_flag_lookup[$key])) {
                            $product_type = 'old';
                        }
                        else
                        {
                            $product_type = 'new';
                        }
                        /*if ($newProdCheck) {
                            $product_type = 'old';
                        }
                        else
                        {
                            $product_type = 'new';
                        }*/
                        // added for checking discount item prevent reinitiate flag starts
                        if ($checkDiscountFlagEnabled->count() != 0) {
                            $key = $single_data->ITEM_CODE;
                            //if (isset($discounted_flag_lookup[$key])) {
                            if ($product_type == 'old') {
                                $discounted_flag = true;

                                // Check for exception item
                                $check_exception_item = DB::table('exception_sku_list_reinitiate')
                                    ->where('item_code', $single_data->ITEM_CODE)
                                    ->where('div_id', $single_data->DIVISION_CODE)
                                    ->where('year', $year)
                                    ->where('is_deleted', 0)
                                    ->exists();
                                if ($check_exception_item) {
                                    $discounted_flag = false;
                                }
                            } else {
                                $discounted_flag = false;
                            }
                        } else {
                            $discounted_flag = false;
                        }
                        // added for checking discount item prevent reinitiate flag ends
                        $listing_data[]=[
                            'item_code' => $single_data->ITEM_CODE,
                            'brand_name' => $single_data->BRAND_NAME,
                            'mother_brand_name' => $single_data->MOTHER_BRAND_NAME,
                            'hsn_code' => $single_data->HSN_CODE,
                            'applicable_gst' => $single_data->APPLICABLE_GST,
                            'composition' => $single_data->COMPOSITION,
                            'type' => $single_data->ITEM_TYPE,
                            'div_name' => $single_data->DIVISION_NAME,
                            'div_id' => $single_data->DIVISION_CODE,
                            'pack' => $single_data->PACK_SIZE,
                            'ptr' => $single_data->PTR,
                            'last_year_percent' => $last_year_percent,
                            'last_year_rate' => $last_year_rate,
                            'last_year_mrp' => $last_year_mrp,
                            'discount_percent' => $dis_per,
                            'discount_rate' => $dis_rate,
                            'mrp' => $single_data->MRP,
                            'mrp_margin'=> $mrp_margin,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                            'discounted_flag' => $discounted_flag,
                            'product_type' => $product_type
                        ];
                    }
                }
                //$final_data = json_decode(json_encode(array_merge ($old_data, $listing_data)),false);
                $final_data = json_decode(json_encode($listing_data),false);
            }else{
                $data1 = VoluntaryQuotationSkuListing::where('vq_id',$vq_listing->id)->get();
                $listing_data = array();
                foreach($data1 as $single_data){
                    /*$checkers = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.div_id')
                    ->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
                    ->whereRaw('QUARTER(voluntary_quotation.created_at) = QUARTER(now())')
                    ->where('voluntary_quotation.parent_vq_id',$vq_listing->id)->distinct()->get();*/
                    
                    $flag=0;
                    // foreach($checkers as $checker){
                    //     if($checker['div_id'] == $single_data->div_id){
                    //         $flag=1;
                    //         break;
                    //     }
                    // }
                    // added for checking discount item prevent reinitiate flag starts
                    if ($checkDiscountFlagEnabled->count() != 0) {
                        //$key = $single_data->item_code . '-' . $single_data->div_id;
                        $key = $single_data->item_code;
                        if (isset($discounted_flag_lookup[$key])) {
                            $discounted_flag = true;

                            // Check for exception item
                            $check_exception_item = DB::table('exception_sku_list_reinitiate')
                                ->where('item_code', $single_data->item_code)
                                ->where('div_id', $single_data->div_id)
                                ->where('year', $year)
                                ->where('is_deleted', 0)
                                ->exists();
                            if ($check_exception_item) {
                                $discounted_flag = false;
                            }
                        } else {
                            $discounted_flag = false;
                        }
                    } else {
                        $discounted_flag = false;
                    }
                    // added for checking discount item prevent reinitiate flag ends
                    $single_data->discounted_flag = $discounted_flag;
                    $single_data->product_type = 'old';
                    
                    if($flag == 0){
                        $listing_data[]=$single_data;
                    }
                }
                //$final_data = json_decode(json_encode(array_merge ($old_data, $listing_data)),false);
                $final_data = json_decode(json_encode($listing_data),false);
            }
            //$uniqueDivisionNames = VoluntaryQuotationSkuListing::select('div_name')->groupBy('div_name')->orderBy('div_name')->get();
            return view('frontend.Initiator.reinitiate',compact('final_data','vq_listing', 'inputData', 'uniqueDivisionNames'));
        }
        else{
            /*$voluntary_quotations = VoluntaryQuotationSkuListing::select('item_code', 'voluntary_quotation.id', 'voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.item_code as sku_item_code')
                ->leftJoin('voluntary_quotation', 'voluntary_quotation_sku_listing.vq_id', '=', 'voluntary_quotation.id')
                ->whereIn('voluntary_quotation_sku_listing.item_code', $item_codes)
                ->where('year', $year)
                ->where('voluntary_quotation.is_deleted', 0)
                ->where('voluntary_quotation_sku_listing.is_deleted', 0)->groupBy('item_code')
                ->get();
            // Prepare the flag lookup
            $discounted_flag_lookup = [];
            foreach ($voluntary_quotations as $vq) {
                $key = $vq->sku_item_code;
                $discounted_flag_lookup[$key] = true;
            }*/
            $listing_data = array();
            $institutions_from_input = json_decode($inputData['institutes']);
            DB::statement("SET SESSION group_concat_max_len = 1000000");
            // $existing_institution_data = DB::table('voluntary_quotation_sku_listing')
            //     ->select('voluntary_quotation_sku_listing.item_code', DB::raw('GROUP_CONCAT(voluntary_quotation.institution_id) AS institution_ids'))
            //     ->leftJoin('voluntary_quotation', 'voluntary_quotation_sku_listing.vq_id', '=', 'voluntary_quotation.id')
            //     ->whereIn('voluntary_quotation_sku_listing.item_code', $item_codes)
            //     ->whereIn('voluntary_quotation.institution_id', $institutions_from_input)
            //     ->where('year', $year)
            //     ->where('voluntary_quotation.is_deleted', 0)
            //     ->where('voluntary_quotation_sku_listing.is_deleted', 0)
            //     ->groupBy('voluntary_quotation_sku_listing.item_code')
            //     ->get();
            // $existing_institution_data_array = [];
            // foreach ($existing_institution_data as $row) {
            //     $item_code = $row->item_code;
            //     $institution_ids = explode(',', $row->institution_ids);
            //     $existing_institution_data_array[$item_code] = $institution_ids;
            // }
            // foreach($data as $single_data){
                
                
            //     $dis_per = 0;
            //     $mid = ($single_data->PTR / 100) * $dis_per;
            //     $dis_rate = $single_data->PTR - $mid;
            //     $last_year_percent = NULL;
            //     $last_year_rate = NULL;
            //     $last_year_mrp = NULL;
            //     $mrp_margin = ((($single_data->MRP -$single_data->PTR)/$single_data->MRP)*100 );
            //     $item_code = $single_data->ITEM_CODE;
            //     $existing_institutions = $existing_institution_data_array[$item_code] ?? [];
            //     $missing_institutions = array_diff($institutions_from_input, $existing_institutions);

            //     if (!empty($missing_institutions)) {
            //         // The item is new for these missing institutions
            //         $product_type = 'new';
            //         if ($checkDiscountFlagEnabled->count() != 0) {
            //             // Check for exception item
            //             $check_exception_item = DB::table('exception_sku_list_reinitiate')
            //                 ->where('item_code', $single_data->ITEM_CODE)
            //                 ->where('div_id', $single_data->DIVISION_CODE)
            //                 ->where('year', $year)
            //                 ->where('is_deleted', 0)
            //                 ->exists();
            //             if ($check_exception_item) {
            //                 $discounted_flag = false;
            //             }
            //             else
            //             {
            //                 $discounted_flag = false;
            //             }
            //         }else {
            //             $discounted_flag = false;
            //         }
            //     } else {
            //         // The item already exists in all institutions
            //         $product_type = 'old';
            //         if ($checkDiscountFlagEnabled->count() != 0) {
            //             // Check for exception item
            //             $check_exception_item = DB::table('exception_sku_list_reinitiate')
            //                 ->where('item_code', $single_data->ITEM_CODE)
            //                 ->where('div_id', $single_data->DIVISION_CODE)
            //                 ->where('year', $year)
            //                 ->where('is_deleted', 0)
            //                 ->exists();
            //             if ($check_exception_item) {
            //                 $discounted_flag = false;
            //             }
            //             else
            //             {
            //                 $discounted_flag = true;
            //             }
            //         }else {
            //             $discounted_flag = false;
            //         }
            //     }
            //     // added for checking discount item prevent reinitiate flag starts
            //     /*if ($checkDiscountFlagEnabled->count() != 0) {
            //         //$key = $single_data->ITEM_CODE . '-' . $single_data->DIVISION_CODE;
            //         $key = $single_data->ITEM_CODE;
            //         if (isset($discounted_flag_lookup[$key])) {
            //             $discounted_flag = true;

            //             // Check for exception item
            //             $check_exception_item = DB::table('exception_sku_list_reinitiate')
            //                 ->where('item_code', $single_data->ITEM_CODE)
            //                 ->where('div_id', $single_data->DIVISION_CODE)
            //                 ->where('year', $year)
            //                 ->where('is_deleted', 0)
            //                 ->exists();
            //             if ($check_exception_item) {
            //                 $discounted_flag = false;
            //             }
            //         } else {
            //             $discounted_flag = false;
            //         }
            //     } else {
            //         $discounted_flag = false;
            //     }
            //     // added for checking discount item prevent reinitiate flag ends
            //     $key = $single_data->ITEM_CODE;
            //     if (isset($discounted_flag_lookup[$key])) {
            //         $product_type = 'old';
            //     }
            //     else
            //     {
            //         $product_type = 'new';
            //     }*/
            //     $listing_data[]=[
            //         'item_code' => $single_data->ITEM_CODE,
            //         'brand_name' => $single_data->BRAND_NAME,
            //         'mother_brand_name' => $single_data->MOTHER_BRAND_NAME,
            //         'hsn_code' => $single_data->HSN_CODE,
            //         'applicable_gst' => $single_data->APPLICABLE_GST,
            //         'composition' => $single_data->COMPOSITION,
            //         'type' => $single_data->ITEM_TYPE,
            //         'div_name' => $single_data->DIVISION_NAME,
            //         'div_id' => $single_data->DIVISION_CODE,
            //         'pack' => $single_data->PACK_SIZE,
            //         'ptr' => $single_data->PTR,
            //         'last_year_percent' => $last_year_percent,
            //         'last_year_rate' => $last_year_rate,
            //         'last_year_mrp' =>$last_year_mrp,
            //         'discount_percent' => $dis_per,
            //         'discount_rate' => $dis_rate,
            //         'mrp' => $single_data->MRP,
            //         'mrp_margin'=> $mrp_margin,
            //         'created_at' => date('Y-m-d H:i:s'),
            //         'updated_at' => date('Y-m-d H:i:s'),
            //         'discounted_flag' => $discounted_flag,
            //         'product_type' => $product_type
            //     ];
            // }

            /** added by arunchandru at 24-12-2024 for Optimized query, code, array and forloop */
            $existing_institution_data_array = DB::table('voluntary_quotation_sku_listing')
                ->select('voluntary_quotation_sku_listing.item_code', DB::raw('GROUP_CONCAT(voluntary_quotation.institution_id) AS institution_ids'))
                ->leftJoin('voluntary_quotation', 'voluntary_quotation_sku_listing.vq_id', '=', 'voluntary_quotation.id')
                ->whereIn('voluntary_quotation_sku_listing.item_code', $item_codes)
                ->whereIn('voluntary_quotation.institution_id', $institutions_from_input)
                ->where('year', $year)
                ->where('voluntary_quotation.is_deleted', 0)
                ->where('voluntary_quotation_sku_listing.is_deleted', 0)
                ->groupBy('voluntary_quotation_sku_listing.item_code')
                // ->get();
                ->pluck('institution_ids', 'item_code') // Fetch as key-value pairs
                ->toArray();

            // Step 2: Prepare data for efficient processing
            foreach ($existing_institution_data_array as $item_code => $institution_ids) {
                $existing_institution_data_array[$item_code] = explode(',', $institution_ids);
            }

            // Step 3: Prepare flag lookup data (if any)
            $exception_items = DB::table('exception_sku_list_reinitiate')
            ->whereIn('item_code', array_column($data, 'ITEM_CODE'))
            ->whereIn('div_id', array_column($data, 'DIVISION_CODE'))
            ->where('year', $year)
            ->where('is_deleted', 0)
            ->pluck('item_code')
            ->toArray();
            // ->flip(); // Flip for quick lookup

            // Step 4: Process the data in batches
            $listing_data = [];
            $current_time = date('Y-m-d H:i:s');

            foreach ($data as $single_data) {
                $item_code = $single_data->ITEM_CODE;
                $division_code = $single_data->DIVISION_CODE;

                // Fetch existing and missing institutions
                $existing_institutions = $existing_institution_data_array[$item_code] ?? [];
                $missing_institutions = array_diff($institutions_from_input, $existing_institutions);

                if (!empty($missing_institutions)) {
                    // The item is new for these missing institutions
                    $product_type = 'new';
                    if ($checkDiscountFlagEnabled->count() != 0) {
                        // Check for exception item
                        // $check_exception_item = DB::table('exception_sku_list_reinitiate')
                        //     ->where('item_code', $single_data->ITEM_CODE)
                        //     ->where('div_id', $single_data->DIVISION_CODE)
                        //     ->where('year', $year)
                        //     ->where('is_deleted', 0)
                        //     ->exists();
                        if (in_array($item_code, $exception_items)) {
                            $discounted_flag = false;
                        }
                        else
                        {
                            $discounted_flag = false;
                        }
                    }else {
                        $discounted_flag = false;
                    }
                } else {
                    // The item already exists in all institutions
                    $product_type = 'old';
                    if ($checkDiscountFlagEnabled->count() != 0) {
                        // Check for exception item
                        // $check_exception_item = DB::table('exception_sku_list_reinitiate')
                        //     ->where('item_code', $single_data->ITEM_CODE)
                        //     ->where('div_id', $single_data->DIVISION_CODE)
                        //     ->where('year', $year)
                        //     ->where('is_deleted', 0)
                        //     ->exists();
                        if (in_array($item_code, $exception_items)) {
                            $discounted_flag = false;
                        }
                        else
                        {
                            $discounted_flag = true;
                        }
                    }else {
                        $discounted_flag = false;
                    }
                } 

                // Calculate values
                $dis_per = 0;
                $dis_rate = $single_data->PTR - (($single_data->PTR / 100) * $dis_per);
                $mrp_margin = (($single_data->MRP - $single_data->PTR) / $single_data->MRP) * 100;

                // Prepare listing data
                $listing_data[] = [
                    'item_code' => $single_data->ITEM_CODE,
                    'brand_name' => $single_data->BRAND_NAME,
                    'mother_brand_name' => $single_data->MOTHER_BRAND_NAME,
                    'hsn_code' => $single_data->HSN_CODE,
                    'applicable_gst' => $single_data->APPLICABLE_GST,
                    'composition' => $single_data->COMPOSITION,
                    'type' => $single_data->ITEM_TYPE,
                    'div_name' => $single_data->DIVISION_NAME,
                    'div_id' => $single_data->DIVISION_CODE,
                    'pack' => $single_data->PACK_SIZE,
                    'ptr' => $single_data->PTR,
                    'last_year_percent' => null,
                    'last_year_rate' => null,
                    'last_year_mrp' => null,
                    'discount_percent' => $dis_per,
                    'discount_rate' => $dis_rate,
                    'mrp' => $single_data->MRP,
                    'mrp_margin' => $mrp_margin,
                    'created_at' => $current_time,
                    'updated_at' => $current_time,
                    'discounted_flag' => $discounted_flag,
                    'product_type' => $product_type,
                ];
            }
         
            //$final_data = json_decode(json_encode(array_merge ($old_data, $listing_data)),false);
            $final_data = json_decode(json_encode($listing_data),false);
            //$uniqueDivisionNames = VoluntaryQuotationSkuListing::select('div_name')->groupBy('div_name')->orderBy('div_name')->get();
            return view('frontend.Initiator.reinitiate',compact('final_data', 'inputData', 'uniqueDivisionNames'));

        }
    }
    

    public function manageSku(Request $request){
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $level =strTolower(Session::get('level'));
        $level_no = 1;
        switch($level){
            case 'l1': $level_no = 1;break;
            case 'l2': $level_no = 2;break;
            case 'l3': $level_no = 3;break;
            case 'l4': $level_no = 4;break;
            case 'l5': $level_no = 5;break;
            case 'l6': $level_no = 6;break;
            case 'l8': $level_no = 8;break;//added ceo level idap-33
        }
        if(preg_replace('/[^0-9.]+/', '', Session::get("level"))>2){
            //$institute = VoluntaryQuotation::join('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation_sku_listing.'.$level.'_status',0)->where('voluntary_quotation.current_level', '>=', $level_no)->select('voluntary_quotation.id','hospital_name')->where('year',$year)->where('voluntary_quotation.is_deleted', 0)->distinct()->get();//commented on 29042024 for fetching the insitution items from vq table
           $institute = VoluntaryQuotation::select('institution_id', 'hospital_name')->where('year', $year)->groupBy('institution_id', 'hospital_name')->orderBy('hospital_name')->get();//added on 29042024 for fetching the insitution items from vq table
            $brand = DB::table('brands')->select('brand_name')->whereIn('div_id',explode(',',Session::get("division_id")))->orderBy('brand_name','ASC')->get();//changed to group by instead of distinct on 08052024
        }else{
           // $institute = VoluntaryQuotation::join('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation_sku_listing.'.$level.'_status',0)->where('voluntary_quotation.current_level', '<=', $level_no)->select('voluntary_quotation.id','hospital_name')->where('year',$year)->where('voluntary_quotation.is_deleted', 0)->distinct()->get();
            /*$institute = VoluntaryQuotation::select('voluntary_quotation.*','voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status as status_vq', 'voluntary_quotation_sku_listing.deleted_by as deleted_by', \DB::raw('(CASE  WHEN voluntary_quotation.parent_vq_id != "0" THEN (SELECT COUNT(*) FROM voluntary_quotation as vq1 where vq1.parent_vq_id = voluntary_quotation.parent_vq_id AND vq1.id <= voluntary_quotation.id) ELSE "0"  END) AS revision_count'))
            ->where('current_level','>=',preg_replace('/[^0-9.]+/', '', Session::get("level")))->where('year',$year)
            ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            ->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','voluntary_quotation.id')
            ->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))
            ->where('voluntary_quotation_sku_listing.is_deleted','==',0)
            ->where('voluntary_quotation.is_deleted', 0)
            ->where('institution_division_mapping.employee_code',Session::get("emp_code"))
            ->distinct()->get();*///commented on 29042024 for fetching the insitution items from vq table
            $institute = VoluntaryQuotation::select('institution_id', 'hospital_name')->where('year', $year)->groupBy('institution_id', 'hospital_name')->orderBy('hospital_name')->get();//added on 29042024 for fetching the insitution items from vq table
            //$brand = VoluntaryQuotationSkuListing::select('brand_name')->whereIn('div_id',explode(',',Session::get("division_id")))->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','voluntary_quotation_sku_listing.vq_id')->where('voluntary_quotation_sku_listing.is_deleted','!=',1)->where('institution_division_mapping.employee_code',Session::get("emp_code"))->groupBy('brand_name')->orderBy('brand_name','ASC')->get();//changed to group by instead of distinct on 08052024
            $brand = DB::table('brands')->select('brand_name')->whereIn('div_id',explode(',',Session::get("division_id")))->orderBy('brand_name','ASC')->get();
        }
       

        
        return view('frontend.Approver.manage_sku',compact('institute','brand'));

    }

    public function listBaseInstitute(Request $request){
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $level = Session::get('level');
        $level_no = 1;
        switch($level){
            case 'L1': $level_no = 1;break;
            case 'L2': $level_no = 2;break;
            case 'L3': $level_no = 3;break;
            case 'L4': $level_no = 4;break;
            case 'L5': $level_no = 5;break;
            case 'L6': $level_no = 6;break;
            case 'L8': $level_no = 8;break;//added ceo level idap-33
        }

        if($request['btnValue']=='All institution'){
            if(preg_replace('/[^0-9.]+/', '', Session::get("level"))>2){

                $data = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.*','voluntary_quotation.hospital_name','voluntary_quotation.current_level')->join('voluntary_quotation','voluntary_quotation.id','=','voluntary_quotation_sku_listing.vq_id')
                //->where('voluntary_quotation.current_level', '>=', $level_no)//commented on 29042024
                ->where('voluntary_quotation.current_level', '=', $level_no)//added for selecting only vq pending at user level
                ->whereIn('brand_name',$request['brandName'])->where('voluntary_quotation.is_deleted', 0)->where('year',$year)->where('voluntary_quotation_sku_listing.is_deleted',0)->distinct()->get();
            }else{
                $data = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.*','voluntary_quotation.hospital_name','voluntary_quotation.current_level')->join('voluntary_quotation','voluntary_quotation.id','=','voluntary_quotation_sku_listing.vq_id')
                 ->where('voluntary_quotation.current_level', '=', $level_no)//added for selecting only vq pending at user level
                ->whereIn('voluntary_quotation_sku_listing.brand_name',$request['brandName'])->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','voluntary_quotation_sku_listing.vq_id')->where('voluntary_quotation_sku_listing.is_deleted','==',0)->where('institution_division_mapping.employee_code',Session::get("emp_code"))->where('voluntary_quotation.is_deleted', 0)->where('year',$year)->distinct()->get();
            }
            
            return $data;
        }else{
            $data2 = implode(',',$request['institute']);
            $data3 = explode(',',$data2);
            /*$data = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.*','voluntary_quotation.hospital_name','voluntary_quotation.current_level')->join('voluntary_quotation','voluntary_quotation.id','=','voluntary_quotation_sku_listing.vq_id')
            ->whereIn('vq_id',$data3)->where('brand_name',$request['brandName'])->where('year',$year)->get();*/
             $data = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.*','voluntary_quotation.hospital_name','voluntary_quotation.current_level')->join('voluntary_quotation','voluntary_quotation.id','=','voluntary_quotation_sku_listing.vq_id')->where('voluntary_quotation.current_level', '=', $level_no)->whereIn('brand_name',$request['brandName'])->where('voluntary_quotation.is_deleted', 0)->where('year',$year)->where('voluntary_quotation_sku_listing.is_deleted',0)->whereIn('institution_id',$data3)->get();
            return $data;
        }
    }
    public function getDiscountMarginViewLogData(Request $request)
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
        $columns = ['id', 'item_code', 'created_at', 'employee_master.emp_code', 'id'];
       
        $details = ActivityTracker::select(
            'activity_trackers.*',
            'employee_master.emp_code',
            'employee_master.emp_name',
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.fin_year")) as fin_year'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.ip_address")) as ip_address'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.user_agent")) as user_agent'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.changed_at")) as changed_at'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.item_code")) as item_code'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.changed_to")) as changed_to'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.brand_name")) as brand_name'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.sap_itemcode")) as sap_itemcode'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.changed_discount_margin")) as changed_discount_margin'),
            DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.pervious_discount_margin")) as pervious_discount_margin')
        )
        ->leftJoin('employee_master', 'employee_master.emp_code', '=', 'activity_trackers.emp_code')
        ->where('type', 'update_discount_margin')
        ->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.fin_year")) = ?', [$year]);
        
        // Apply common search filter
        if (!empty($common_search)) {
            $details->where(function ($q) use ($common_search) {
                $q->where('employee_master.emp_code', 'like', "%$common_search%")
                ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.fin_year")) LIKE ?', ["%$common_search%"])
                ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.changed_at")) LIKE ?', ["%$common_search%"])
                ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.item_code")) LIKE ?', ["%$common_search%"])
                ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.changed_to")) LIKE ?', ["%$common_search%"])
                ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.brand_name")) LIKE ?', ["%$common_search%"])
                ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.sap_itemcode")) LIKE ?', ["%$common_search%"]);
            });
        }

        // Filter by brand name
        if (isset($request->brand_name_filter)) {
            $item_code = $request->brand_name_filter;

            if (is_array($item_code)) {
                $placeholders = implode(',', array_fill(0, count($item_code), '?'));
                $details->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(activity, '$.item_code')) IN ($placeholders)", $item_code);
            } else {
                $details->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(activity, "$.item_code")) = ?', [$item_code]);
            }
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
        ->leftJoin('discount_margin_master', 'discount_margin_master.item_code', '=', DB::raw('JSON_UNQUOTE(JSON_EXTRACT(activity_trackers.activity, "$.item_code"))')) // join required
        ->where('activity_trackers.type', 'update_discount_margin')
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

    public function getVQdetaillistData(Request $request)
    {
        
        $vq_id = $request->vq_id;
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $common_search = $request->input('search.value');
        $orderColumnIndex = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir');
        $columns = ['div_name', 'vq_id', 'mother_brand_name', 'item_code', 'brand_name','discount_percent', 'discount_rate', 'vq_id', 'vq_id', 'discount_margin', 'vq_id', 'pack','applicable_gst', 'last_year_percent', 'last_year_rate', 'mrp','last_year_mrp', 'ptr', 'mrp_margin', 'discount_rate', 'discount_rate','vq_id', 'vq_id','type','hsn_code','sap_itemcode','composition'];
        /*$details = VoluntaryQuotationSkuListing::with('getSkuStockist.getStockistDetails')->where('vq_id',$vq_id)->where('is_deleted','!=',1);*/
        //$details1 = VoluntaryQuotationSkuListing::with('getSkuStockist.getStockistDetails')->where('vq_id',$vq_id)->where('is_deleted',1);
        $details = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
            $query->where('is_deleted', 0)->with('getStockistDetails');
        }])
        ->select('voluntary_quotation_sku_listing.*', 
        DB::raw('COALESCE(discount_margin_master.discount_margin, 10) as discount_margin'))
        ->leftJoin('discount_margin_master','discount_margin_master.item_code','=','voluntary_quotation_sku_listing.item_code')
        ->where('vq_id', $vq_id);
        // ->where('is_deleted', '!=', 1); // hide by arun at 23/04/2025
        
        // $details1 = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
        //     $query->where('is_deleted', 0)->with('getStockistDetails');
        // }])
        // ->select('voluntary_quotation_sku_listing.*', DB::raw('COALESCE(discount_margin_master.discount_margin, 10) as discount_margin'))
        // ->leftJoin('discount_margin_master','discount_margin_master.item_code','=','voluntary_quotation_sku_listing.item_code')
        // ->where('vq_id', $vq_id)
        // ->where('is_deleted', 1); // hide by arun at 23/04/2025

        // Apply common search filter
        if (!empty($common_search)) {
            $details->where(function ($q) use ($common_search) {
                $q->where('div_name', 'like', "%$common_search%")
                  ->orWhere('mother_brand_name', 'like', "%$common_search%")
                  ->orWhere('voluntary_quotation_sku_listing.item_code', 'like', "%$common_search%")
                  ->orWhere('brand_name', 'like', "%$common_search%")
                  ->orWhere('applicable_gst', 'like', "%$common_search%")
                  ->orWhere('last_year_percent', 'like', "%$common_search%")
                  ->orWhere('last_year_rate', 'like', "%$common_search%")
                  ->orWhere('mrp', 'like', "%$common_search%")
                  ->orWhere('last_year_mrp', 'like', "%$common_search%")
                  ->orWhere('ptr', 'like', "%$common_search%")
                  ->orWhere('discount_percent', 'like', "%$common_search%")
                  ->orWhere('discount_rate', 'like', "%$common_search%")
                  ->orWhere('mrp_margin', 'like', "%$common_search%")
                  ->orWhere('type', 'like', "%$common_search%")
                  ->orWhere('hsn_code', 'like', "%$common_search%")
                  ->orWhere('sap_itemcode', 'like', "%$common_search%")
                  ->orWhere('composition', 'like', "%$common_search%");
            });
            // $details1->where(function ($q) use ($common_search) {
            //     $q->where('div_name', 'like', "%$common_search%")
            //       ->orWhere('mother_brand_name', 'like', "%$common_search%")
            //       ->orWhere('item_code', 'like', "%$common_search%")
            //       ->orWhere('brand_name', 'like', "%$common_search%")
            //       ->orWhere('applicable_gst', 'like', "%$common_search%")
            //       ->orWhere('last_year_percent', 'like', "%$common_search%")
            //       ->orWhere('last_year_rate', 'like', "%$common_search%")
            //       ->orWhere('mrp', 'like', "%$common_search%")
            //       ->orWhere('last_year_mrp', 'like', "%$common_search%")
            //       ->orWhere('ptr', 'like', "%$common_search%")
            //       ->orWhere('discount_percent', 'like', "%$common_search%")
            //       ->orWhere('discount_rate', 'like', "%$common_search%")
            //       ->orWhere('mrp_margin', 'like', "%$common_search%")
            //       ->orWhere('type', 'like', "%$common_search%")
            //       ->orWhere('hsn_code', 'like', "%$common_search%")
            //       ->orWhere('sap_itemcode', 'like', "%$common_search%")
            //       ->orWhere('composition', 'like', "%$common_search%");
            // }); // hide by arun at 23/04/2025
        }
        if(isset($request->division_filter))
        {
            $division = $request->division_filter;
            $details->where('div_name', $division);
            // $details1->where('div_name', $division); // hide by arun at 23/04/2025
        }

        // $recordsFiltered = $details->count()+$details1->count(); // hide by arun at 23/04/2025
        $recordsFiltered = $details->count();

        // Apply sorting
        if(isset($orderColumnIndex)&&array_key_exists($orderColumnIndex, $columns))
        {
            $orderColumnName = $columns[$orderColumnIndex];
            $details->orderBy($orderColumnName, $orderDirection);
            // $details1->orderBy($orderColumnName, $orderDirection); // hide by arun at 23/04/2025
        }

        //combine the query
        // $combined_query = $details->union($details1);
        // if(isset($orderColumnIndex)&&array_key_exists($orderColumnIndex, $columns))
        // {
        //     $orderColumnName = $columns[$orderColumnIndex];
        //     $combined_query->orderBy($orderColumnName, $orderDirection);
        // } // hide by arun at 23/04/2025

        // Fetch data for the current page
        $data = $details->offset($start)->limit($length)->get();

        // $sql = $details->toSql();
        // $bindings = $details->getBindings();

        // dd($sql, $bindings);

        $queryTotal1 = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
            $query->where('is_deleted', 0)->with('getStockistDetails');
        }])
        ->select('voluntary_quotation_sku_listing.*', DB::raw('COALESCE(discount_margin_master.discount_margin, 10) as discount_margin'))
        ->leftJoin('discount_margin_master','discount_margin_master.item_code','=','voluntary_quotation_sku_listing.item_code')
        ->where('vq_id',$vq_id)
        // ->where('is_deleted','!=',1) // hide by arun at 23/04/2025
        ->count();
        // $queryTotal2 = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
        //     $query->where('is_deleted', 0)->with('getStockistDetails');
        // }])
        // ->select('voluntary_quotation_sku_listing.*', DB::raw('COALESCE(discount_margin_master.discount_margin, 10) as discount_margin'))
        // ->leftJoin('discount_margin_master','discount_margin_master.item_code','=','voluntary_quotation_sku_listing.item_code')
        // ->where('vq_id',$vq_id)->where('is_deleted',1)->count();
        // $recordsTotal = $queryTotal1 + $queryTotal2; // hide by arun at 23/04/2025
        $recordsTotal = $queryTotal1;

        $stockist_margin = Config::where('meta_key', 'stockist_margin')->first();
        // Prepare JSON response for DataTables
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            'stockist_margin' =>$stockist_margin->meta_value
        ]);
    }
    public function getApproverVQdetaillistData(Request $request){
        $vq_id = $request->vq_id;
        $current_level = $request->current_level;
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $common_search = strtolower($request->input('search.value'));
        $orderColumnIndex = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir');
        $columns = ['id','div_name', 'mother_brand_name', 'item_code', 'brand_name', 'discount_percent', 'discount_rate','id','id','discount_margin','id','pack','applicable_gst', 'last_year_percent', 'last_year_rate', 'mrp', 'last_year_mrp', 'ptr', 'mrp_margin', 'discount_rate', 'discount_rate','mrp_margin', 'discount_rate', 'discount_rate','id','id','type', 'hsn_code','sap_itemcode','composition'];
        $total_rows = $request->total_rows;
        $details_listing_data_deleted_check = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.*')->where('voluntary_quotation_sku_listing.vq_id',$vq_id)->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.is_deleted',1)->where('deleted_by', preg_replace('/[^0-9.]+/', '', Session::get("level")))->exists();

        if($current_level >= preg_replace('/[^0-9.]+/', '', Session::get("level"))){
            if(Session::get("level")!='L1'){
                $detailsbig = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
                    $query->where('is_deleted', 0)->with('getStockistDetails');
                }])->select('voluntary_quotation_sku_listing.*','voluntary_quotation.current_level','max_discount_cap.max_discount','ceiling_master.discount_percent as ceiling_percent', DB::raw('COALESCE(discount_margin_master.discount_margin, 10) as discount_margin'))->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.vq_id',$vq_id)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->leftJoin('max_discount_cap','voluntary_quotation_sku_listing.div_id','=','max_discount_cap.div_id')->leftJoin('ceiling_master','ceiling_master.sku_id','=','voluntary_quotation_sku_listing.item_code')
                ->leftJoin('discount_margin_master','discount_margin_master.item_code','=','voluntary_quotation_sku_listing.item_code')
                ->whereRaw('voluntary_quotation_sku_listing.discount_percent > ceiling_master.discount_percent');
                if(!$details_listing_data_deleted_check)
                {
                    $detailsbig = $detailsbig->where('voluntary_quotation_sku_listing.is_deleted', 0);
                }
                $detailssmall = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
                    $query->where('is_deleted', 0)->with('getStockistDetails');
                }])->select('voluntary_quotation_sku_listing.*','voluntary_quotation.current_level','max_discount_cap.max_discount','ceiling_master.discount_percent as ceiling_percent', DB::raw('COALESCE(discount_margin_master.discount_margin, 10) as discount_margin'))->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.vq_id',$vq_id)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->leftJoin('max_discount_cap','voluntary_quotation_sku_listing.div_id','=','max_discount_cap.div_id')->leftJoin('ceiling_master','ceiling_master.sku_id','=','voluntary_quotation_sku_listing.item_code')
                ->leftJoin('discount_margin_master','discount_margin_master.item_code','=','voluntary_quotation_sku_listing.item_code')
                ->whereRaw('voluntary_quotation_sku_listing.discount_percent < ceiling_master.discount_percent');
                if(!$details_listing_data_deleted_check)
                {
                    $detailssmall = $detailssmall->where('voluntary_quotation_sku_listing.is_deleted', 0);
                }
                $detailssame = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
                    $query->where('is_deleted', 0)->with('getStockistDetails');
                }])->select('voluntary_quotation_sku_listing.*','voluntary_quotation.current_level','max_discount_cap.max_discount','ceiling_master.discount_percent as ceiling_percent', DB::raw('COALESCE(discount_margin_master.discount_margin, 10) as discount_margin'))->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.vq_id',$vq_id)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->leftJoin('max_discount_cap','voluntary_quotation_sku_listing.div_id','=','max_discount_cap.div_id')->leftJoin('ceiling_master','ceiling_master.sku_id','=','voluntary_quotation_sku_listing.item_code')
                ->leftJoin('discount_margin_master','discount_margin_master.item_code','=','voluntary_quotation_sku_listing.item_code')
                ->whereRaw('voluntary_quotation_sku_listing.discount_percent = ceiling_master.discount_percent');
                if(!$details_listing_data_deleted_check)
                {
                    $detailssame = $detailssame->where('voluntary_quotation_sku_listing.is_deleted', 0);
                }
                $detailsmissing = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
                    $query->where('is_deleted', 0)->with('getStockistDetails');
                }])->select('voluntary_quotation_sku_listing.*','voluntary_quotation.current_level','max_discount_cap.max_discount','ceiling_master.discount_percent as ceiling_percent', DB::raw('COALESCE(discount_margin_master.discount_margin, 10) as discount_margin'))->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.vq_id',$vq_id)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->leftJoin('max_discount_cap','voluntary_quotation_sku_listing.div_id','=','max_discount_cap.div_id')->leftJoin('ceiling_master','ceiling_master.sku_id','=','voluntary_quotation_sku_listing.item_code')
                ->leftJoin('discount_margin_master','discount_margin_master.item_code','=','voluntary_quotation_sku_listing.item_code')
                ->whereNull('ceiling_master.discount_percent');
                if(!$details_listing_data_deleted_check)
                {
                    $detailsmissing = $detailsmissing->where('voluntary_quotation_sku_listing.is_deleted', 0);
                }
                $detailsdiscarded = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
                    $query->where('is_deleted', 0)->with('getStockistDetails');
                }])->select('voluntary_quotation_sku_listing.*','voluntary_quotation.current_level','max_discount_cap.max_discount','ceiling_master.discount_percent as ceiling_percent', DB::raw('COALESCE(discount_margin_master.discount_margin, 10) as discount_margin'))->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.vq_id',$vq_id)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->leftJoin('max_discount_cap','voluntary_quotation_sku_listing.div_id','=','max_discount_cap.div_id')->leftJoin('ceiling_master','ceiling_master.sku_id','=','voluntary_quotation_sku_listing.item_code')
                ->leftJoin('discount_margin_master','discount_margin_master.item_code','=','voluntary_quotation_sku_listing.item_code')->where('voluntary_quotation_sku_listing.is_discarded',1);
 
                $recordsTotal = $total_rows;
                // Apply common search filter
                if (!empty($common_search)) {
                    $detailsbig->where(function ($q) use ($common_search) {
                        $q->where('voluntary_quotation_sku_listing.div_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mother_brand_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.item_code', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.brand_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.applicable_gst', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_percent', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_rate', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mrp', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_mrp', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.ptr', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.discount_percent', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.discount_rate', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mrp_margin', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.type', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.hsn_code', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.sap_itemcode', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.composition', 'like', "%$common_search%");
                    });
                    $detailssmall->where(function ($q) use ($common_search) {
                        $q->where('voluntary_quotation_sku_listing.div_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mother_brand_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.item_code', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.brand_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.applicable_gst', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_percent', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_rate', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mrp', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_mrp', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.ptr', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.discount_percent', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.discount_rate', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mrp_margin', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.type', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.hsn_code', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.sap_itemcode', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.composition', 'like', "%$common_search%");
                    });
                    $detailssame->where(function ($q) use ($common_search) {
                        $q->where('voluntary_quotation_sku_listing.div_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mother_brand_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.item_code', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.brand_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.applicable_gst', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_percent', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_rate', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mrp', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_mrp', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.ptr', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.discount_percent', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.discount_rate', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mrp_margin', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.type', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.hsn_code', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.sap_itemcode', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.composition', 'like', "%$common_search%");
                    });
                    $detailsmissing->where(function ($q) use ($common_search) {
                        $q->where('voluntary_quotation_sku_listing.div_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mother_brand_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.item_code', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.brand_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.applicable_gst', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_percent', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_rate', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mrp', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_mrp', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.ptr', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.discount_percent', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.discount_rate', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mrp_margin', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.type', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.hsn_code', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.sap_itemcode', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.composition', 'like', "%$common_search%");
                    });
                    $detailsdiscarded->where(function ($q) use ($common_search) {
                        $q->where('voluntary_quotation_sku_listing.div_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mother_brand_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.item_code', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.brand_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.applicable_gst', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_percent', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_rate', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mrp', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_mrp', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.ptr', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.discount_percent', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.discount_rate', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mrp_margin', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.type', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.hsn_code', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.sap_itemcode', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.composition', 'like', "%$common_search%");
                    });
                }
                // // Apply sorting
                // if(isset($orderColumnIndex)&&array_key_exists($orderColumnIndex, $columns))
                // {
                //     $orderColumnName = $columns[$orderColumnIndex];
                //     $detailsbig->orderBy($orderColumnName, $orderDirection);
                //     $detailssmall->orderBy($orderColumnName, $orderDirection);
                //     $detailssame->orderBy($orderColumnName, $orderDirection);
                //     $detailsmissing->orderBy($orderColumnName, $orderDirection);
                // }
                //$details = new \Illuminate\Database\Eloquent\Collection;

                // $recordsFiltered = $detailsbig->count() + $detailssmall->count() + $detailssame->count() + $detailsmissing->count();
                
                //combine the query
                $combined_query = $detailsbig->union($detailssmall)->union($detailssame)->union($detailsmissing)->union($detailsdiscarded);
                $recordsFiltered = $combined_query->get()->count();
                // if(isset($orderColumnIndex)&&array_key_exists($orderColumnIndex, $columns))
                // {
                //     $orderColumnName = $columns[$orderColumnIndex];
                //     $combined_query->orderBy($orderColumnName, $orderDirection);
                // }
                
                $details = $combined_query->offset($start)->limit($length)->get();
                if (strtolower($orderDirection) === 'desc') {
                    $orderColumnName = $columns[$orderColumnIndex];
                $details = $details->sortByDesc($orderColumnName)->values();
                } else {
                    $orderColumnName = $columns[$orderColumnIndex];
                $details = $details->sortBy($orderColumnName)->values();
                }


            }
            else{
                $details = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
                    $query->where('is_deleted', 0)->with('getStockistDetails');
                }])->select('voluntary_quotation_sku_listing.*','voluntary_quotation.current_level','max_discount_cap.max_discount','ceiling_master.discount_percent as ceiling_percent', DB::raw('COALESCE(discount_margin_master.discount_margin, 10) as discount_margin'))->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.vq_id',$vq_id)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->leftJoin('max_discount_cap','voluntary_quotation_sku_listing.div_id','=','max_discount_cap.div_id')->leftJoin('ceiling_master','ceiling_master.sku_id','=','voluntary_quotation_sku_listing.item_code')
                ->leftJoin('discount_margin_master','discount_margin_master.item_code','=','voluntary_quotation_sku_listing.item_code')
                ->distinct();
                if(!$details_listing_data_deleted_check)
                {
                    $details = $details->where('voluntary_quotation_sku_listing.is_deleted', 0);
                }
                $recordsTotal = $total_rows;
                // Apply common search filter
                if (!empty($common_search)) {
                    $details->where(function ($q) use ($common_search) {
                        $q->where('voluntary_quotation_sku_listing.div_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mother_brand_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.item_code', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.brand_name', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.applicable_gst', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_percent', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_rate', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mrp', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.last_year_mrp', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.ptr', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.discount_percent', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.discount_rate', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.mrp_margin', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.type', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.hsn_code', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.sap_itemcode', 'like', "%$common_search%")
                          ->orWhere('voluntary_quotation_sku_listing.composition', 'like', "%$common_search%");
                    });
                }
                $recordsFiltered = $details->get()->count();
                /* // // Apply sorting
                if(isset($orderColumnIndex)&&array_key_exists($orderColumnIndex, $columns))
                {
                    $orderColumnName = $columns[$orderColumnIndex];
                    $details->orderBy($orderColumnName, $orderDirection);
                } */ // 
                // $recordsFiltered = $details->get()->count();
                $details = $details->offset($start)->limit($length)->get();
                if (strtolower($orderDirection) === 'desc') {
                    $orderColumnName = $columns[$orderColumnIndex];
                    $details = $details->sortByDesc($orderColumnName)->values();
                } else {
                    $orderColumnName = $columns[$orderColumnIndex];
                    $details = $details->sortBy($orderColumnName)->values();
                }

            }  
             
    //dd($details);

        }else{
            $details =[];
        }
        $data = $details;
        $stockist_margin = Config::where('meta_key', 'stockist_margin')->first();
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $details,
            'stockist_margin' =>$stockist_margin->meta_value
        ]);
    }
    public function getHoVQdetaillistData(Request $request)
    {
        $id = $request->vq_id;
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $common_search = $request->input('search.value');
        $orderColumnIndex = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir');
        $columns = ['div_name', 'mother_brand_name', 'item_code', 'brand_name', 'applicable_gst', 'last_year_percent', 'last_year_rate',  'id', 'id', 'discount_margin', 'mrp', 'last_year_mrp', 'ptr', 'discount_percent', 'discount_rate', 'mrp_margin','type', 'hsn_code','sap_itemcode','composition'];
        $details = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
            $query->where('is_deleted', 0)->with('getStockistDetails');
        }])
        ->select('voluntary_quotation_sku_listing.*', DB::raw('COALESCE(discount_margin_master.discount_margin, 10) as discount_margin'))
        ->leftJoin('discount_margin_master','discount_margin_master.item_code','=','voluntary_quotation_sku_listing.item_code')
        ->where('vq_id',$id)->where('is_deleted',0);

        // Apply common search filter
        if (!empty($common_search)) {
            $details->where(function ($q) use ($common_search) {
                $q->where('div_name', 'like', "%$common_search%")
                  ->orWhere('mother_brand_name', 'like', "%$common_search%")
                  ->orWhere('voluntary_quotation_sku_listing.item_code', 'like', "%$common_search%")
                  ->orWhere('brand_name', 'like', "%$common_search%")
                  ->orWhere('applicable_gst', 'like', "%$common_search%")
                  ->orWhere('last_year_percent', 'like', "%$common_search%")
                  ->orWhere('last_year_rate', 'like', "%$common_search%")
                  ->orWhere('mrp', 'like', "%$common_search%")
                  ->orWhere('last_year_mrp', 'like', "%$common_search%")
                  ->orWhere('ptr', 'like', "%$common_search%")
                  ->orWhere('discount_percent', 'like', "%$common_search%")
                  ->orWhere('discount_rate', 'like', "%$common_search%")
                  ->orWhere('mrp_margin', 'like', "%$common_search%")
                  ->orWhere('type', 'like', "%$common_search%")
                  ->orWhere('hsn_code', 'like', "%$common_search%")
                  ->orWhere('sap_itemcode', 'like', "%$common_search%")
                  ->orWhere('composition', 'like', "%$common_search%");
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

        $recordsTotal = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
            $query->where('is_deleted', 0)->with('getStockistDetails');
        }])->select('id')
        ->leftJoin('discount_margin_master','discount_margin_master.item_code','=','voluntary_quotation_sku_listing.item_code')
        ->where('vq_id',$id)->where('is_deleted',0)->count();

        $stockist_margin = Config::where('meta_key', 'stockist_margin')->first();
        // Prepare JSON response for DataTables
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            'stockist_margin' =>$stockist_margin->meta_value
        ]);
    }
    public function getdistributionVQdetaillistData(Request $request)
    {
        $id = $request->vq_id;
        $draw = $request->input('draw');
        $start = $request->input('start');
        $length = $request->input('length');
        $common_search = $request->input('search.value');
        $orderColumnIndex = $request->input('order.0.column');
        $orderDirection = $request->input('order.0.dir');
        $columns = ['div_name', 'mother_brand_name', 'item_code', 'brand_name', 'applicable_gst', 'last_year_percent', 'last_year_rate', 'id', 'id', 'discount_margin', 'mrp', 'last_year_mrp', 'ptr', 'discount_percent', 'discount_rate', 'mrp_margin','type', 'hsn_code','sap_itemcode','composition'];
        $details = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
            $query->where('is_deleted', 0)->with('getStockistDetails');
        }])
        ->select('voluntary_quotation_sku_listing.*', DB::raw('COALESCE(discount_margin_master.discount_margin, 10) as discount_margin'))
        ->leftJoin('discount_margin_master','discount_margin_master.item_code','=','voluntary_quotation_sku_listing.item_code')
        ->where('vq_id',$id)->where('is_deleted',0);

        // Apply common search filter
        if (!empty($common_search)) {
            $details->where(function ($q) use ($common_search) {
                $q->where('div_name', 'like', "%$common_search%")
                  ->orWhere('mother_brand_name', 'like', "%$common_search%")
                  ->orWhere('voluntary_quotation_sku_listing.item_code', 'like', "%$common_search%")
                  ->orWhere('brand_name', 'like', "%$common_search%")
                  ->orWhere('applicable_gst', 'like', "%$common_search%")
                  ->orWhere('last_year_percent', 'like', "%$common_search%")
                  ->orWhere('last_year_rate', 'like', "%$common_search%")
                  ->orWhere('mrp', 'like', "%$common_search%")
                  ->orWhere('last_year_mrp', 'like', "%$common_search%")
                  ->orWhere('ptr', 'like', "%$common_search%")
                  ->orWhere('discount_percent', 'like', "%$common_search%")
                  ->orWhere('discount_rate', 'like', "%$common_search%")
                  ->orWhere('mrp_margin', 'like', "%$common_search%")
                  ->orWhere('type', 'like', "%$common_search%")
                  ->orWhere('hsn_code', 'like', "%$common_search%")
                  ->orWhere('sap_itemcode', 'like', "%$common_search%")
                  ->orWhere('composition', 'like', "%$common_search%");
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

        $recordsTotal = VoluntaryQuotationSkuListing::with(['getSkuStockist' => function($query) {
            $query->where('is_deleted', 0)->with('getStockistDetails');
        }])
        ->select('id')
        ->leftJoin('discount_margin_master','discount_margin_master.item_code','=','voluntary_quotation_sku_listing.item_code')
        ->where('vq_id',$id)->where('is_deleted',0)->count();

        $stockist_margin = Config::where('meta_key', 'stockist_margin')->first();
        // Prepare JSON response for DataTables
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            'stockist_margin' =>$stockist_margin->meta_value
        ]);
    }
    public function getSKU(){

        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $financialYears = $this->getLastFinancialYears(5);
        $cluster = DB::table('cluster')->select('div_code','cluster')->where('is_deleted',0)->groupBy('cluster')->orderBy('cluster')->get();
        $institute = VoluntaryQuotation::select('institution_id', 'hospital_name')->where('year', $year)->where('is_deleted', 0)->groupBy('institution_id', 'hospital_name')->orderBy('hospital_name')->get();
        $criteria = DB::table('criteria')->select('filter_criteria','filter_condition','id')->where('active',1)->orderBy('filter_criteria')->get();
        /*$details = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.*','voluntary_quotation.*')->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('year', $year)->limit(50)->get();*/

        $stockist_margin = Config::select('meta_value')->where('meta_key', 'stockist_margin')->first();


        return view('frontend.Approver.details_sku',compact('financialYears','cluster','institute','criteria','stockist_margin'));
    }
    public function getApproverVQdetailListCriteria(Request $request)
    {
        try 
        {
            $year = $request->year;
            $clusters = $request->cluster;
            $institutionNames = $request->institutionName;
            $status = $request->status;
            $criteria = $request->criteria;
            $draw = $request->input('draw');
            $start = $request->input('start');
            $length = $request->input('length');
            $common_search = $request->input('search.value');
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir');

            $columns = ['hospital_name','hospital_name', 'sap_code', 'rev_no', 'city','div_name','mother_brand_name','sap_itemcode','brand_name','item_code', 'discount_percent', 'discount_rate','applicable_gst', 'last_year_percent', 'last_year_rate', 'last_year_mrp','mrp',  'ptr', 'mrp_margin','discount_percent', 'discount_percent','discount_percent','composition','composition'];


            $details = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.*','voluntary_quotation.*','voluntary_quotation_sku_listing.id as sku_id')
            ->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            ->where('year', $year)->where('voluntary_quotation_sku_listing.is_deleted',0)->where('voluntary_quotation.is_deleted', 0);
            if (!in_array('all', $institutionNames)) {

                $details->whereIn('institution_id', $institutionNames);
            }
            if (!in_array('all', $clusters)) {
                $div_codes = DB::table('cluster')->select('div_code')->where('is_deleted',0)->whereIn('cluster',$clusters)->pluck('div_code');
                $details->whereIn('div_id', $div_codes);
            }
            if ($criteria && count($criteria) > 0) {
                $details->where(function ($query) use ($criteria) {
                    foreach ($criteria as $condition) {
                        if (preg_match('/^>=(\d+)$/', $condition, $matches)) {
                            $query->orWhere('discount_percent', '>=', (int)$matches[1]);
                        } elseif (preg_match('/^>(\d+)$/', $condition, $matches)) {
                            $query->orWhere('discount_percent', '>', (int)$matches[1]);
                        } elseif (preg_match('/between (\d+) and (\d+)$/', $condition, $matches)) {
                            $query->orWhereBetween('discount_percent', [(int)$matches[1], (int)$matches[2]]);
                        } elseif (preg_match('/^<=(\d+)$/', $condition, $matches)) {
                            $query->orWhere('discount_percent', '<=', (int)$matches[1]);
                        } elseif (preg_match('/^<(\d+)$/', $condition, $matches)) {
                            $query->orWhere('discount_percent', '<', (int)$matches[1]);
                        } elseif (preg_match('/^=(\d+)$/', $condition, $matches)) {
                            $query->orWhere('discount_percent', '=', (int)$matches[1]);
                        }
                    }
                });
            }
            if($status == 'pending')
            {
                $details->where('current_level', preg_replace('/[^0-9.]+/', '', Session::get("level")))->where('voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status',0);
            }
            elseif($status == 'approved')
            {
                $details->where('voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status',1);
            }
            else
            {
                $details->whereIn('current_level', [7,preg_replace('/[^0-9.]+/', '', Session::get("level"))])->whereIn('voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status',[1,0]);

                /*$details->leftJoin('exception_sku_list', 'voluntary_quotation_sku_listing.item_code' ,'=', 'exception_sku_list.item_code')
                ->whereNull('exception_sku_list.item_code');*/
            }
            

            $recordsTotal = ($status == 'approved')? $details->count() :'';

            if (!empty($common_search)) {
                $details->where(function ($q) use ($common_search) {
                    $q->where('hospital_name', 'like', "%$common_search%")
                      ->orWhere('mother_brand_name', 'like', "%$common_search%")
                      ->orWhere('item_code', 'like', "%$common_search%")
                      ->orWhere('brand_name', 'like', "%$common_search%")
                      ->orWhere('applicable_gst', 'like', "%$common_search%")
                      ->orWhere('last_year_percent', 'like', "%$common_search%")
                      ->orWhere('last_year_rate', 'like', "%$common_search%")
                      ->orWhere('mrp', 'like', "%$common_search%")
                      ->orWhere('last_year_mrp', 'like', "%$common_search%")
                      ->orWhere('ptr', 'like', "%$common_search%")
                      ->orWhere('discount_percent', 'like', "%$common_search%")
                      ->orWhere('discount_rate', 'like', "%$common_search%")
                      ->orWhere('mrp_margin', 'like', "%$common_search%")
                      ->orWhere('sap_itemcode', 'like', "%$common_search%")
                      ->orWhere('composition', 'like', "%$common_search%")
                      ->orWhere('div_name', 'like', "%$common_search%");
                });
            }
            $recordsFiltered = ($status == 'approved')? $details->count() : '';
            if(isset($orderColumnIndex)&&array_key_exists($orderColumnIndex, $columns))
            {
                $orderColumnName = $columns[$orderColumnIndex];
                $details->orderBy($orderColumnName, $orderDirection);
            }
            else
            {
                $details->orderBy('hospital_name');
            }
            if($status == 'approved'):
                $data = $details->offset($start)->limit($length)->get();
                return response()->json([
                    'draw' => $draw,
                    'recordsTotal' => $recordsTotal,
                    'recordsFiltered' => $recordsFiltered,
                    'data' => $data,
                ]);
            elseif($status == 'pending' || $status == 'all'):
                $data = $details->get();
                return response()->json([
                    //'draw' => $draw,
                    //'recordsTotal' => $recordsTotal,
                    //'recordsFiltered' => $recordsFiltered,
                    'data' => $data,
                ]);
            endif;
           
        }
        catch (\Exception $e) {
            Log::error('Error in getApproverVQdetailListCriteria: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while processing your request.'
            ], 500);
        }
    }
    public function getLastFinancialYears($count) {
        $Current_years = $this->getFinancialYear(date('Y-m-d'),"Y");
        $currentYear = (int)explode('-' , $Current_years )[0];
        $financialYears = [];

        for ($i = 0; $i < 5; $i++) {
            $year = $currentYear - $i;
            $inputDate = $year . '-04-01'; // Using April 1st to get the correct financial year
            $financialYear = $this->getFinancialYear($inputDate , "Y" , true);
            $financialYears[] = $financialYear;
        }

        return $financialYears;
    }
}
