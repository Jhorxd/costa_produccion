<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Inventory\Models\InventoryTransfer;
use Modules\Inventory\Models\ItemWarehouse;

class TransferResource2 extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $id = $this->id;
        $inventory_transfer = InventoryTransfer::findOrFail($id);
        $items = [];
        foreach($inventory_transfer->item as $i)
        {
            $items [] = [
                'id' => $i->id,
                'quantity' => $i->quantity,
                'description' => $i->description,
                'barcode' => $i->barcode,
            ];
        }
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'warehouse_id' => $this->warehouse_id,
            'warehouse_destination_id' => $this->warehouse_destination_id,
            'warehouse_description' => $this->warehouse->description,
            'state' => $this->state,
            // 'stock' => ItemWarehouse::where([['item_id', $this->item_id],['warehouse_id', $this->warehouse_id]])->first()->stock,
            'quantity' => $this->quantity,
            'detail' => $this->detail,
            'item' => $this->item,
            'inventory_transfer' => $inventory_transfer,
            'inventory_items' => $items,
        ];
    }
}