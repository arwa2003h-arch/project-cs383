<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //POST /api/register
    //creat account = get token
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'phone'=> 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed', // need password_confirmation
        ]);

        $user = User::create($data);//save user
        $user->refresh(); // reload, role from db default customer 

        return response()->json($this->tokenResponse($user), 201);//created
    }

    //POST /api/login
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'=> 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();//find user by email

        if (!$user || !Hash::check($data['password'], $user->password)) {//no user or wrong password
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        return response()->json($this->tokenResponse($user), 200);
    }

    //GET /api/me (logged in)
    public function me(Request $request)
    {
        $user = User::find($request->user()->id);//id from token, load user from db

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return new UserResource($user);
    }

    //POST /api/logout 
  public function logout()
    {
        return response()->json(['message' => 'Logged out. Delete the token on the client.'], 200);
    }

    private function tokenResponse(User $user): array
    {
        $token = JwtService::encode([
            'sub' => $user->id,    
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,  
        ]);

        return [
            'user'=> new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JwtService::ttl() * 60, // seconds
        ];
    }
}
