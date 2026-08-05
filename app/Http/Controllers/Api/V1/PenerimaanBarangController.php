<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePenerimaanBarangRequest;
use App\Services\PenerimaanBarangService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PenerimaanBarangController extends Controller
{
    protected $penerimaanService;

    public function __construct(PenerimaanBarangService $penerimaanService)
    {
        $this->penerimaanService = $penerimaanService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $penerimaans = $this->penerimaanService->getAll();

        return response()->json([
            'success' => true,
            'message' => 'List Data Penerimaan Barang',
            'data' => $penerimaans,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePenerimaanBarangRequest $request): JsonResponse
    {
        $file = $request->file('foto_bon');
        $data = $request->validated();

        try {
            $penerimaan = $this->penerimaanService->store($data, $file);

            return response()->json([
                'success' => true,
                'message' => 'Penerimaan Barang Berhasil Disimpan',
                'data' => $penerimaan,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify the specified penerimaan barang and update stock.
     */
    public function verify(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'catatan_verifikasi' => 'nullable|string|max:1000',
        ]);

        try {
            $penerimaan = $this->penerimaanService->verify($id, $request->input('catatan_verifikasi'));

            return response()->json([
                'success' => true,
                'message' => 'Penerimaan barang berhasil diverifikasi dan stok telah diperbarui.',
                'data' => $penerimaan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $penerimaan = $this->penerimaanService->getById($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail Penerimaan Barang',
                'data' => $penerimaan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
    }
}
