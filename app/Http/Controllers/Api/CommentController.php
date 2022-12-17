<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use App\Models\Comment;
// use App\Models\Post;
use App\Http\Resources\ApiResource;

class CommentController extends Controller
{
    public function getCommentsByPost(Request $request, $id_post) {
        $comments = Comment::with(['user'])->where('id_post', $id_post)->orderBy('created_at', 'desc')->get()->map(function($com) {
            $com->is_owner = $com->id_user == Auth::id();
            return $com;
        });
        return new ApiResource(200, 'Berhasil mengambil data', $comments);
    }

    public function createComment(Request $request, $id_post) {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return new ApiResource(422, "Terjadi kesalahan!", $validator->errors());
        }

        $comment = new Comment([
            'id_user' => Auth::id(),
            'id_post' => $id_post,
            'content' => $request->content,
        ]);
        // $comment->id_user = Auth::id();
        // $comment->id_post = $id_post;
        // $comment->content = $request->content;
        $comment->save();

        return new ApiResource(200, 'Berhasil membuat komentar', $comment);
    }

    public function editComment(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return new ApiResource(422, "Terjadi kesalahan!", $validator->errors());
        }

        $comment = Comment::where('id', $id)->where('id_user', Auth::id())->first();
        if($comment != null) {
            $comment->content = $request->content;
            $comment->save();
            return new ApiResource(200, 'Berhasil mengubah komentar', $comment);
        } else {
            return new ApiResource(404, 'Komentar tidak ditemukan', null);
        }
    }

    public function deleteComment(Request $request, $id) {
        $comment = Comment::where('id', $id)->where('id_user', Auth::id())->first();
        if($comment != null) {
            $comment->delete();
            return new ApiResource(200, 'Berhasil menghapus komentar', null);
        } else {
            return new ApiResource(404, 'Komentar tidak ditemukan', null);
        }
    }
}
