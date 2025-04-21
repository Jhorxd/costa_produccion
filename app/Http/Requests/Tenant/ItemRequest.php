<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class ItemRequest
 *
 * @package App\Http\Requests\Tenant
 * @mixin FormRequest
 */
class ItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->input('id');
        return [
            'item_files.*.file' => 'file|max:2048', // Máximo 2MB por archivo
            'item_files.*.filename' => 'string',
            'item_files.*.created_at' => 'date',
            'item_files.*.user_created_at' => 'string',
            'internal_id' => [
                'nullable',
                Rule::unique('tenant.items')->ignore($id),
            ],
            'description' => [
                'required', 'max:600'
            ],
            'name' => [
                'max:600'
            ],
            'second_name' => [
                'max:600'
            ],
            // 'name' => [
            //     'required',
            // ],
            // 'second_name' => [
            //     'required',
            // ],
            'unit_type_id' => [
                'required',
            ],
            'currency_type_id' => [
                'required'
            ],
            'sale_unit_price' => [
                'required',
                'numeric',
                'gt:0'
            ],
            'purchase_unit_price' => [
                'required', 'numeric'
            ],
            'stock' => [
                'required',
                // 'gt:0'
            ],
            'stock_min' => [
                'required',
                // 'gt:0'
            ],
            'sale_affectation_igv_type_id' => [
                'required'
            ],
            'purchase_affectation_igv_type_id' => [
                'required'
            ],
            'pharmaceutical_unit_type_id' => [
                'required'
            ],
            // 'category_id' => [
            //     'required_if:is_set,false',
            // ],
            // 'brand_id' => [
            //     'required_if:is_set,false',
            // ],
            'model' => 'max:100',
            
            'system_isc_type_id' => [
                'required_if:has_isc, 1',
            ],
            'percentage_isc' => [
                'required_if:has_isc, 1',
                'numeric',
                'min:0',
            ],

            'purchase_system_isc_type_id' => [
                'required_if:purchase_has_isc, 1',
            ],
            'purchase_percentage_isc' => [
                'required_if:purchase_has_isc, 1',
                'numeric',
            ],
        ];
    }

    public function messages()
    {
        return [
            'description.required' => 'El campo nombre es obligatorio.',
            'pharmaceutical_unit_type_id.required' => 'El campo forma farmaceútica es obligatorio.',
            'sale_price.gt' => 'El precio de venta debe ser mayor a 0.',
            'name.max' => 'La descripción debe ser inferior a 600 caracteres.',
            'sale_unit_price.gt' => 'El precio unitario debe ser mayor a 0.',
        ];
    }
}
