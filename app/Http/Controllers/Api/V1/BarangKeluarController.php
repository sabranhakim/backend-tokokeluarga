<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBarangKeluarRequest;
use App\Services\BarangKeluarService;
use Illuminate\Http\JsonResponse;

class BarangKeluarController extends Controller
{
    protected $barangKeluarService;

    public function __construct(BarangKeluarService $barangKeluarService)
    {
        $this->barangKeluarService = $barangKeluarService;
    }

    public function index(): JsonResponse
    {
        $barangKeluars = $this->barangKeluarService->getAll();

        return response()->json([
            'success' => true,
            'message' => 'List Data Barang Keluar',
            'data' => $barangKeluars,
        ]);
    }

    public function store(StoreBarangKeluarRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $barangKeluar = $this->barangKeluarService->store($data);

            return response()->json([
                'success' => true,
                'message' => 'Barang Keluar Berhasil Disimpan dan Stok Telah Diperbarui',
                'data' => $barangKeluar,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $barangKeluar = $this->barangKeluarService->getById($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail Barang Keluar',
                'data' => $barangKeluar,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
    }
}
