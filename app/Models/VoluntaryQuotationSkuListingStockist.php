<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoluntaryQuotationSkuListingStockist extends Model
{
    use HasFactory;
    protected $table='voluntary_quotation_sku_listing_stockist';
    protected $guarded=['id'];

    public function getStockistDetails(){
        return $this->hasOne(Stockist_master::class, 'id', 'stockist_id');
    }
}
