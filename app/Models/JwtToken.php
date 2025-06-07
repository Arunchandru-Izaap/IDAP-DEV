<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class jwtToken extends Model
{
    use HasFactory;
    protected $table='jwt_index';
    protected $guarded=['id'];
}
