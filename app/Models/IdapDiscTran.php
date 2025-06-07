<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdapDiscTran extends Model
{
    use HasFactory;

    protected $table = 'IDAP_DISC_TRAN';
    public $timestamps = false; // Disable timestamps if not required
    public $incrementing = false; // Composite primary key

    protected $primaryKey = [
        'FIN_YEAR',
        'INST_ID',
        'DIV_CODE',
        'ITEM_CODE',
        'REV_NO',
        'STOCKIST_CODE',
        'DISCOUNT_MODE',
        'IDAP_PRNT_Q_ID'
    ];

    protected $fillable = [
        'FIN_YEAR',
        'INST_ID',
        'DIV_CODE',
        'ITEM_CODE',
        'DISC_PCT',
        'DISC_RATE',
        'IDAP_PRNT_Q_ID',
        'METIS_Q_ID',
        'Q_TYPE',
        'REV_NO',
        'UPD_FLAG',
        'Q_DATE',
        'METIS_UPD_DATE',
        'Q_STRT_DATE',
        'Q_END_DATE',
        'STOCKIST_CODE',
        'DISCOUNT_MODE',
        'NETDISCOUNTPERC_STOCKIST',
        'IS_DELETED'
    ];
}