<?php

namespace App\Http\Controllers;

use App\Models\Subreddit;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    function findByName(Request $request) {
        $searchName = $request->name;
        $users = User::where('name', 'LIKE' , '%'.$searchName.'%')->get();
        $subreddits = Subreddit::where('name', 'LIKE' , '%'.$searchName.'%')->get();

        return ['users' => $users, 'subreddits' => $subreddits];
    }
}
