<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Follow;
use App\Http\Resources\ApiResource;
use Illuminate\Support\Facades\DB;

class FollowController extends Controller
{
    public function getFollowing(Request $request, $id)
    {
        $user = User::find($id);
        if($user != null) {
            $followings = $user->followings()->get();
            return new ApiResource(200, 'Berhasil mengambil data', $followings);
        } else {
            return new ApiResource(404, 'User tidak ditemukan', null);
        }
    }

    public function getFollower(Request $request, $id)
    {
        $user = User::find($id);
        if($user != null) {
            $followers = $user->followers()->get();
            return new ApiResource(200, 'Berhasil mengambil data', $followers);
        } else {
            return new ApiResource(404, 'User tidak ditemukan', null);
        }
    }

    public function follow(Request $request, $id)
    {
        $user = Auth::user();
        if($user->id == $id) {
            return new ApiResource(400, 'Tidak dapat mengikuti diri sendiri', null);
        }

        $follow = Follow::where('id_target', $id)->where('id_follower', $user->id)->first();
        if($follow == null) {
            $follow = new Follow([
                'id_target' => $id,
                'id_follower' => $user->id,
            ]);
            // $follow->id_target = $id;
            // $follow->id_follower = $user->id;
            $follow->save();
            return new ApiResource(200, 'Berhasil mengikuti user', $follow);
        } else {
            return new ApiResource(400, 'Anda sudah mengikuti user ini', null);
        }
    }

    public function unfollow(Request $request, $id)
    {
        $user = Auth::user();
        $follow = Follow::where('id_target', $id)->where('id_follower', $user->id)->first();
        if($follow != null) {
            // Hard delete karena Laravel tidak support delete untuk composite primary key
            DB::table('follows')->where('id_target', $id)->where('id_follower', $user->id)->delete();
            return new ApiResource(200, 'Berhasil berhenti mengikuti user', $follow);
        } else {
            return new ApiResource(400, 'Anda belum mengikuti user ini', $follow);
        }
    }
}
