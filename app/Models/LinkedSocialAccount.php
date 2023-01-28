<?php

namespace App\Models;

use App\Traits\dateTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LinkedSocialAccount extends Model
{
    use HasFactory;
    use dateTrait;

    protected $fillable = [
        'user_id', 'provider_name', 'provider_id', 'unionid', 'token', 'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
