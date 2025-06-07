<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VoluntaryQuotation;
use App\Models\VoluntaryQuotationSkuListing;
use App\Models\VoluntaryQuotationSkuListingStockist;
use App\Http\Controllers\Api\VqListingController;
use App\Models\DiscountMarginMaster;
use App\Models\Stockist_master;
use GuzzleHttp\Client as GuzzleClient;
use DB;
use App\Models\JwtToken;

class ManuallySendQuotation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manual_sendquotation:update {current_level} {revision_no}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $current_level = $this->argument('current_level'); 
        $revision_no =  $this->argument('revision_no');
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $vqInstitutions = VoluntaryQuotation::select('*')->where('current_level', $current_level)->where('rev_no', $revision_no)->where('year',$year)->where('is_deleted', 0)->get();
        // dd($vqInstitutions);
        print_r("init ");
        foreach($vqInstitutions as $vq_value){
            print_r($vq_value->id);
            print_r(" - ");
            print_r($vq_value->institution_id);
            print_r(" - ");
            $vq = VoluntaryQuotation::where('id',$vq_value->id)->where('is_deleted', 0)->first();
            
            $stockists = Stockist_master::where('institution_code', data_get($vq, 'institution_id'))->where('stockist_type_flag', 1)->select('id', 'stockist_name', 'stockist_code')->get();
            
            $sku = VoluntaryQuotationSkuListing::where('vq_id', $vq_value->id)->where('is_deleted', 0)->get();

            $vqslStockistExists = VoluntaryQuotationSkuListingStockist::where('vq_id', $vq_value->id)->exists();

            $revision_count = $vq_value->rev_no;

            $data = [];
            
            if(!$vqslStockistExists){
                try {
                    foreach($stockists as $stk){
                        foreach($sku as $s){
                            $DiscountMargin = DiscountMarginMaster::where('item_code', $s->item_code)->get()->toArray();
                            $inputMargin = ($DiscountMargin)? (($DiscountMargin[0]['discount_margin'])? $DiscountMargin[0]['discount_margin'] : 10) : 10 ;
                            $ptr = $s->ptr;
                            
                            $discount_percent = $s->discount_percent;
                            if($ptr == 0)
                            {
                                // echo '|>out<|';
                                $netDiscountRateToStockist = 0;
                            }
                            else
                            {
                                // echo '|>in<|';
                                $discountamt = $ptr - (($ptr * $discount_percent) / 100);
                                $marginamt = $discountamt * $inputMargin / 100;
                                $nrv = $discountamt - $marginamt;
                                $netDiscountRateToStockist = ($ptr - $nrv) / $ptr * 100;
                                $netDiscountRateToStockist = number_format((float)$netDiscountRateToStockist, 2, '.', '');
                            }

                            // print_r("PTR");
                            // print_r(" : ");
                            // print_r($s->ptr);

                            // print_r("Item Code");
                            // print_r(" : ");
                            // print_r($s->item_code);

                            // print_r("Net Discount percent");
                            // print_r(" : ");
                            // print_r($netDiscountRateToStockist);


                            $data[] = [
                                'vq_id' => $vq_value->id,
                                'sku_id' => $s->id,
                                'item_code' => $s->item_code,
                                'stockist_id' => $stk->id,
                                'payment_mode' => 'DM',
                                'parent_vq_id' => data_get($vq, 'parent_vq_id'),
                                'net_discount_percent' => $netDiscountRateToStockist,
                                'revision_count' => $revision_count,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                    // added to optimise the skulisting table insert starts
                    $chunkSize = 100;
                    foreach (array_chunk($data, $chunkSize) as $chunk) {
                        VoluntaryQuotationSkuListingStockist::insert($chunk);
                    }
                    print_r("Insert SKU Listing Stockist");
                    print_r(" - ");
                    $updation = VoluntaryQuotation::where('id',$vq_value->id)->where('is_deleted', 0)->update(['current_level' => 7, 'vq_status'=>1]);//added on 21032025 for updating the vq status earlier in vqlistingcontroller
                    print_r("Update Status");
                    print_r(" - ");
                } catch (\Exception $e) {
                    Log::error("Batch insert failed: " . $e->getMessage());
                }
                // added to optimise the skulisting table insert ends
            }
        }
        $this->info('VoluntaryQuotationSkuListingStockist inserted successfully.');
    }
}
