<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class VqInitiateDates extends Model
{
    use HasFactory;
    protected $table='vq_initiate_dates';
    protected $guarded=['id'];
}