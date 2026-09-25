<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_code',
        'transfer_date',
        'staff_id',
        'item_id',
        'quantity',
        'remarks',
        'created_by',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
