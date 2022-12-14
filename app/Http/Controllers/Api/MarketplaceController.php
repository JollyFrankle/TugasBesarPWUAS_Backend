<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\Marketplace;
use App\Http\Resources\ApiResource;

class MarketplaceController extends Controller
{
    public function index(Request $request) {
        $marketplaces = Marketplace::orderBy('created_at', 'desc')->get();
        return new ApiResource(200, 'Berhasil mengambil data', $marketplaces);
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
            'image' => 'required|string',
            'price' => 'required|integer',
        ]);

        if($validator->fails()) {
            return new ApiResource(422, 'Validasi gagal', $validator->errors());
        }

        $marketplace = new Marketplace([
            'id_user' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
            'image' => $request->image,
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
}
