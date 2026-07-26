<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBarangKeluarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tgl_keluar' => 'required|date',
            'keterangan' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barangs,id|distinct',
            'items.*.jumlah' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'items.*.barang_id.distinct' => 'Barang yang sama tidak boleh ditambahkan lebih dari satu kali dalam satu transaksi.',
        ];
    }
}
