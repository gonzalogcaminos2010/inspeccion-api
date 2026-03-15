<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canApproveInspections(): bool
    {
        return $this->isSupervisor() || $this->isAdmin();
    }

    public function approvedInspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'approved_by');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'inspector_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class, 'inspector_id');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class, 'resolved_by');
    }
}
