<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        // \App\Models\Institution::factory(20)->create();
        // \App\Models\Stockist_master::factory(20)->create();
        // \App\Models\SpecialPrice::factory(20)->create();
        // \App\Models\CielingMaster::factory(20)->create(); 
        \App\Models\LastYearPrice::factory(20)->create();
        
    }
}
