<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Http\Resources\WarehouseCollection;
use Modules\Inventory\Http\Resources\WarehouseResource;
use Modules\Inventory\Http\Requests\WarehouseRequest;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\InventoryWarehouseLocation;
use App\Models\Tenant\Establishment;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    public function index()
    {
        return view('inventory::warehouses.index');
    }

    public function columns()
    {
        return [
            'description' => 'Nombre',
            'responsible' => 'Responsable',
            'establishment_description' => 'Sucursal',
        ];
    }

    public function records(Request $request)
    {   
        $records = Warehouse::where($request->column, 'like', "%{$request->value}%")
                            ->orderBy('description');
        return new WarehouseCollection($records->paginate(config('tenant.items_per_page')));
    }


    
    public function recordsByCustomFields(Request $request)
    {          
        $records = Warehouse::select(
            'warehouses.id as id_warehouse','warehouses.description as warehouse_description',
            'establishments.description as establishment_description',
            'warehouses.address',
            'length', 'width', 'height', 'responsible'
        )
        ->join('establishments', 'warehouses.establishment_id', '=', 'establishments.id');
        if ($request->has(['column', 'value']) && !empty($request->column) && !empty($request->value)) {
            if (in_array($request->column, ['description', 'address', 'length', 'width', 'height', 'responsible'])) {
                $column = "warehouses.{$request->column}";
            } elseif ($request->column === 'establishment_description') {
                $column = "establishments.description";
            } else {
                $column = null; // Para evitar SQL injection
            }
            if ($column) {
                $records->where($column, 'like', "%{$request->value}%");
            }
        }
        $records = $records->paginate(config('tenant.items_per_page'));    
        $records->getCollection()->transform(function ($record) {
         // Obtener conteo de tipos de ubicación por almacén
        $locationTypes = InventoryWarehouseLocation::where('warehouse_id', $record->id_warehouse)
        ->join('warehouse_location_type', 'inventory_warehouse_locations.type_id', '=', 'warehouse_location_type.id')
        ->select('warehouse_location_type.name', DB::raw('COUNT(*) as total'))
        ->groupBy('warehouse_location_type.name')
        ->get()
        ->pluck('total', 'name'); 
        return [
            'warehouse_description' => $record->warehouse_description,
            'establishment_description' => $record->establishment_description,
            'address' => $record->address,
            'dimensions' => [
                'length' => $record->length,
                'width' => $record->width,
                'height' => $record->height,
            ],
            'responsible' => $record->responsible,
            'id' => $record->id_warehouse,
            'location_types' => $locationTypes,           
        ];
       });
       return $records;
    }
    
    public function destroy($id)
    {   
        try {
            $record = Warehouse::findOrFail($id);
            $record->delete();
            return [
                'success' => true,
                'message' => 'Almacén eliminado con éxito'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }       
    }
    public function record($id)
    {
        $record = new WarehouseResource(Warehouse::findOrFail($id));

        return $record;
    }
    
    public function getEstablishments()
    {
        return Establishment::select('id', 'description')->get()->makeHidden(['country', 'department', 'province', 'district']);

    }
    public function getWarehouse($id)
    {
        return Warehouse::findOrFail($id)->makeHidden(['created_at', 'updated_at']);
    }
    public function storeWarehouse2(WarehouseRequest $request){

    $id = $request->input('id');
    $record = Warehouse::firstOrNew(['id' => $id]);
    $record->fill($request->all());
    $record->save();
    return [
        'success' => true,
        'message' => 'Almacén guardado correctamente.'
      ];   
    }
    public function store(WarehouseRequest $request)
    {
        $id = $request->input('id');
        if(!$id) {
            $establishment_id = auth()->user()->establishment_id;
            $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
            if($warehouse) {
                return [
                    'success' => false,
                    'message' => 'Solo es posible registrar un almacén por establecimiento.'
                ];
            }
        }

        $record = Warehouse::firstOrNew(['id' => $id]);
        $record->fill($request->all());
        if(!$id) {
            $record->establishment_id = auth()->user()->establishment_id;
        }
        $record->save();

        return [
            'success' => true,
            'message' => ($id)?'Almacén editado con éxito':'Almacén registrado con éxito',
            'id' => $record->id
        ];
    }
}