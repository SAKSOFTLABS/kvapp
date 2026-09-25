<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckinVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'checkin_date',
        'operator_id',
        'total_boxes',
        'remarks',
        'created_by',
    ];

    public function operator()
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(CheckinVoucherItem::class, 'checkin_voucher_id');
    }
}
