<?php

namespace App\Models;

use App\Support\MarketingAccountScoped;
use Illuminate\Database\Eloquent\Model;

class MarketingLeadSegment extends Model
{
    use MarketingAccountScoped;

    protected $fillable = [
        'marketing_account_id',
        'name',
        'color',
    ];
}
