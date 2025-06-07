<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stockist_master extends Model
{
    use HasFactory;
    protected $table='stockist_master';
    protected $guarded =['id'];
    public $timestamps = false;
}
