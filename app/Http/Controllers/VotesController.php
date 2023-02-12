<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VotesController extends Controller
{

    public function vote($name, $type, $id) {
        if($name === 'post') {
            if($type === 'upvote') {
                return $this->incVotePost($id);
            } elseif($type === 'downvote') {
                return $this->decVotePost($id);
            }
        } elseif($name === 'comment') {
            if($type === 'upvote') {
                return $this->incVoteComment($id);
            } elseif($type === 'downvote') {
                return $this->decVoteComment($id);
            }
        }
    }
    private function incVoteComment($id) {
        $comment = Comment::find($id);

        $user = $comment->user()->first();

        $condition = $comment->votes()->where('user_id', Auth::id())->first();

        if($condition !== null && $condition->pivot->type == 'upvote') {
            $comment->votes()->detach(Auth::id(), ['type'=> 'upvote']);
            $user->karma--;
            $user->save();
            return $this->calculateVotes($comment);
        } elseif($condition !== null && $condition->pivot->type == 'downvote'){
            $comment->votes()->detach(Auth::id(), ['type'=> 'downvote']);
            $user->karma++;
        }

        $comment->votes()->attach(Auth::id(), ['type' => 'upvote']);
        $user->karma++;
        $user->save();
        return $this->calculateVotes($comment);
    }

    private function decVoteComment($id) {
        $comment = Comment::find($id);

        $user = User::find($comment->user_id);

        $condition = $comment->votes()->where('user_id', Auth::id())->first();

        if($condition !== null && $condition->pivot->type == 'downvote') {
            $comment->votes()->detach(Auth::id(), ['type'=> 'downvote']);
            $user->karma++;
            $user->save();
            return $this->calculateVotes($comment);
        } elseif($condition !== null && $condition->pivot->type == 'upvote'){
            $comment->votes()->detach(Auth::id(), ['type'=> 'upvote']);
            $user->karma--;
        }

        $comment->votes()->attach(Auth::id(), ['type' => 'downvote']);
        $user->karma--;
        $user->save();
        return $this->calculateVotes($comment);
    }

    private function incVotePost($id) {
        $post = Post::find($id);

        $user = $post->user()->first();

        $condition = $post->votes()->where('user_id', Auth::id())->first();

        if($condition !== null && $condition->pivot->type == 'upvote') {
            $post->votes()->detach(Auth::id(), ['type'=> 'upvote']);
            $user->karma--;
            $user->save();
            return $this->calculateVotes($post);
        } elseif($condition !== null && $condition->pivot->type == 'downvote'){
            $post->votes()->detach(Auth::id(), ['type'=> 'downvote']);
            $user->karma++;
        }

        $post->votes()->attach(Auth::id(), ['type' => 'upvote']);
        $user->karma++;
        $user->save();
        return $this->calculateVotes($post);
    }

    private function decVotePost($id) {
        $post = Post::find($id);

        $user = $post->user()->first();

        $condition = $post->votes()->where('user_id', Auth::id())->first();

        if($condition !== null && $condition->pivot->type == 'downvote') {
            $post->votes()->detach(Auth::id(), ['type'=> 'downvote']);
            $user->karma++;
            $user->save();
            return $this->calculateVotes($post);
        } elseif($condition !== null && $condition->pivot->type == 'upvote'){
            $post->votes()->detach(Auth::id(), ['type'=> 'upvote']);
            $user->karma--;
        }

        $post->votes()->attach(Auth::id(), ['type' => 'downvote']);
        $user->karma--;
        $user->save();
        return $this->calculateVotes($post);
    }

    static function calculateVotes($item) {
        $upvotes = $item->votes()->where('type', 'upvote')->get();
        $downvotes = $item->votes()->where('type', 'downvote')->get();
        return $upvotes->count() - $downvotes->count();
    }
}
