<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subreddit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'about',
        'members',
        'profile_image',
        'cover_image',
        'type'
    ];

    public function users() {
        return $this->belongsToMany(User::class)->withPivot('moderator');;
    }

    public function posts() {
        return $this->hasMany(Post::class);
    }

    public function categories() {
        return $this->belongsToMany(Categories::class);
    }

    public function rules() {
        return $this->hasMany(Rules::class);
    }

}
