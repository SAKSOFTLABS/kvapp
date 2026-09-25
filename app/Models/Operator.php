<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    use HasFactory;

    protected $table = 'operators';

    protected $fillable = [
        'operator_name',
        'operator_code',
        'contact_person',
        'mobile',
        'location',
        'status',
    ];

    public function boxes()
    {
        return $this->hasMany(SetTopBox::class, 'operator_id');
    }

    public function checkinVouchers()
    {
        return $this->hasMany(CheckinVoucher::class, 'operator_id');
    }
}
