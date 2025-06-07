<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalEmailScheduleMaster extends Model
{
    use HasFactory;
    protected $table='approval_email_schedule';
    protected $guarded =['id'];
}
