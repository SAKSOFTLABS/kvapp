<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_transaction_id',
        'item_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function serviceTransaction()
    {
        return $this->belongsTo(ServiceTransaction::class, 'service_transaction_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
