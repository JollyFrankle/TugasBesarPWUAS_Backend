<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ApiResource;
use Illuminate\Auth\Events\Registered;
// use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'username' => 'required|string|unique:users',
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return new ApiResource(422, "Terjadi kesalahan!", $validator->errors());
        }

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => bcrypt($request->password),
        ]);

        
        if($user->save()) {
            // event(new Registered($user));

            return new ApiResource(200, 'User berhasil dibuat', $user);
        } else {
            return new ApiResource(500, 'User gagal dibuat', $user);
        }
    }

    public function login(Request $request)
    {
        $cred = $request->only('username', 'password');
        $user = null;
        if (Auth::attempt($cred)) {
            $user = Auth::user();
        } else if (Auth::attempt(['email' => $cred['username'] ?? null, 'password' => $cred['password'] ?? null])) {
            $user = Auth::user();
        }
        
        if($user != null) {
            $token = $user->createToken('auth_token')->accessToken;
        
            return new ApiResource(200, 'Login berhasil', [
                'user' => $user,
                'token' => $token,
            ]);
        }

        return new ApiResource(401, 'Unauthorized', "Username atau password salah");
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $user->tokens()->delete();
        return new ApiResource(200, 'Logout berhasil', null);
    }
}
