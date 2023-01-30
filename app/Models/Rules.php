<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rules extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'subreddit_id'
    ];

    public function subreddit() {
        return $this->belongsTo(Subreddit::class);
    }
}
