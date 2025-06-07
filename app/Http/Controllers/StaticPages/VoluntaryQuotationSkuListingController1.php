<?php

namespace App\Http\Controllers\StaticPages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use DB;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\ActivityTracker;
use App\Models\LastYearPrice;
use App\Models\CeilingMaster;
use App\Models\Config;
use App\Models\JwtToken;
use App\Models\Stockist_master;
use GuzzleHttp\Client as GuzzleClient;
class VoluntaryQuotationSkuListingController extends Controller
{
    public function approverDetails($id){
        $vq=VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();
        if($vq->current_level >= preg_replace('/[^0-9.]+/', '', Session::get("level"))){
            if(Session::get("level")!='L1'){
                $detailsbig = VoluntaryQuotationSkuListing::with('getSkuStockist.getStockistDetails')->select('voluntary_quotation_sku_listing.*','voluntary_quotation.current_level','max_discount_cap.max_discount','ceiling_master.discount_percent as ceiling_percent')->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.vq_id',$id)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->leftJoin('max_discount_cap','voluntary_quotation_sku_listing.div_id','=','max_discount_cap.div_id')->leftJoin('ceiling_master','ceiling_master.sku_id','=','voluntary_quotation_sku_listing.item_code')->whereRaw('voluntary_quotation_sku_listing.discount_percent > ceiling_master.discount_percent')->get();
                $detailssmall = VoluntaryQuotationSkuListing::with('getSkuStockist.getStockistDetails')->select('voluntary_quotation_sku_listing.*','voluntary_quotation.current_level','max_discount_cap.max_discount','ceiling_master.discount_percent as ceiling_percent')->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.vq_id',$id)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->leftJoin('max_discount_cap','voluntary_quotation_sku_listing.div_id','=','max_discount_cap.div_id')->leftJoin('ceiling_master','ceiling_master.sku_id','=','voluntary_quotation_sku_listing.item_code')->whereRaw('voluntary_quotation_sku_listing.discount_percent < ceiling_master.discount_percent')->get();
                $detailssame = VoluntaryQuotationSkuListing::with('getSkuStockist.getStockistDetails')->select('voluntary_quotation_sku_listing.*','voluntary_quotation.current_level','max_discount_cap.max_discount','ceiling_master.discount_percent as ceiling_percent')->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.vq_id',$id)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->leftJoin('max_discount_cap','voluntary_quotation_sku_listing.div_id','=','max_discount_cap.div_id')->leftJoin('ceiling_master','ceiling_master.sku_id','=','voluntary_quotation_sku_listing.item_code')->whereRaw('voluntary_quotation_sku_listing.discount_percent = ceiling_master.discount_percent')->get();
                $details = new \Illuminate\Database\Eloquent\Collection;
                $details =$details->merge($detailsbig);
                $details =$details->merge($detailssmall);
                $details =$details->merge($detailssame);
            }else{
                $details = VoluntaryQuotationSkuListing::with('getSkuStockist.getStockistDetails')->select('voluntary_quotation_sku_listing.*','voluntary_quotation.current_level','max_discount_cap.max_discount','ceiling_master.discount_percent as ceiling_percent')->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))->where('voluntary_quotation_sku_listing.vq_id',$id)->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->leftJoin('max_discount_cap','voluntary_quotation_sku_listing.div_id','=','max_discount_cap.div_id')->leftJoin('ceiling_master','ceiling_master.sku_id','=','voluntary_quotation_sku_listing.item_code')->get();
            }
        }else{
            $details =[];
        }
        $stockists = Stockist_master::where('institution_code', data_get($id, 'institution_id'))->get();
        $stockist_margin = Config::where('meta_key', 'stockist_margin')->first();
        $data['vq_data']=$vq;
        $data['jwt']=$jwt['jwt_token'];
        $data['details']=$details;
        $data['stockists']=$stockists;
        $data['stockist_margin']=$stockist_margin->meta_value;
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
        $details = VoluntaryQuotationSkuListing::with('getSkuStockist.getStockistDetails')->where('vq_id',$id)->where('is_deleted',0)->get();
        
        $stockists = Stockist_master::where('institution_code', data_get($vq, 'institution_id'))->get();
        $stockist_margin = Config::where('meta_key', 'stockist_margin')->first();

        $data['vq_data']=$vq;
        $data['details']=$details;
        $data['stockists']=$stockists;
        $data['stockist_margin']=$stockist_margin->meta_value;
        return view('frontend.Initiator.details',compact('data'));
    }

    public function pocDetails($id){
        $vq=VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $details = VoluntaryQuotationSkuListing::with('getSkuStockist.getStockistDetails')->where('vq_id',$id)->where('is_deleted',0)->get();
        $stockists = Stockist_master::where('institution_code', data_get($id, 'institution_id'))->get();
        $stockist_margin = Config::where('meta_key', 'stockist_margin')->first();
        $data['vq_data']=$vq;
        $data['details']=$details;
        $data['stockists']=$stockists;
        $data['stockist_margin']=$stockist_margin->meta_value;
        return view('frontend.Poc.details',compact('data'));
    }

    public function distributionDetails($id){
        $vq=VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $details = VoluntaryQuotationSkuListing::with('getSkuStockist.getStockistDetails')->where('vq_id',$id)->where('is_deleted',0)->get();
        $stockists = Stockist_master::where('institution_code', data_get($id, 'institution_id'))->get();
        $stockist_margin = Config::where('meta_key', 'stockist_margin')->first();
        $data['vq_data']=$vq;
        $data['details']=$details;
        $data['stockists']=$stockists;
        $data['stockist_margin']=$stockist_margin->meta_value;
        return view('frontend.Distribution.details',compact('data'));
    }

    public function hoDetails($id){
        $vq=VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $details = VoluntaryQuotationSkuListing::with('getSkuStockist.getStockistDetails')->where('vq_id',$id)->where('is_deleted',0)->get();
        $stockists = Stockist_master::where('institution_code', data_get($id, 'institution_id'))->get();
        $stockist_margin = Config::where('meta_key', 'stockist_margin')->first();
        $data['vq_data']=$vq;
        $data['details']=$details;
        $data['stockists']=$stockists;
        $data['stockist_margin']=$stockist_margin->meta_value;
        return view('frontend.Ho.details',compact('data'));
    }

    public function userDetails($id){
        $vq=VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $details = VoluntaryQuotationSkuListing::with('getSkuStockist.getStockistDetails')->where('vq_id',$id)->where('is_deleted',0)->get();
        $stockists = Stockist_master::where('institution_code', data_get($id, 'institution_id'))->get();
        $stockist_margin = Config::where('meta_key', 'stockist_margin')->first();
        $data['vq_data']=$vq;
        $data['details']=$details;
        $data['stockists']=$stockists;
        $data['stockist_margin']=$stockist_margin->meta_value;
        return view('frontend.User.details',compact('data'));
    }


	public function reinitiate_listing($id,Request $request){
        $inputData = $request->all();
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
        if($institute_count == 1){
            $id = json_decode($inputData['institutes'])[0];
            $year = $this->getFinancialYear(date('Y-m-d'),"Y");
            $vq_listing = VoluntaryQuotation::where('institution_id',$id)->where('year',$year)->where('parent_vq_id',0)->where('is_deleted', 0)->first();
            $sku_count = VoluntaryQuotationSkuListing::where('vq_id',$vq_listing->id)->count();
            
            // dd($data);
            if(count($data) != $sku_count){
                $listing_data = array();
                $checkers = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.div_id')
                ->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
                ->whereRaw('QUARTER(voluntary_quotation.created_at) = QUARTER(now())')
                ->where('voluntary_quotation.parent_vq_id',$vq_listing->id)->distinct()->get();
                $last_year_data_main = LastYearPrice::where('institution_id',$vq_listing->institution_id)->where('year',$year)->get();
                $old_data_main = VoluntaryQuotationSkuListing::where('vq_id',$vq_listing->id)->get();
                $ceiling_data_main = ceilingMaster::get();

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
                        $last_year_data = $last_year_data_main->where('institution_id',$vq_listing->institution_id)->where('division_id',$single_data->DIVISION_CODE)->first();
                        $old_data = $old_data_main->where('item_code',$single_data->ITEM_CODE)->first();
                        if(is_null($old_data)){
                            $ceiling_data = $ceiling_data_main->where('sku_id',$single_data->ITEM_CODE)->first();
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
                        }
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
                        ];
                    }
                }
                //$final_data = json_decode(json_encode(array_merge ($old_data, $listing_data)),false);
                $final_data = json_decode(json_encode($listing_data),false);
            }else{
                $data1 = VoluntaryQuotationSkuListing::where('vq_id',$vq_listing->id)->get();
                $listing_data = array();
                foreach($data1 as $single_data){
                    $checkers = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.div_id')
                    ->leftJoin('voluntary_quotation','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
                    ->whereRaw('QUARTER(voluntary_quotation.created_at) = QUARTER(now())')
                    ->where('voluntary_quotation.parent_vq_id',$vq_listing->id)->distinct()->get();
                    
                    $flag=0;
                    // foreach($checkers as $checker){
                    //     if($checker['div_id'] == $single_data->div_id){
                    //         $flag=1;
                    //         break;
                    //     }
                    // }
                    if($flag == 0){
                        $listing_data[]=$single_data;
                    }
                }
                //$final_data = json_decode(json_encode(array_merge ($old_data, $listing_data)),false);
                $final_data = json_decode(json_encode($listing_data),false);
            }
            return view('frontend.Initiator.reinitiate',compact('final_data','vq_listing', 'inputData'));
        }
        else{

            $listing_data = array();
            

            foreach($data as $single_data){
                    
                  
                    $dis_per = 0;
                    $mid = ($single_data->PTR / 100) * $dis_per;
                    $dis_rate = $single_data->PTR - $mid;
                    $last_year_percent = NULL;
                    $last_year_rate = NULL;
                    $last_year_mrp = NULL;
                    $mrp_margin = ((($single_data->MRP -$single_data->PTR)/$single_data->MRP)*100 );
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
                        'last_year_mrp' =>$last_year_mrp,
                        'discount_percent' => $dis_per,
                        'discount_rate' => $dis_rate,
                        'mrp' => $single_data->MRP,
                        'mrp_margin'=> $mrp_margin,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                }
            //$final_data = json_decode(json_encode(array_merge ($old_data, $listing_data)),false);
            $final_data = json_decode(json_encode($listing_data),false);
            return view('frontend.Initiator.reinitiate',compact('final_data', 'inputData'));

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
        }
        if(preg_replace('/[^0-9.]+/', '', Session::get("level"))>2){
            $institute = VoluntaryQuotation::join('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation_sku_listing.'.$level.'_status',0)->where('voluntary_quotation.current_level', '>=', $level_no)->select('voluntary_quotation.id','hospital_name')->where('year',$year)->where('voluntary_quotation.is_deleted', 0)->distinct()->get();
            $brand = VoluntaryQuotationSkuListing::select('brand_name')->whereIn('div_id',explode(',',Session::get("division_id")))->distinct()->orderBy('brand_name','ASC')->get();
        }else{
           // $institute = VoluntaryQuotation::join('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')->where('voluntary_quotation_sku_listing.'.$level.'_status',0)->where('voluntary_quotation.current_level', '<=', $level_no)->select('voluntary_quotation.id','hospital_name')->where('year',$year)->where('voluntary_quotation.is_deleted', 0)->distinct()->get();
            $institute = VoluntaryQuotation::select('voluntary_quotation.*','voluntary_quotation_sku_listing.'.strtolower(Session::get("level")).'_status as status_vq', 'voluntary_quotation_sku_listing.deleted_by as deleted_by', \DB::raw('(CASE  WHEN voluntary_quotation.parent_vq_id != "0" THEN (SELECT COUNT(*) FROM voluntary_quotation as vq1 where vq1.parent_vq_id = voluntary_quotation.parent_vq_id AND vq1.id <= voluntary_quotation.id) ELSE "0"  END) AS revision_count'))
            ->where('current_level','>=',preg_replace('/[^0-9.]+/', '', Session::get("level")))->where('year',$year)
            ->leftJoin('voluntary_quotation_sku_listing','voluntary_quotation_sku_listing.vq_id','=','voluntary_quotation.id')
            ->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','voluntary_quotation.id')
            ->whereIn('voluntary_quotation_sku_listing.div_id',explode(',',Session::get("division_id")))
            ->where('voluntary_quotation_sku_listing.is_deleted','==',0)
            ->where('voluntary_quotation.is_deleted', 0)
            ->where('institution_division_mapping.employee_code',Session::get("emp_code"))
            ->distinct()->get();
            $brand = VoluntaryQuotationSkuListing::select('brand_name')->whereIn('div_id',explode(',',Session::get("division_id")))->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','voluntary_quotation_sku_listing.vq_id')->where('voluntary_quotation_sku_listing.is_deleted','==',0)->where('institution_division_mapping.employee_code',Session::get("emp_code"))->distinct()->orderBy('brand_name','ASC')->get();
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
        }

        if($request['btnValue']=='All institution'){
            if(preg_replace('/[^0-9.]+/', '', Session::get("level"))>2){
                $data = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.*','voluntary_quotation.hospital_name')->join('voluntary_quotation','voluntary_quotation.id','=','voluntary_quotation_sku_listing.vq_id')->where('voluntary_quotation.current_level', '>=', $level_no)->where('brand_name',$request['brandName'])->where('voluntary_quotation.is_deleted', 0)->where('year',$year)->where('voluntary_quotation_sku_listing.is_deleted',0)->distinct()->get();
            }else{
                $data = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.*','voluntary_quotation.hospital_name','voluntary_quotation.current_level')->join('voluntary_quotation','voluntary_quotation.id','=','voluntary_quotation_sku_listing.vq_id')->where('voluntary_quotation_sku_listing.brand_name',$request['brandName'])->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','voluntary_quotation_sku_listing.vq_id')->where('voluntary_quotation_sku_listing.is_deleted','==',0)->where('institution_division_mapping.employee_code',Session::get("emp_code"))->where('voluntary_quotation.is_deleted', 0)->where('year',$year)->distinct()->get();
            }
            
            return $data;
        }else{
            $data2 = implode(',',$request['institute']);
            $data3 = explode(',',$data2);
            $data = VoluntaryQuotationSkuListing::select('voluntary_quotation_sku_listing.*','voluntary_quotation.hospital_name','voluntary_quotation.current_level')->join('voluntary_quotation','voluntary_quotation.id','=','voluntary_quotation_sku_listing.vq_id')
            ->whereIn('vq_id',$data3)->where('brand_name',$request['brandName'])->where('year',$year)->get();
            return $data;
        }
    }

}
