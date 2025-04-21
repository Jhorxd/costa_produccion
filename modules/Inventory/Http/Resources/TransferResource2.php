<?php

namespace Modules\Inventory\Http\Resources;

use App\Models\Tenant\ItemPosition;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Inventory\Models\InventoryTransfer;
use Modules\Inventory\Models\ItemWarehouse;
use Modules\Item\Models\ItemLotsGroup;

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
            $item_positions=ItemPosition::where('item_id',$i->id)->get();
            $item_lots=ItemLotsGroup::where('item_id',$i->id)->count();
            $items [] = [
                'id' => $i->id,
                'quantity' => $i->quantity,
                'description' => $i->description,
                'barcode' => $i->barcode,
                'has_position' => $item_positions->count()>0,
                'has_lots' => $item_lots>0,
                'location_id' => $item_positions->count()>0?$item_positions->first()->location_id:null
            ];
        }
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'warehouse_id' => $this->warehouse_id,
            'warehouse_destination_id' => $this->warehouse_destination_id,
            'location_destination_id' => $this->location_destination_id,
            'position_destination_id' => $this->position_destination_id,
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