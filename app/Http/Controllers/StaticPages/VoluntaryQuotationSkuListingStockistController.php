<?php

namespace App\Http\Controllers\StaticPages;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\VqListingController;
use App\Exports\SkuListExport;
use App\Jobs\ApproveVq;
use App\Models\JwtToken;
use App\Models\Stockist_master;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\VoluntaryQuotationSkuListingStockist;
use App\Models\IgnoredInstitutions;
use App\Models\DiscountMarginMaster;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Excel as BaseExcel;
use Excel;
use Session;
use DB;
set_time_limit(0);
class VoluntaryQuotationSkuListingStockistController extends Controller
{
    
    public function paymentMode($id){
        $vq_id_Session_data = Session::get("paymode_vq_ids");
        $edit_paymode_vq_id_listing = Session::get("edit_paymode_vq_id_listing");
        // print_r($vq_id_Session_data);die;

        $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $stockists = Stockist_master::where('institution_code', data_get($vq, 'institution_id'))->where('stockist_type_flag', 1)->select('id', 'stockist_name', 'stockist_code')->get();
        $sku = VoluntaryQuotationSkuListing::where('vq_id', $id)->where('is_deleted', 0)->select('id','item_code')->get();

        $vqslStockistExists = VoluntaryQuotationSkuListingStockist::where('vq_id', $id)->exists();

        /*if($vq->parent_vq_id !=0){
            $revision_count = VoluntaryQuotation::where('parent_vq_id',$vq->parent_vq_id)->where('id','<=',$id)->where('is_deleted', 0)->count();

        }else{  
            $revision_count="0";
        }*/
        $revision_count = $vq->rev_no;

        $data = [];//added on 03052024 for use laravel batch processing
        DB::beginTransaction();
        if(!$vqslStockistExists){
            try {
                foreach($stockists as $stk){
                    foreach($sku as $s){
                        $data[] = [
                            'vq_id' => $id,
                            'sku_id' => $s->id,
                            'item_code' => $s->item_code,
                            'stockist_id' => $stk->id,
                            'parent_vq_id' => data_get($vq, 'parent_vq_id'),
                            'revision_count' => $revision_count,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];//added on 03052024 for use laravel batch processing
                        /*$vqsl_stockist = new VoluntaryQuotationSkuListingStockist;
                        $vqsl_stockist->vq_id = $id;
                        $vqsl_stockist->sku_id = $s->id;
                        $vqsl_stockist->item_code = $s->item_code;
                        $vqsl_stockist->stockist_id = $stk->id;
                        $vqsl_stockist->parent_vq_id = data_get($vq, 'parent_vq_id');
                        $vqsl_stockist->revision_count = $revision_count;
                        $vqsl_stockist->save();*///commented on 03052024 due to performance issue 
                    }
                }
                // added to optimise the skulisting table insert starts
                $chunkSize = 100;
                foreach (array_chunk($data, $chunkSize) as $chunk) {
                    VoluntaryQuotationSkuListingStockist::insert($chunk);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Batch insert failed: " . $e->getMessage());
            }
            // added to optimise the skulisting table insert ends
        }

        $vqslStockistData = VoluntaryQuotationSkuListingStockist::join('voluntary_quotation_sku_listing', 'voluntary_quotation_sku_listing.id', '=', 'voluntary_quotation_sku_listing_stockist.sku_id')
            ->select('voluntary_quotation_sku_listing_stockist.id', 'voluntary_quotation_sku_listing_stockist.payment_mode', 'voluntary_quotation_sku_listing_stockist.sku_id', 'voluntary_quotation_sku_listing_stockist.vq_id', 'voluntary_quotation_sku_listing_stockist.net_discount_percent', 'voluntary_quotation_sku_listing.div_name', 'voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.item_code', 'voluntary_quotation_sku_listing.brand_name', 'voluntary_quotation_sku_listing.mother_brand_name', 'voluntary_quotation_sku_listing.hsn_code', 'voluntary_quotation_sku_listing.applicable_gst', 'voluntary_quotation_sku_listing.pack', 'voluntary_quotation_sku_listing.last_year_percent','voluntary_quotation_sku_listing.last_year_rate','voluntary_quotation_sku_listing.last_year_mrp', 'voluntary_quotation_sku_listing.discount_percent', 'voluntary_quotation_sku_listing.mrp', 'voluntary_quotation_sku_listing.ptr', 'voluntary_quotation_sku_listing.discount_rate', 'voluntary_quotation_sku_listing.mrp_margin', 'voluntary_quotation_sku_listing.type', 'voluntary_quotation_sku_listing.composition', 'stockist_master.stockist_name','stockist_master.id as stockist_id')
            ->join('stockist_master', 'stockist_master.id', '=', 'voluntary_quotation_sku_listing_stockist.stockist_id')
            ->where('voluntary_quotation_sku_listing_stockist.vq_id', $id)
            ->where('voluntary_quotation_sku_listing.is_deleted',0)
            ->where('voluntary_quotation_sku_listing_stockist.is_deleted',0)
	    ->where('stockist_type_flag', 1)
            ->get();

        $flag = VoluntaryQuotationSkuListingStockist::where(['vq_id' => $id, 'payment_mode' => null])->exists();

        $DiscountMargin = DiscountMarginMaster::pluck('discount_margin', 'item_code')->toArray();
        // print_r($DiscountMargin);die;
        $data['DiscountMargin_datas'] = $DiscountMargin;
        $data['vq_data'] = $vq;
        $data['details'] = $vqslStockistData;
        $data['stockists'] = $stockists;
        $data['payModeUpdatedForAllSku'] = !$flag;

        return view('frontend.Initiator.payment_mode_details', compact('data'));
        
    }

    public function editPaymentMode($id){
        $year = $this->getFinancialYear(date('Y-m-d'),"Y");
        $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        if($vq->parent_vq_id == 0){
            $allVq = VoluntaryQuotation::where('parent_vq_id', $id)->where('is_deleted', 0)->get();
        }else{
            $allVq = VoluntaryQuotation::where('parent_vq_id', $vq->parent_vq_id)->orWhere('id', $vq->parent_vq_id)->where('is_deleted', 0)->get();
        }

        $edit_paymode_vq_id = Session::get('edit_paymode_vq_id_listing');
        /** Get IgnoredInstitutions table data's by VoluntaryQuotation institution_id */
        $ignoredinstitutions = IgnoredInstitutions::where('parent_institution_id', data_get($vq, 'institution_id'))->select('parent_institution_id','institution_id')->get();
        // $ignoredinstitutions = IgnoredInstitutions::where('institution_id', data_get($vq, 'institution_id'))->select('parent_institution_id','institution_id')->get();
        if(!empty($ignoredinstitutions) && empty($edit_paymode_vq_id)):
            $listing_id = [];
            foreach($ignoredinstitutions as $ig_inst):
                /** Check data there or not in VoluntaryQuotation table query */
                $ignoreinstitution_vq = VoluntaryQuotation::where('institution_id', $ig_inst->institution_id)->where('vq_status', 1)->where('year', $year)->where('is_deleted', 0)->select('id','institution_id')->first();
                if(!empty($ignoreinstitution_vq)):
                    $listing_id[] = $ignoreinstitution_vq->id;
                endif;
            endforeach;
            $filter_listing_ids = array_filter($listing_id, function($value) {
                return $value !== '';
            });
            Session::put('edit_paymode_vq_id_listing', $filter_listing_ids);
        endif;

        /*$stockists = Stockist_master::where('institution_code', data_get($vq, 'institution_id'))->where('stockist_type_flag', 1)->select('id', 'stockist_name')->get();*/  
        $stockists = Stockist_master::where('institution_code', data_get($vq, 'institution_id'))->where('stockist_type_flag', 1)->select('id', 'stockist_name','stockist_code'); 
        // filter only the added stockist from add delete stockist page starts
        if(Session::has("stockist_id_arr"))
        {
            $stockists = $stockists->whereIn('id',Session::get("stockist_id_arr"))->get();

        }else
        {
            $stockists = $stockists->get();
        }
        // filter only the added stockist from add delete stockist page ends
        $idArr = [];

        $insArr = [];

        foreach($allVq as $v){
            array_push($idArr, data_get($v, 'id'));

            array_push($insArr, data_get($v, 'institution_id'));
        }

        $sku = VoluntaryQuotationSkuListing::join('voluntary_quotation', 'voluntary_quotation.id', '=', 'voluntary_quotation_sku_listing.vq_id')->whereIn('vq_id', $idArr)->select('voluntary_quotation.id as vq_id', 'voluntary_quotation_sku_listing.id as sku_id')->get();

        // foreach($sku as $s){

        //     if(!VoluntaryQuotationSkuListingStockist::where('sku_id', data_get($s, 'sku_id'))->exists()){
        //         $v = VoluntaryQuotation::where('id', data_get($s, 'vq_id'))->first();

        //         if($v->parent_vq_id !=0){
        //             $revision_count = VoluntaryQuotation::where('parent_vq_id',$v->parent_vq_id)->where('id','<=', data_get($v, 'id'))->count();
        
        //         }else{
        //             $revision_count="0";
        //         }
                
        //         foreach($stockists as $stk){
        //             $vqsl_stockist = new VoluntaryQuotationSkuListingStockist;
        //             $vqsl_stockist->vq_id = $s->vq_id;
        //             $vqsl_stockist->sku_id = $s->sku_id;
        //             $vqsl_stockist->stockist_id = $stk->id;
        //             $vqsl_stockist->parent_vq_id = $v->parent_vq_id;
        //             $vqsl_stockist->revision_count = $revision_count;
        //             $vqsl_stockist->save();
        //         }
        //     }

        // }
        //till here done

        array_push($idArr, $id);

        array_push($insArr, $vq->institution_id);

        $maxRevSubquery = DB::table('voluntary_quotation_sku_listing as s')
            ->select(DB::raw('MAX(v2.rev_no) AS max_rev_no'), 's.item_code', 'v2.institution_id')
            ->leftJoin('voluntary_quotation as v2', 'v2.id', '=', 's.vq_id')
            ->where('v2.year', $year)
            ->where('s.is_deleted', 0)
            ->where('v2.vq_status', 1)
            ->where('v2.is_deleted', 0)
            ->where('v2.institution_id', $vq->institution_id)
            ->groupBy('s.item_code');
        $vqslStockistData = DB::table('voluntary_quotation_sku_listing AS parent_sku')
            ->select('vq.id as vq_id', 'hospital_name', 'vq.institution_id', 'institution_key_account', 'sap_code', 'city', 'zone', 'YEAR', 
             'institution_zone', 'institution_region', 'parent_sku.item_code', 'sap_itemcode', 'brand_name', 'mother_brand_name', 
             'hsn_code', 'applicable_gst', 'type', 'div_name', 'div_id', 'pack', 'last_year_ptr', 'last_year_percent', 
             'last_year_rate', 'last_year_mrp', 'mrp', 'ptr', 'parent_sku.discount_percent', 
             'parent_sku.discount_rate', 'parent_sku.mrp_margin', 'rev_no as revision_count','composition','vqsls.id','vqsls.payment_mode','vqsls.sku_id', 'vqsls.net_discount_percent' ,'stockist_master.stockist_name' ,'stockist_master.id as stockist_id')
            ->selectRaw('"1" AS new')
            ->leftJoin('voluntary_quotation AS vq', 'vq.id', '=', 'parent_sku.vq_id')
            ->join('voluntary_quotation_sku_listing_stockist AS vqsls', 'parent_sku.id', '=', 'vqsls.sku_id')
            ->join('stockist_master', 'stockist_master.id', '=', 'vqsls.stockist_id')
            /*->join('z_max_rev AS max_rev', function ($join) {
                $join->on('max_rev.item_code', '=', 'parent_sku.item_code')
                ->on('max_rev.max_rev_no', '=', 'vq.rev_no');
            })*///added on 14052024 for performance issue
            /*->join('z_latest_rev_ptr AS max_rev', function ($join) {
                $join->on('max_rev.vq_id', '=', 'parent_sku.vq_id')
                ->on('max_rev.item_code', '=', 'parent_sku.item_code');
            })*///commented on 14052924 for performance improve
            ->joinSub($maxRevSubquery, 'max_rev', function ($join) use($vq) {
                $join->on('parent_sku.item_code', '=', 'max_rev.item_code')
                    ->where('vq.institution_id',  $vq->institution_id)
                    ->on('vq.rev_no', '=', 'max_rev.max_rev_no');
            })
            ->where('vq.year', $year)
            ->where('vq.institution_id',   $vq->institution_id)//changed the wherein to where and added institution_id 
            //->where('max_rev.institution_id',   $vq->institution_id)//added institution_id to improve performance
            ->where('parent_sku.is_deleted', 0)
            ->where('vq.is_deleted',0)
            ->where('vqsls.is_deleted', 0)
            ->where('stockist_master.stockist_type_flag', 1);
            /*->where('vq.rev_no', function($subquery)  use ($year) {
                $subquery->select(DB::raw('MAX(v.rev_no)'))
                    ->from('voluntary_quotation_sku_listing AS s')
                    ->leftJoin('voluntary_quotation AS v', 'v.id', '=', 's.vq_id')
                    ->where('v.year', $year)
                    ->where('s.is_deleted', 0)
                    ->where('v.vq_status', 1)
                    ->whereColumn('v.institution_id', 'vq.institution_id')
                    ->whereColumn('s.item_code', 'parent_sku.item_code');
            })*/
        //->get();
        // filter only the added stockist from add delete stockist page starts
        if(Session::has("stockist_id_arr"))
        {
            $vqslStockistData = $vqslStockistData->whereIn('stockist_master.id',Session::get("stockist_id_arr"))->get();

        }else
        {
            $vqslStockistData = $vqslStockistData->get();
        }
        // filter only the added stockist from add delete stockist page ends
         //dd($vqslStockistData);
        $flag = VoluntaryQuotationSkuListingStockist::where(['vq_id' => $id, 'payment_mode' => null])->exists();

        $DiscountMargin = DiscountMarginMaster::pluck('discount_margin', 'item_code')->toArray();
        $data['DiscountMargin_datas'] = $DiscountMargin;
        $data['vq_data'] = $vq;
        $data['details'] = $vqslStockistData;
        $data['stockists'] = $stockists;
        $data['check_stockist_id_arr'] = (Session::has("stockist_id_arr"))? 'add_stockist' : 'edit_paymode';
        $data['payModeUpdatedForAllSku'] = !$flag;

        return view('frontend.Initiator.edit_payment_mode_details', compact('data'));
    }

    public function saveSkuPaymentMode(Request $request){
        if(Session::get('idArr')):
            Session::forget('idArr');
        endif;
        $vq_id = data_get($request, 'vq_id');
        $changePayModeData = json_decode(data_get($request, 'data'));
        // $inputMargin = 10;
        $idArr = [];
        $updatedDiscountData = [];
        foreach($changePayModeData as $data){
            
            $id = data_get($data, 'id');
            $payMode = data_get($data, 'payMode');
            $netDiscountRateToStockist = data_get($data, 'netDiscPercent');
            array_push($idArr, $id);

            // if($payMode == 'DM'){

            //     $skuId = data_get($data, 'sku_id');
            //     $skuDetails = VoluntaryQuotationSkuListing::find($skuId)->select('ptr', 'discount_percent')->first();
            //     $ptr = data_get($skuDetails, 'ptr');
            //     $inputDiscRate = data_get($skuDetails, 'discount_percent');
            //     $discountamt = $ptr - (($ptr * $inputDiscRate) / 100);

            //     $marginamt = $discountamt * $inputMargin / 100;
            //     $nrv = $discountamt - $marginamt;
            //     $netDiscountRateToStockist = ($ptr - $nrv) / $ptr * 100;
            //     dd($netDiscountRateToStockist);
            // }elseif($payMode == 'CN'){

            //     $netDiscountRateToStockist = $inputMargin;
            // }

            array_push($updatedDiscountData, $netDiscountRateToStockist);

            //VoluntaryQuotationSkuListingStockist::find($id)->update(['payment_mode' => $payMode, 'net_discount_percent' => $netDiscountRateToStockist]);//commented on 04052024 to update in approve vq job
        }
        Session::put('changePayModeData', $changePayModeData);//added on 04052024 to update in approve vq job
        Session::put('idArr', $idArr);//added on 15052024 for activity log issue
        Session::put('modeofscreen', 'Paymode');//added on 28042025
        // dd(Excel::download(new SkuListExport($idArr), "test.xlsx"));
        $vqData = VoluntaryQuotation::where('id', $vq_id)->where('is_deleted', 0)->first();
        if(data_get($vqData, 'vq_status') == 1){
            Session::put('modeofscreen', 'EditPaymode');//added on 28042025
            $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();

            // Code to update in oracle table starts here
            //ApproveVq::dispatch($vq_id, $jwt->jwt_token, $idArr, Session::get('changePayModeData'));//commented on 090924 approve vq to be called only when send quotation button is clicked in poc page.

            //  Code to update in oracle table ends here

        //     $data = [
        //         'hospitalName' => data_get($vqData, 'hospital_name'),
        //         'subject' => "Changed payment mode for hospital ".data_get($vqData, 'hospital_name')
        //     ];
        //     $skuListingExcel = Excel::raw(new SkuListExport($idArr), BaseExcel::XLSX);

        //     Mail::send('frontend.Initiator.payModeChangesEmail', $data, function($message)use($data, $skuListingExcel) {
        //         $message->to('sumeet@noesis.tech')
        //         ->cc('bhagyeshvijay.joshi@sunpharma.tech')
        //         // ->replyTo('idap.support@sunpharma.com')
        //         ->subject($data['subject'])
        //         ->attachData($skuListingExcel, "test.xlsx");
        //         });
        }

        return response()->json([
            'success'=>true, 
            'message' => "Payment mode has been updated for the selected SKU's.",
            'data' => $updatedDiscountData
        ]);
        
    } 

}
