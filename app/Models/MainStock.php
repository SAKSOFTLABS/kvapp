<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainStock extends Model
{
    use HasFactory;

    protected $table = 'main_stocks';

    protected $fillable = [
        'item_id',
        'quantity',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
