<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PocMaster extends Model
{
    use HasFactory;
    protected $table='poc_master';
    protected $guarded =['id'];
}
