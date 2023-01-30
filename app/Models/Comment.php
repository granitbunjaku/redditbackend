<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'post_id',
        'comment_id',
        'upvotes',
        'downvotes'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function post() {
        return $this->belongsTo(Post::class);
    }

    public function comments() {
        return $this->belongsToMany(User::class);
    }

    public function parent() {
        return $this->belongsToOne(static::class, 'comment_id');
    }
    public function children() {
        return $this->hasMany(static::class, 'comment_id')->orderBy('id', 'asc');
    }
}
