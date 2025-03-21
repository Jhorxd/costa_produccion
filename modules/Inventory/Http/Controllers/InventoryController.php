<?php

namespace Modules\Inventory\Http\Controllers;

use App\Models\Tenant\Item;
use App\Models\Tenant\Series;
use Barryvdh\DomPDF\Facade as PDF;
use Exception;

//use App\Models\Tenant\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\Guide;
use Modules\Inventory\Models\InventoryTransfer;
use Modules\Item\Models\ItemLot;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Inventory\Models\Inventory;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\ItemWarehouse;
use Modules\Inventory\Traits\InventoryTrait;
use Modules\Inventory\Models\InventoryKardex;
use Modules\Inventory\Models\PhysicalInventoryCategory;
use Modules\Inventory\Models\InventoryTransaction;
use Modules\Inventory\Models\InventoryTransferItem;
use Modules\Inventory\Http\Requests\InventoryRequest;
use Modules\Inventory\Http\Resources\InventoryResource;
use Modules\Inventory\Http\Resources\InventoryCollection;
use App\Imports\StockImport;
use App\Models\Tenant\User;
use App\Models\Tenant\Warehouse as TenantWarehouse;
use Maatwebsite\Excel\Excel;
use Modules\Inventory\Http\Requests\LocationRequest;
use Modules\Inventory\Http\Requests\RemoveRequest;
use Modules\Inventory\Models\InventoryWarehouseLocation;
use Modules\Inventory\Models\WarehouseLocationPosition;
use Modules\Inventory\Models\WarehouseLocationType;
use App\Models\Tenant\Establishment;
use Modules\Inventory\Models\PhysicalInventory; 
use Modules\Inventory\Models\PhysicalInventoryDetail;

class InventoryController extends Controller
{
    protected $location;
    use InventoryTrait;

    public function index()
    {
        return view('inventory::inventory.index');
    }
    public function indexPhysicalInventory()
    {
        return view('inventory::inventory.physicalInventory');
    }
    public function indexPhysicalInventoryList()
    {
        return view('inventory::inventory.physicalInventoryList');
    }
    public function indexWarehouses()
    {
        return view('inventory::warehouses.warehousesList');
    }
    public function columns()
    {
        return [
            'description' => 'Producto',
            'internal_id' => 'Código interno',
            'warehouse' => 'Almacén',
        ];
    }
    public function getPdfInventory($id)
    {   
        $data = PhysicalInventory::join('establishments', 'physical_inventories.establishment_id', '=', 'establishments.id')
        ->join('warehouses', 'physical_inventories.warehouse_id', '=', 'warehouses.id')
        ->join('physical_inventory_adjustment_types', 'physical_inventories.adjustment_type_id', '=', 'physical_inventory_adjustment_types.id')
        ->select(
            'physical_inventories.*', 
            'establishments.description as establishment_description', 
            'warehouses.description as warehouse_description',
            'physical_inventory_adjustment_types.name as adjustment_type_name'
        )->where('physical_inventories.id','=',$id)->first();

        $details = PhysicalInventoryDetail::join('items', 'inventory_physical_details.item_id', '=', 'items.id')
        ->join('physical_inventory_categories', 'inventory_physical_details.category_id', '=', 'physical_inventory_categories.id')
        ->select(
            'inventory_physical_details.*',            
            'items.description as item_description',
            'physical_inventory_categories.name as category_name'
        )
        ->where('inventory_physical_details.physical_inventory_id', '=', $id) 
        ->get();
               
        
        // Renderizar la vista Blade con los datos
        $pdf = PDF::loadView('inventory::inventory.pdfPhysicalIventory', compact('data','details'));

        // Descargar el PDF o mostrarlo en el navegador
        return $pdf->stream('reporte.pdf');
    }


    public function records(Request $request)
    {
        $column = $request->input('column');

        if ($column == 'warehouse') {
            $records = ItemWarehouse::with(['item', 'warehouse'])
                ->whereHas('item', function ($query) use ($request) {
                    $query->where('unit_type_id', '!=', 'ZZ');
                    $query->whereNotIsSet();
                })
                ->whereHas('warehouse', function ($query) use ($request) {
                    $query->where('description', 'like', '%' . $request->value . '%');
                })
                ->orderBy('item_id');
        } else {
            $records = $this->getCommonRecords($request);
        }

        return new InventoryCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function getEstablishmentsByName(Request $request)
    {   
        //Log::debug($request->all());
        $establishments = Establishment::where('description', 'like', '%' . $request->input('value') . '%')->get();
        return response()->json([
            'success' => true,
            'data' => $establishments
        ]);
    }
    public function getAllPhysicalInventoryCategories()
    {
        return PhysicalInventoryCategory::all();
    }
    public function getAllPhysicalInventories(Request $request){
        $query = PhysicalInventory::join('establishments', 'physical_inventories.establishment_id', '=', 'establishments.id')
        ->join('warehouses', 'physical_inventories.warehouse_id', '=', 'warehouses.id')
        ->join('physical_inventory_adjustment_types', 'physical_inventories.adjustment_type_id', '=', 'physical_inventory_adjustment_types.id')
        ->select(
            'physical_inventories.*', 
            'establishments.description as establishment_description', 
            'warehouses.description as warehouse_description',
            'physical_inventory_adjustment_types.name as adjustment_type_name'
        )->orderBy('physical_inventories.id');
        $query = $query->paginate(config('tenant.items_per_page'));    
        return $query;
    }
    public function store3(Request $request){
        DB::beginTransaction(); 
            try {        
            $physicalInventory = PhysicalInventory::create($request->except('details'));

            if ($request->has('details')) {
                foreach ($request->details as $detail) {
                    if ($detail['counted_quantity'] < 0) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'La cantidad de stock real debe ser mayor o igual a 0'
                        ], 400);
                    }

            $type = 1;
            $quantity_new = $detail['counted_quantity'] - $detail['system_quantity'];
            if ($detail['counted_quantity'] < $detail['system_quantity']) {
                $quantity_new = $detail['system_quantity'] - $detail['counted_quantity'];
                $type = null;
            }           
            $inventory = new Inventory();
            $inventory->type = $type;
            $inventory->description = 'Stock Real';
            $inventory->item_id = $detail['item_id'];
            $inventory->warehouse_id = $request->warehouse_id; 
            $inventory->quantity = $quantity_new;
            if ($detail['counted_quantity'] != $detail['system_quantity']) {
                $inventory->inventory_transaction_id = 28;
            }
            $inventory->real_stock = $detail['counted_quantity'];
            $inventory->system_stock = $detail['system_quantity'];
            $inventory->save();     
            PhysicalInventoryDetail::create([
                'physical_inventory_id' => $physicalInventory->id,
                'item_id' => $detail['item_id'],
                'counted_quantity' => $detail['counted_quantity'],
                'system_quantity' => $detail['system_quantity'],
                'difference' => $detail['counted_quantity'] - $detail['system_quantity'],
                'category_id' => $detail['category_id'] ?? null, 
            ]);
        }
    }

        DB::commit(); 
        return response()->json(['message' => 'Inventario y detalle guardado satisfactoriamente'], 201);
    } catch (\Exception $e) {
        DB::rollBack(); 
        return response()->json(['error' => $e->getMessage()], 500);
    }
    }
    public function getProductsByEstablishmentAndWarehouse(Request $request){         
        $establishment_id = $request->input('establishment_id');
        $warehouse_id = $request->input('warehouse_id');
         
        
        $searchValue = $request->input('value'); // Puede ser nulo
        //$searchValue=null;       
        $query = ItemWarehouse::join('items', 'item_warehouse.item_id', '=', 'items.id')
            ->join('warehouses', 'item_warehouse.warehouse_id', '=', 'warehouses.id')
            ->where('item_warehouse.warehouse_id', $warehouse_id)
            ->where('warehouses.establishment_id', $establishment_id);

        if (!empty($searchValue)) {
            $query->where('items.description', 'like', '%' . $searchValue . '%');
        }
            $products = $query->select(
                    'item_warehouse.id',
                    'item_warehouse.item_id',
                    'item_warehouse.warehouse_id',
                    'item_warehouse.stock',
                    'items.sale_unit_price',
                    'items.description'
                )
        ->orderBy('item_warehouse.item_id')
        ->get();
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
    public function getWarehousesByEstablishment($id)
    {
        $warehouses = Warehouse::where('establishment_id', $id)->get();
        return response()->json([
            'success' => true,
            'data' => $warehouses
        ]);
    }
    /**
     *
     * Obtener registros
     *
     * @param Request $request
     * @return ItemWarehouse
     */
    public function getCommonRecords($request)
    {
        return ItemWarehouse::with(['item', 'warehouse'])
            ->whereHas('item', function ($query) use ($request) {
                $query->where('unit_type_id', '!=', 'ZZ');
                $query->whereNotIsSet();

                if ($this->applyAdvancedRecordsSearch() && $request->column === 'description') {
                    if ($request->value) $query->whereAdvancedRecordsSearch($request->column, $request->value);
                } else {
                    $query->where($request->column, 'like', '%' . $request->value . '%');
                }
            })
            ->orderBy('item_id');
    }


    public function tables()
    {
        return [
            'items' => $this->optionsItem(),
            'warehouses' => $this->optionsWarehouse()
        ];
    }

    public function record($id)
    {
        if (is_numeric($id)) {
            $record = new InventoryResource(ItemWarehouse::with(['item', 'warehouse'])->findOrFail($id));
        } else {
            request()->validate([
                'ids' => 'required|array|min:1'
            ]);
            $data = ItemWarehouse::with(['item', 'warehouse'])
                ->whereIn('id', request('ids'))
                ->get();

            $record = InventoryResource::collection($data);
        }

        return $record;
    }

    public function tables_transaction($type)
    {
        return [
            //            'items' => $this->optionsItemFull(),
            'warehouses' => $this->optionsWarehouse(),
            'inventory_transactions' => $this->optionsInventoryTransaction($type),
        ];
    }
    
    
    /**
     * 
     * Busqueda de productos en movimientos - ingresos y salidas
     *
     * @param  Request $request
     * @return array
     */
    public function searchItems(Request $request)
    {
        $search = $request->input('search');
        $search_item_by_barcode = $request->has('search_item_by_barcode') && (bool) $request->search_item_by_barcode;
        $take = $search_item_by_barcode ? 1 : 20;

        return [
            'items' => $this->optionsItemFull($search, $take, $search_item_by_barcode),
        ];
    }


    public function ExtraDataList()
    {
        return view('inventory::extra_info.index');
    }

    public function store(Request $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $item_id = $request->input('item_id');
            $warehouse_id = $request->input('warehouse_id');
            $quantity = $request->input('quantity');

            $item_warehouse = ItemWarehouse::firstOrNew(['item_id' => $item_id,
                'warehouse_id' => $warehouse_id]);
            if ($item_warehouse->id) {
                return [
                    'success' => false,
                    'message' => 'El producto ya se encuentra registrado en el almacén indicado.'
                ];
            }

            // $item_warehouse->stock = $quantity;
            // $item_warehouse->save();

            $inventory = new Inventory();
            $inventory->type = 1;
            $inventory->description = 'Stock inicial';
            $inventory->item_id = $item_id;
            $inventory->warehouse_id = $warehouse_id;
            $inventory->quantity = $quantity;
            $inventory->save();

            return [
                'success' => true,
                'message' => 'Producto registrado en almacén'
            ];
        });

        return $result;
    }

    public function store_transaction(InventoryRequest $request)
    {
        DB::connection('tenant')->beginTransaction();
        try {

            // dd($request->all());
            $type = $request->input('type');
            $item_id = $request->input('item_id');
            $warehouse_id = $request->input('warehouse_id');
            $inventory_transaction_id = $request->input('inventory_transaction_id');
            $quantity = $request->input('quantity');
            $lot_code = $request->input('lot_code');
            $comments = $request->input('comments');
            $created_at = $request->input('created_at');


            $lots = ($request->has('lots')) ? $request->input('lots') : [];

            $item_warehouse = ItemWarehouse::firstOrNew(['item_id' => $item_id,
                'warehouse_id' => $warehouse_id]);

            $inventory_transaction = InventoryTransaction::findOrFail($inventory_transaction_id);

            if ($type == 'output' && ($quantity > $item_warehouse->stock)) {
                return [
                    'success' => false,
                    'message' => 'La cantidad no puede ser mayor a la que se tiene en el almacén.'
                ];
            }

            $inventory = new Inventory();
            $inventory->type = null;
            $inventory->description = $inventory_transaction->name;
            $inventory->item_id = $item_id;
            $inventory->warehouse_id = $warehouse_id;
            $inventory->quantity = $quantity;
            $inventory->inventory_transaction_id = $inventory_transaction_id;
            $inventory->lot_code = $lot_code;
            $inventory->comments = $comments;


            if ($created_at) {
                $inventory->date_of_issue = $created_at;
            }

            $inventory->save();

            $warehouse = Warehouse::query()->find($warehouse_id);

            $itm = Item::query()
                ->select('id', 'description')
                ->where('id', $item_id)
                ->first();
            $guide_items[] = [
                'id' => $item_id,
                'name' => $itm->description,
                'stock_add' => $quantity
            ];
            $res = (new GuideController())->storeWithData([
                'establishment_id' => $warehouse->establishment_id,
                'warehouse_id' => $warehouse_id,
                'date_of_issue' => now()->format('Y-m-d'),
                'time_of_issue' => now()->format('H:i:s'),
                'inventory_transaction_id' => $inventory_transaction_id,
                'observations' => $comments,
                'items' => $guide_items
            ]);

            if (!$res['success']) {
                throw new Exception($res['message']);
            }

            $inventory->update([
                'guide_id' => $res['data']['id']
            ]);

            $lots_enabled = isset($request->lots_enabled) ? $request->lots_enabled : false;

            if ($type == 'input') {
                foreach ($lots as $lot) {
                    /*$inventory->lots()->create([
                        'date' => $lot['date'],
                        'series' => $lot['series'],
                        'item_id' => $item_id,
                        'warehouse_id' => $warehouse_id,
                        'has_sale' => false
                    ]);*/

                    $inventory->lots()->create([
                        'date' => $lot['date'],
                        'series' => $lot['series'],
                        'item_id' => $item_id,
                        'warehouse_id' => $warehouse_id,
                        'has_sale' => false,
                        'state' => $lot['state'],
                    ]);
                }

                if ($lots_enabled) {
                    ItemLotsGroup::create([
                        'code' => $lot_code,
                        'quantity' => $quantity,
                        'date_of_due' => $request->date_of_due,
                        'item_id' => $item_id
                    ]);
                }
            } else {
                foreach ($lots as $lot) {
                    if ($lot['has_sale']) {
                        $item_lot = ItemLot::findOrFail($lot['id']);
                        // $item_lot->delete();
                        $item_lot->has_sale = true;
                        $item_lot->state = 'Inactivo';
                        $item_lot->save();
                    }
                }

                if (isset($request->IdLoteSelected)) {
                    if (is_array($request->IdLoteSelected)) {
                        foreach ($request->IdLoteSelected as $row) {
                            Log::info($row);
                            $lot = ItemLotsGroup::find($row['id']);
                            $lot->quantity = ($lot->quantity - $row['compromise_quantity']);
                            $lot->save();
                        }
                    } else {
                        $lot = ItemLotsGroup::find($request->IdLoteSelected);
                        $lot->quantity = ($lot->quantity - $quantity);
                        $lot->save();
                    }
                }

            }
            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'message' => ($type == 'input') ? 'Ingreso registrado correctamente' : 'Salida registrada correctamente'
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }
    }

    public function moveMultiples(Request $request)
    {
        $request->validate([
            'items' => 'required|array'
        ]);

        DB::connection('tenant')->beginTransaction();
        try {
            $items = $request->items;
            foreach ($items as $item) {
                $item_id = $item['item_id'];
                $warehouse_id = $item['warehouse_id'];
                $warehouse_new_id = $item['warehouse_new_id'];
                $quantity = $item['quantity'];
                $quantity_move = $item['quantity_move'];
                $detail = $item['detail'];
                if ($quantity_move <= 0) {
                    throw new Exception("La cantidad del producto {$item['item_description']} a trasladar debe ser mayor a 0", 500);
                }

                if ($warehouse_id === $warehouse_new_id) {
                    throw new Exception("El almacén destino del producto {$item['item_description']} no puede ser igual al de origen", 500);
                }
                if ($quantity < $quantity_move) {
                    throw new Exception("La cantidad a trasladar del producto {$item['item_description']} no puede ser mayor al que se tiene en el almacén.", 500);
                }

                $inventory = new Inventory();
                $inventory->type = 2;
                $inventory->description = 'Traslado';
                $inventory->item_id = $item_id;
                $inventory->warehouse_id = $warehouse_id;
                $inventory->warehouse_destination_id = $warehouse_new_id;
                $inventory->quantity = $quantity_move;
                $inventory->detail = $detail;

                $inventory->save();
            }
            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Productos trasladados con éxito'
            ], 200);
        } catch (\Throwable $th) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

//	public function move(Request $request)
//	{
//		$result = DB::connection('tenant')->transaction(function () use ($request) {
//			$id = $request->input('id');
//			$item_id = $request->input('item_id');
//			$warehouse_id = $request->input('warehouse_id');
//			$warehouse_new_id = $request->input('warehouse_new_id');
//			$quantity = $request->input('quantity');
//			$quantity_move = $request->input('quantity_move');
//			$lots = ($request->has('lots')) ? $request->input('lots') : [];
//			$detail = $request->input('detail');
//
//			if ($quantity_move <= 0) {
//				return  [
//					'success' => false,
//					'message' => 'La cantidad a trasladar debe ser mayor a 0'
//				];
//			}
//
//			if ($warehouse_id === $warehouse_new_id) {
//				return  [
//					'success' => false,
//					'message' => 'El almacén destino no puede ser igual al de origen'
//				];
//			}
//			if ($quantity < $quantity_move) {
//				return  [
//					'success' => false,
//					'message' => 'La cantidad a trasladar no puede ser mayor al que se tiene en el almacén.'
//				];
//			}
//
//			$inventory = new Inventory();
//			$inventory->type = 2;
//			$inventory->description = 'Traslado';
//			$inventory->item_id = $item_id;
//			$inventory->warehouse_id = $warehouse_id;
//			$inventory->warehouse_destination_id = $warehouse_new_id;
//			$inventory->quantity = $quantity_move;
//			$inventory->detail = $detail;
//
//			$inventory->save();
//
//			foreach ($lots as $lot) {
//				if ($lot['has_sale']) {
//					$item_lot = ItemLot::findOrFail($lot['id']);
//					$item_lot->warehouse_id = $inventory->warehouse_destination_id;
//					$item_lot->update();
//				}
//			}
//
//			return  [
//				'success' => true,
//				'message' => 'Producto trasladado con éxito'
//			];
//		});
//
//		return $result;
//	}

//	public function remove(Request $request)
//	{
//		$result = DB::connection('tenant')->transaction(function () use ($request) {
//			// dd($request->all());
//			$item_id = $request->input('item_id');
//			$warehouse_id = $request->input('warehouse_id');
//			$quantity = $request->input('quantity');
//			$quantity_remove = $request->input('quantity_remove');
//			$lots = ($request->has('lots')) ? $request->input('lots') : [];
//
//			//Transaction
//			$item_warehouse = ItemWarehouse::where('item_id', $item_id)
//										   ->where('warehouse_id', $warehouse_id)
//										   ->first();
//			if (!$item_warehouse) {
//				return [
//					'success' => false,
//					'message' => 'El producto no se encuentra en el almacén indicado'
//				];
//			}
//
//			if ($quantity < $quantity_remove) {
//				return  [
//					'success' => false,
//					'message' => 'La cantidad a retirar no puede ser mayor al que se tiene en el almacén.'
//				];
//			}
//
//			// $item_warehouse->stock = $quantity - $quantity_remove;
//			// $item_warehouse->save();
//
//			$inventory = new Inventory();
//			$inventory->type = 3;
//			$inventory->description = 'Retirar';
//			$inventory->item_id = $item_id;
//			$inventory->warehouse_id = $warehouse_id;
//			$inventory->quantity = $quantity_remove;
//			$inventory->save();
//
//			foreach ($lots as $lot) {
//				if ($lot['has_sale']) {
//					$item_lot = ItemLot::findOrFail($lot['id']);
//					$item_lot->delete();
//				}
//			}
//
//			return  [
//				'success' => true,
//				'message' => 'Producto trasladado con éxito'
//			];
//		});
//
//		return $result;
//	}


    public function stock(Request $request)
    {           
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $id = $request->input('id');
            $item_id = $request->input('item_id');
            $warehouse_id = $request->input('warehouse_id');
            $quantity = $request->input('quantity');
            $quantity_real = $request->input('quantity_real');
            $lots = ($request->has('lots')) ? $request->input('lots') : [];

            if ($quantity_real < 0) {
                return [
                    'success' => false,
                    'message' => 'La cantidad de stock real debe ser mayor o igual a 0'
                ];
            }
            $type = 1;
            $quantity_new = 0;
            $quantity_new = $quantity_real - $quantity;
            if ($quantity_real < $quantity) {
                $quantity_new = $quantity - $quantity_real;
                $type = null;
            }

            $inventory = new Inventory();
            $inventory->type = $type;
            $inventory->description = 'Stock Real';
            $inventory->item_id = $item_id;
            $inventory->warehouse_id = $warehouse_id;
            $inventory->quantity = $quantity_new;
            if ($quantity_real != $quantity) {
                $inventory->inventory_transaction_id = 28;
            }

            $inventory->real_stock = $request->quantity_real;
            $inventory->system_stock = $request->quantity;

            $inventory->save();

            return [
                'success' => true,
                'message' => 'Cantidad de stock actualizado con éxito'
            ];
        });

        return $result;
    }
    
    public function stockMultiples(Request $request)
    {
        $request->validate([
            'items' => 'required|array'
        ]);

        DB::connection('tenant')->beginTransaction();
        try {
            $items = $request->items;
            foreach ($items as $item) {
                $item_id = $item['item_id'];
                $warehouse_id = $item['warehouse_id'];
                $quantity = $item['quantity'];
                $quantity_real = $item['quantity_real'];

                if ($quantity_real < 0) throw new Exception("La cantidad del producto {$item['item_description']} a modificar debe ser mayor o igual a 0", 500);

                $type = 1;
                $quantity_new = 0;
                $quantity_new = $quantity_real - $quantity;
                if ($quantity_real < $quantity) {
                    $quantity_new = $quantity - $quantity_real;
                    $type = null;
                }

                $inventory = new Inventory();
                $inventory->type = $type;
                $inventory->description = 'STock Real';
                $inventory->item_id = $item_id;
                $inventory->warehouse_id = $warehouse_id;
                $inventory->quantity = $quantity_new;
                if ($quantity_real < $quantity) {
                    $inventory->inventory_transaction_id = 28;
                }

                $inventory->real_stock = $item['quantity_real'];
                $inventory->system_stock = $item['quantity'];

                $inventory->save();

            }
            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Cantidad de stock actualizado con éxito'
            ], 200);
        } catch (\Throwable $th) {
            DB::connection('tenant')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|numeric|min:1'
        ]);
        if ($request->hasFile('file')) {
            try {
                $import = new StockImport();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                return [
                    'success' => true,
                    'message' => __('app.actions.upload.success'),
                    'data' => $data
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' => __('app.actions.upload.error'),
        ];
    }

    public function move(Request $request)
    {
        DB::connection('tenant')->beginTransaction();
        try {
            $id = $request->input('id');
            $item_id = $request->input('item_id');
            $warehouse_id = $request->input('warehouse_id');
            $warehouse_new_id = $request->input('warehouse_new_id');
            $quantity = $request->input('quantity');
            $quantity_move = $request->input('quantity_move');
            $lots = ($request->has('lots')) ? $request->input('lots') : [];
            $detail = $request->input('detail');

            if ($quantity_move <= 0) {
                return [
                    'success' => false,
                    'message' => 'La cantidad a trasladar debe ser mayor a 0'
                ];
            }

            if ($warehouse_id === $warehouse_new_id) {
                return [
                    'success' => false,
                    'message' => 'El almacén destino no puede ser igual al de origen'
                ];
            }
            if ($quantity < $quantity_move) {
                return [
                    'success' => false,
                    'message' => 'La cantidad a trasladar no puede ser mayor al que se tiene en el almacén.'
                ];
            }

            $document_type_id = 'U4';
            $warehouse = Warehouse::query()
                ->select('id', 'establishment_id')
                ->where('id', $warehouse_id)
                ->first();

            $series = Series::query()
                ->select('number')
                ->where('establishment_id', $warehouse->establishment_id)
                ->where('document_type_id', 'U4')
                ->first();

            if (!$series) {
                throw new Exception('No se encontraron series en el establecimiento.');
            }

            $row = InventoryTransfer::query()
                ->create([
                    'description' => $detail,
                    'warehouse_id' => $warehouse_id,
                    'warehouse_destination_id' => $warehouse_new_id,
                    'quantity' => 1,
                    'document_type_id' => $document_type_id,
                    'series' => $series->number,
                    'number' => '#',
                ]);

            if($request->lots_group) {
                foreach($request->lots_group as $lot) {
                    InventoryTransferItem::query()->create([
                        'inventory_transfer_id' => $row->id,
                        'item_lots_group_id' => $lot['id'],
                    ]);
                }
            }

            if($request->lots) {
                foreach($request->lots as $lot) {
                    InventoryTransferItem::query()->create([
                        'inventory_transfer_id' => $row->id,
                        'item_lot_id' => $lot['id'],
                    ]);
                }
            }

            $inventory = new Inventory();
            $inventory->type = 2;
            $inventory->description = 'Traslado';
            $inventory->item_id = $item_id;
            $inventory->warehouse_id = $warehouse_id;
            $inventory->warehouse_destination_id = $warehouse_new_id;
            $inventory->quantity = $quantity_move;
            $inventory->inventories_transfer_id = $row->id;
            $inventory->detail = $detail;
            $inventory->save();

            foreach ($lots as $lot) {
                if ($lot['has_sale']) {
                    $item_lot = ItemLot::findOrFail($lot['id']);
                    $item_lot->warehouse_id = $inventory->warehouse_destination_id;
                    $item_lot->update();
                }
            }

            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'message' => 'Producto trasladado con éxito'
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function remove(RemoveRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            // dd($request->all());
            $item_id = $request->input('item_id');
            $warehouse_id = $request->input('warehouse_id');
            $quantity = $request->input('quantity');
            $quantity_remove = $request->input('quantity_remove');
            $lots = ($request->has('lots')) ? $request->input('lots') : [];

            //Transaction
            $item_warehouse = ItemWarehouse::where('item_id', $item_id)
                ->where('warehouse_id', $warehouse_id)
                ->first();
            if (!$item_warehouse) {
                return [
                    'success' => false,
                    'message' => 'El producto no se encuentra en el almacén indicado'
                ];
            }

            if ($quantity < $quantity_remove) {
                return [
                    'success' => false,
                    'message' => 'La cantidad a retirar no puede ser mayor al que se tiene en el almacén.'
                ];
            }

            // $item_warehouse->stock = $quantity - $quantity_remove;
            // $item_warehouse->save();

            $inventory = new Inventory();
            $inventory->type = 3;
            $inventory->inventory_transaction_id = '12';
            $inventory->description = 'Retiro';
            $inventory->item_id = $item_id;
            $inventory->warehouse_id = $warehouse_id;
            $inventory->quantity = $quantity_remove;
            $inventory->save();

            $warehouse = Warehouse::query()->find($warehouse_id);

            $itm = Item::query()
                ->select('id', 'description')
                ->where('id', $item_id)
                ->first();

            $guide_items[] = [
                'id' => $item_id,
                'name' => $itm->description,
                'stock_add' => $quantity_remove
            ];

            $res = (new GuideController())->storeWithData([
                'establishment_id' => $warehouse->establishment_id,
                'warehouse_id' => $warehouse_id,
                'date_of_issue' => now()->format('Y-m-d'),
                'time_of_issue' => now()->format('H:i:s'),
                'inventory_transaction_id' => '12',
                'observations' => 'Retiro',
                'items' => $guide_items
            ]);

            if (!$res['success']) {
                throw new Exception($res['message']);
            }

            $inventory->update([
                'guide_id' => $res['data']['id']
            ]);

            foreach ($lots as $lot) {
                if ($lot['has_sale']) {
                    $item_lot = ItemLot::findOrFail($lot['id']);
                    $item_lot->delete();
                }
            }

            $this->removeItemLotsGroup($request);

            return [
                'success' => true,
                'message' => 'Producto trasladado con éxito'
            ];
        });

        return $result;
    }


    /**
     * Remover lotes
     *
     * @param RemoveRequest $request
     * @return void
     */
    public function removeItemLotsGroup($request)
    {
        $selected_lots_group = $request->selected_lots_group ?? null;

        if ($selected_lots_group) {
            foreach ($selected_lots_group as $lots_group) {
                $lot = $this->getItemLotsGroupById($lots_group['id']);
                $lot->quantity = $lot->quantity - $lots_group['compromise_quantity'];
                $lot->save();
            }
        }
    }


    public function initialize()
    {
        $this->initializeInventory();
    }

    public function regularize_stock()
    {
        DB::connection('tenant')->transaction(function () {
            $item_warehouses = ItemWarehouse::get();

            foreach ($item_warehouses as $it_warehouse) {
                $inv_kardex = InventoryKardex::where([['item_id', $it_warehouse->item_id], ['warehouse_id', $it_warehouse->warehouse_id]])->sum('quantity');
                $it_warehouse->stock = $inv_kardex;
                $it_warehouse->save();
            }
        });

        return [
            'success' => true,
            'message' => 'Stock regularizado'
        ];
    }

    public function location_index()
    {
        $locationTypes = WarehouseLocationType::all();
        return view('inventory::locations.index', compact('locationTypes'));
    }

    public function getTypes() {
        $locationTypes = WarehouseLocationType::all();
        if (!$locationTypes->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => $locationTypes
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron tipos de ubicación.'
            ], 404);
        }
    }

    public function create()
    {
        return view('inventory::locations.form');
    }

    public function submit(LocationRequest $request)
    {
        DB::connection('tenant')->transaction(function () use ($request) {
            $data = $request->all();
            
            $this->location = new InventoryWarehouseLocation();
            $this->location->fill($data);
            $this->location->warehouse_id = $data['warehouse_id'];
            $this->location->save();

            // Crear posiciones basadas en filas y columnas
            foreach ($data['positions'] as $positionData) {
                $this->location->positions()->create([
                    'row' => $positionData['row'],
                    'column' => $positionData['column'],
                    'status' => $positionData['status'] // Estado enviado desde el frontend
                ]);
            }
        });
        
        return [
            'success' => true,
            'message' => 'Ubicación registrada correctamente',
            'data' => [
                'id' => $this->location->id
            ],
        ];
    }

    public function list(Request $request)
    {
        $query = InventoryWarehouseLocation::query();

        // Aplicar filtros si existen
        if ($request->has('column') && $request->has('value')) {
            $query->where($request->column, 'like', '%' . $request->value . '%');
        }

        // Paginación
        $records = $query->paginate(10);

        return response()->json([
            'data' => $records->items(),
            'meta' => [
                'total' => $records->total(),
                'per_page' => $records->perPage(),
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
            ],
        ]);
    }

    public function locationColumns()
    {
        return response()->json([
            'name' => 'Nombre',
            'code' => 'Código'
        ]);
    }

    public function show($id)
    {
        $location = InventoryWarehouseLocation::with('positions')->find($id);
        if ($location) {
            return response()->json([
                'success' => true,
                'data' => $location,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Ubicación no encontrada.',
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $location = InventoryWarehouseLocation::find($id);
            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ubicación no encontrada.',
                ], 404);
            }

            $location->update($request->only(['name', 'code', 'status', 'type_id', 'rows', 'columns', 'maximum_stock']));

            $currentRows = $location->positions()->pluck('row')->unique();
            $currentColumns = $location->positions()->pluck('column')->unique();

            if ($request->has('positions')) {
                $updatedRows = collect();
                $updatedColumns = collect();

                foreach ($request->positions as $positionData) {
                    $location->positions()
                        ->updateOrCreate(
                            [
                                'row' => $positionData['row'],
                                'column' => $positionData['column']
                            ],
                            ['status' => $positionData['status']]
                        );

                    $updatedRows->push($positionData['row']);
                    $updatedColumns->push($positionData['column']);
                }

                $rowsToDelete = $currentRows->diff($updatedRows->unique());
                if ($rowsToDelete->isNotEmpty()) {
                    $location->positions()->whereIn('row', $rowsToDelete)->delete();
                }

                $columnsToDelete = $currentColumns->diff($updatedColumns->unique());
                if ($columnsToDelete->isNotEmpty()) {
                    $location->positions()->whereIn('column', $columnsToDelete)->delete();
                }
            } else {
                $location->positions()->delete();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Ubicación y posiciones actualizadas correctamente.',
        ]);
    }

    public function updatePositions(Request $request, $id)
    {
        $location = InventoryWarehouseLocation::find($id);
        if ($location) {
            // Eliminar las posiciones existentes
            WarehouseLocationPosition::where('location_id', $id)->delete();

            // Crear las nuevas posiciones
            foreach ($request->positions as $position) {
                WarehouseLocationPosition::create([
                    'location_id' => $id,
                    'row' => $position['row'],
                    'column' => $position['column'],
                    'status' => $position['status'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Posiciones actualizadas correctamente.',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Ubicación no encontrada.',
            ], 404);
        }
    }

    public function edit($id)
    {
        return view('inventory::locations.edit', [
            'id' => $id,
        ]);
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $location = InventoryWarehouseLocation::find($id);
            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ubicación no encontrada.',
                ], 404);
            }

            $location->positions()->delete();

            $location->delete();

        });
        return response()->json([
            'success' => true,
            'message' => 'Ubicación eliminada correctamente.',
        ]);
    }

    public function warehouses(){
        $warehouses = TenantWarehouse::all();

        if(!$warehouses->isEmpty()){
            return response()->json([
                'success' => true,
                'message' => 'Almacenes encontrados correctamente',
                'data' => $warehouses
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Almacenes no encontrados'
            ]);
        }
        
    }

    public function getLocationsById($id){

        $query = InventoryWarehouseLocation::where('warehouse_id', $id);

        // Paginación
        $records = $query->paginate(10);

        return response()->json([
            'data' => $records->items(),
            'meta' => [
                'total' => $records->total(),
                'per_page' => $records->perPage(),
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
            ],
        ]);
    }

    public function createWarehouse($id){
        return view('inventory::locations.formWithInput',[ 'id' => $id ]);
    }
}
