<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\MarketingAccountScoped;

class Contact extends Model
{
    use HasFactory, MarketingAccountScoped;

    protected $fillable = [
        'marketing_account_id',
        'firstname',
        'lastname',
        'email',
        'budget',
        'details',
    ];
}
