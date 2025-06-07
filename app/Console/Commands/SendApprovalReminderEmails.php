<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use App\Models\VoluntaryQuotation;
use App\Models\Employee;
use App\Http\Controllers\Api\VqListingController;
use App\Exports\SendApprovalReminderExport;
use Maatwebsite\Excel\Excel as BaseExcel;
use Excel;

class SendApprovalReminderEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approvalremainderemails:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs daily for email to approvers in the VQ details.';

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
        $vq_listing_controller = new VqListingController;
        $year = $vq_listing_controller->getFinancialYear(date('Y-m-d'),"Y");
        $current_date = strtotime(date('Y-m-d H:i:s'));
        
        $schedules = DB::table('approval_email_schedule')->get();
        foreach ($schedules as $schedule) {

            $vq_ids = [];
            $pendingVQs = VoluntaryQuotation::where('current_level', $schedule->level)
                ->where('year', $year)
                ->where('vq_status', 0)
                ->where('is_deleted', 0)
                ->get();
                // print_r("Level: ");
                // print_r($schedule->level);
                // print_r("\n");
            if(!empty($pendingVQs)):
                foreach ($pendingVQs as $vq) {
                    // print_r("created_at: ");
                    // print_r($vq->created_at->toDateString());
                    // print_r("\n");
                    $daysPending = now()->diffInDays(Carbon::parse($vq->created_at)); // 3
                    // print_r("daysPending: ");
                    // print_r($daysPending);
                    // print_r("\n");
                    
                    // Check if days passed is more than start_days
                    if ($daysPending >= $schedule->start_days) {
                        $daysAfterStart = $daysPending - $schedule->start_days; //2
                        // print_r("daysAfterStart : ");
                        // print_r($daysAfterStart);
                        // print_r("\n");

                        // print_r("reminder : ");
                        // $ggg = $daysAfterStart % $schedule->frequency_days;
                        // print_r($ggg);
                        // print_r("\n");

                        // Now check frequency:
                        if ($schedule->frequency_days == 1 || $daysAfterStart % $schedule->frequency_days == 0) {
                            $vq_ids[] = $vq->id;
                        }
                    }
                }
            endif;
        
            // print_r($vq_ids);
            // print_r("\n");

            if(!empty($vq_ids)):
                $data1['year']=$year;
                $data1['count_institution'] = count($vq_ids);
                $data1["subject"]="Pending for your approval for (".$data1['count_institution']." Institutions)";
            
                $emp_email = Employee::where('emp_level','L'.$schedule->level)->get()->toArray();
                
                $data1['email'] =  array_column($emp_email, 'emp_email') ;
                
                $data1['manager_cc']= array_column($emp_email, 'manager_cc');

                $data1['link'] = env('APP_URL').'/login';

                $data1['SendApprovalRemainderExport'] = Excel::raw(new SendApprovalReminderExport($year, $vq_ids, $schedule->level, $schedule->frequency_days), BaseExcel::XLSX);
                print_r("Export Data");
                
                try{
                    $level = $schedule->level;
                    Mail::send('admin.emails.approval_reminder', $data1, function($message)use($data1, $level) {
                        $message->to($data1["email"])
                        ->cc($data1["manager_cc"])
                        // ->replyTo('idap.support@sunpharma.com')
                        ->subject($data1["subject"]);
                        if($data1['count_institution'] > 0){
                            $message->attachData($data1["SendApprovalRemainderExport"], 'L'.$level.'-pending-institutions.xlsx');
                        }
                    });

                }catch(JWTException $exception){
                    $this->serverstatuscode = "0";
                    $this->serverstatusdes = $exception->getMessage();
                }
                if (Mail::failures()) {
                    $this->statusdesc  =   "Error sending mail";
                    $this->statuscode  =   "0";
        
                }else{
                print_r('mail sent');
                Log::debug('Send approver parent vq for L'.$schedule->level);
                    $this->statusdesc  =   "Message sent Succesfully";
                    $this->statuscode  =   "1";
                }
            endif;
        }
        
        return 0;
    }
    
}
