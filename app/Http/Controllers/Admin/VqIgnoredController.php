<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\ApproveVq;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ApprovalPeriod;
use App\Models\IgnoredInstitutions;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\VoluntaryQuotationSkuListingStockist;
use App\Models\JwtToken;
use Illuminate\Support\Facades\DB;
use Session;
use DateTime; 
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Mail;

class VqIgnoredController extends Controller
{
    public function index(){
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.ignored_institutions.add');
        }
        else
        {
            return view('admin.ignored_institutions.add');
        }
    }
    public function list(){
        $data = DB::table('ignored_institutions')->get();
        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.ignored_institutions.list',compact('data'));
        }
        else
        {
            return view('admin.ignored_institutions.list',compact('data'));
        }
    }
    public function store(Request $request){
        $validatedData = $request->validate([
            'institution_id' => 'required',
            'parent_institution_id' => 'required'
        ]);

        $dataExists = IgnoredInstitutions::where('institution_id', $request->institution_id)->exists();
        
        if($dataExists){
            return back()->withErrors('Data for this Institution is already present!');
        }

        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $date = new DateTime();
        $year1 = $date->format('Y');
        $month = $date->format('m');
        if ($month < 4) {
            $year1 -= 1;
        }
        $financialYearEndDate = new DateTime(($year1 + 1) . '-03-31 00:00:00');
        $child_institutionCheckVQ = VoluntaryQuotation::where('year', $year)->where('is_deleted', 0)->where('institution_id', $request->institution_id)->exists();
        $vq_created = date('Y-m-d H:i:s');
        $vq_created = date('Y-m-d H:i:s');
        $phpdate1 = strtotime( $vq_created );
        $start = date( 'Y-m-d H:i:s', $phpdate1 ); //contract_start_date
        $finddayyear = date("Y") + 1;
        $finddaymonth = date("3");
        $days = cal_days_in_month(CAL_GREGORIAN, $finddaymonth, $finddayyear);
        $enddateyear = strtotime( $finddayyear.'-'.$finddaymonth.'-'.$days );
        $end = date('Y-m-d H:i:s', $enddateyear);  //contract_end_date
        DB::beginTransaction();
        try 
        {
            /** hide previous case and new case added on 17122024 */
            if(empty($child_institutionCheckVQ))
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
                
                $r = $client->request('POST', env('API_URL').'/API/InstitutionOnly', [
                    'body' => $body
                ]);
                $response = $r->getBody()->getContents();
                
                $response = json_decode($response, true);
                $resp_collection = collect($response);
                $chain_hospital_institution = $resp_collection->where('INST_ID', $request->institution_id)->first();
                $inst = VoluntaryQuotation::Create([
                    'hospital_name' => $chain_hospital_institution['INST_NAME'],
                    'institution_id' => $chain_hospital_institution['INST_ID'],
                    'institution_key_account' => $chain_hospital_institution['KEY_ACC_NAME'],
                    'city' => $chain_hospital_institution['CITY'],
                    'addr1'=>$chain_hospital_institution['ADDR1'],
                    'addr2'=>$chain_hospital_institution['ADDR2'],
                    'addr3'=>$chain_hospital_institution['ADDR3'],
                    'parent_vq_id' => 0,
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
                    'contract_start_date' => date('Y-m-d H:i:s'),
                    'contract_end_date' => $financialYearEndDate->format('Y-m-d H:i:s'),
                    'year' => $year,
                    'sap_code' => $chain_hospital_institution['SAP_CODE'],
                    'institution_zone' => isset($chain_hospital_institution['LSTZONEMAPPING'][0]['ZSM_ZONE']) ? $chain_hospital_institution['LSTZONEMAPPING'][0]['ZSM_ZONE'] : '',
                    'institution_region' => isset($chain_hospital_institution['LSTZONEMAPPING'][0]['RSM_REGION']) ? $chain_hospital_institution['LSTZONEMAPPING'][0]['RSM_REGION'] : '',
                    'created_at' => $vq_created,
                    'updated_at' => $vq_created,
                    'rev_no' =>0,
                ]);
            
                // $maxRevSubquery = DB::table('voluntary_quotation_sku_listing as s')
                // ->select(DB::raw('MAX(v2.rev_no) AS max_rev_no'), 's.item_code', 'v2.institution_id')
                // ->leftJoin('voluntary_quotation as v2', 'v2.id', '=', 's.vq_id')
                // ->where('v2.year', $year)
                // ->where('s.is_deleted', 0)
                // ->where('v2.vq_status', 1)
                // ->where('v2.is_deleted', 0)
                // ->where('v2.institution_id', $request->parent_institution_id)
                // ->groupBy('s.item_code');
                // $revisedData = DB::table('voluntary_quotation_sku_listing as vqsl')
                // ->select('vqsl.*', 'vq.*')
                // ->leftJoin('voluntary_quotation as vq', 'vqsl.vq_id', '=', 'vq.id')
                // ->joinSub($maxRevSubquery, 'max_rev', function ($join) use($data) {
                //     $join->on('vqsl.item_code', '=', 'max_rev.item_code')
                //         ->where('vq.institution_id',  $request->parent_institution_id)
                //         ->on('vq.rev_no', '=', 'max_rev.max_rev_no');
                // })
                // ->where('vq.institution_id',  $request->parent_institution_id)
                // ->where('vq.year', $year)
                // ->where('vq.vq_status', 1)
                // ->where('vq.is_deleted', 0)
                // ->where('vqsl.is_deleted', 0)
                // ->get();
                $rateTransferInstitution = $request->parent_institution_id;
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
                $revisedData = DB::table('voluntary_quotation_sku_listing as vqsl')
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
                ->get();
                $listing_data = [];
                foreach($revisedData as $single_data){
                    $listing_data[]=[
                        'vq_id' =>$inst->id,
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
                    ];
                }
                foreach (array_chunk($listing_data,100) as $t)  
                {
                    DB::table('voluntary_quotation_sku_listing')->insert($t); 
                }
            }else{
                /** Get VoluntaryQuotation last rev_no query */
                $newestClient = VoluntaryQuotation::where('institution_id', $request->institution_id)->where('year', $year)->where('is_deleted', 0)->orderBy('rev_no', 'desc')->first(); // gets the one row
                // $maxValue = $newestClient->rev_no;
                // $rev_no = ($maxValue == NULL)? $maxValue+0 : $maxValue+1;
                $rev_no = (!empty($newestClient->toArray()))? $newestClient->rev_no+1 : '0';
                $ignoreinstitution_vq = VoluntaryQuotation::where('institution_id', $request->institution_id)->where('parent_vq_id', 0)->where('year', $year)->where('is_deleted', 0)->first();
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
                // $get_vq_sku_listing = VoluntaryQuotationSkuListing::where('vq_id', $newestClient->id)->where('is_deleted', 0)->get();
                $rateTransferInstitution = $request->parent_institution_id;
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
                $revisedData = DB::table('voluntary_quotation_sku_listing as vqsl')
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
                ->get();
                $listing_data = [];
                foreach($revisedData as $single_data):
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
                foreach (array_chunk($listing_data,100) as $t)  
                {
                    DB::table('voluntary_quotation_sku_listing')->insert($t); 
                }
            }
            // $this->dispatch(new ApproveVq($inst->id, $jwt->jwt_token, Session::get('idArr'),Session::get('changePayModeData')));//added idArr from session 18122024
            DB::commit();
        }
        catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors("Batch insert failed: " . $e->getMessage());
        }
        
        
        IgnoredInstitutions::create($validatedData);
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/ignored-institutions-list')->with('message', 'Institution is successfully validated and data has been saved');
        }
        else
        {
            return redirect('/admin/ignored-institutions-list')->with('message', 'Institution is successfully validated and data has been saved');
        }
    }

    public function edit($id){
        $data = IgnoredInstitutions::find($id);

        if(Session::get("type") == 'initiator')
        {
            return view('frontend.Initiator.admin.ignored_institutions.edit',compact('data'));
        }
        else
        {
            return view('admin.ignored_institutions.edit',compact('data'));
        }
    }
    public function update(Request $request){
        $validatedData = $request->validate([
            'institution_id' => 'required',
            'parent_institution_id' => 'required'
        ]);

        $dataExists = IgnoredInstitutions::where('institution_id', $request->institution_id)->exists();

        $dataExists = IgnoredInstitutions::where('parent_institution_id', $request->parent_institution_id)
        ->where('institution_id', $request->institution_id)
        ->exists();
     
        if($dataExists){
            return back()->withErrors('Data for this Institution is already present!');
        }

        $get_parent_data =  IgnoredInstitutions::where('parent_institution_id', $request->institution_id)
        ->get();
        // print_r($get_parent_data->toArray());die;
        if(!empty($get_parent_data)):
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $date = new DateTime();
        $year1 = $date->format('Y');
        $month = $date->format('m');
        if ($month < 4) {
            $year1 -= 1;
        }
        $financialYearEndDate = new DateTime(($year1 + 1) . '-03-31 00:00:00');
        $child_institutionCheckVQ = VoluntaryQuotation::where('year', $year)->where('is_deleted', 0)->where('institution_id', $request->institution_id)->exists();
        $vq_created = date('Y-m-d H:i:s');
        $vq_created = date('Y-m-d H:i:s');
        $phpdate1 = strtotime( $vq_created );
        $start = date( 'Y-m-d H:i:s', $phpdate1 ); //contract_start_date
        $finddayyear = date("Y") + 1;
        $finddaymonth = date("3");
        $days = cal_days_in_month(CAL_GREGORIAN, $finddaymonth, $finddayyear);
        $enddateyear = strtotime( $finddayyear.'-'.$finddaymonth.'-'.$days );
        $end = date('Y-m-d H:i:s', $enddateyear);  //contract_end_date
        DB::beginTransaction();
        try 
        {
            /** hide previous case and new case added on 17122024 */
            if(empty($child_institutionCheckVQ))
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
                
                $r = $client->request('POST', env('API_URL').'/API/InstitutionOnly', [
                    'body' => $body
                ]);
                $response = $r->getBody()->getContents();
                
                $response = json_decode($response, true);
                $resp_collection = collect($response);
                $chain_hospital_institution = $resp_collection->where('INST_ID', $request->institution_id)->first();
                $inst = VoluntaryQuotation::Create([
                    'hospital_name' => $chain_hospital_institution['INST_NAME'],
                    'institution_id' => $chain_hospital_institution['INST_ID'],
                    'institution_key_account' => $chain_hospital_institution['KEY_ACC_NAME'],
                    'city' => $chain_hospital_institution['CITY'],
                    'addr1'=>$chain_hospital_institution['ADDR1'],
                    'addr2'=>$chain_hospital_institution['ADDR2'],
                    'addr3'=>$chain_hospital_institution['ADDR3'],
                    'parent_vq_id' => 0,
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
                    'contract_start_date' => date('Y-m-d H:i:s'),
                    'contract_end_date' => $financialYearEndDate->format('Y-m-d H:i:s'),
                    'year' => $year,
                    'sap_code' => $chain_hospital_institution['SAP_CODE'],
                    'institution_zone' => isset($chain_hospital_institution['LSTZONEMAPPING'][0]['ZSM_ZONE']) ? $chain_hospital_institution['LSTZONEMAPPING'][0]['ZSM_ZONE'] : '',
                    'institution_region' => isset($chain_hospital_institution['LSTZONEMAPPING'][0]['RSM_REGION']) ? $chain_hospital_institution['LSTZONEMAPPING'][0]['RSM_REGION'] : '',
                    'created_at' => $vq_created,
                    'updated_at' => $vq_created,
                    'rev_no' =>0,
                ]);
            
                
                $rateTransferInstitution = $request->parent_institution_id;
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
                $revisedData = DB::table('voluntary_quotation_sku_listing as vqsl')
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
                ->get();
                $listing_data = [];
                foreach($revisedData as $single_data){
                    $listing_data[]=[
                        'vq_id' =>$inst->id,
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
                    ];
                }
                foreach (array_chunk($listing_data,100) as $t)  
                {
                    DB::table('voluntary_quotation_sku_listing')->insert($t); 
                }
            }else{
                /** Get VoluntaryQuotation last rev_no query */
                $newestClient = VoluntaryQuotation::where('institution_id', $request->institution_id)->where('year', $year)->where('is_deleted', 0)->orderBy('rev_no', 'desc')->first(); // gets the one row
                // $maxValue = $newestClient->rev_no;
                // $rev_no = ($maxValue == NULL)? $maxValue+0 : $maxValue+1;
                $rev_no = (!empty($newestClient->toArray()))? $newestClient->rev_no+1 : '0';
                $ignoreinstitution_vq = VoluntaryQuotation::where('institution_id', $request->institution_id)->where('parent_vq_id', 0)->where('year', $year)->where('is_deleted', 0)->first();
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
                // $get_vq_sku_listing = VoluntaryQuotationSkuListing::where('vq_id', $newestClient->id)->where('is_deleted', 0)->get();
                $rateTransferInstitution = $request->parent_institution_id;
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
                $revisedData = DB::table('voluntary_quotation_sku_listing as vqsl')
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
                ->get();
                $listing_data = [];
                foreach($revisedData as $single_data):
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
                foreach (array_chunk($listing_data,100) as $t)  
                {
                    DB::table('voluntary_quotation_sku_listing')->insert($t); 
                }
            }
            // $this->dispatch(new ApproveVq($inst->id, $jwt->jwt_token, Session::get('idArr'),Session::get('changePayModeData')));//added idArr from session 18122024
            DB::commit();
        }
        catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors("Batch insert failed: " . $e->getMessage());
        }
        endif;

        IgnoredInstitutions::where('id',$request['id'])->update($validatedData);
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/ignored-institutions-list')->with('message', 'Institution is successfully validated and data has been updated');
        }
        else
        {
            return redirect('/admin/ignored-institutions-list')->with('message', 'Institution is successfully validated and data has been updated');
        }
    }
    public function delete($id){
        $institution=IgnoredInstitutions::find($id);
        $institution->delete();
        if(Session::get("type") == 'initiator')
        {
            return redirect('/initiator/ignored-institutions-list')->with('message', 'Institution is successfully deleted');
        }
        else
        {
            return redirect('/admin/ignored-institutions-list')->with('message', 'Institution is successfully deleted');
        }
    } 
}
