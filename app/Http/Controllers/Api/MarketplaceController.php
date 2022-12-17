<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\Marketplace;
use App\Http\Resources\ApiResource;
use Illuminate\Support\Facades\File;

class MarketplaceController extends Controller
{
    public function index(Request $request) {
        $marketplaces = Marketplace::with(['user'])->orderBy('created_at', 'desc')->get();
        return new ApiResource(200, 'Berhasil mengambil data', $marketplaces);
    }

    public function getMarketplaceByUserId(Request $request, $id_user) {
        $marketplaces = Marketplace::with(['user'])->where('id_user', $id_user)->orderBy('created_at', 'desc')->get();
        return new ApiResource(200, 'Berhasil mengambil data', $marketplaces);
    }

    public function getCurrentLoggedInUserMarketplace(Request $request) {
        return $this->getMarketplaceByUserId($request, Auth::id());
    }

    public function show(Request $request, $id) {
        $marketplace = Marketplace::find($id);
        if($marketplace != null) {
            return new ApiResource(200, 'Berhasil mengambil data', $marketplace);
        } else {
            return new ApiResource(404, 'Marketplace tidak ditemukan', null);
        }
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'price' => 'required|integer',
        ]);

        if($validator->fails()) {
            return new ApiResource(422, 'Validasi gagal', $validator->errors());
        }

        $image = null;
        $folder = 'images/marketplace';
        // if($request->hasFile('image')) {
            $image = $request->file('image')->store($folder, 'public');
            // get image name only
            $image = basename($image);
        // }

        $marketplace = new Marketplace([
            'id_user' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
            'image' => $image,
            'price' => $request->price,
        ]);
        $marketplace->save();
        return new ApiResource(200, 'Berhasil membuat marketplace', $marketplace);
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string',
            'image' => 'required|string',
            'price' => 'required|integer',
        ]);

        if($validator->fails()) {
            return new ApiResource(422, 'Validasi gagal', $validator->errors());
        }

        $marketplace = Marketplace::find($id);
        if($marketplace != null) {
            $marketplace->name = $request->name;
            $marketplace->description = $request->description;
            $marketplace->image = $request->image;
            $marketplace->price = $request->price;
            $marketplace->save();
            return new ApiResource(200, 'Berhasil mengubah marketplace', $marketplace);
        } else {
            return new ApiResource(404, 'Marketplace tidak ditemukan', null);
        }
    }

    public function destroy(Request $request, $id) {
        $marketplace = Marketplace::find($id);
        if($marketplace != null) {
            if($marketplace->delete()) {
                File::delete(public_path('storage/images/marketplace/'.$marketplace->image));
            }
            return new ApiResource(200, 'Berhasil menghapus marketplace', $marketplace);
        } else {
            return new ApiResource(404, 'Marketplace tidak ditemukan', null);
        }
    }
}
