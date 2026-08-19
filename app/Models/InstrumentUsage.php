<?php

namespace App\Models;

use Database\Factories\InstrumentUsageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $label_id
 * @property int $patient_id
 * @property int|null $user_id
 * @property string $idempotency_key
 * @property Carbon $used_at
 * @property string|null $procedure_type
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class InstrumentUsage extends Model
{
    /** @use HasFactory<InstrumentUsageFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'label_id',
        'patient_id',
        'user_id',
        'idempotency_key',
        'used_at',
        'procedure_type',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
        ];
    }

    /**
     * Get the label associated with the usage.
     *
     * @return BelongsTo<Label, $this>
     */
    public function label(): BelongsTo
    {
        return $this->belongsTo(Label::class);
    }

    /**
     * Get the patient associated with the usage.
     *
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the practitioner/user who recorded the usage.
     *
     * @return BelongsTo<User, $this>
     */
    public function practitioner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
