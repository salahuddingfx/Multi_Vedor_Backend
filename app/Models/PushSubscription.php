<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    protected $fillable = [
        'site_id',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding'
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
