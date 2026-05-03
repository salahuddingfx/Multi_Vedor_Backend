<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewMedia extends Model
{
    use HasFactory;

    protected $fillable = ['review_id', 'file_path', 'type'];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }
}
