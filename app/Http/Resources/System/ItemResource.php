<?php

    namespace App\Http\Resources\System;

use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\WarehouseLocationPosition;
use Modules\Item\Models\ItemLotsGroup;

    /**
     * Class ItemResource
     *
     * @package App\Http\Resources\Tenant
     * @mixin JsonResource
     */
    class ItemResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @param Request
         *
         * @return array
         */
        public function toArray($request)
        {
            return [
                'id' => $this->id,
                'description' => $this->description,
                'sanitary' => $this->sanitary,
                'cod_digemid' => $this->cod_digemid,
                'item_unit_types' => $this->item_unit_types,
                
            ];
        }
    }
