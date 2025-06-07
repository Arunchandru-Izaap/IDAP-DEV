<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ceilingMaster extends Model
{
    use HasFactory;
    protected $table='ceiling_master';
    protected $guarded=['id'];
}
