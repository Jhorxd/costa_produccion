<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ItemPosition;
use App\Models\Tenant\Warehouse;
use Illuminate\Support\Facades\Response;
use Modules\Inventory\Models\InventoryWarehouseLocation;
use Modules\Inventory\Models\WarehouseLocationPosition;

class PositionController extends Controller
{
    public function getWarehousebyActiveEstablishment()
    {   
        $establishment_id = auth()->user()->establishment_id;
        $warehouse_user_active = Warehouse::where('establishment_id', $establishment_id)->select('id','description')->get();
        if(!empty($warehouse_user_active)){
            return Response::json([
                'success' => true,
                'message' => "Almacenes obtenidos correctamente",
                'data' => $warehouse_user_active
            ], 200);
        }else{
            return Response::json([
                'success' => false,
                'message' => "Error al obtener los almacenes"
            ],500);
        }
    }

    
    public function getPositionsForAddItem($location_id, $item_id)
    {
        $positions = WarehouseLocationPosition::where('location_id', $location_id)->get();
        $location = InventoryWarehouseLocation::find($location_id);
        if(!$positions->isEmpty()){
            foreach ($positions as $position_element) {
                $item_position = ItemPosition::where('position_id',$position_element['id'])->where('item_id',$item_id)->first();
                $stock_available = (int)$location->maximum_stock - (int)$position_element['quantity_used'];
                $position_element['stock_available'] = $stock_available;
                $position_element['code_location'] = $location->code;
                if($item_position){
                    $position_element['exist_item']=true;
                }else{
                    $position_element['exist_item']=false;
                }
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
