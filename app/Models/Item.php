<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'item_code',
        'opening_stock',
        'purchase_price',
        'sales_price',
        'description',
        'status',
    ];

    public function mainStock()
    {
        return $this->hasOne(MainStock::class, 'item_id');
    }

    public function staffStocks()
    {
        return $this->hasMany(StaffStock::class, 'item_id');
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class, 'item_id');
    }

    public function stockTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'item_id');
    }

    public function serviceItems()
    {
        return $this->hasMany(ServiceItem::class, 'item_id');
    }

    public function getMainStockQtyAttribute()
    {
        return $this->mainStock ? $this->mainStock->quantity : 0;
    }
}
