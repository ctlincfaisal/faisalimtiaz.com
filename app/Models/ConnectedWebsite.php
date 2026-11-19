<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\MarketingAccountScoped;

class ConnectedWebsite extends Model
{
    use HasFactory, MarketingAccountScoped;

    protected $fillable = [
        'marketing_account_id',
        'name',
        'domain',
        'tracking_key',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
