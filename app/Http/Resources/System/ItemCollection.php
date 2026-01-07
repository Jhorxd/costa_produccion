<?php

namespace App\Http\Resources\System;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Tenant\Configuration;

class ItemCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {


        return $this->collection->transform(function($row, $key){
            /** @var \App\Models\Tenant\Item  $row */

            return [
                'id' => $row->id,
                'unit_type_id' => $row->unit_type_id,
                'description' => $row->description,
                'cod_digemid' => $row->cod_digemid,
                'sanitary' => $row->sanitary,
                /*'item_unit_types' => collect($row->item_unit_types)->transform(function($row) use($configuration){
                    return [
                        'id' => $row->id,
                        'description' => "{$row->description}",
                        'item_id' => $row->item_id,
                        'unit_type_id' => $row->unit_type_id,
                        'quantity_unit' => number_format($row->quantity_unit, $configuration->decimal_quantity, ".",""),
                        'price1' => number_format($row->price1, $configuration->decimal_quantity, ".",""),
                        'price2' => number_format($row->price2, $configuration->decimal_quantity, ".",""),
                        'price3' => number_format($row->price3, $configuration->decimal_quantity, ".",""),
                        'price_default' => $row->price_default,
                    ];
                }),*/
            ];
        });
    }
}
