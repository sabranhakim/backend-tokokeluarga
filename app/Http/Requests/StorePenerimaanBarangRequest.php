<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenerimaanBarangRequest extends FormRequest
{
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'no_terima' => 'required|string|unique:penerimaan_barangs,no_terima',
            'supplier_id' => 'required|exists:suppliers,id',
            'tgl_terima' => 'required|date',
            'foto_bon' => 'nullable|image|max:5120|mimes:jpg,png,jpeg', // Max 5MB
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ];
    }
}
