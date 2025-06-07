<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LastYearPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'sku_id'=>$this->faker->numerify('##'),
            'institution_id'=>$this->faker->name(),
            'division_id'=>$this->faker->unique()->numerify('ABC###'),
            'discount_percent'=>$this->faker->randomFloat(2, 10, 100),
            'discount_rate'=>$this->faker->randomFloat(2, 10, 100),
            'created_at'=>now(),
            'updated_at'=>now()
        ];
    }
}
