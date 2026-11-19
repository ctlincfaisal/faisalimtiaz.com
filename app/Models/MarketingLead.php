<?php

namespace App\Models;

use App\Support\MarketingAccountScoped;
use Illuminate\Database\Eloquent\Model;

class MarketingLead extends Model
{
    use MarketingAccountScoped;

    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'replied' => 'Replied',
    ];

    protected $fillable = [
        'marketing_account_id',
        'first_name',
        'last_name',
        'company',
        'email',
        'status',
        'segment',
    ];
}
