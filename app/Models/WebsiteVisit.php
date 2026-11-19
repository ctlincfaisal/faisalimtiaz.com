<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\MarketingAccountScoped;

class WebsiteVisit extends Model
{
    use HasFactory, MarketingAccountScoped;

    protected $fillable = [
        'marketing_account_id',
        'connected_website_id',
        'session_id',
        'ip_address',
        'user_agent',
        'url',
        'path',
        'referrer',
        'country',
        'region',
        'city',
        'postal',
        'timezone',
        'latitude',
        'longitude',
        'organization',
        'browser',
        'browser_version',
        'operating_system',
        'device_type',
        'screen_width',
        'screen_height',
        'viewport_width',
        'viewport_height',
        'visited_at',
        'last_seen_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function clicks()
    {
        return $this->hasMany(WebsiteClick::class);
    }

    public function scopeExceptOwnTraffic(Builder $query): Builder
    {
        return $query->whereNot(fn ($q) => $q->where('city', 'Mountain View')->where('region', 'California')->where('country', 'United States'));
    }
}
