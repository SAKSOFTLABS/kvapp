<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'staff_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isFrontOffice(): bool
    {
        if ($this->isAdmin()) return true;
        if ($this->role === 'front_office') return true;
        return $this->staff && $this->staff->designation === 'Front Office';
    }

    public function isFrontOfficeOnly(): bool
    {
        if ($this->isAdmin()) return false;
        if ($this->role === 'front_office') return true;
        return $this->staff && strtolower($this->staff->designation ?? '') === 'front office';
    }

    public function isQc(): bool
    {
        if ($this->isAdmin()) return true;
        if ($this->role === 'qc') return true;
        return $this->staff && $this->staff->designation === 'QC';
    }

    public function isService(): bool
    {
        if ($this->isAdmin()) return true;
        if ($this->role === 'service') return true;
        return $this->staff && $this->staff->designation === 'Service';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['staff', 'front_office', 'qc', 'service']);
    }
}
