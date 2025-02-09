<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $requestData = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:20', 'unique:users,name'],
            'phone_number' => ['required', 'min:11', 'max:11'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                // 'confirmed', 
                Password::min(8)
                // ->letters()
                // ->mixedCase()
                // ->numbers()
                // ->symbols()
            ],
        ]);

        $user = User::create($requestData);

        if (! $user) {
            return response()->json(['message' => 'Could not create user'], 500);
        }

        Auth::login($user);

        $token = $user->createToken('API TOKEN')->plainTextToken;
        $token = explode('|', $token)[1];

        return response()->json([
            'message' => 'User created succesfully',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $requestData = $request->validate([
            'email' => ['required', 'string', 'min:3',],
            'password' => [
                'required',
                // 'confirmed', 
                Password::min(8)
                // ->letters()
                // ->mixedCase()
                // ->numbers()
                // ->symbols()
            ],
        ]);

        $user = User::where('email', $requestData['email'])
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'The inputed email or username does not exist in our records'], 400);
        }

        if (! Auth::attempt(['email' => $user->email, 'password' => $request->password])) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('API TOKEN')->plainTextToken;
        $cleanToken = explode('|', $token)[1];

        return response()->json([
            'message' => 'Login succesful',
            'token' => $cleanToken,
            'user' => $user,
        ], 200);
    }

    public function logout()
    {
        $user =  Auth::user();

        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
