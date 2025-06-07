<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaxDiscountCap extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table='max_discount_cap';
    protected $guarded=['id'];
}
