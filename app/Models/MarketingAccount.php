<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'plan',
        'owner_user_id',
        'paddle_customer_id',
        'paddle_subscription_id',
        'paddle_subscription_status',
        'paddle_next_billed_at',
        'paddle_scheduled_change_action',
        'paddle_scheduled_change_effective_at',
        'paddle_last_event_at',
    ];

    protected $casts = [
        'paddle_next_billed_at' => 'datetime',
        'paddle_scheduled_change_effective_at' => 'datetime',
        'paddle_last_event_at' => 'datetime',
    ];

    public function billingSubscriptions()
    {
        return $this->hasMany(MarketingBillingSubscription::class, 'marketing_account_id');
    }
}
