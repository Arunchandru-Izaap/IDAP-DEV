<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Institution;
class VoluntaryQuotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'hospital_name'=>$this->faker->name(),
            'institution_id'=>$this->faker->numerify('##'),
            'vq_status'=>1,
            'parent_vq_id'=>1,
            'contract_start_date'=>now(),
            'contract_end_date'=>now(),
            'metis_quotation_id'=>1
        ];
    }
}
