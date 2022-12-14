<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;

use Laravel\Passport\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'name',
        'email',
        'bio',
        'tanggal_lahir',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'tanggal_lahir' => 'date',
    ];

    public function getCreatedAtAttribute() {
        if(!is_null($this->attributes['created_at'])) {
            return Carbon::parse($this->attributes['created_at'])->format('Y-m-d H:i:s');
        }
    }

    public function getUpdatedAtAttribute() {
        if(!is_null($this->attributes['updated_at'])) {
            return Carbon::parse($this->attributes['updated_at'])->format('Y-m-d H:i:s');
        }
    }

    // foreign key
    public function posts() {
        // function 'posts' untuk mengambil data post dari user
        // Param 1: nama model
        // Param 2: nama kolom di tabel post
        // Param 3: nama kolom di tabel user
        return $this->hasMany('App\Models\Post', 'id_user', 'id');
    }

    public function followers() {
        return $this->hasMany('App\Models\Follow', 'id_target', 'id');
    }

    public function followings() {
        return $this->hasMany('App\Models\Follow', 'id_follower', 'id');
    }

    public function likes() {
        return $this->hasMany('App\Models\Like', 'id_user', 'id');
    }

    public function comments() {
        return $this->hasMany('App\Models\Comment', 'id_user', 'id');
    }

    public function marketplaces() {
        return $this->hasMany('App\Models\Marketplace', 'id_user', 'id');
    }
}
