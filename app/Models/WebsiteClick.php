<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\MarketingAccountScoped;

class WebsiteClick extends Model
{
    use HasFactory, MarketingAccountScoped;

    protected $fillable = [
        'marketing_account_id',
        'connected_website_id',
        'website_visit_id',
        'session_id',
        'ip_address',
        'url',
        'path',
        'element',
        'element_text',
        'x',
        'y',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function visit()
    {
        return $this->belongsTo(WebsiteVisit::class, 'website_visit_id');
    }

    public function scopeExceptOwnTraffic(Builder $query): Builder
    {
        return $query->whereDoesntHave('visit', fn ($q) => $q->where('city', 'Mountain View')->where('region', 'California')->where('country', 'United States'));
    }
}
