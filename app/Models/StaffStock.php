<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffStock extends Model
{
    use HasFactory;

    protected $table = 'staff_stocks';

    protected $fillable = [
        'staff_id',
        'item_id',
        'quantity',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
