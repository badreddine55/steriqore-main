<?php

namespace App\Models;

use Database\Factories\CabinetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Cabinet extends Model
{
    /** @use HasFactory<CabinetFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
    ];

    /**
     * Get the users/staff belonging to this cabinet.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the administrator who owns this cabinet.
     *
     * @return HasOne<User, $this>
     */
    public function admin(): HasOne
    {
        return $this->hasOne(User::class)->whereIn('role', ['admin', 'administrator']);
    }

    /**
     * Get the patients registered in this cabinet.
     *
     * @return HasMany<Patient, $this>
     */
    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    /**
     * Get the sterilization labels belonging to this cabinet.
     *
     * @return HasMany<Label, $this>
     */
    public function labels(): HasMany
    {
        return $this->hasMany(Label::class);
    }

    /**
     * Get the instrument usages recorded in this cabinet.
     *
     * @return HasMany<InstrumentUsage, $this>
     */
    public function instrumentUsages(): HasMany
    {
        return $this->hasMany(InstrumentUsage::class);
    }
}
