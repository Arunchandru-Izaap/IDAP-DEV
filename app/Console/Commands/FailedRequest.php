<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Mail;
use ZipArchive;
use Illuminate\Support\Facades\Storage;

class FailedRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'failed_request:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs daily for email the failed request details.';

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
        $today = Carbon::today();
        $lastMonth = Carbon::today()->subMonth();
        // Step 1: Retrieve all requests (vq_metis_object)
        $requests = DB::table('activity_trackers')
            ->select('vq_id', 'id', 'type', 'activity', 'created_at')
            ->where('type', 'vq_metis_object')
            ->whereDate('created_at', $today)
            ->get()
            ->toArray(); // Convert to array

        // Step 2: Retrieve all responses (vq_metis_response)
        $responses = DB::table('activity_trackers')
            ->select('vq_id', 'id', 'type', 'activity', 'created_at')
            ->where('type', 'vq_metis_response')
            ->whereDate('created_at',$lastMonth)
            ->get()
            ->toArray(); // Convert to array

        $vqIdsWithNoResponse = array_map(function ($request) use ($responses) {
            $responseFound = false;
            foreach ($responses as $response) {
                if ($request->vq_id == $response->vq_id) {
                    $requestCreatedAt = Carbon::parse($request->created_at);
                    $responseCreatedAt = Carbon::parse($response->created_at);

                    if ($responseCreatedAt->diffInMinutes($requestCreatedAt) <= 15) {
                        $responseFound = true;
                        break;
                    }
                }
            }
            if (!$responseFound) {
                // Add an empty `response` field for requests with no response
                $request->response = null;
                return $request;
            }
            return null; // Filter out requests with responses
        }, $requests);

        // Remove `null` entries
        $vqIdsWithNoResponse = array_filter($vqIdsWithNoResponse);

        // Filter requests with responses where `activity` status is false and add the response
        $requestWithFalseResponse = array_map(function ($request) use ($responses) {
            foreach ($responses as $response) {
                if ($request->vq_id == $response->vq_id) {
                    $activity = json_decode($response->activity, true);
                    if (isset($activity['status']) && $activity['status'] === false) {
                        // Add the matching response to the request
                        $request->response = $response->activity;
                        return $request;
                    }
                }
            }
            return null; // Filter out requests without false responses
        }, $requests);

        // Remove `null` entries
        $requestWithFalseResponse = array_filter($requestWithFalseResponse);

        // Step 5: Combine both filtered lists
        $results = array_merge(
            array_values($vqIdsWithNoResponse),  // Requests with no response
            array_values($requestWithFalseResponse) // Requests with response status as false
        );
        if (empty($results)) {
            return 0;
        }
        usort($results, function ($a, $b) {
            return $a->id <=> $b->id; // Ascending order (use `$b->id <=> $a->id` for descending)
        });
        

        if (empty($results)) {
            return 0;
        }

        $jsonDir = storage_path('app/');

        foreach ($results as $index => $result) {
            $fileName = $jsonDir . 'failed_request_' . ($result->id).'_'.($result->vq_id) . '.txt';
            file_put_contents($fileName, $result->activity);
        }

        $zipFilePath = storage_path('app/failed_requests_' . $today->toDateString() . '.zip');

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach (glob($jsonDir . "*.txt") as $file) {
                $zip->addFile($file, basename($file)); // Add file to the ZIP
            }
            $zip->close();
        } else {
            throw new \Exception("Failed to create ZIP file.");
        }

    
        array_map('unlink', glob($jsonDir . "*.txt"));

        if (!file_exists($zipFilePath)) {
            throw new Exception("File not found: $zipFilePath");
        }
        $data = ['results' => $results];
        $generated_date = Carbon::now()->format('Y-m-d H:i:s');
        $toEmails = DB::table('email_configurations')
            ->where('email_type', 'TO')
            ->where('status', 'ACTIVE')
            ->where('used_for', 'failed_send_quotation')
            ->pluck('email_address')
            ->toArray();

        $ccEmails = DB::table('email_configurations')
            ->where('email_type', 'CC')
            ->where('status', 'ACTIVE')
            ->where('used_for', 'failed_send_quotation')
            ->pluck('email_address')
            ->toArray();
        try {
            Mail::send('admin.emails.failed_request', $data, function ($message) use ($zipFilePath, $generated_date, $toEmails, $ccEmails) {
                $message->to($toEmails)
                    ->cc($ccEmails)
                    ->replyTo('noreply@domain.com')
                    ->subject('List of failed VQ requests during Send Quotation as on '.$generated_date)
                    ->attach($zipFilePath);
            });
        } catch (\Exception $exception) {
            $this->error("Email sending failed: " . $exception->getMessage());
        }

        @unlink($zipFilePath);
        return 0;
    }

}
