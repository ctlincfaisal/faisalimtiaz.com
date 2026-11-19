<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

trait MarketingAccountScoped
{
    protected static function bootMarketingAccountScoped(): void
    {
        static::addGlobalScope('marketingAccount', function (Builder $builder) {
            $accountId = auth()->user()?->marketing_account_id;

            if ($accountId) {
                $builder->where($builder->getModel()->qualifyColumn('marketing_account_id'), $accountId);
            }
        });

        static::creating(function ($model) {
            if (! $model->marketing_account_id && ($accountId = auth()->user()?->marketing_account_id)) {
                $model->marketing_account_id = $accountId;
            }
        });
    }
}
