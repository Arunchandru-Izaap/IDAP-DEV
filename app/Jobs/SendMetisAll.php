<?php

namespace App\Jobs;
use App\Models\Institution;
use App\Models\ceilingMaster;
use App\Models\LastYearPrice;
use App\Models\VoluntaryQuotation;
use App\Models\PocMaster;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\ActivityTracker;
use App\Models\Employee;
use App\Http\Controllers\Api\VqListingController;
use Maatwebsite\Excel\Excel as BaseExcel;
use DB;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


use Illuminate\Support\Facades\Mail;
use App\Models\Signature;
use PDF;
use App\Exports\InitiatorExport;
use App\Models\IgnoredInstitutions;
use App\Models\VoluntaryQuotationSkuListingStockist;
use Excel;
class SendMetisAll implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $jwt;
    public $timeout = 999999;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($jwt)
    {
        //
        $this->jwt = $jwt;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $vq_listing_controller = new VqListingController;
        
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $vqs = VoluntaryQuotation::where('year',$year)->where('vq_status',1)->whereNotIn('id',['1','2','3','4','5','7','8','9','19','21','22','23','24','25','29','31','32','35','41','42','50','55','56','57','60','61','62','63','67','69','72','75','79','80','82','83','86','88','89','91','93','94','95','96','97','101','103','104','105','106','107','108','109','110','111','112','113','118','119','122','123','125','126','128','129','130','134','149','150','158','163','164','165','166','167','168','169','170','171','172','174','176','177','178','179','180','181','183','184','185','186','187','188','190','191','193','198','199','200','204','205','208','209','210','211','212','214','215','216','220','228','231','233','236','245','262','265','267','268','269','270','271','272','273','274','275','283','284','285','287','288','289','296','307','315','316','317','318','322','323','324','325','326','331','334','337','338','339','340','346','347','348','349','354','355','356','357','359','360','366','368','369','370','371','372','373','374','375','377','379','380','381','382','383','385','386','388','389','390','391','392','394','395','396','397','398','399','401','402','404','406','407','408','409','429','430','431','432','433','434','440','442','446','451','454','456','459','460','461','465','466','469','473','475','476','480','482','483','484','486','493','495','496','497','498','500','503','505','514','515','516','521','523','524','525','526','528','529','535','536','538','539','541','542','545','548','551','553','554','556','559','563','565','567','570','571','573','574','575','577','579','580','581','582','583','584','587','592','593','595','596','597','598','599','600','601','604','605','606','609','610','611','612','613','615','618','621','622','623','624','625','626','627','630','632','635','636','637','639','643','644','645','647','648'])->where('is_deleted', 0)->get();
        foreach($vqs as $vq_single){
            $id = $vq_single->id;
            $data = array();
            $vq = VoluntaryQuotation::where('id',$id)->where('is_deleted', 0)->first();

            // $listing_data = VoluntaryQuotationSkuListing::where('vq_id',$id)->where('is_deleted',0)->get();
            $listing_data = VoluntaryQuotationSkuListingStockist::join('voluntary_quotation_sku_listing', 'voluntary_quotation_sku_listing.id', '=', 'voluntary_quotation_sku_listing_stockist.sku_id')
            ->select('voluntary_quotation_sku_listing_stockist.payment_mode', 'voluntary_quotation_sku_listing_stockist.net_discount_percent', 'voluntary_quotation_sku_listing.div_id', 'voluntary_quotation_sku_listing.item_code',  'voluntary_quotation_sku_listing.discount_percent', 'voluntary_quotation_sku_listing.discount_rate', 'voluntary_quotation_sku_listing.mrp', 'stockist_master.stockist_code')
            ->join('stockist_master', 'stockist_master.id', '=', 'voluntary_quotation_sku_listing_stockist.stockist_id')
            ->where('voluntary_quotation_sku_listing_stockist.vq_id', $id)
            ->where('is_deleted', 0)
            ->get();
            
            
            if($vq->parent_vq_id !=0){
                $data['revision_count']=VoluntaryQuotation::where('parent_vq_id',$vq->parent_vq_id)->where('id','<=',$vq->id)->where('is_deleted', 0)->count();
            
            }else{
                $data['revision_count']=0;
            }
            $send_json = array();
            $date=date_create($vq->created_at);
            if (date_format($date,"m") >= 4) {//On or After April (FY is current year - next year)
                $financial_year = (date_format($date,'Y')) . '-' . (date_format($date,'y')+1);
            } else {//On or Before March (FY is previous year - current year)
                $financial_year = (date_format($date,'Y')-1) . '-' . date_format($date,'y');
            }
            $send_json['fin_year'] = $financial_year;
            $send_json['institute_code'] = $vq->institution_id;
            $send_json['vq_id'] = $vq->id;
            $send_json['quotation_type'] = 'VQ';
            $send_json['revision_number'] = $data['revision_count'];
            $send_json['quotation_start_date'] = $vq->contract_start_date;
            $send_json['quotation_end_date'] = $vq->contract_end_date;
            
            //Update last year data with current year
            foreach($listing_data as $single_data){
               
                $sku_arr['item_code'] = $single_data['item_code'];
                $sku_arr['div_code'] = $single_data['div_id'];
                $sku_arr['discount_percent'] = $single_data['discount_percent'];
                $sku_arr['discount_rate'] = $single_data['discount_rate'];
                $sku_arr['stockist_code'] = $single_data['stockist_code'];
                $sku_arr['payment_mode'] = $single_data['payment_mode'];
                $sku_arr['net_discount_percent'] = $single_data['net_discount_percent'];
            
                $send_json['sku'][]=$sku_arr;
            }
            $lastYear = ActivityTracker::updateOrCreate(['vq_id' => $vq->id,'emp_code' => '1' ,'type' => 'vq_metis_object'], [ 'activity' => json_encode($send_json), 'updated_at' => date('Y-m-d H:i:s'),'created_at' => date('Y-m-d H:i:s') ]);
            //$vq_listing_controller->activityTracker($vq->id,'1',json_encode($send_json), 'vq_metis_object');
            $headers = [
                'Content-Type' => 'application/json',
                'AccessToken' => 'key',
                'Authorization' => 'Bearer '.$this->jwt,
            ];
            
            $client = new GuzzleClient([
                'headers' => $headers,
                'verify' => false
            ]);
            
            $body = json_encode($send_json);
            
            $r = $client->request('POST', env('API_URL').'/api/QuotationPush', [
                'body' => $body
            ]);
            $response = $r->getBody()->getContents();
            $data = json_decode($response);
            // $vq_listing_controller->activityTracker($vq->id,'1',$response, 'vq_metis_response');
            $lastYear = ActivityTracker::updateOrCreate(['vq_id' => $vq->id,'emp_code' => '1' ,'type' => 'vq_metis_response'], [ 'activity' => $response, 'updated_at' => date('Y-m-d H:i:s'),'created_at' => date('Y-m-d H:i:s') ]);

            // Check if Child VQ are present in ignored_institutions table
            $ignoredVq = IgnoredInstitutions::where('parent_institution_id', $vq->institution_id)->get();
            foreach($ignoredVq as $child){

                $send_json['institute_code'] = $child->institution_id;
                $send_json['vq_id'] = $vq->id.'_'.$child->id;

                $body = json_encode($send_json);
            
                $r = $client->request('POST', env('API_URL').'/api/QuotationPush', [
                    'body' => $body
                ]);
                $response = $r->getBody()->getContents();
            } 


        }
        
        return 0;
    }
    
}
