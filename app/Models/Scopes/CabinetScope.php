<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CabinetScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check()) {
            $user = auth()->user();

            if ($user->isSuperAdmin()) {
                // Super admins have no cabinet and cannot access tenant-scoped data
                $builder->whereRaw('1 = 0');
            } elseif ($user->cabinet_id) {
                $builder->where($model->qualifyColumn('cabinet_id'), $user->cabinet_id);
            }
        }
    }
}
