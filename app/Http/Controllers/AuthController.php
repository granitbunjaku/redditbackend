<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function register(Request $request) {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'gender' => 'required'
        ]);

        if($request->hasFile('avatar')) {
            $file = $request['avatar']->getClientOriginalName();
            $name = pathinfo($file, PATHINFO_FILENAME);
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $image = time().'-'.$name.'.'.$ext;
            Storage::putFileAs('public/', $request['avatar'], $image);
        }

        return User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password), 'gender' => $request->gender, 'avatar' => $image]);
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        if(!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return 'User is not authenticated';
        };

        $user = Auth::user();

        $token = $user->createToken('logintoken')->plainTextToken;

        return $token;
    }

    public function logout() {
        Auth::user()->tokens->each(function($token, $key) {
            $token->delete();
        });

        return response()->json('Successfully logged out');
    }

    public function profilepic($id) {
        $filename = User::findOrFail($id)->avatar;
        $file = Storage::disk('public')->get($filename);
        return response($file, 200)
        ->header('Content-Type', 'image/*');
    }

    public function user() {
        return Auth::user();
    }
}
