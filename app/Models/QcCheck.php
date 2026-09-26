<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QcCheck extends Model
{
    use HasFactory;

    protected $table = 'qc_checks';

    protected $fillable = [
        'voucher_number',
        'set_top_box_id',
        'service_transaction_id',
        'qc_user_id',
        'qc_status',
        'qc_date',
        'remarks',
    ];

    public function getVoucherNumberAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }
        $dateStr = $this->qc_date ? \Carbon\Carbon::parse($this->qc_date)->format('Ymd') : ($this->created_at ? $this->created_at->format('Ymd') : date('Ymd'));
        return 'QC-' . $dateStr . '-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    public function setTopBox()
    {
        return $this->belongsTo(SetTopBox::class, 'set_top_box_id');
    }

    public function serviceTransaction()
    {
        return $this->belongsTo(ServiceTransaction::class, 'service_transaction_id');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'qc_user_id');
    }
}
