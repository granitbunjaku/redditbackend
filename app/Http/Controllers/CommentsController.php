<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentsController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required',
            'post_id' => 'required',
        ]);

        $request->merge(['user_id' => Auth::id()]);
        $data = $request->only('content', 'user_id', 'post_id', 'comment_id');

        $user_name = Auth::user()->name;
        $postTitle = Post::find($request->post_id)->title;

        $comment = Comment::create($data);

        return Response(["data" => $comment ,"user_name" => $user_name , "post" => $postTitle, "votes" => 0]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        return Response()->json($this->showComments($id, Auth::id()));
    }
    public function showComment($id)
    {
        return Response()->json($this->showComments($id, null));
    }

    function getReplies($comment, $userid) {
        $arr = [];
        foreach ($comment->children()->get() as $reply) {
            $user = User::find($reply->user_id)->name;
            $condition = $userid ? $reply->votes()->where('user_id', Auth::id())->first() : null;
            $arr[] = ['data' => $reply, 'user_name' => $user, 'isVoted' => $condition ? $condition->pivot->type : "no", "votes" => VotesController::calculateVotes($reply)];
            $this->getReplies($reply, $userid);
        }
        $comment->setAttribute('replies', $arr);
    }

    public function showComments($id, $userid)
    {
        $post = Post::findOrFail($id);
        $comments = [];

        foreach ($post->comments()->where('comment_id', null)->get() as $c){
            $this->getReplies($c, $userid);
            $condition = $userid ? $c->votes()->where('user_id', Auth::id())->first() : null;
            $user_name = User::find($c->user_id)->name;
            $postTitle = Post::find($c->post_id)->title;
            $comment = ["data" => $c ,"user_name" => $user_name , "post" => $postTitle, "votes" => VotesController::calculateVotes($c), "isVoted" => $condition ? $condition->pivot->type : "no"];
            $comments[] = $comment;
        }

        return $comments;
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required'
        ]);

        $data = $request->only('content');

        $comment = Comment::find($id);
        $comment->update($data);
        $comment->save();
        return Response("Successfully updated comment");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $comment = Comment::find($id);
        $comment->delete();
    }
}
