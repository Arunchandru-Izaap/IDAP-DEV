<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client as GuzzleClient;
use DB;
use App\Models\JwtToken;

class updateBrands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'brands:update';

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
        $jwt = JwtToken::orderBy('updated_at', 'desc')->first();

        if (!$jwt) {
            $this->error('JWT token not found');
            return;
        }

        $headers = [
            'Content-Type' => 'application/json',
            'AccessToken' => 'key',
            'Authorization' => 'Bearer ' . $jwt->jwt_token,
        ];

        $client = new GuzzleClient([
            'headers' => $headers,
            'verify' => false,
        ]);

        $body = '{}';

        $response = $client->request('POST', env('API_URL') . '/api/Products', [
            'body' => $body,
        ]);

        $data = json_decode($response->getBody()->getContents());

        // Prepare data to insert
        $dataToInsert = [];
        foreach ($data as $item) {
            $dataToInsert[] = [
                'brand_name' => $item->BRAND_NAME,
                'div_id' => $item->DIVISION_CODE,
                'div_name' => $item->DIVISION_NAME,
                'item_code' => $item->ITEM_CODE,
                'item_type' => $item->ITEM_TYPE,
                'sap_itemcode' => $item->SAP_ITEMCODE,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }


        DB::table('brands')->truncate();

        // Insert new records into the database
        if (!empty($dataToInsert)) {
            DB::table('brands')->insert($dataToInsert);
            $this->info('Brands fetched and inserted successfully.');
        } else {
            $this->info('No brands to insert.');
        }
    }
}
