<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ApiResource;

use App\Mail\RegisMail;
use Illuminate\Support\Facades\Mail;
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

        $user->save();
        try {
            Mail::to($user->email)->send(new RegisMail($user->id, $user->name));

            return new ApiResource(200, 'User berhasil mendaftar, cek email untuk verifikasi!', $user);
        } catch (\Exception $e) {
            return new ApiResource(500, 'User berhasil dibuat, namun email gagal dikirim!', $e->getMessage());
        }
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
            if($user->email_verified_at == null) {
                return new ApiResource(401, 'Unauthorized', "Email belum diverifikasi");
            }
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

    public function verify_email(Request $request, $token) {
        $tokenData = \App\Models\VerifToken::where('token', $token)->where('expired_at', '>', now())->first();
        if($tokenData == null) {
            return new ApiResource(404, 'Token tidak ditemukan', null);
        }

        $user = User::find($tokenData->user_id);
        if($user == null) {
            return new ApiResource(404, 'User tidak ditemukan', null);
        }

        $user->email_verified_at = now();
        $user->save();

        $tokenData->delete();

        return new ApiResource(200, 'Email berhasil diverifikasi', null);
    }
}
