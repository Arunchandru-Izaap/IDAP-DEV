<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class VoluntaryQuotationTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\VoluntaryQuotation::factory(10)->create();
    }
}
