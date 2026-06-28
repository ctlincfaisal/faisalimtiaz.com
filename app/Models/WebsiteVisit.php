<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteVisit extends Model
{
    use HasFactory;

    protected $fillable = [
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
}
