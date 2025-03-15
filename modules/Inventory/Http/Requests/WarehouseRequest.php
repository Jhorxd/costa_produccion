<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'description' => ['required', 'string'],
            'length' => ['required', 'numeric', 'min:0.1'],
            'width' => ['required', 'numeric', 'min:0.1'], 
            'height' => ['required', 'numeric', 'min:0.1'], 
            'responsible' => ['required', 'string'],
            'address' => ['required', 'string'],
            'establishment_id' => ['required', 'integer'], 
        ];
    }

    public function messages()
    {
        return [
            'description.required' => 'El nombre es obligatoria.',
            'length.required' => 'La longitud es obligatoria.',
            'length.numeric' => 'La longitud debe ser un número.',
            'length.min' => 'La longitud debe ser mayor a 0.',
            'width.required' => 'El ancho es obligatorio.',
            'width.numeric' => 'El ancho debe ser un número.',
            'width.min' => 'El ancho debe ser mayor a 0.',
            'height.required' => 'La altura es obligatoria.',
            'height.numeric' => 'La altura debe ser un número.',
            'height.min' => 'La altura debe ser mayor a 0.',
            'responsible.required' => 'El responsable es obligatorio.',
            'address.required' => 'La dirección es obligatoria.',
            'establishment_id.required' => 'El establecimiento es obligatorio.',        
        ];
    }
}