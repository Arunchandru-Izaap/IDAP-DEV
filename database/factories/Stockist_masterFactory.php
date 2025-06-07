<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class Stockist_masterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'stockist_name'=> $this->faker->name(),
            'email_id'=>$this->faker->unique()->safeEmail(),
            'stockist_type_flag'=>$this->faker->boolean(),
            'updated_at'=>now()
        ];
    }
}
