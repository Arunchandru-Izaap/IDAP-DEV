<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SpecialPriceFactory extends Factory
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
            'discount_percent'=>$this->faker->randomFloat(2, 10, 100),
            'discount_rate'=>$this->faker->randomFloat(2, 10, 100),
            'created_at'=>now(),
            'updated_at'=>now()
            //
        ];
    }
}
