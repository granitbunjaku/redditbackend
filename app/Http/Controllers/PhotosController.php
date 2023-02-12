<?php

namespace App\Http\Controllers;

use App\Models\Subreddit;
use App\Models\User;
use http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotosController extends Controller
{
    public function getPic($filename) {
        $file = Storage::disk('public')->get($filename);
        return response($file, 200)
            ->header('Content-Type', 'image/*');
    }

    static function updatePhotos($id, $model, $request) {
        $item = $model::findOrFail($id);
        $fileDest = $request->fileDest;
        $arr = ['nocover.jpg', 'nopfp.jpg', 'subreddit-noprofile.jpg'];

        if($request->hasFile("file")) {
            $file = $request["file"]->getClientOriginalName();
            $name = pathinfo($file, PATHINFO_FILENAME);
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $image = time().'-'.$name.'.'.$ext;
            if(!in_array($item->$fileDest, $arr)) Storage::delete('public/'.$item->$fileDest);
            Storage::putFileAs('public/', $request["file"], $image);
            $item->$fileDest = $image;
            $item->save();
            return Response()->json($image);
        }
    }

    public function updatePic($name, $id, Request $request) {
        if($name === "profile") {
            return self::updatePhotos($id, User::class , $request);
        } else if($name === "subreddit") {
            return self::updatePhotos($id, Subreddit::class , $request);
        }
    }
}
