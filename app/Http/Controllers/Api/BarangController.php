<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $barangs = Barang::with('kategori')
            ->when($search, function ($query, $search) {
                return $query->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%");
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'List Data Barang',
            'data' => $barangs
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $barang = Barang::with('kategori')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail Data Barang',
                'data' => $barang
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }
}
