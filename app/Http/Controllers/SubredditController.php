<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Subreddit;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Psy\Exception\ErrorException;
use function PHPUnit\Framework\isNull;

class SubredditController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $subreddits = [];

        foreach (Subreddit::orderByDesc("members")->get() as $p){
            $user = $p->users()->find(Auth::id());
            $subreddit = ["data" => $p ,"role" => $user ? "moderator" : "user"];

            $subreddits[] = $subreddit;
        }

        return Response()->json($subreddits);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|min:3",
            "type" => "required",
            "categories" => "required"
        ]);

        $data = $request->only(['name', 'type']);
        $subreddit = Subreddit::create($data);
        $subreddit->users()->attach(Auth::id(), ['moderator'=> 1 ]);
        $categories = $request->categories;

        if($categories) {
            $subreddit->categories()->attach($categories);
            return $subreddit->id;
        } else {
            return Response('Category doesnt exist', 404);
        }

    }

    private function getSubredditPosts($id, $name) {
        $posts = [];
        $subreddit = Subreddit::where("name", $name)->first();

        foreach ($subreddit->posts()->orderByDesc("id")->get() as $p){
            $posts[] = PostController::postData($id, $p);
        }

        return $posts;
    }

    public function subredditPostsNotAuth($name) {
        return Response()->json($this->getSubredditPosts(null, $name));
    }

    public function subredditPosts($name) {
        return Response()->json($this->getSubredditPosts(Auth::id(), $name));
    }

    function joinSubreddit(Request $request) {
        $subreddit = Subreddit::where('name', $request->name)->first();

        $user = $subreddit->users()->find(Auth::id());
        $moderators = $subreddit->users()->where('moderator', '1')->get();

        if(!$user) {
            $subreddit->users()->attach(Auth::id(), ['moderator'=> 0 ]);
            $subreddit->members++;
            $subreddit->save();
            return $subreddit->members;
        } else {
            if($user->pivot->moderator) {
                if(count($moderators) > 1) {
                    $subreddit->users()->detach(Auth::id());
                    $subreddit->members--;
                    $subreddit->save();
                    return $subreddit->members;
                } else {
                    return Response("You can't leave since you are the only moderator", 404);
                }
            } else {
                $subreddit->users()->detach(Auth::id());
                $subreddit->members--;
                $subreddit->save();
                return $subreddit->members;
            }
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($name)
    {
        return Response()->json($this->showSubreddit(Auth::id(), $name));
    }

    public function showSubNotAuth($name)
    {
        return Response()->json($this->showSubreddit(null, $name));
    }

    private function showSubreddit($id, $name) {
        $subreddit = Subreddit::where('name', $name)->first();

        if(!$subreddit) {
            abort(404);
        }

        $user = $id ? $subreddit->users()->get()->find($id) : null;

        $moderators = $subreddit->users()->where('moderator', '1')->get();

        if ($user) {
            return ["data" => $subreddit, "isJoined" => true, "isModerator" => $user->pivot->moderator, 'moderators' => $moderators];
        }

        return ["data" => $subreddit, "isJoined" => false, 'moderators' => $moderators];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $subreddit = Subreddit::findOrFail($id);
        $subreddit->about = $request->about;
        if($subreddit->save()) {
            return $subreddit->about;
        };
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
