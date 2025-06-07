<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalPeriod extends Model
{
    use HasFactory;
    protected $table='approval_period';
    protected $guarded=['id'];
}
