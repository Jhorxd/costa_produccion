<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocationRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        //$id = $this->input('id');
        return [
            'warehouse_id' => [
                'required',
            ],
            'name' => [
                'required',
            ],
            'code' => [
                'required',
            ], 
            'status' => [
                'required',
            ],
            'type_id' => [
                'required',
            ],
            'rows' => [
                'required',
            ], 
            'columns' => [
                'required',
            ],
            'maximum_stock' => [
                'required',
            ], 
        ];
    }
}