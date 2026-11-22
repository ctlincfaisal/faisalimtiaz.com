<?php

namespace App\Models;

use App\Support\MarketingAccountScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory, MarketingAccountScoped;

    protected $fillable = [
        'marketing_account_id',
        'firstname',
        'lastname',
        'email',
        'project_type',
        'budget_range',
        'budget',
        'details',
    ];
}
