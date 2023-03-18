<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

        $exists = User::where('email', $request->email)->first();

        if($exists) {
            return Response()->json("A user with this email already exists!", 401);
        }

        if($request->hasFile('avatar')) {
            $file = $request['avatar']->getClientOriginalName();
            $name = pathinfo($file, PATHINFO_FILENAME);
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $image = time().'-'.$name.'.'.$ext;
            Storage::putFileAs('public/', $request['avatar'], $image);
            $user = User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password), 'gender' => $request->gender, 'avatar' => $image]);
            $user->assignRole('user');
            Mail::to($request->email)->send(new ConfirmEmail($user->id));
            return $user;
        }

        $user = User::create(['name' => $request->name, 'email' => $request->email, 'password' => Hash::make($request->password), 'gender' => $request->gender]);
        $user->assignRole('user');
        Mail::to($request->email)->send(new ConfirmEmail($user->id));
        return $user;
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        if(!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return Response('Email or password is wrong!', 401);
        };

        $user = Auth::user();

        if($user->is_Verified) {
            $token = $user->createToken('logintoken')->plainTextToken;

            return $token;
        }

        return Response('You should verify your email before you login!', 401);
    }

    public function logout() {
        Auth::user()->tokens->each(function($token, $key) {
            $token->delete();
        });

        return response()->json('Successfully logged out');
    }

    public function user() {
        Auth::user()->setAttribute('isAdmin', Auth::user()->hasRole('admin'));
        return Auth::user();
    }

}
