<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use App\Models\Like;
use App\Http\Resources\ApiResource;
use Illuminate\Support\Facades\DB;

class LikeController extends Controller
{
    public function getLikesByPost(Request $request, $id_post) {
        $likes = Like::where('id_post', $id_post)->orderBy('created_at', 'desc')->get();
        return new ApiResource(200, 'Berhasil mengambil data', $likes);
    }

    public function like(Request $request, $id_post) {
        $like = Like::where('id_user', Auth::id())->where('id_post', $id_post)->first();
        if($like == null) {
            $like = new Like([
                'id_user' => Auth::id(),
                'id_post' => $id_post,
            ]);
            $like->save();
            return new ApiResource(200, 'Berhasil membuat like', $like);
        } else {
            return new ApiResource(400, 'Like sudah ada', $like);
        }
    }

    public function unlike(Request $request, $id_post) {
        $like = Like::where('id_user', Auth::id())->where('id_post', $id_post)->first();
        if($like != null) {
            // Hard delete karena Laravel tidak support delete untuk composite primary key
            DB::table('likes')->where('id_user', Auth::id())->where('id_post', $id_post)->delete();
            return new ApiResource(200, 'Berhasil menghapus like', $like);
        } else {
            return new ApiResource(404, 'Like tidak ditemukan', null);
        }
    }
}
