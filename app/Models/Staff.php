<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'name',
        'designation',
        'mobile',
        'address',
        'username',
        'status',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'staff_id');
    }

    public function stocks()
    {
        return $this->hasMany(StaffStock::class, 'staff_id');
    }

    public function transfers()
    {
        return $this->hasMany(StockTransfer::class, 'staff_id');
    }

    public function services()
    {
        return $this->hasMany(ServiceTransaction::class, 'staff_id');
    }
}
