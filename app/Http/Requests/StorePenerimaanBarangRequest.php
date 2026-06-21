<?php

namespace App\Http\Requests;

use App\Models\PenerimaanBarang;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePenerimaanBarangRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('id')) {
            $existing = PenerimaanBarang::with(['supplier', 'user', 'detailPenerimaans.barang'])
                ->find($this->input('id'));

            if ($existing) {
                throw new HttpResponseException(response()->json([
                    'success' => true,
                    'message' => 'Penerimaan Barang Berhasil Disimpan',
                    'data' => $existing,
                ], 200));
            }
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'nullable|uuid|unique:penerimaan_barangs,id',
            'no_terima' => 'nullable|string',
            'supplier_id' => 'required|exists:suppliers,id',
            'tgl_terima' => 'required|date',
            'foto_bon' => 'nullable|image|max:5120|mimes:jpg,png,jpeg', // Max 5MB
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|uuid|unique:detail_penerimaans,id',
            'items.*.barang_id' => 'required|exists:barangs,id|distinct',
            'items.*.jumlah' => 'required|integer|min:1',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.*.barang_id.distinct' => 'Barang yang sama tidak boleh ditambahkan lebih dari satu kali dalam satu transaksi.',
        ];
    }
}
