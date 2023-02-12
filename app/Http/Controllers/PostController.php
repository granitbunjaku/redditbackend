<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Subreddit;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return Response()->json($this->getPosts(Auth::id()));
    }

    public function showAllPosts()
    {
        return Response()->json($this->getPosts(null));
    }

    private function getPosts($id) {
        $posts = [];

        foreach (Post::orderByDesc("id")->get() as $p){
            $posts[] = $this->postData($id, $p);
        }

        return $posts;
    }

    public function getCommunitiesPosts() {
        $posts = [];

        $user = User::findOrFail(Auth::id());

        $subreddits = $user->subreddits()->get();

        foreach ($subreddits as $s){
            foreach ($s->posts()->orderByDesc("id")->get() as $item) {
                $posts[] = $this->postData(Auth::id(), $item);
            }
        }

        return $posts;
    }


    public function getTrendingPosts() {

        $posts = Post::join('post_user as p_u', 'p_u.post_id', '=', 'posts.id')
            ->join('subreddits as s', 's.id', '=', 'posts.subreddit_id')
            ->where('p_u.type', 'upvote')
            ->whereNotNull('posts.postfile')
            ->where('posts.postfile', 'not like', '%.mp4')
            ->groupBy('p_u.post_id')
            ->selectRaw('posts.title, posts.postfile, s.name as subreddit, COUNT(p_u.post_id) as votes')
            ->orderBy('votes', 'desc')
            ->take(4)
            ->get();

        return $posts;
    }

    static function postData($id, $post) {
        $user_name = Post::find($post->id)->user()->pluck('name');
        $subreddit = Post::find($post->id)->subreddit()->pluck('name');
        $pvotes = $id ? Post::find($post->id)->votes()->where('user_id', Auth::id())->first() : null;
        $post = ["data" => $post ,"user_name" => $user_name , "subreddit" => $subreddit, "isVoted" => $pvotes ? $pvotes->pivot->type : "no", "votes" => VotesController::calculateVotes($post)];
        return $post;
    }


        /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'title' => 'required',
            'subreddit_id' => 'required'
        ]);

        if($request->hasFile('postfile')) {
            $file = $request['postfile']->getClientOriginalName();
            $name = pathinfo($file, PATHINFO_FILENAME);
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $image = time().'-'.$name.'.'.$ext;
            Storage::putFileAs('postFiles/', $request['postfile'], $image);
            return Post::create(['title' => $request->title, 'subreddit_id' => $request->subreddit_id, 'content' => $request->content, 'postfile' => $image, 'user_id' => Auth::id()]);
        }

        return Post::create(['title' => $request->title, 'subreddit_id' => $request->subreddit_id, 'content' => $request->content, 'postfile' => null, 'user_id' => Auth::id()]);

    }

    public function returnFile($filename) {
        $file = Storage::disk('postFiles')->get($filename);

        $pathinfo = pathinfo($filename, PATHINFO_EXTENSION);
        $type = $pathinfo === "mp4" ? "video/".$pathinfo : "image/".$pathinfo;

        return response($file, 200)
            ->header('Content-Type', $type);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return Response()->json($this->getPost($id, Auth::id()));
    }

    public function showPost($id)
    {
        return Response()->json($this->getPost($id,null));
    }
    private function getPost($id, $userid) {
        $post = Post::findOrFail($id);
        $user_name = $post->user()->pluck('name');
        $subreddit = $post->subreddit()->pluck('name');
        $pvotes = $userid ? $post->votes()->where('user_id', Auth::id())->first() : null;
        $result = ["data" => $post ,"user_name" => $user_name, "subreddit" => $subreddit, "isVoted" => $pvotes ? $pvotes->pivot->type : "no", "votes" => VotesController::calculateVotes($post)];
        return $result;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if($request->hasFile('postfile')) {
            $file = $request['postfile']->getClientOriginalName();
            $name = pathinfo($file, PATHINFO_FILENAME);
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $image = time().'-'.$name.'.'.$ext;

            if($post->postfile) Storage::delete('postFiles/'.$post->postfile);
            Storage::putFileAs('postFiles/', $request['postfile'], $image);
            $post->postfile = $image;
        }

        $post->content = $request->content;

        if($post->save()) {
            return Response()->json(["postfile" => $post->postfile, "content" => $post->content]);
        }

        return Response()->json("Something went wrong!", 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
    }

}
