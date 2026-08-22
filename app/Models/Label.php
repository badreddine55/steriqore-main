<?php

namespace App\Models;

use Database\Factories\LabelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $product_name
 * @property string|null $reference
 * @property string|null $lot_number
 * @property Carbon $expiration_date
 * @property string $status
 * @property int|null $cycle_id
 * @property string|null $cycle_number
 * @property string|null $autoclave_name
 * @property Carbon|null $sterilization_date
 * @property string|null $recall_reason
 * @property Carbon|null $used_at
 * @property int|null $used_by_patient_id
 * @property string|null $used_by_patient_name
 * @property string|null $operator_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Label extends Model
{
    /** @use HasFactory<LabelFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'product_name',
        'reference',
        'lot_number',
        'expiration_date',
        'status',
        'cycle_id',
        'cycle_number',
        'autoclave_name',
        'sterilization_date',
        'recall_reason',
        'used_at',
        'used_by_patient_id',
        'used_by_patient_name',
        'operator_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expiration_date' => 'datetime',
            'sterilization_date' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * Get the usages for the label.
     *
     * @return HasMany<InstrumentUsage, $this>
     */
    public function usages(): HasMany
    {
        return $this->hasMany(InstrumentUsage::class);
    }

    /**
     * Get the patient who used the instrument.
     *
     * @return BelongsTo<Patient, $this>
     */
    public function usedByPatient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'used_by_patient_id');
    }

    /**
     * Determine if the label is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || $this->expiration_date->isPast();
    }

    /**
     * Determine if the label is recalled.
     */
    public function isRecalled(): bool
    {
        return $this->status === 'recalled';
    }

    /**
     * Determine if the label is already used.
     */
    public function isAlreadyUsed(): bool
    {
        return $this->status === 'already_used' || $this->used_at !== null;
    }
}
