<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetTopBox extends Model
{
    use HasFactory;

    protected $table = 'set_top_boxes';

    protected $fillable = [
        'box_model_id',
        'operator_id',
        'box_name',
        'barcode_number',
        'remarks',
        'status',
        'stb_status',
    ];

    public function boxModel()
    {
        return $this->belongsTo(BoxModel::class, 'box_model_id');
    }

    public function operator()
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }

    public function getBoxNameAttribute($value)
    {
        return $this->boxModel ? $this->boxModel->model_name : $value;
    }

    public function serviceHistory()
    {
        return $this->hasMany(ServiceTransaction::class, 'set_top_box_id')->orderBy('service_date', 'desc');
    }

    public function qcHistory()
    {
        return $this->hasMany(QcCheck::class, 'set_top_box_id')->orderBy('qc_date', 'desc');
    }

    public function isDelivered(): bool
    {
        if ($this->stb_status === 'delivered') {
            return true;
        }

        $latestCheckout = CheckoutVoucherItem::where('set_top_box_id', $this->id)
            ->latest('id')
            ->first();

        if (!$latestCheckout) {
            return false;
        }

        $latestCheckin = CheckinVoucherItem::where('set_top_box_id', $this->id)
            ->latest('id')
            ->first();

        if (!$latestCheckin) {
            return true;
        }

        return $latestCheckout->id > $latestCheckin->id;
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isDelivered()) {
            return 'Delivered (Out with Operator)';
        }

        return match ($this->stb_status) {
            'complaint' => 'Complaint (In Check-in)',
            'reservice' => 'Reservice (< 30 Days)',
            'service_done' => 'Service Done',
            'tested_ok' => 'Tested OK (QC Passed)',
            'flash' => 'Flash (Dead Box)',
            'send_to_pk' => 'Send to PK',
            'delivered' => 'Delivered (Out with Operator)',
            default => ucfirst($this->stb_status ?? 'complaint'),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->isDelivered()) {
            return 'bg-info-subtle text-info border border-info fw-bold';
        }

        return match ($this->stb_status) {
            'complaint' => 'bg-warning-subtle text-warning border border-warning',
            'reservice' => 'bg-danger text-white border border-danger fw-bold',
            'service_done' => 'bg-primary-subtle text-primary border border-primary',
            'tested_ok' => 'bg-success-subtle text-success border border-success',
            'flash' => 'bg-danger-subtle text-danger border border-danger',
            'send_to_pk' => 'bg-dark-subtle text-dark border border-secondary',
            'delivered' => 'bg-info-subtle text-info border border-info fw-bold',
            default => 'bg-secondary-subtle text-secondary',
        };
    }
}
