<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QcCheck extends Model
{
    use HasFactory;

    protected $table = 'qc_checks';

    protected $fillable = [
        'set_top_box_id',
        'service_transaction_id',
        'qc_user_id',
        'qc_status',
        'qc_date',
        'remarks',
    ];

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
