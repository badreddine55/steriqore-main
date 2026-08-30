<?php

namespace App\Concerns;

use App\Models\Cabinet;
use App\Models\Scopes\CabinetScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCabinet
{
    /**
     * Boot the cabinet scoping trait for a model.
     */
    public static function bootBelongsToCabinet(): void
    {
        static::addGlobalScope(new CabinetScope);

        static::creating(function ($model) {
            if (empty($model->cabinet_id) && auth()->check() && auth()->user()?->cabinet_id) {
                $model->cabinet_id = auth()->user()->cabinet_id;
            }
        });
    }

    /**
     * Get the cabinet that this model belongs to.
     *
     * @return BelongsTo<Cabinet, $this>
     */
    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class);
    }
}
