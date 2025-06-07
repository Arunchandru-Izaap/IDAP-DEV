<?php

namespace App\Jobs;
use App\Models\Institution;
use App\Models\CeilingMaster;
use App\Models\LastYearPrice;
use App\Models\VoluntaryQuotation;
use App\Models\ActivityTracker;
use App\Models\InstitutionDivisionMapping;
use App\Http\Controllers\Api\VqListingController;
use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\Stockist_master;
use App\Models\Employee;
use App\Exports\LatestExport;
use App\Models\PocMaster;
use Maatwebsite\Excel\Excel as BaseExcel;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Excel;
//use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Date;
/*use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;*/

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Session;
/*ini_set('memory_limit', '4G');
ini_set('max_execution_time', 0);*/
class HistoricalReportDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $emp_code;
    protected $emp_type;
    protected $emp_level;
    protected $div_id;

  
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 999999;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($emp_code,$emp_type,$emp_level,$div_id)
    {
        //
       
        $this->emp_code = $emp_code;
        $this->emp_type = $emp_type;
        $this->emp_level = $emp_level;
        $this->div_id = $div_id;
       
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
	//dd("old");
    $vq_listing_controller = new VqListingController;

    $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");  
    $zip_file = 'public/historicalreport'.$this->emp_code.'.zip'; // Name of our archive to download
    
    $zip = new \ZipArchive();
    $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
    // Logic for HO user
    if($this->emp_type == 'ho' || $this->emp_type == 'initiator'){

        // North
        $this->regionGenerator($year,$zip,'NORTH');
        $this->regionGenerator($year,$zip,'SOUTH');
        $this->regionGenerator($year,$zip,'WEST');
        $this->regionGenerator($year,$zip,'EAST');
        $this->regionGenerator($year,$zip,'CENTRAL');

    }
    elseif($this->emp_type == 'poc'){
        $this->pocGenerator($year, $zip);
    }
    elseif($this->emp_type == 'distribution'){
        $this->distributionGenerator($year, $zip);
    }
    elseif($this->emp_type == 'approver'){
        $this->approverGenerator($year, $zip);
    }
    $zip->close();
    }


    function usersGenerator($mergedCollection) {
        foreach ($mergedCollection as $user) {
            yield $user;
        }
    }

    function regionGenerator($year,$zip,$region) {
        $data_original = [];
        $data_child = [];
        $tempFilePath = sys_get_temp_dir() . '/' . $region . '.xlsx';

        // Create the writer instance
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($tempFilePath);

        // Add header row
        $headerStyle = (new StyleBuilder())->setFontBold()->build();
        $reportNameRow = WriterEntityFactory::createRowFromArray(['IDAP - Historical Report'], $headerStyle);
        $dateRow = WriterEntityFactory::createRowFromArray(['Report generated on ' . Date::now()->format('d M Y H:i:s')], $headerStyle);

        $writer->addRow($reportNameRow);
        $writer->addRow($dateRow);

        // Add empty row
        //$writer->addRow(WriterEntityFactory::createRow());

        $headerRow = WriterEntityFactory::createRowFromArray(
            [
                'Hospital Name', 'VQ ID', 'Institution ID', 'Item Code', 'Brand Name', 'Mother Brand Name', 'HSN Code',
                'Applicable GST', 'Last Year Percent', 'Last Year Rate', 'Last Year Margin on MRP', 'MRP',
                'Last Year MRP', 'PTR', 'Discount Percent', 'MRP Margin', 'SAP Itemcode', 'Composition',
                'Type', 'Div Name', 'Pack', 'Discount Rate', 'Rev No'
            ],
            $headerStyle
        );
        $writer->addRow($headerRow);
        $original_list = DB::table('voluntary_quotation_sku_listing AS parent_sku')
            ->select(
                'vq.hospital_name',
                'vq.id as vq_id',
                'vq.institution_id',
                'parent_sku.item_code',
                'parent_sku.brand_name',
                'parent_sku.mother_brand_name',
                'parent_sku.hsn_code',
                'parent_sku.applicable_gst',
                'parent_sku.last_year_percent',
                'parent_sku.last_year_rate',
                \DB::raw('
                    CASE
                        WHEN parent_sku.last_year_mrp IS NOT NULL AND parent_sku.last_year_ptr IS NOT NULL
                        THEN ((parent_sku.last_year_mrp - parent_sku.last_year_ptr) / parent_sku.last_year_mrp) * 100
                        ELSE "-"
                    END AS last_year_margin_on_mrp
                '),
                'parent_sku.mrp',
                'parent_sku.last_year_mrp',
                'parent_sku.ptr',
                'parent_sku.discount_percent',
                'parent_sku.mrp_margin',
                'parent_sku.sap_itemcode',
                'parent_sku.composition',
                'parent_sku.type',
                'parent_sku.div_name',
                'parent_sku.pack',
                'parent_sku.discount_rate',
                
                
                /*\DB::raw('CAST((CASE  WHEN vq.parent_vq_id != "0" THEN (SELECT COUNT(*)+1 FROM voluntary_quotation as vq1 where vq1.parent_vq_id = vq.parent_vq_id AND vq1.id <= vq.id) ELSE 1  END) as UNSIGNED) AS revision_count')*/
                'vq.rev_no'
            )
            ->leftJoin('voluntary_quotation AS vq', 'vq.id','parent_sku.vq_id')
            ->where('vq.year', $year)
            ->where('vq.zone',$region)
            ->where('parent_sku.is_deleted',0)//added on 06082024
            ->where('vq.is_deleted',0)//added on 06082024
            ->when($this->emp_type == 'initiator' && $this->div_id != '', function ($query) {
                return $query->whereIn('parent_sku.div_id', explode(',', $this->div_id));
            })
            ->distinct()
            // ->whereIn('vq.institution_id',['DM1083','IDP0125'])
            ->orderBy('vq.hospital_name','ASC')
            ->orderBy('parent_sku.item_code','ASC');
            /*->chunk(200, function ($rows) use (&$data_original) {
                // Accumulate the data into the $data array
                foreach ($rows as $row) {
                    $data_original[] = $row;
                }
            }
        );*/
            
        $original_list->chunk(10000, function($rows) use ($writer) {
            foreach ($rows as $row) {
                $dataRow = WriterEntityFactory::createRowFromArray([
                    $row->hospital_name, $row->vq_id, $row->institution_id, $row->item_code,
                    $row->brand_name, $row->mother_brand_name, $row->hsn_code, $row->applicable_gst,
                    $row->last_year_percent, $row->last_year_rate, $row->last_year_margin_on_mrp,
                    $row->mrp, $row->last_year_mrp, $row->ptr, $row->discount_percent, $row->mrp_margin,
                    $row->sap_itemcode, $row->composition, $row->type, $row->div_name, $row->pack,
                    $row->discount_rate, $row->rev_no
                ]);
                $writer->addRow($dataRow);
                //echo 'Peak memory usage: ' . memory_get_peak_usage() . ' bytes';
            }

            // Clear memory
            unset($rows);
            gc_collect_cycles();
        });

        // Close the writer to save the file
        $writer->close();

        // Add the file to the ZIP archive
        $zip->addFile($tempFilePath, $region . '.xlsx');

        // Clear memory
        unset($writer);
        gc_collect_cycles();
        //$zip->addFile((new FastExcel($this->usersGenerator(collect($data_original))))->export(sys_get_temp_dir().'/'.$region.'.xlsx'),$region.'.xlsx');
    }

    function pocGenerator($year, $zip){

        $pocData = Employee::where('emp_code', $this->emp_code)->first();
        $data_original = [];
        $data_child = [];
        $original_list = DB::table('voluntary_quotation_sku_listing AS parent_sku')
            ->select(
                'vq.hospital_name',
                'vq.id as vq_id',
                'vq.institution_id',
                'parent_sku.item_code',
                'parent_sku.brand_name',
                'parent_sku.mother_brand_name',
                'parent_sku.hsn_code',
                'parent_sku.applicable_gst',
                'parent_sku.last_year_percent',
                'parent_sku.last_year_rate',
                \DB::raw('
                    CASE
                        WHEN parent_sku.last_year_mrp IS NOT NULL AND parent_sku.last_year_ptr IS NOT NULL
                        THEN ((parent_sku.last_year_mrp - parent_sku.last_year_ptr) / parent_sku.last_year_mrp) * 100
                        ELSE "-"
                    END AS last_year_margin_on_mrp
                '),
                'parent_sku.mrp',
                'parent_sku.last_year_mrp',
                'parent_sku.ptr',
                'parent_sku.discount_percent',
                'parent_sku.mrp_margin',
                'parent_sku.sap_itemcode',
                'parent_sku.composition',
                'parent_sku.type',
                'parent_sku.div_name',
                'parent_sku.pack',
                'parent_sku.discount_rate',
                
                
                /*\DB::raw('CAST((CASE  WHEN vq.parent_vq_id != "0" THEN (SELECT COUNT(*)+1 FROM voluntary_quotation as vq1 where vq1.parent_vq_id = vq.parent_vq_id AND vq1.id <= vq.id) ELSE 1  END) as UNSIGNED) AS revision_count')*/
                'vq.rev_no'
            )
            ->leftJoin('voluntary_quotation AS vq', 'vq.id','parent_sku.vq_id')
            ->leftJoin('poc_master','poc_master.institution_id','vq.institution_id')
            ->where('poc_master.'.strtolower($pocData->emp_type).'_code',$this->emp_code)
            ->where('vq_status',1)
            ->where('vq.year', $year)
            ->where('parent_sku.is_deleted',0)//added on 06082024
            ->where('vq.is_deleted',0)//added on 06082024
            ->distinct()
            // ->where('vq.zone',$region)
            // ->whereIn('vq.institution_id',['DM1083','IDP0125'])
            ->orderBy('vq.hospital_name','ASC')
            ->orderBy('parent_sku.item_code','ASC')
            ->chunk(200, function ($rows) use (&$data_original) {
                // Accumulate the data into the $data array
                foreach ($rows as $row) {
                    $data_original[] = $row;
                }
            }
        );
    
        // Export consumes only a few MB, even with 10M+ rows.
    


        $zip->addFile((new FastExcel($this->usersGenerator(collect($data_original))))->export(sys_get_temp_dir().'/'.$this->emp_code.'.xlsx'),$this->emp_code.'.xlsx');
    }

    function distributionGenerator($year, $zip){
        $data_original = [];
        $data_child = [];
        $original_list = DB::table('voluntary_quotation_sku_listing AS parent_sku')
            ->select(
                'vq.hospital_name',
                'vq.id as vq_id',
                'vq.institution_id',
                'parent_sku.item_code',
                'parent_sku.brand_name',
                'parent_sku.mother_brand_name',
                'parent_sku.hsn_code',
                'parent_sku.applicable_gst',
                'parent_sku.last_year_percent',
                'parent_sku.last_year_rate',
                \DB::raw('
                    CASE
                        WHEN parent_sku.last_year_mrp IS NOT NULL AND parent_sku.last_year_ptr IS NOT NULL
                        THEN ((parent_sku.last_year_mrp - parent_sku.last_year_ptr) / parent_sku.last_year_mrp) * 100
                        ELSE "-"
                    END AS last_year_margin_on_mrp
                '),
                'parent_sku.mrp',
                'parent_sku.last_year_mrp',
                'parent_sku.ptr',
                'parent_sku.discount_percent',
                'parent_sku.mrp_margin',
                'parent_sku.sap_itemcode',
                'parent_sku.composition',
                'parent_sku.type',
                'parent_sku.div_name',
                'parent_sku.pack',
                'parent_sku.discount_rate',
                
                
                /*\DB::raw('CAST((CASE  WHEN vq.parent_vq_id != "0" THEN (SELECT COUNT(*)+1 FROM voluntary_quotation as vq1 where vq1.parent_vq_id = vq.parent_vq_id AND vq1.id <= vq.id) ELSE 1  END) as UNSIGNED) AS revision_count')*/
                'vq.rev_no'
            )
            ->leftJoin('voluntary_quotation AS vq', 'vq.id','parent_sku.vq_id')
            ->whereIn('vq.cfa_code',explode(',',$this->div_id))
            ->where('vq_status',1)
            ->where('vq.year', $year)
            ->where('parent_sku.is_deleted',0)//added on 06082024
            ->where('vq.is_deleted',0)//added on 06082024
            ->distinct()
            // ->where('vq.zone',$region)
            // ->whereIn('vq.institution_id',['DM1083','IDP0125'])
            ->orderBy('vq.hospital_name','ASC')
            ->orderBy('parent_sku.item_code','ASC')
            ->chunk(200, function ($rows) use (&$data_original) {
                // Accumulate the data into the $data array
                foreach ($rows as $row) {
                    $data_original[] = $row;
                }
            }
        );
    
        // Export consumes only a few MB, even with 10M+ rows.
    


        $zip->addFile((new FastExcel($this->usersGenerator(collect($data_original))))->export(sys_get_temp_dir().'/'.$this->emp_code.'.xlsx'),$this->emp_code.'.xlsx');
    }

    function approverGenerator($year, $zip){
        print_r("approver");
        $data_original = [];
        $data_child = [];

        if(preg_replace('/[^0-9.]+/', '', $this->emp_level)>2){
            $original_list = DB::table('voluntary_quotation_sku_listing AS parent_sku')
                ->select(
                    'vq.hospital_name',
                    'vq.id as vq_id',
                    'vq.institution_id',
                    'parent_sku.item_code',
                    'parent_sku.brand_name',
                    'parent_sku.mother_brand_name',
                    'parent_sku.hsn_code',
                    'parent_sku.applicable_gst',
                    'parent_sku.last_year_percent',
                    'parent_sku.last_year_rate',
                    \DB::raw('
                        CASE
                            WHEN parent_sku.last_year_mrp IS NOT NULL AND parent_sku.last_year_ptr IS NOT NULL
                            THEN ((parent_sku.last_year_mrp - parent_sku.last_year_ptr) / parent_sku.last_year_mrp) * 100
                            ELSE "-"
                        END AS last_year_margin_on_mrp
                    '),
                    'parent_sku.mrp',
                    'parent_sku.last_year_mrp',
                    'parent_sku.ptr',
                    'parent_sku.discount_percent',
                    'parent_sku.mrp_margin',
                    'parent_sku.sap_itemcode',
                    'parent_sku.composition',
                    'parent_sku.type',
                    'parent_sku.div_name',
                    'parent_sku.pack',
                    'parent_sku.discount_rate',
                    
                    
                    /*\DB::raw('CAST((CASE  WHEN vq.parent_vq_id != "0" THEN (SELECT COUNT(*)+1 FROM voluntary_quotation as vq1 where vq1.parent_vq_id = vq.parent_vq_id AND vq1.id <= vq.id) ELSE 1  END) as UNSIGNED) AS revision_count')*/
                    'vq.rev_no'
                )
                ->whereIn('parent_sku.div_id',explode(',',$this->div_id))
                ->leftJoin('voluntary_quotation AS vq', 'vq.id','parent_sku.vq_id')
                ->where('vq.current_level','>=',preg_replace('/[^0-9.]+/', '', $this->emp_level))
                ->where('parent_sku.is_deleted',0)
                ->where('vq.is_deleted',0)//added on 06082024
                ->where('vq.year', $year)
                ->distinct()
                // ->where('vq.zone',$region)
                // ->whereIn('vq.institution_id',['DM1083','IDP0125'])
                ->orderBy('vq.hospital_name','ASC')
                ->orderBy('parent_sku.item_code','ASC')
                ->chunk(200, function ($rows) use (&$data_original) {
                    // Accumulate the data into the $data array
                    foreach ($rows as $row) {
                        $data_original[] = $row;
                    }
                }
            );
        }
        else{
            $original_list = DB::table('voluntary_quotation_sku_listing AS parent_sku')
                ->select(
                    'vq.hospital_name',
                    'vq.id as vq_id',
                    'vq.institution_id',
                    'parent_sku.item_code',
                    'parent_sku.brand_name',
                    'parent_sku.mother_brand_name',
                    'parent_sku.hsn_code',
                    'parent_sku.applicable_gst',
                    'parent_sku.last_year_percent',
                    'parent_sku.last_year_rate',
                    \DB::raw('
                        CASE
                            WHEN parent_sku.last_year_mrp IS NOT NULL AND parent_sku.last_year_ptr IS NOT NULL
                            THEN ((parent_sku.last_year_mrp - parent_sku.last_year_ptr) / parent_sku.last_year_mrp) * 100
                            ELSE "-"
                        END AS last_year_margin_on_mrp
                    '),
                    'parent_sku.mrp',
                    'parent_sku.last_year_mrp',
                    'parent_sku.ptr',
                    'parent_sku.discount_percent',
                    'parent_sku.mrp_margin',
                    'parent_sku.sap_itemcode',
                    'parent_sku.composition',
                    'parent_sku.type',
                    'parent_sku.div_name',
                    'parent_sku.pack',
                    'parent_sku.discount_rate',
                    
                    
                    /*\DB::raw('CAST((CASE  WHEN vq.parent_vq_id != "0" THEN (SELECT COUNT(*)+1 FROM voluntary_quotation as vq1 where vq1.parent_vq_id = vq.parent_vq_id AND vq1.id <= vq.id) ELSE 1  END) as UNSIGNED) AS revision_count')*/
                    'vq.rev_no'
                )
                ->leftJoin('voluntary_quotation AS vq', 'vq.id','parent_sku.vq_id')
                ->where('vq.current_level','>=',preg_replace('/[^0-9.]+/', '', $this->emp_level))
                ->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','vq.id')
                ->where('institution_division_mapping.employee_code',$this->emp_code)
                ->whereIn('parent_sku.div_id',explode(',',$this->div_id))
                ->where('parent_sku.is_deleted',0)
                ->where('vq.year', $year)
                ->where('vq.is_deleted',0)//added on 06082024
                ->distinct()
                // ->where('vq.zone',$region)
                // ->whereIn('vq.institution_id',['DM1083','IDP0125'])
                ->orderBy('vq.hospital_name','ASC')
                ->orderBy('parent_sku.item_code','ASC')
                ->chunk(200, function ($rows) use (&$data_original) {
                    // Accumulate the data into the $data array
                    foreach ($rows as $row) {
                        $data_original[] = $row;
                    }
                }
            );
        }
    
        // Export consumes only a few MB, even with 10M+ rows.
    


        $zip->addFile((new FastExcel($this->usersGenerator(collect($data_original))))->export(sys_get_temp_dir().'/'.$this->emp_code.'.xlsx'),$this->emp_code.'.xlsx');
    }
}
