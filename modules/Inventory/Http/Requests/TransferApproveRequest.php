<?php

namespace Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferApproveRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        //$id = $this->input('id');

        return [
            'warehouse_destination_id' => [
                'required',
            ],
            'date_of_transfer' => [
                'required',
            ],
            'id' => [
                'required',
            ],
            'items' => [
                'required',
            ],
            'location_destination_id' => [
                'nullable'
            ],
            'position_destination_id' => [
                'nullable'
            ],
            'warehouse_init_id' => [
                'required'
            ]
        ];
    }


}
