<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'password_hash',
    ];

    protected $hidden = [
        'password_hash',
    ];
}
