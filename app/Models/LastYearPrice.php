<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LastYearPrice extends Model
{
    use HasFactory;
    protected $table='last_year_price';
    protected $guarded =['id'];
}
