<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoxModel extends Model
{
    use HasFactory;

    protected $table = 'box_models';

    protected $fillable = [
        'model_name',
        'model_code',
        'description',
        'status',
    ];

    public function setTopBoxes()
    {
        return $this->hasMany(SetTopBox::class, 'box_model_id');
    }
}
