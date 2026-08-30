<?php

namespace App\Models;

use App\Concerns\BelongsToCabinet;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $cabinet_id
 * @property string $first_name
 * @property string $last_name
 * @property string $dossier_id
 * @property array<string>|null $allergies
 * @property array<string>|null $allergy_severities
 * @property Carbon|null $last_visit
 * @property string|null $cabinet_room
 * @property Carbon|null $birth_date
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $notes
 * @property-read string $full_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use BelongsToCabinet, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'cabinet_id',
        'first_name',
        'last_name',
        'dossier_id',
        'allergies',
        'allergy_severities',
        'last_visit',
        'cabinet_room',
        'birth_date',
        'phone',
        'email',
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
            'cabinet_id' => 'integer',
            'allergies' => 'array',
            'allergy_severities' => 'array',
            'last_visit' => 'date:Y-m-d',
            'birth_date' => 'date:Y-m-d',
        ];
    }

    /**
     * Get the patient's full name.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => trim(($attributes['first_name'] ?? '').' '.($attributes['last_name'] ?? '')),
        );
    }
}
