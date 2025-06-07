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
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Date;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Session;
ini_set('memory_limit', '2048M'); // Increase memory limit to 2GB
ini_set('max_execution_time', 0); // Unlimited execution time
class ReportDownload implements ShouldQueue
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
    $zip_file = 'public/latestreport'.$this->emp_code.'.zip'; // Name of our archive to download
    
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
        $original_list = DB::table('voluntary_quotation_sku_listing AS parent_sku')
            ->select('MAX(parent_sku.id) AS id')
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
                /*\DB::raw('1 as revision_count')*/
                'vq.rev_no'
            )
            ->leftJoin('voluntary_quotation AS vq', 'vq.id','parent_sku.vq_id')
            ->where('vq.year', $year)
            ->where('vq.zone',$region)
            ->where('vq.parent_vq_id',  0)
            ->where('vq.vq_status',1)//added on 18072024
            ->where('parent_sku.is_deleted',0)//added on 26072024
            ->where('vq.is_deleted',0)//added on 26072024
            ->when($this->emp_type == 'initiator' && $this->div_id != '', function ($query) {
                return $query->whereIn('parent_sku.div_id', explode(',', $this->div_id));
            })
            // ->whereIn('vq.institution_id',['DM1083','IDP0125'])
            ->orderBy('vq.hospital_name','ASC')
            ->chunk(200, function ($rows) use (&$data_original) {
                // Accumulate the data into the $data array
                foreach ($rows as $row) {
                    $data_original[] = $row;
                }
            }
        );
        $child_list = DB::table('voluntary_quotation_sku_listing AS parent_sku')
            // ->select('MAX(parent_sku.id) AS id')
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
            ->where('vq.parent_vq_id','!=' , 0)
            ->where('vq.vq_status',1)//added on 18072024
            ->where('parent_sku.is_deleted',0)//added on 26072024
            ->where('vq.is_deleted',0)//added on 26072024
            // ->whereIn('vq.institution_id',['DM1083','IDP0125'])
            ->when($this->emp_type == 'initiator' && $this->div_id != '', function ($query) {
                return $query->whereIn('parent_sku.div_id', explode(',', $this->div_id));
            })
            ->orderBy('parent_sku.id','DESC')

        //    ->groupBy('parent_sku.item_code')
        ->chunk(200, function ($rows) use (&$data_child) {
            // Accumulate the data into the $data array
            foreach ($rows as $row) {
                $data_child[] = $row;
            }
        });


        // $groupedData = collect($data_child)->groupBy(['item_code', 'institution_id']);
        $resultCollection = collect($data_child)->groupBy(['item_code', 'institution_id'])->reduce(function ($carry, $group) {
            // dd($carry);

            // $maxIdRow = $group->sortByDesc('id')->first();

            // $carry->push($maxIdRow);
            // return $carry;
            $maxIdRow = $group->map(function ($groupp) {
                $maxIdRow = $groupp->sortByDesc('id')->first();
                // print_r($maxIdRow);
        
                return $maxIdRow;
            });
            $carry->push($maxIdRow);
            return $carry;
        }, collect())->flatten()->values();
        
        $originalCollection = collect($data_original);
        $childCollection = $resultCollection;
        
    
        // Merge the two associative arrays, giving priority to the child elements
        $mergedCollection = $originalCollection->map(function ($item) use ($childCollection) {
            print_r($item->item_code);
            $childItem = $childCollection->where('institution_id',$item->institution_id)->firstWhere('item_code', $item->item_code);
            return $childItem ?? $item;
        });
        $mergedCollection = $mergedCollection->concat($childCollection->whereNotIn('item_code',$originalCollection->pluck('item_code')->all()));

        // Export consumes only a few MB, even with 10M+ rows.
    

        // Adding file: second parameter is what will the path inside of the archive
        // So it will create another folder called "storage/" inside ZIP, and put the file there.
        /*$temp = (sys_get_temp_dir());//commented on 19072024
        // $xlsxContent = (new FastExcel($this->usersGenerator($mergedCollection)))->export('text.xlsx');
        // $zip->addFromString($region.'.xlsx', $xlsxContent);
        $zip->addFile((new FastExcel($this->usersGenerator($mergedCollection)))->export(sys_get_temp_dir().'/'.$region.'.xlsx'),$region.'.xlsx');*///commented on 19072024

        $tempFilePath = sys_get_temp_dir() . '/' . $region . '.xlsx';
        (new FastExcel($this->usersGenerator($mergedCollection)))->export($tempFilePath);
        $spreadsheet = IOFactory::load($tempFilePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Insert rows at the beginning
        $sheet->insertNewRowBefore(1, 3); // Inserting three rows
        $sheet->setCellValue('A1', 'IDAP - Latest Report');
        $sheet->mergeCells('A1:W1');
        $sheet->setCellValue('A2', 'Report generated on ' . Date::now()->format('d M Y H:i:s'));
        $sheet->mergeCells('A2:W2');

        // Insert human-readable headings
        $sheet->setCellValue('A3', 'Hospital Name');
        $sheet->setCellValue('B3', 'VQ ID');
        $sheet->setCellValue('C3', 'Institution ID');
        $sheet->setCellValue('D3', 'Item Code');
        $sheet->setCellValue('E3', 'Brand Name');
        $sheet->setCellValue('F3', 'Mother Brand Name');
        $sheet->setCellValue('G3', 'HSN Code');
        $sheet->setCellValue('H3', 'Applicable GST');
        $sheet->setCellValue('I3', 'Last Year Percent');
        $sheet->setCellValue('J3', 'Last Year Rate');
        $sheet->setCellValue('K3', 'Last Year Margin on MRP');
        $sheet->setCellValue('L3', 'MRP');
        $sheet->setCellValue('M3', 'Last Year MRP');
        $sheet->setCellValue('N3', 'PTR');
        $sheet->setCellValue('O3', 'Discount Percent');
        $sheet->setCellValue('P3', 'MRP Margin');
        $sheet->setCellValue('Q3', 'SAP Item Code');
        $sheet->setCellValue('R3', 'Composition');
        $sheet->setCellValue('S3', 'Type');
        $sheet->setCellValue('T3', 'Division Name');
        $sheet->setCellValue('U3', 'Pack');
        $sheet->setCellValue('V3', 'Discount Rate');
        $sheet->setCellValue('W3', 'Revision Number');

        // Apply styling to the first two rows
        $firstTwoRowsStyleArray = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF000000'], // Black color
            ],
        ];

        $sheet->getStyle('A1:W2')->applyFromArray($firstTwoRowsStyleArray);

        // Apply styling to the heading row (third row) without background color
        $headingStyleArray = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF000000'], // Black color
            ],
            /*'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],*/
        ];

        $sheet->getStyle('A3:W3')->applyFromArray($headingStyleArray);
        $sheet->removeRow(4);

        // Save the modified file
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFilePath);
        $zip->addFile($tempFilePath, $region . '.xlsx');
        print_r($region.'processingover');
    }

    function pocGenerator($year,$zip) {
        $pocData = Employee::where('emp_code', $this->emp_code)->first();
        $data_original = [];
        $data_child = [];
        $original_list = DB::table('voluntary_quotation_sku_listing AS parent_sku')
            ->select('MAX(parent_sku.id) AS id')
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
                /*\DB::raw('1 as revision_count')*/
                'vq.rev_no'
            )
            ->leftJoin('voluntary_quotation AS vq', 'vq.id','parent_sku.vq_id')
            ->leftJoin('poc_master','poc_master.institution_id','vq.institution_id')
            ->where('poc_master.'.strtolower($pocData->emp_type).'_code',$this->emp_code)
            ->where('vq_status',1)
            ->where('vq.year', $year)
            // ->where('vq.zone',$region)
            ->where('vq.parent_vq_id',  0)
            ->where('parent_sku.is_deleted',0)//added on 26072024
            ->where('vq.is_deleted',0)//added on 26072024
            ->when($this->div_id['filter_div_id'] != 'all', function ($query) {
                return $query->whereIn('parent_sku.div_id', explode(',', $this->div_id['filter_div_id']));
            })
            ->orderBy('vq.hospital_name','ASC')
            ->chunk(200, function ($rows) use (&$data_original) {
                // Accumulate the data into the $data array
                foreach ($rows as $row) {
                    $data_original[] = $row;
                }
            }
        );
        $child_list = DB::table('voluntary_quotation_sku_listing AS parent_sku')
            // ->select('MAX(parent_sku.id) AS id')
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
            // ->where('vq.zone',$region)
            ->where('vq.parent_vq_id','!=' , 0)
            ->where('parent_sku.is_deleted',0)//added on 26072024
            ->where('vq.is_deleted',0)//added on 26072024
            ->when($this->div_id['filter_div_id'] != 'all', function ($query) {
                return $query->whereIn('parent_sku.div_id', explode(',', $this->div_id['filter_div_id']));
            })
            ->orderBy('parent_sku.id','DESC')

        ->chunk(200, function ($rows) use (&$data_child) {
            // Accumulate the data into the $data array
            foreach ($rows as $row) {
                $data_child[] = $row;
            }
        });


        $resultCollection = collect($data_child)->groupBy(['item_code', 'institution_id'])->reduce(function ($carry, $group) {
            
            $maxIdRow = $group->map(function ($groupp) {
                $maxIdRow = $groupp->sortByDesc('id')->first();
                // print_r($maxIdRow);
        
                return $maxIdRow;
            });
            $carry->push($maxIdRow);
            return $carry;
        }, collect())->flatten()->values();
        $originalCollection = collect($data_original);
        $childCollection = $resultCollection;
        
    
        // Merge the two associative arrays, giving priority to the child elements
        $mergedCollection = $originalCollection->map(function ($item) use ($childCollection) {
            $childItem = $childCollection->where('institution_id',$item->institution_id)->firstWhere('item_code', $item->item_code);
            return $childItem ?? $item;
        });
        $mergedCollection = $mergedCollection->concat($childCollection->whereNotIn('item_code',$originalCollection->pluck('item_code')->all()));

        // Export consumes only a few MB, even with 10M+ rows.
    

        // Adding file: second parameter is what will the path inside of the archive
        // So it will create another folder called "storage/" inside ZIP, and put the file there.
        /*$temp = (sys_get_temp_dir());

        $zip->addFile((new FastExcel($this->usersGenerator($mergedCollection)))->export(sys_get_temp_dir().'/'.$this->emp_code.'.xlsx'),$this->emp_code.'.xlsx');*///commented on 19072024
        $tempFilePath = sys_get_temp_dir() . '/' . $this->emp_code . '.xlsx';
        (new FastExcel($this->usersGenerator($mergedCollection)))->export($tempFilePath);
        $spreadsheet = IOFactory::load($tempFilePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Insert rows at the beginning
        $sheet->insertNewRowBefore(1, 3); // Inserting three rows
        $sheet->setCellValue('A1', 'IDAP - Latest Report');
        $sheet->mergeCells('A1:W1');
        $sheet->setCellValue('A2', 'Report generated on ' . Date::now()->format('d M Y H:i:s'));
        $sheet->mergeCells('A2:W2');

        // Insert human-readable headings
        $sheet->setCellValue('A3', 'Hospital Name');
        $sheet->setCellValue('B3', 'VQ ID');
        $sheet->setCellValue('C3', 'Institution ID');
        $sheet->setCellValue('D3', 'Item Code');
        $sheet->setCellValue('E3', 'Brand Name');
        $sheet->setCellValue('F3', 'Mother Brand Name');
        $sheet->setCellValue('G3', 'HSN Code');
        $sheet->setCellValue('H3', 'Applicable GST');
        $sheet->setCellValue('I3', 'Last Year Percent');
        $sheet->setCellValue('J3', 'Last Year Rate');
        $sheet->setCellValue('K3', 'Last Year Margin on MRP');
        $sheet->setCellValue('L3', 'MRP');
        $sheet->setCellValue('M3', 'Last Year MRP');
        $sheet->setCellValue('N3', 'PTR');
        $sheet->setCellValue('O3', 'Discount Percent');
        $sheet->setCellValue('P3', 'MRP Margin');
        $sheet->setCellValue('Q3', 'SAP Item Code');
        $sheet->setCellValue('R3', 'Composition');
        $sheet->setCellValue('S3', 'Type');
        $sheet->setCellValue('T3', 'Division Name');
        $sheet->setCellValue('U3', 'Pack');
        $sheet->setCellValue('V3', 'Discount Rate');
        $sheet->setCellValue('W3', 'Revision Number');

        // Apply styling to the first two rows
        $firstTwoRowsStyleArray = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF000000'], // Black color
            ],
        ];

        $sheet->getStyle('A1:W2')->applyFromArray($firstTwoRowsStyleArray);

        // Apply styling to the heading row (third row) without background color
        $headingStyleArray = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF000000'], // Black color
            ],
            /*'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],*/
        ];

        $sheet->getStyle('A3:W3')->applyFromArray($headingStyleArray);
        $sheet->removeRow(4);

        // Save the modified file
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFilePath);
        $zip->addFile($tempFilePath, $this->emp_code . '.xlsx');
    }
    
    function distributionGenerator($year,$zip) {
        $pocData = Employee::where('emp_code', $this->div_id)->first();
        $data_original = [];
        $data_child = [];
        $original_list = DB::table('voluntary_quotation_sku_listing AS parent_sku')
            ->select('MAX(parent_sku.id) AS id')
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
                /*\DB::raw('1 as revision_count')*/
                'vq.rev_no'
            )
            ->leftJoin('voluntary_quotation AS vq', 'vq.id','parent_sku.vq_id')
             ->whereIn('vq.cfa_code',explode(',',$this->div_id['session_div_id']))
            ->where('vq_status',1)
            ->where('vq.year', $year)
            // ->where('vq.zone',$region)
            ->where('vq.parent_vq_id',  0)
            ->where('parent_sku.is_deleted',0)//added on 26072024
            ->where('vq.is_deleted',0)//added on 26072024
            ->when($this->div_id['filter_div_id'] != 'all', function ($query) {
                return $query->whereIn('parent_sku.div_id', explode(',', $this->div_id['filter_div_id']));
            })
            ->orderBy('vq.hospital_name','ASC')
            ->chunk(200, function ($rows) use (&$data_original) {
                // Accumulate the data into the $data array
                foreach ($rows as $row) {
                    $data_original[] = $row;
                }
            }
        );
        $child_list = DB::table('voluntary_quotation_sku_listing AS parent_sku')
            // ->select('MAX(parent_sku.id) AS id')
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
            ->whereIn('vq.cfa_code',explode(',',$this->div_id['session_div_id']))
            ->where('vq_status',1)
            ->where('parent_sku.is_deleted',0)//added on 26072024
            ->where('vq.is_deleted',0)//added on 26072024
            ->where('vq.year', $year)
            // ->where('vq.zone',$region)
            ->where('vq.parent_vq_id','!=' , 0)
            ->when($this->div_id['filter_div_id'] != 'all', function ($query) {
                return $query->whereIn('parent_sku.div_id', explode(',', $this->div_id['filter_div_id']));
            })
            ->orderBy('parent_sku.id','DESC')

        ->chunk(200, function ($rows) use (&$data_child) {
            // Accumulate the data into the $data array
            foreach ($rows as $row) {
                $data_child[] = $row;
            }
        });

        $resultCollection = collect($data_child)->groupBy(['item_code', 'institution_id'])->reduce(function ($carry, $group) {
            
            $maxIdRow = $group->map(function ($groupp) {
                $maxIdRow = $groupp->sortByDesc('id')->first();
                // print_r($maxIdRow);
        
                return $maxIdRow;
            });
            $carry->push($maxIdRow);
            return $carry;
        }, collect())->flatten()->values();
        $originalCollection = collect($data_original);
        $childCollection = $resultCollection;
        
    
        // Merge the two associative arrays, giving priority to the child elements
        $mergedCollection = $originalCollection->map(function ($item) use ($childCollection) {
            $childItem = $childCollection->where('institution_id',$item->institution_id)->firstWhere('item_code', $item->item_code);
            return $childItem ?? $item;
        });
        $mergedCollection = $mergedCollection->concat($childCollection->whereNotIn('item_code',$originalCollection->pluck('item_code')->all()));

        // Export consumes only a few MB, even with 10M+ rows.
    

        // Adding file: second parameter is what will the path inside of the archive
        // So it will create another folder called "storage/" inside ZIP, and put the file there.
        /*$temp = (sys_get_temp_dir());

        $zip->addFile((new FastExcel($this->usersGenerator($mergedCollection)))->export(sys_get_temp_dir().'/'.$this->emp_code.'.xlsx'),$this->emp_code.'.xlsx');*///commented on 19072024
        $tempFilePath = sys_get_temp_dir() . '/' . $this->emp_code . '.xlsx';
        (new FastExcel($this->usersGenerator($mergedCollection)))->export($tempFilePath);
        $spreadsheet = IOFactory::load($tempFilePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Insert rows at the beginning
        $sheet->insertNewRowBefore(1, 3); // Inserting three rows
        $sheet->setCellValue('A1', 'IDAP - Latest Report');
        $sheet->mergeCells('A1:W1');
        $sheet->setCellValue('A2', 'Report generated on ' . Date::now()->format('d M Y H:i:s'));
        $sheet->mergeCells('A2:W2');

        // Insert human-readable headings
        $sheet->setCellValue('A3', 'Hospital Name');
        $sheet->setCellValue('B3', 'VQ ID');
        $sheet->setCellValue('C3', 'Institution ID');
        $sheet->setCellValue('D3', 'Item Code');
        $sheet->setCellValue('E3', 'Brand Name');
        $sheet->setCellValue('F3', 'Mother Brand Name');
        $sheet->setCellValue('G3', 'HSN Code');
        $sheet->setCellValue('H3', 'Applicable GST');
        $sheet->setCellValue('I3', 'Last Year Percent');
        $sheet->setCellValue('J3', 'Last Year Rate');
        $sheet->setCellValue('K3', 'Last Year Margin on MRP');
        $sheet->setCellValue('L3', 'MRP');
        $sheet->setCellValue('M3', 'Last Year MRP');
        $sheet->setCellValue('N3', 'PTR');
        $sheet->setCellValue('O3', 'Discount Percent');
        $sheet->setCellValue('P3', 'MRP Margin');
        $sheet->setCellValue('Q3', 'SAP Item Code');
        $sheet->setCellValue('R3', 'Composition');
        $sheet->setCellValue('S3', 'Type');
        $sheet->setCellValue('T3', 'Division Name');
        $sheet->setCellValue('U3', 'Pack');
        $sheet->setCellValue('V3', 'Discount Rate');
        $sheet->setCellValue('W3', 'Revision Number');

        // Apply styling to the first two rows
        $firstTwoRowsStyleArray = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF000000'], // Black color
            ],
        ];

        $sheet->getStyle('A1:W2')->applyFromArray($firstTwoRowsStyleArray);

        // Apply styling to the heading row (third row) without background color
        $headingStyleArray = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF000000'], // Black color
            ],
            /*'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],*/
        ];

        $sheet->getStyle('A3:W3')->applyFromArray($headingStyleArray);
        $sheet->removeRow(4);

        // Save the modified file
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFilePath);
        $zip->addFile($tempFilePath, $this->emp_code . '.xlsx');
    }

    function approverGenerator($year,$zip) {
        $pocData = Employee::where('emp_code', $this->emp_code)->first();
        $data_original = [];
        $data_child = [];

        if(preg_replace('/[^0-9.]+/', '', $this->emp_level)>2){
            $original_list = DB::table('voluntary_quotation_sku_listing AS parent_sku')
                ->select('MAX(parent_sku.id) AS id')
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
                /*\DB::raw('1 as revision_count')*/
                'vq.rev_no'
                )
                ->leftJoin('voluntary_quotation AS vq', 'vq.id','parent_sku.vq_id')
                ->whereIn('parent_sku.div_id',explode(',',$this->div_id))
                ->where('vq.current_level','>=',preg_replace('/[^0-9.]+/', '', $this->emp_level))
                ->where('parent_sku.is_deleted',0)
                ->where('vq.is_deleted',0)//added on 26072024
                ->distinct()
                ->where('vq.year', $year)
                // ->where('vq.zone',$region)
                ->where('vq.parent_vq_id',  0)
                ->where('vq.vq_status',1)//added on 18072024
                ->orderBy('vq.hospital_name','ASC')
                ->chunk(200, function ($rows) use (&$data_original) {
                    // Accumulate the data into the $data array
                    foreach ($rows as $row) {
                        $data_original[] = $row;
                    }
                }
            );
            $child_list = DB::table('voluntary_quotation_sku_listing AS parent_sku')
                // ->select('MAX(parent_sku.id) AS id')
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
                ->whereIn('parent_sku.div_id',explode(',',$this->div_id))
                ->where('vq.current_level','>=',preg_replace('/[^0-9.]+/', '', $this->emp_level))
                ->where('parent_sku.is_deleted',0)
                ->where('vq.is_deleted',0)//added on 26072024
                ->distinct()
                ->where('vq.year', $year)
                // ->where('vq.zone',$region)
                ->where('vq.parent_vq_id','!=' , 0)
                ->where('vq.vq_status',1)//added on 18072024
                ->orderBy('parent_sku.id','DESC')

            ->chunk(200, function ($rows) use (&$data_child) {
                // Accumulate the data into the $data array
                foreach ($rows as $row) {
                    $data_child[] = $row;
                }
            });
        }
        else{
            $original_list = DB::table('voluntary_quotation_sku_listing AS parent_sku')
                ->select('MAX(parent_sku.id) AS id')
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
                   /* \DB::raw('1 as revision_count')*/
                   'vq.rev_no'
                )
                ->leftJoin('voluntary_quotation AS vq', 'vq.id','parent_sku.vq_id')
                ->where('vq.current_level','>=',preg_replace('/[^0-9.]+/', '', $this->emp_level))
                ->whereIn('parent_sku.div_id',explode(',',$this->div_id))
                ->where('parent_sku.is_deleted',0)
                ->where('vq.is_deleted',0)//added on 26072024
                ->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','vq.id')
                ->where('institution_division_mapping.employee_code',$this->emp_code)
                ->distinct()
                ->where('vq.year', $year)
                // ->where('vq.zone',$region)
                ->where('vq.parent_vq_id',  0)
                ->where('vq.vq_status',1)//added on 18072024
                ->orderBy('vq.hospital_name','ASC')
                ->chunk(200, function ($rows) use (&$data_original) {
                    // Accumulate the data into the $data array
                    foreach ($rows as $row) {
                        $data_original[] = $row;
                    }
                }
            );
            $child_list = DB::table('voluntary_quotation_sku_listing AS parent_sku')
                // ->select('MAX(parent_sku.id) AS id')
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
                    //\DB::raw('CAST((CASE  WHEN vq.parent_vq_id != "0" THEN (SELECT COUNT(*)+1 FROM voluntary_quotation as vq1 where vq1.parent_vq_id = vq.parent_vq_id AND vq1.id <= vq.id) ELSE 1  END) as UNSIGNED) AS revision_count')
                'vq.rev_no'
                )
                ->leftJoin('voluntary_quotation AS vq', 'vq.id','parent_sku.vq_id')
                ->where('vq.current_level','>=',preg_replace('/[^0-9.]+/', '', $this->emp_level))
                ->whereIn('parent_sku.div_id',explode(',',$this->div_id))
                ->where('parent_sku.is_deleted',0)
                ->where('vq.is_deleted',0)//added on 26072024
                ->leftJoin('institution_division_mapping','institution_division_mapping.vq_id','=','vq.id')
                ->where('institution_division_mapping.employee_code',$this->emp_code)
                ->distinct()
                ->where('vq.year', $year)
                // ->where('vq.zone',$region)
                ->where('vq.parent_vq_id','!=' , 0)
                ->where('vq.vq_status',1)//added on 18072024
                ->orderBy('parent_sku.id','DESC')

            ->chunk(200, function ($rows) use (&$data_child) {
                // Accumulate the data into the $data array
                foreach ($rows as $row) {
                    $data_child[] = $row;
                }
            });
        }


        $resultCollection = collect($data_child)->groupBy(['item_code', 'institution_id'])->reduce(function ($carry, $group) {
            
            $maxIdRow = $group->map(function ($groupp) {
                $maxIdRow = $groupp->sortByDesc('id')->first();
                // print_r($maxIdRow);
                print_r($groupp->first()->item_code);

                return $maxIdRow;
            });
            $carry->push($maxIdRow);
            return $carry;
        }, collect())->flatten()->values();
        $originalCollection = collect($data_original);
        $childCollection = $resultCollection;
        print_r($childCollection->count());
    
        // Merge the two associative arrays, giving priority to the child elements
        $mergedCollection = $originalCollection->map(function ($item) use ($childCollection) {
            $childItem = $childCollection->where('institution_id',$item->institution_id)->firstWhere('item_code', $item->item_code);
            return $childItem ?? $item;
        });
        $mergedCollection = $mergedCollection->concat($childCollection->whereNotIn('item_code',$originalCollection->pluck('item_code')->all()));
        // Export consumes only a few MB, even with 10M+ rows.
    

        // Adding file: second parameter is what will the path inside of the archive
        // So it will create another folder called "storage/" inside ZIP, and put the file there.
        /*$temp = (sys_get_temp_dir());

        $zip->addFile((new FastExcel($this->usersGenerator($mergedCollection)))->export(sys_get_temp_dir().'/'.$this->emp_code.'.xlsx'),$this->emp_code.'.xlsx');*///commented on 19072024
        $tempFilePath = sys_get_temp_dir() . '/' . $this->emp_code . '.xlsx';
        (new FastExcel($this->usersGenerator($mergedCollection)))->export($tempFilePath);
        $spreadsheet = IOFactory::load($tempFilePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Insert rows at the beginning
        $sheet->insertNewRowBefore(1, 3); // Inserting three rows
        $sheet->setCellValue('A1', 'IDAP - Latest Report');
        $sheet->mergeCells('A1:W1');
        $sheet->setCellValue('A2', 'Report generated on ' . Date::now()->format('d M Y H:i:s'));
        $sheet->mergeCells('A2:W2');

        // Insert human-readable headings
        $sheet->setCellValue('A3', 'Hospital Name');
        $sheet->setCellValue('B3', 'VQ ID');
        $sheet->setCellValue('C3', 'Institution ID');
        $sheet->setCellValue('D3', 'Item Code');
        $sheet->setCellValue('E3', 'Brand Name');
        $sheet->setCellValue('F3', 'Mother Brand Name');
        $sheet->setCellValue('G3', 'HSN Code');
        $sheet->setCellValue('H3', 'Applicable GST');
        $sheet->setCellValue('I3', 'Last Year Percent');
        $sheet->setCellValue('J3', 'Last Year Rate');
        $sheet->setCellValue('K3', 'Last Year Margin on MRP');
        $sheet->setCellValue('L3', 'MRP');
        $sheet->setCellValue('M3', 'Last Year MRP');
        $sheet->setCellValue('N3', 'PTR');
        $sheet->setCellValue('O3', 'Discount Percent');
        $sheet->setCellValue('P3', 'MRP Margin');
        $sheet->setCellValue('Q3', 'SAP Item Code');
        $sheet->setCellValue('R3', 'Composition');
        $sheet->setCellValue('S3', 'Type');
        $sheet->setCellValue('T3', 'Division Name');
        $sheet->setCellValue('U3', 'Pack');
        $sheet->setCellValue('V3', 'Discount Rate');
        $sheet->setCellValue('W3', 'Revision Number');

        // Apply styling to the first two rows
        $firstTwoRowsStyleArray = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF000000'], // Black color
            ],
        ];

        $sheet->getStyle('A1:W2')->applyFromArray($firstTwoRowsStyleArray);

        // Apply styling to the heading row (third row) without background color
        $headingStyleArray = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF000000'], // Black color
            ],
            /*'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],*/
        ];

        $sheet->getStyle('A3:W3')->applyFromArray($headingStyleArray);
        $sheet->removeRow(4);

        // Save the modified file
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFilePath);
        $zip->addFile($tempFilePath, $this->emp_code . '.xlsx');
    }

}
