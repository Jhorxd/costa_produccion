<?php

namespace App\Traits;
use App\Models\Tenant\Item;
use App\Models\Tenant\Kardex;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\ItemWarehouse;
use Modules\Inventory\Models\InventoryConfiguration;



trait KardexTrait
{

    public function saveKardex($type, $item_id, $id, $quantity, $relation) {

        $kardex = Kardex::create([
            'type' => $type,
            'date_of_issue' => date('Y-m-d'),
            'item_id' => $item_id,
            'document_id' => ($relation == 'document') ? $id : null,
            'purchase_id' => ($relation == 'purchase') ? $id : null,
            'purchase_settlement_id' => ($relation == 'purchase_settlement') ? $id : null,
            'sale_note_id' => ($relation == 'sale_note') ? $id : null,
            'quantity' => $quantity,
        ]);

        return $kardex;

    }

    public function updateStock($item_id, $quantity, $is_sale){
        
        $item = Item::find($item_id);
        /* dd($item); */
        $item->stock = ($is_sale) ? $item->stock - $quantity : $item->stock + $quantity;
        $item->save();

    }

    /* public function updateStock($item_id, $quantity, $is_sale){
        
        $maxAttempts = 3;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            try {
                DB::transaction(function () use ($item_id, $quantity, $is_sale) {
                    $item = Item::where('id', $item_id)->lockForUpdate()->first();
                    $item->stock = $is_sale ? $item->stock - $quantity : $item->stock + $quantity;
                    $item->save();
                });
                break; // Éxito: salimos del bucle
            } catch (\Illuminate\Database\QueryException $e) {
                if (str_contains($e->getMessage(), 'Deadlock')) {
                    $attempt++;
                    usleep(100000); // Esperar 100ms antes de reintentar
                    continue;
                }
                throw $e; // Otro error, lo relanzamos
            }
        }

    } */

    public function restoreStockInWarehpuse($item_id, $warehouse_id, $quantity)
    {
        $item_warehouse = ItemWarehouse::firstOrNew(['item_id' => $item_id, 'warehouse_id' => $warehouse_id]);
        $item_warehouse->stock = $item_warehouse->stock + $quantity;
        $item_warehouse->save();
    }

}
