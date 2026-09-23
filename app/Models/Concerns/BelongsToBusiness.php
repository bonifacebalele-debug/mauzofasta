<?php

namespace App\Models\Concerns;

use App\Models\Business;
use App\Support\Tenancy\CurrentBusiness;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Applies row-level multi-tenant isolation (spec §12-13): every query against
 * a model using this trait is automatically scoped to the current business,
 * and new records are auto-stamped with it. business_id is never accepted
 * from request input.
 */
trait BelongsToBusiness
{
    public static function bootBelongsToBusiness(): void
    {
        static::addGlobalScope('business', function (Builder $builder) {
            $business = app(CurrentBusiness::class)->get();

            if ($business) {
                $builder->where($builder->getModel()->getTable().'.business_id', $business->id);
            }
        });

        static::creating(function ($model) {
            if (! $model->business_id) {
                $business = app(CurrentBusiness::class)->get();

                if ($business) {
                    $model->business_id = $business->id;
                }
            }
        });
    }

    /**
     * Deliberately bypass tenant scoping. Only for Super Admin cross-tenant
     * views — every call site must be paired with an audit log entry.
     */
    public static function withoutTenantScope(): Builder
    {
        return static::withoutGlobalScope('business');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
