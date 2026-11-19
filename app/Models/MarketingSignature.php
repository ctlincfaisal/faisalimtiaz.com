<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\MarketingAccountScoped;

class MarketingSignature extends Model
{
    use HasFactory, MarketingAccountScoped;

    protected $fillable = [
        'marketing_account_id',
        'name',
        'image_path',
        'image_name',
        'mime_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
