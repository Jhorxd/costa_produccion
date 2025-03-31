<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ItemPosition;
use Modules\Inventory\Models\InventoryWarehouseLocation;
use Modules\Inventory\Models\WarehouseLocationPosition;
use Illuminate\Support\Facades\Response;

class PositionController extends Controller
{
    public function getLocations($warehouse_id){
        $locations = InventoryWarehouseLocation::where('warehouse_id', $warehouse_id)->get();
        if(!$locations->isEmpty()){
            return Response::json([
                'success' => true,
                'message' => 'Ubicaciones encontradas',
                'data' => $locations
            ],200);
        }else{
            return Response::json([
                'success' => false,
                'message' => 'Datos no encontrados'
            ],404);
        }
    }

    public function getPositions($location_id, $item_id=null){
        $location = InventoryWarehouseLocation::find($location_id);
        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Ubicación no encontrada',
            ], 404);
        }
        $positions = WarehouseLocationPosition::where('location_id', $location_id)->get();
        if(!$positions->isEmpty()){
            foreach ($positions as $position) {
                if($item_id!=null){
                    $item_positions = ItemPosition::where('item_id', $item_id)
                    ->where('position_id', $position->id)
                    ->with('lots_group')
                    ->get();

                    $works_with_lots = $item_positions->count() > 1 || 
                                    ($item_positions->count() == 1 && $item_positions->first()->lots_group_id !== null);

                    if ($works_with_lots) {
                        // Caso CON lotes
                        $position->stock_item = $item_positions->sum('stock');
                        $position->lots_group_list = $item_positions->map(function($ip) {
                            return [
                                'id' => $ip->lots_group_id,
                                'code' => optional($ip->lots_group)->code,
                                'stock' => $ip->stock,
                                'item_position_id' => $ip->id
                            ];
                        });
                        $position->uses_lots = true;
                    } else {
                        // Caso SIN lotes
                        $item_position = $item_positions->first();
                        $position->stock_item = $item_position->stock ?? 0;
                        $position->lots_group_id = null;
                        $position->lots_group_list = collect([]);
                        $position->uses_lots = false;
                    }
                }
                $position->stock_available = $location->maximum_stock - $position->quantity_used;
                $position->code_location = $location->code;
                $position->is_selected = false;
            }
            return Response::json([
                'success' => true,
                'message' => 'Posiciones encontrados',
                'data' => $positions
            ],200);
        }else{
            return Response::json([
                'success' => false,
                'message' => 'Datos no encontrados'
            ],404);
        }
    }
    
}
