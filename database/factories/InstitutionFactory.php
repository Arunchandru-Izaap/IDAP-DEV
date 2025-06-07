<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class InstitutionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'institution_name'=>$this->faker->name(),
            'institution_code'=>$this->faker->numerify('##'),
            'key_account_name'=>Str::random(2),
            'city'=>$this->faker->city(),
            'region'=>Str::random(4),
            'hq'=>Str::random(3),
            'zone'=>Str::random(5),
            'retailer_name_1'=>$this->faker->name().'1',
            'retailer_name_2'=>$this->faker->name().'2',
            'retailer_name_3'=>$this->faker->name().'3',
            'address'=>$this->faker->address(),
            'updated_at'=>now()
        ];
    }
}
