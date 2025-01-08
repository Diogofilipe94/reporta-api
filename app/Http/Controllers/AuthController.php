<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $doesUserAlreadyExists = User::where("email", $request->email)->first();

        if ($doesUserAlreadyExists) {
            return response()->json([
                "error" => "Email already registered"
            ], 400);
        }

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->telephone = $request->telephone;
        $user->password = Hash::make($request->password);
        $user->address_id = $request->address_id;
        $user->role_id = $request->role_id;

        $user->save();

        $token = JWTAuth::claims([
            "role" => $user->role->role
        ])->fromUser($user);

        return response()->json([
            "user" => $user,
            "token" => $token,
        ], 201);
    }

    public function login(Request $request) {


        $login = JWTAuth::attempt([
            "email" => $request->email,
            "password" => $request->password
        ]);


        if(!$login) {
            return response()->json([
                "error" => "Wrong credentials"
            ],400);
        }


        $user = auth()->user();

        $token = JWTAuth::claims([
            "role"=> "user"
        ])->fromUser($user);

        return response()->json([
            "token" => $token
        ]);

    }
}
