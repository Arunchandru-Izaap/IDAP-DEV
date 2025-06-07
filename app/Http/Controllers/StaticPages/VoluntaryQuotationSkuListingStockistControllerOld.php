<?php

namespace App\Http\Controllers\StaticPages;

use App\Http\Controllers\Controller;
use App\Exports\SkuListExport;
use App\Jobs\ApproveVq;
use App\Models\JwtToken;
use App\Models\Stockist_master;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\VoluntaryQuotationSkuListingStockist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Excel as BaseExcel;
use Excel;
use Session;
class VoluntaryQuotationSkuListingStockistController extends Controller
{
    
    public function paymentMode($id){
        $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        $stockists = Stockist_master::where('institution_code', data_get($vq, 'institution_id'))->select('id', 'stockist_name')->get();
        $sku = VoluntaryQuotationSkuListing::where('vq_id', $id)->select('id','item_code')->get();

        $vqslStockistExists = VoluntaryQuotationSkuListingStockist::where('vq_id', $id)->exists();

        if($vq->parent_vq_id !=0){
            $revision_count = VoluntaryQuotation::where('parent_vq_id',$vq->parent_vq_id)->where('id','<=',$id)->where('is_deleted', 0)->count();

        }else{  
            $revision_count="0";
        }

        if(!$vqslStockistExists){
            foreach($stockists as $stk){
                foreach($sku as $s){
                    $vqsl_stockist = new VoluntaryQuotationSkuListingStockist;
                    $vqsl_stockist->vq_id = $id;
                    $vqsl_stockist->sku_id = $s->id;
                    $vqsl_stockist->item_code = $s->item_code;
                    $vqsl_stockist->stockist_id = $stk->id;
                    $vqsl_stockist->parent_vq_id = data_get($vq, 'parent_vq_id');
                    $vqsl_stockist->revision_count = $revision_count;
                    $vqsl_stockist->save();
                }
            }
        }

        $vqslStockistData = VoluntaryQuotationSkuListingStockist::join('voluntary_quotation_sku_listing', 'voluntary_quotation_sku_listing.id', '=', 'voluntary_quotation_sku_listing_stockist.sku_id')
            ->select('voluntary_quotation_sku_listing_stockist.id', 'voluntary_quotation_sku_listing_stockist.payment_mode', 'voluntary_quotation_sku_listing_stockist.sku_id', 'voluntary_quotation_sku_listing_stockist.vq_id', 'voluntary_quotation_sku_listing_stockist.net_discount_percent', 'voluntary_quotation_sku_listing.div_name', 'voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.item_code', 'voluntary_quotation_sku_listing.brand_name', 'voluntary_quotation_sku_listing.mother_brand_name', 'voluntary_quotation_sku_listing.hsn_code', 'voluntary_quotation_sku_listing.applicable_gst', 'voluntary_quotation_sku_listing.pack', 'voluntary_quotation_sku_listing.last_year_percent','voluntary_quotation_sku_listing.last_year_rate','voluntary_quotation_sku_listing.last_year_mrp', 'voluntary_quotation_sku_listing.discount_percent', 'voluntary_quotation_sku_listing.mrp', 'voluntary_quotation_sku_listing.ptr', 'voluntary_quotation_sku_listing.discount_rate', 'voluntary_quotation_sku_listing.mrp_margin', 'voluntary_quotation_sku_listing.type', 'voluntary_quotation_sku_listing.composition', 'stockist_master.stockist_name')
            ->join('stockist_master', 'stockist_master.id', '=', 'voluntary_quotation_sku_listing_stockist.stockist_id')
            ->where('voluntary_quotation_sku_listing_stockist.vq_id', $id)
            ->where('is_deleted',0)
            ->get();

        $flag = VoluntaryQuotationSkuListingStockist::where(['vq_id' => $id, 'payment_mode' => null])->exists();

        $data['vq_data'] = $vq;
        $data['details'] = $vqslStockistData;
        $data['stockists'] = $stockists;
        $data['payModeUpdatedForAllSku'] = !$flag;

        return view('frontend.Initiator.payment_mode_details', compact('data'));
        
    }

    public function editPaymentMode($id){

        $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();
        if($vq->parent_vq_id == 0){
            $allVq = VoluntaryQuotation::where('parent_vq_id', $id)->where('is_deleted', 0)->get();
        }else{
            $allVq = VoluntaryQuotation::where('parent_vq_id', $vq->parent_vq_id)->orWhere('id', $vq->parent_vq_id)->where('is_deleted', 0)->get();
        }

        $stockists = Stockist_master::where('institution_code', data_get($vq, 'institution_id'))->select('id', 'stockist_name')->get();   

        $idArr = [];

        foreach($allVq as $v){
            array_push($idArr, data_get($v, 'id'));
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

        $vqslStockistData = VoluntaryQuotationSkuListingStockist::join('voluntary_quotation_sku_listing', 'voluntary_quotation_sku_listing.id', '=', 'voluntary_quotation_sku_listing_stockist.sku_id')
            ->select('voluntary_quotation_sku_listing_stockist.id', 'voluntary_quotation_sku_listing_stockist.payment_mode', 'voluntary_quotation_sku_listing_stockist.sku_id', 'voluntary_quotation_sku_listing_stockist.vq_id', 'voluntary_quotation_sku_listing_stockist.net_discount_percent', 'voluntary_quotation_sku_listing_stockist.revision_count as revision_count','voluntary_quotation_sku_listing.div_name', 'voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.item_code', 'voluntary_quotation_sku_listing.brand_name', 'voluntary_quotation_sku_listing.mother_brand_name', 'voluntary_quotation_sku_listing.hsn_code', 'voluntary_quotation_sku_listing.applicable_gst', 'voluntary_quotation_sku_listing.pack', 
            'voluntary_quotation_sku_listing.last_year_rate','voluntary_quotation_sku_listing.last_year_mrp','voluntary_quotation_sku_listing.last_year_percent', 'voluntary_quotation_sku_listing.discount_percent', 'voluntary_quotation_sku_listing.mrp', 'voluntary_quotation_sku_listing.ptr', 'voluntary_quotation_sku_listing.discount_rate', 'voluntary_quotation_sku_listing.mrp_margin', 'voluntary_quotation_sku_listing.type', 'voluntary_quotation_sku_listing.composition', 'stockist_master.stockist_name')
            ->join('stockist_master', 'stockist_master.id', '=', 'voluntary_quotation_sku_listing_stockist.stockist_id')
            ->selectRaw('CASE
		WHEN (
		SELECT COUNT(*) FROM voluntary_quotation_sku_listing_stockist as vqsl left join voluntary_quotation on voluntary_quotation.id = vqsl.vq_id WHERE vqsl.item_code = voluntary_quotation_sku_listing_stockist.item_code and vqsl.stockist_id = voluntary_quotation_sku_listing_stockist.stockist_id and voluntary_quotation.is_deleted=0
		) = voluntary_quotation_sku_listing_stockist.revision_count+1 THEN 1
		ELSE 0
		END as "new"
                        
                ')
            ->whereIn('voluntary_quotation_sku_listing_stockist.vq_id', $idArr)
            ->where('is_deleted',0)
            // ->orderBy('voluntary_quotation_sku_listing_stockist.stockist_id')
            // ->orderBy('voluntary_quotation_sku_listing_stockist.vq_id')
            ->orderBy('voluntary_quotation_sku_listing.item_code')
            ->get();
           
        // dd($vqslStockistData);

        $flag = VoluntaryQuotationSkuListingStockist::where(['vq_id' => $id, 'payment_mode' => null])->exists();

        $data['vq_data'] = $vq;
        $data['details'] = $vqslStockistData;
        $data['stockists'] = $stockists;
        $data['payModeUpdatedForAllSku'] = !$flag;

        return view('frontend.Initiator.edit_payment_mode_details', compact('data'));
    }

    public function saveSkuPaymentMode(Request $request){
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

            VoluntaryQuotationSkuListingStockist::find($id)->update(['payment_mode' => $payMode, 'net_discount_percent' => $netDiscountRateToStockist]);
        }
        // dd(Excel::download(new SkuListExport($idArr), "test.xlsx"));
        $vqData = VoluntaryQuotation::where('id', $vq_id)->where('is_deleted', 0)->first();
        if(data_get($vqData, 'vq_status') == 1){

            $jwt = JwtToken::where('emp_code',Session::get("emp_code"))->first();

            // Code to update in oracle table starts here
            ApproveVq::dispatch($vq_id, $jwt->jwt_token, $idArr);

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
