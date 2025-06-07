<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountMarginMaster extends Model
{
    use HasFactory;
    protected $table='discount_margin_master';
    protected $guarded =['id'];
}
