<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\PdResource;
use App\Models\Biodata;
use App\Http\Requests\BiodataRequest;

class BiodataController extends Controller
{
    public function index()
    {
        $biodata = Biodata::Active()->latest()->paginate(5);
        return new PdResource(true, 'list biodata', $biodata);
    }

    public function store(BiodataRequest $request)
    {
        $validator = $request->validated();

        $biodata = Biodata::create($validator);
        return new PdResource(true, 'Data berhasil ditambah', $biodata);
    }

    public function show($id)
    {
        $biodata = Biodata::findOrFail($id);
        return new PdResource(true, 'Detail data', $biodata);
    }

    public function update(BiodataRequest $request, $id)
    {
        $biodata = Biodata::findOrFail($id);

        $validator = $request->validated();

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $biodata->update($validator->validated());

        return new PdResource(true, 'Data berhasil diubah', $biodata);
    }
}
