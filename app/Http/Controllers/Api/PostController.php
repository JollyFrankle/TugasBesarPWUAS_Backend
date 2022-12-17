<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Post;
use App\Http\Resources\ApiResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

// validation

class PostController extends Controller
{
    public function showFollowingsPost(Request $request)
    {
        $user = User::find(Auth::id());
        // get post by followings in chronological order, check if it has been liked by the user, and get total likes and comments
        $posts = Post::with(['user'])->whereIn('id_user', $user->followings()->pluck('id_target'))->orderBy('created_at', 'desc')->limit(15)->get()->map(function($post) {
            $post->liked = $post->likes()->where('id_user', Auth::id())->first() != null;
            $post->is_owner = $post->id_user == Auth::id();
            $post->total_likes = $post->likes()->count();
            $post->total_comments = $post->comments()->count();
            return $post;
        });
        // $posts = Post::whereIn('id_user', $user->followings()->pluck('id_target'))->orderBy('created_at', 'desc')->limit(10)->get();
        return new ApiResource(200, 'Berhasil mengambil data', $posts);
    }

    public function explorePost(Request $request)
    {
        // get random post, check if it has been liked by the user, and get total likes and comments
        $posts = Post::with(['user'])->inRandomOrder()->limit(15)->get()->map(function($post) {
            $post->liked = $post->likes()->where('id_user', Auth::id())->first() != null;
            $post->is_owner = $post->id_user == Auth::id();
            $post->total_likes = $post->likes()->count();
            $post->total_comments = $post->comments()->count();
            return $post;
        });
        // $posts = Post::inRandomOrder()->limit(10)->get();
        return new ApiResource(200, 'Berhasil mengambil data', $posts);
    }

    public function getPostByUserId(Request $request, $id)
    {
        $user = User::find($id);
        if($user != null) {
            $posts = $user->posts()->orderBy('created_at', 'desc')->get()->map(function($post) {
                $post->liked = $post->likes()->where('id_user', Auth::id())->first() != null;
                $post->is_owner = $post->id_user == Auth::id();
                $post->total_likes = $post->likes()->count();
                $post->total_comments = $post->comments()->count();
                return $post;
            });
            return new ApiResource(200, 'Berhasil mengambil data', $posts);
        } else {
            return new ApiResource(404, 'User tidak ditemukan', null);
        }
    }

    public function showPost(Request $request, $id)
    {
        // get post by id, check if it has been liked by the user, and get total likes and comments, and top 10 comments
        $post = Post::with(['user', 'comments.user'])->find($id);
        if($post != null) {
            $post->liked = $post->likes()->where('id_user', Auth::id())->first() != null;
            $post->is_owner = $post->id_user == Auth::id();
            $post->total_likes = $post->likes()->count();
            $post->total_comments = $post->comments()->count();
            $post->comments = $post->comments()->orderBy('created_at', 'desc')->limit(10)->get();
            return new ApiResource(200, 'Berhasil mengambil data', $post);
        } else {
            return new ApiResource(404, 'Post tidak ditemukan', null);
        }
    }

    public function createPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'image' => 'image',
        ]);

        if ($validator->fails()) {
            return new ApiResource(422, "Terjadi kesalahan!", $validator->errors());
        }

        // upload image
        $image = null;
        $folder = 'images/posts';
        if($request->hasFile('image')) {
            $image = $request->file('image')->store($folder, 'public');
            // get image name only
            $image = basename($image);
        }

        $post = new Post([
            'id_user' => Auth::user()->id,
            'content' => $request->content,
            'image' => $image,
        ]);

        if($post->save()) {
            return new ApiResource(200, 'Post berhasil dibuat', $post);
        } else {
            return new ApiResource(500, 'Post gagal dibuat', $post);
        }
    }

    public function editPost(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string'
        ]);

        if ($validator->fails()) {
            return new ApiResource(422, "Terjadi kesalahan!", $validator->errors());
        }

        // Get post kalau memang punya user
        $post = Post::where('id', $id)->where('id_user', Auth::user()->id)->first();
        if($post != null) {
            $post->content = $request->content;
            if($post->save()) {
                return new ApiResource(200, 'Post berhasil diubah', $post);
            } else {
                return new ApiResource(500, 'Post gagal diubah', $post);
            }
        } else {
            return new ApiResource(404, 'Post tidak ditemukan', null);
        }
    }

    public function deletePost(Request $request, $id)
    {
        // Get post kalau memang punya user
        $post = Post::where('id', $id)->where('id_user', Auth::user()->id)->first();
        if($post != null) {
            if($post->delete()) {
                // unlink image
                if($post->image != null) {
                    File::delete(public_path('storage/images/posts/'.$post->image));
                }
                return new ApiResource(200, 'Post berhasil dihapus', $post);
            } else {
                return new ApiResource(500, 'Post gagal dihapus', $post);
            }
        } else {
            return new ApiResource(404, 'Post tidak ditemukan', null);
        }
    }
}
