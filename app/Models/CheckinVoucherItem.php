<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckinVoucherItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'checkin_voucher_id',
        'set_top_box_id',
        'barcode_number',
        'stb_status',
    ];

    public function voucher()
    {
        return $this->belongsTo(CheckinVoucher::class, 'checkin_voucher_id');
    }

    public function checkinVoucher()
    {
        return $this->belongsTo(CheckinVoucher::class, 'checkin_voucher_id');
    }

    public function setTopBox()
    {
        return $this->belongsTo(SetTopBox::class, 'set_top_box_id');
    }
}
