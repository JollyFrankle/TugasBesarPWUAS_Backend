<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Http\Resources\ApiResource;

class UserController extends Controller
{
    public function show(Request $request, $id) {
        $uid = Auth::id();
        $user = User::find($id);
        if($user != null) {
            if($uid == $id) {
                $user->isAuth = true;
                return new ApiResource(200, 'Berhasil mengambil data', $user);
            } else {
                $user->email = null;
                $user->email_verified_at = null;
                $user->remember_token = null;
                $user->updated_at = null;
                $user->isAuth = false;
                return new ApiResource(200, 'Berhasil mengambil data', $user);
            }
        } else {
            return new ApiResource(404, 'User tidak ditemukan', null);
        }
    }
    public function find(Request $request){
        $user = User::where('username', 'LIKE', '%'. $request->username .'%')->orWhere('name', 'LIKE', '%'. $request->username .'%')->limit(10)->get();
        return new ApiResource(200, 'Berhasil mengambil data', $user);
    }
    public function getCurrentLoggedInUser(Request $request) {
        return $this->show($request, Auth::id());
    }

    public function edit(Request $request) {
        $user = User::find(Auth::id());

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email,'.$user->id,
            'username' => 'required|string|unique:users,username,'.$user->id,
            'bio' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return new ApiResource(422, "Terjadi kesalahan!", $validator->errors());
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->bio = $request->bio;
        $user->tanggal_lahir = $request->tanggal_lahir;

        if($user->save()) {
            return new ApiResource(200, 'Berhasil mengubah data', $user);
        } else {
            return new ApiResource(500, 'Gagal mengubah data', $user);
        }
    }

    public function changeProfilePicture(Request $request) {
        $user = User::find(Auth::id());

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'avatar.required' => 'Avatar tidak boleh kosong',
            'avatar.image' => 'Avatar harus berupa gambar',
            'avatar.mimes' => 'Avatar harus berupa gambar dengan format jpeg, png, jpg, gif, svg',
            'avatar.max' => 'Ukuran avatar maksimal 2MB',
        ]);

        if ($validator->fails()) {
            return new ApiResource(422, "Terjadi kesalahan!", $validator->errors());
        }

        $image = null;
        $folder = 'images/users';
        $image = $request->file('avatar')->store($folder, 'public');
        // get image name only
        $image = basename($image);

        // delete old image
        if($user->avatar != null) {
            $old_image = $user->avatar;
            $old_image_path = public_path('storage/'.$folder.'/'.$old_image);
            if(file_exists($old_image_path)) {
                unlink($old_image_path);
            }
        }

        $user->avatar = $image;

        if($user->save()) {
            return new ApiResource(200, 'Berhasil mengubah avatar', $user);
        } else {
            return new ApiResource(500, 'Gagal mengubah avatar', $user);
        }
    }

    public function delete(Request $request) {
        $user = User::find(Auth::id());

        if($user->delete()) {
            return new ApiResource(200, 'Berhasil menghapus akun', null);
        } else {
            return new ApiResource(500, 'Gagal menghapus akun', null);
        }
    }
}
