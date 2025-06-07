<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoluntaryQuotationSkuListing extends Model
{
    use HasFactory;
    protected $table='voluntary_quotation_sku_listing';
    protected $guarded=['id'];

    public function getSkuStockist()
    {
        return $this->hasMany(VoluntaryQuotationSkuListingStockist::class, 'sku_id', 'id')
        ->whereHas('getStockistDetails', function ($query) {
            $query->where('stockist_type_flag', 1); 
        });
    }
}
