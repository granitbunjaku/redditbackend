<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'flair',
        'subreddit_id',
        'content',
        'user_id'
    ];

    public function user() {
        return $this->belongsTo(Post::class);
    }

    public function subreddit() {
        return $this->belongsTo(Subreddit::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }
}
