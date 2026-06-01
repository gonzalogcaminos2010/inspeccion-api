<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// CategoryEquipmentField is in this same namespace (App\Models); no import needed.

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_item_id',
        'inspection_template_id',
        'equipment_id',
        'inspector_id',
        'approved_by',
        'status',
        'overall_result',
        'observations',
        'score',
        'started_at',
        'completed_at',
        'approved_at',
        'supervisor_notes',
        'inspector_signature',
        'inspector_signed_at',
        'supervisor_signature',
        'supervisor_signed_at',
        'client_signature',
        'client_signed_at',
        'certificate_number',
        'certificate_issued_at',
        'qr_token',
        'equipment_data',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'approved_at' => 'datetime',
            'inspector_signed_at' => 'datetime',
            'supervisor_signed_at' => 'datetime',
            'client_signed_at' => 'datetime',
            'certificate_issued_at' => 'datetime',
            'equipment_data' => 'array',
        ];
    }

    public function workOrderItem(): BelongsTo
    {
        return $this->belongsTo(WorkOrderItem::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InspectionTemplate::class, 'inspection_template_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(InspectionAnswer::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(InspectionPhoto::class);
    }

    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class);
    }

    public static function generateCertificateNumber(): string
    {
        $date = now()->format('Ymd');
        $last = static::where('certificate_number', 'like', "CERT-{$date}-%")
            ->orderByDesc('certificate_number')
            ->value('certificate_number');
        $seq = $last ? (int) substr($last, -4) + 1 : 1;

        return sprintf('CERT-%s-%04d', $date, $seq);
    }

    /**
     * Resolved identification fields for this inspection's equipment category,
     * each merged with its current value and a `locked` flag.
     *
     * Value precedence: in-flight buffer (equipment_data) over persisted
     * equipment.metadata. `locked` = immutable field that already has a
     * persisted value (first-capture-only, mirrors syncEquipmentData()).
     *
     * Resolves the category only from already-loaded relations, so list
     * endpoints that don't eager-load it pay no N+1 cost (returns []).
     *
     * @return array<int, array<string, mixed>>
     */
    public function identificationFields(): array
    {
        $category = null;
        if ($this->relationLoaded('workOrderItem') && $this->workOrderItem?->relationLoaded('category')) {
            $category = $this->workOrderItem->category;
        }
        if (! $category && $this->relationLoaded('equipment') && $this->equipment?->relationLoaded('category')) {
            $category = $this->equipment->category;
        }
        if (! $category) {
            return [];
        }

        $fields = $category->relationLoaded('equipmentFields')
            ? $category->equipmentFields
            : $category->equipmentFields()->get();

        $buffer = $this->equipment_data ?? [];
        $persisted = $this->equipment?->metadata ?? [];

        return $fields
            ->sortBy('sort_order')
            ->map(function (CategoryEquipmentField $field) use ($buffer, $persisted) {
                $hasPersisted = array_key_exists($field->key_name, $persisted)
                    && $persisted[$field->key_name] !== null
                    && $persisted[$field->key_name] !== '';
                $value = $buffer[$field->key_name] ?? $persisted[$field->key_name] ?? null;

                return [
                    'key_name' => $field->key_name,
                    'label' => $field->label,
                    'type' => $field->type,
                    'options' => $field->options,
                    'unit' => $field->unit,
                    'is_required' => (bool) $field->is_required,
                    'is_mutable' => (bool) $field->is_mutable,
                    'sort_order' => $field->sort_order,
                    'value' => $value,
                    'locked' => ! $field->is_mutable && $hasPersisted,
                ];
            })
            ->values()
            ->all();
    }
}
