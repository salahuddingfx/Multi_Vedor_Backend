<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = ['site_id', 'name', 'email', 'subject', 'message', 'is_read'];

    public function site() {
        return $this->belongsTo(Site::class);
    }
}
