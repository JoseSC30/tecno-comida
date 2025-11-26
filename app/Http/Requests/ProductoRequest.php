<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0|max:9999999',
            'cost' => 'nullable|numeric',
            'category_id' => 'required|exists:categorias,cat_id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:51200',
            'available' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio',
            'name.max' => 'El nombre no puede exceder 255 caracteres',
            'description.max' => 'La descripción no puede exceder 1000 caracteres',
            'price.required' => 'El precio es obligatorio',
            'price.numeric' => 'El precio debe ser un número',
            'price.min' => 'El precio debe ser mayor o igual a 0',
            'price.max' => 'El precio es demasiado alto',
            'category_id.required' => 'Debe seleccionar una categoría',
            'category_id.exists' => 'La categoría seleccionada no existe',
            'image.image' => 'El archivo debe ser una imagen',
            'image.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg o webp',
            'image.max' => 'La imagen no puede superar 2MB',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'price' => 'precio',
            'category_id' => 'categoría',
            'image' => 'imagen',
            'available' => 'disponibilidad',
        ];
    }
}
