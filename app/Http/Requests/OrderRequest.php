<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.food_id' => 'required|exists:productos,pro_id',
            'items.*.quantity' => 'required|integer|min:1|max:99',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Debe agregar al menos un producto al pedido',
            'items.array' => 'Los items del pedido son inválidos',
            'items.min' => 'Debe agregar al menos un producto',
            'items.*.food_id.required' => 'Cada item debe tener un producto',
            'items.*.food_id.exists' => 'Uno de los productos seleccionados no existe',
            'items.*.quantity.required' => 'Debe especificar la cantidad',
            'items.*.quantity.integer' => 'La cantidad debe ser un número entero',
            'items.*.quantity.min' => 'La cantidad mínima es 1',
            'items.*.quantity.max' => 'La cantidad máxima es 99',
            'notes.max' => 'Las notas no pueden exceder 1000 caracteres',
        ];
    }

    public function attributes(): array
    {
        return [
            'items' => 'productos',
            'items.*.food_id' => 'producto',
            'items.*.quantity' => 'cantidad',
            'notes' => 'notas',
        ];
    }
}
