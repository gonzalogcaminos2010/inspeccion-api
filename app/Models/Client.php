<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ruc',
        'address',
        'contact_name',
        'contact_email',
        'contact_phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function inspectionRequests(): HasMany
    {
        return $this->hasMany(InspectionRequest::class);
    }
}
