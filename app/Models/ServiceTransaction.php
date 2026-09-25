<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_code',
        'service_date',
        'set_top_box_id',
        'staff_id',
        'total_cost',
        'remarks',
        'created_by',
    ];

    public function setTopBox()
    {
        return $this->belongsTo(SetTopBox::class, 'set_top_box_id');
    }

    public function technician()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function items()
    {
        return $this->hasMany(ServiceItem::class, 'service_transaction_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
