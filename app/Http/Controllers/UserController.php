<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function userPosts($id) {
        $posts = [];

        foreach (Post::where("user_id", $id)->get() as $p){
            $posts[] = PostController::postData($id, $p);
        }

        return $posts;
    }

    public function verifyEmail($id) {
        $user = User::findOrFail($id);
        $user->is_Verified = true;
        if($user->save()) {
            return Response()->json('Email Verified');
        };
    }

    public function getUserById($id) {
        return User::findOrFail($id);
    }
}
