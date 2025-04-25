<?php
namespace App\Http\Controllers\Tenant;

use App\Exports\DigemidItemExport;
use App\Exports\ItemExport;
use App\Exports\ItemExportWp;
use App\Exports\ItemExtraDataExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PdfUnionController;
use App\Http\Controllers\SearchItemController;
use App\Http\Requests\Tenant\ItemRequest;
use App\Http\Resources\Tenant\ItemCollection;
use App\Http\Resources\Tenant\ItemResource;
use App\Imports\CatalogImport;
use App\Imports\ItemsImport;
use App\Imports\ItemsImportRestaurant;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\AttributeType;
use App\Models\Tenant\Catalogs\CatColorsItem;
use App\Models\Tenant\Catalogs\CatItemMoldCavity;
use App\Models\Tenant\Catalogs\CatItemMoldProperty;
use App\Models\Tenant\Catalogs\CatItemPackageMeasurement;
use App\Models\Tenant\Catalogs\CatItemProductFamily;
use App\Models\Tenant\Catalogs\CatItemStatus;
use App\Models\Tenant\Catalogs\CatItemUnitBusiness;
use App\Models\Tenant\Catalogs\CatItemUnitsPerPackage;
use App\Models\Tenant\Catalogs\ChargeDiscountType;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\OperationType;
use App\Models\Tenant\Catalogs\PriceType;
use App\Models\Tenant\Catalogs\SystemIscType;
use App\Models\Tenant\Catalogs\Tag;
use App\Models\Tenant\Catalogs\UnitType;
use App\Models\Tenant\CatItemSize;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\InventoryState;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemFile;
use App\Models\Tenant\ItemImage;
use App\Models\Tenant\ItemMovement;
use App\Models\Tenant\ItemPosition;
use App\Models\Tenant\ItemSalesCondition;
use App\Models\Tenant\ItemSupply;
use App\Models\Tenant\ItemTag;
use App\Models\Tenant\ItemUnitType;
use App\Models\Tenant\ItemWarehousePrice;
use App\Models\Tenant\Person;
use App\Models\Tenant\PharmaceuticalItemUnitType;
use App\Models\Tenant\Warehouse;
use App\Traits\OfflineTrait;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel;
use Modules\Account\Models\Account;
use Modules\Digemid\Models\CatDigemid;
use Modules\Finance\Helpers\UploadFileHelper;
use Modules\Inventory\Models\ItemWarehouse;
use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;
use Modules\Item\Models\ItemLot;
use Modules\Item\Models\ItemLotsGroup;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use setasign\Fpdi\Fpdi;
use Modules\Inventory\Models\InventoryConfiguration;
use Modules\Inventory\Models\InventoryWarehouseLocation;
use Modules\Inventory\Models\WarehouseLocationPosition;
use Symfony\Component\HttpFoundation\StreamedResponse;

#Importación Momentanea para abastecer la solicitud de notificaciones
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    use OfflineTrait;

    public function index()
    {
        $type = 'PRODUCTS';
        return view('tenant.items.index', compact('type'));
    }

    public function indexServices()
    {
        $type = 'ZZ';
        return view('tenant.items.index', compact('type'));
    }

    public function index_ecommerce()
    {
        return view('tenant.items_ecommerce.index');
    }

    public function columns()
    {
        return [
            'description' => 'Nombre',
            'internal_id' => 'Código interno',
            'barcode' => 'Código de barras',
            'model' => 'Modelo',
            'brand' => 'Marca',
            'date_of_due' => 'Fecha vencimiento',
            'lot_code' => 'Código lote',
            'active' => 'Habilitados',
            'inactive' => 'Inhabilitados',
            'category' => 'Categoria',
            'active_principle' => 'Principio Activo',
            'pharmaceutical_item_unit_type' => 'Presentación',
        ];
    }

    public function records(Request $request)
    {

        // dd($request->all());
        $records = $this->getRecords($request);

        return new ItemCollection($records->paginate(config('tenant.items_per_page')));
    }


    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRecords(Request $request)
    {

        // $records = Item::whereTypeUser()->whereNotIsSet();
        $records = $this->getInitialQueryRecords();
        // dd($records->get());

        switch ($request->column)
        {

            case 'brand':
                $records->whereHas('brand',function($q) use($request){
                                    $q->where('name', 'like', "%{$request->value}%");
                                });
                break;
            case 'category':
                $records->whereHas('category',function($q) use($request){
                                    $q->where('name', 'like', "%{$request->value}%");
                                });
                break;
            
            case 'active_principle':
                $records->where('active_principle', 'like', "%{$request->value}%");
                break;

            case 'pharmaceutical_item_unit_type':
                $records->whereHas('pharmaceutical_item_unit_type', function($q) use($request){
                    $q->where('description', 'like', "%{$request->value}%");
                });

                break;

            case 'active':
                $records->whereIsActive();
                break;

            case 'inactive':
                $records->whereIsNotActive();
                break;

            default:
                if($request->has('column'))
                {
                    if($this->applyAdvancedRecordsSearch() && $request->column === 'description')
                    {
                        if($request->value) $records->whereAdvancedRecordsSearch($request->column, $request->value);
                    }
                    else
                    {
                        $records->where($request->column, 'like', "%{$request->value}%");
                    }
                }
                break;
        }

        if ($request->type) {
            if($request->type ==='PRODUCTS') {
                // listar solo productos en la lista de productos
                $records->whereNotService();
            }else{
                $records->whereService();
            }
        }
        $isPharmacy = false;
        if($request->has('isPharmacy') ){
            $isPharmacy = ($request->isPharmacy==='true')?true:false;
        }
        if($isPharmacy == true){
            $records->Pharmacy()
                ->with(['cat_digemid']);
        }

        $isRestaurant = $request->has('isRestaurant') && $request->isRestaurant === 'true';
        $isEcommerce = $request->has('isEcommerce') && $request->isEcommerce === 'true';
        
        if ($request->has('list_value')) {
            switch ($request->list_value) {
                case 'visible':
                    if ($isRestaurant) {
                        $records->where('apply_restaurant', 1);
                    }
                    if ($isEcommerce) {
                        $records->where('apply_store', 1);
                    }
                    break;
        
                case 'hidden':
                    if ($isRestaurant) {
                        $records->where('apply_restaurant', 0);
                    }
                    if ($isEcommerce) {
                        $records->where('apply_store', 0);
                    }
                    break;
            }
        }


        return $records->orderBy('id', 'desc');

    }


    /**
     *
     * Aplicar filtros iniciales a la consulta
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getInitialQueryRecords()
    {

        if(Configuration::getRecordIndividualColumn('list_items_by_warehouse'))
        {
            $records = Item::whereWarehouse()->whereNotIsSet();
        }
        else
        {
            $records = Item::whereTypeUser()->whereNotIsSet();
        }

        return $records;
    }


    public function create()
    {
        return view('tenant.items.form');
    }

    public function tables()
    {
        $establishment_id = auth()->user()->establishment_id;
        $unit_types = UnitType::whereActive()->orderByDescription()->get();
        $currency_types = CurrencyType::whereActive()->orderByDescription()->get();
        $attribute_types = AttributeType::whereActive()->orderByDescription()->get();
        $system_isc_types = SystemIscType::whereActive()->orderByDescription()->get();
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $warehouses = Warehouse::where('establishment_id', $establishment_id)->get();
        $accounts = Account::all();
        $tags = Tag::all();
        $categories = Category::all();
        $brands = Brand::all();
        $configuration= Configuration::first();
        /** Informacion adicional */
        $colors = collect([]);
        $CatItemStatus=$colors;
        $CatItemUnitBusiness = $colors;
        $CatItemMoldCavity = $colors;
        $CatItemPackageMeasurement =$colors;
        $CatItemUnitsPerPackage = $colors;
        $CatItemMoldProperty = $colors;
        $CatItemProductFamily= $colors;
        $CatItemSize= $colors;
        if($configuration->isShowExtraInfoToItem()){
            $colors = CatColorsItem::all();
            $CatItemStatus= CatItemStatus::all();
            $CatItemSize= CatItemSize::all();
            $CatItemUnitBusiness = CatItemUnitBusiness::all();
            $CatItemMoldCavity = CatItemMoldCavity::all();
            $CatItemPackageMeasurement = CatItemPackageMeasurement::all();
            $CatItemUnitsPerPackage = CatItemUnitsPerPackage::all();
            $CatItemMoldProperty = CatItemMoldProperty::all();
            $CatItemProductFamily= CatItemProductFamily::all();
        }
        /** Informacion adicional */
        $configuration = $configuration->getCollectionData();
        $inventory_configuration = InventoryConfiguration::firstOrFail();
        $sales_conditions = ItemSalesCondition::all();
        $suppliers = Person::where('type','suppliers')->get();
        $pharmaceutical_item_unit_types = PharmaceuticalItemUnitType::where('active', 1)->get();

        
        $states = InventoryState::all();
        /*
        $configuration = Configuration::select(
            'affectation_igv_type_id',
            'is_pharmacy',
            'show_extra_info_to_item'
        )->firstOrFail();
        */
        return compact(
            'pharmaceutical_item_unit_types',
            'states',
            'unit_types',
            'currency_types',
            'attribute_types',
            'system_isc_types',
            'affectation_igv_types',
            'warehouses',
            'accounts',
            'tags',
            'categories',
            'brands',
            'configuration',
            'colors',
            'CatItemSize',
            'CatItemMoldCavity',
            'CatItemMoldProperty',
            'CatItemUnitBusiness',
            'CatItemStatus',
            'CatItemPackageMeasurement',
            'CatItemProductFamily',
            'CatItemUnitsPerPackage',
            'inventory_configuration',
            'sales_conditions',
            'suppliers'
        );
    }

    public function getLocationsByWarehouse($warehouse_id){
        $locations = InventoryWarehouseLocation::where('warehouse_id', $warehouse_id)->get();
        if(empty($locations)){
            return response()->json([
                'success' => false,
                'message' => 'Ubicación no encontrada',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Ubicaciones encontradas',
            'data' => $locations
        ], 200);
    }
    
    public function getLocations(Request $request){        
        $locations = ItemPosition::where('warehouse_id', $request->warehouse_id)
            ->where('item_id', $request->item_id)
            ->groupBy('location_id')
            ->pluck('location_id');

        $datos= InventoryWarehouseLocation::whereIn('id', $locations)->get();        
        return $datos;        
    }
    
    public function positions($location_id, $item_id = null)
    {
        $location = InventoryWarehouseLocation::find($location_id);

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'Ubicación no encontrada',
            ], 404);
        }

        if($item_id!=null){
            $positions = WarehouseLocationPosition::with(['lots' => function ($query) use ($item_id) {
                if ($item_id) {
                    $query->whereHas('lots_group', function ($query) use ($item_id) {
                        $query->where('item_id', $item_id);
                    });
                }
            }, 'lots.lots_group'])
                ->where('location_id', $location_id)
                ->get();
        }else{
            $positions = WarehouseLocationPosition::with('lots.lots_group')
            ->where('location_id', $location_id)
            ->get();
        }
        foreach ($positions as $position) {
            $position->stock_available = $location->maximum_stock - $position->quantity_used;
            $position->code_location = $location->code;
        }

        $itemPositions = [];

        if ($item_id) {
            $itemPositionsSelected = ItemPosition::where('item_id', $item_id)->where('location_id',$location_id)->get();

            if ($itemPositionsSelected) {
                foreach ($itemPositionsSelected as $positionSelected) {
                    $itemPosition = WarehouseLocationPosition::find($positionSelected->position_id);
                    if ($itemPosition) {
                        $itemPosition->stock = $positionSelected->stock;
                        $itemPositions[] = $itemPosition;
                    }
                }
            }
        }

        $responseData = [
            'success' => true,
            'message' => 'Datos extraídos correctamente',
            'data' => [
                'positions' => $positions,
                'item_positions' => $itemPositions,
            ],
        ];

        return response()->json($responseData, 200);
    }

    //GEORGE
    public function notifications(){
        $top_products = Item::productsWithoutRotation()->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
            ];
        });;


        //$records = $this->getInitialQueryRecords();
        //$top_products = $records->whereNotService();

        //dd(tenant());
        /*$top_products = DB::table('items')
                        ->where('status', 1) // Filtrar por status 1
                        ->select('id', 'name') // Seleccionar id y name
                        ->get(); // Obtener los resultados*/

         // Productos sin rotación (últimos 30 días), formateados para JSON
        $productos_sin_rotacion = collect(Item::getProductsWithoutRecentSales(30))->map(function ($item) {
            return [
                'codigo' => $item->id,
                'nombre' => $item->name ?: $item->description,
                'fecha' => '—',
                'lote' => '—',
            ];
        });

        $facturas_vencidas = collect(Item::getOverduePurchaseInvoices())->map(function ($item) {
            return [
                'codigo' => $item->series . '-' . $item->number,
                'nombre' => 'Factura vencida',
                'fecha' => $item->date_of_due,
                'lote' => '—',
            ];
        });

        $productos_stock_minimo = collect(Item::getItemsWithLowStock())->map(function ($item) {
            return [
                'codigo' => $item->id,
                'nombre' =>  $item->name ?: $item->description,
                'fecha' => '—',
                'lote' => 'Stock: ' . $item->stock . ' / Mín: ' . $item->stock_min,
            ];
        });

        $productos_por_vencer = collect(Item::getItemsNearExpiration())->map(function ($item) {
            return [
                'codigo' => $item->id,
                'nombre' => $item->name ?: $item->description,
                'fecha' => $item->fecha_lote ?? $item->fecha_item,
                'lote' => $item->code_lote ?? '—',
            ];
        });

        $proveedores_retraso = collect(Item::getDelayedSupplierDeliveries())->map(function ($item) {
            return [
                'codigo' => $item->series . '-' . $item->number,
                'nombre' => $item->proveedor,
                'fecha' => $item->date_of_due,
                'lote' => 'No entregado',
            ];
        });

        return response()->json([
            'top-products' => $top_products,
            'notificaciones' => [
                [
                    'titulo' => 'Productos sin Rotación en los último 30 Días:',
                    'accion' => 'Tomar acción',
                    'url' => './documents/create',
                    'productos' => $productos_sin_rotacion,
                ],
                [
                    'titulo' => 'Pagos Vencidos a Proveedores:',
                    'accion' => 'Tomar acción',
                    'url' => './purchases',
                    'productos' => $facturas_vencidas,
                ],
                [
                    'titulo' => 'Stock mínimo alcanzado:',
                    'accion' => 'Revisar inventario',
                    'url' => './items',
                    'productos' => $productos_stock_minimo,
                ],
                [
                    'titulo' => 'Productos próximos a vencer (30 días):',
                    'accion' => 'Revisar vencimientos',
                    'url' => './items',
                    'productos' => $productos_por_vencer,
                ],
                [
                    'titulo' => 'Proveedores con retraso de entrega:',
                    'accion' => 'Ver órdenes pendientes',
                    'url' => './purchases',
                    'productos' => $proveedores_retraso,
                ]
            ]
        ]);
    }

    public function record($id)
    {
        $record = new ItemResource(Item::findOrFail($id));

        return $record;
    }

    public function getPositionsSelected($item_id){
        $item_positions = [];
        $item_positions_selected = ItemPosition::where('item_id', $item_id)->get();
        if($item_positions_selected){
            foreach($item_positions_selected as $position_selected){
                $item_position = WarehouseLocationPosition::find($position_selected->position_id);
                if($item_position){
                    $item_position->stock=$position_selected->stock;
                    array_push($item_positions, $item_position);
                }
            }
        }
        if(!empty($item_positions)){
            return response()->json([
                'success' => true,
                'message' => 'Datos extraidos correctamente',
                'data' => $item_positions
            ], 200);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Datos no encontrados'
            ], 401);
        }
    }

    public function saveDocuments(Request $request, $id)
    {
        try {
            $item = Item::findOrFail($id);

            if ($request->has('files_deleted')) {
                $filesDeleted = json_decode($request->input('files_deleted'), true);

                foreach ($filesDeleted as $fileData) {
                    try {
                        $fileToDelete = $item->item_files()->find($fileData['id']);
                        if ($fileToDelete) {
                            Storage::disk('tenant')->delete($fileToDelete->route);
                            $fileToDelete->delete();
                        }
                    } catch (Exception $e) {
                        Log::error('Error al eliminar archivo: ' . $e->getMessage());
                        return response()->json([
                            'success' => false,
                            'message' => 'Error al eliminar archivo: ' . $e->getMessage(),
                        ], 500);
                    }
                }
            }

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $document) {
                    try {
                        $file_name_old = $document->getClientOriginalName();
                        $file_info = pathinfo($file_name_old);
                        $file_content = file_get_contents($document->getPathname());
                        $file_name = Str::slug($file_info['filename']) . "-" . $item->id . '.' . $file_info['extension'];
                        $path_file = 'document_products' . DIRECTORY_SEPARATOR . $file_name;

                        // Guardar el archivo en el almacenamiento
                        Storage::disk('tenant')->put($path_file, $file_content);

                        // Crear el registro en la base de datos
                        $item->item_files()->create([
                            'item_id' => $id,
                            'filename' => $file_name_old,
                            'route' => $path_file,
                            'user_created_at' => auth()->user()->email,
                        ]);
                    } catch (Exception $e) {
                        Log::error('Error al subir archivo: ' . $e->getMessage());
                        return response()->json([
                            'success' => false,
                            'message' => 'Error al subir archivo: ' . $e->getMessage(),
                        ], 500);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Documentos guardados y eliminados correctamente',
            ]);

        } catch (Exception $e) {
            Log::error('Error en saveDocuments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en el servidor: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function downloadDocument($id){
        $item_file = ItemFile::find($id);
        if($item_file){
            $rutaCompleta = Storage::disk('tenant')->path($item_file->route);
            /* return json_encode([
                'success' => false,
                'message' => 'Archivo no encontrado con el id: '.$id,
                'data' => $item_file,
                'ruta' => $rutaCompleta
            ]); */
            if (!file_exists($rutaCompleta)) {
                return json_encode([
                    'success' => false,
                    'message' => 'Archivo no encontrado',
                    'id' => $id
                ]);
            }
            return response()->download($rutaCompleta);
        }else{
            return json_encode([
                'success' => false,
                'message' => 'Archivo no encontrado con el id: '.$id,
                'id' => $id
            ]);
        }
    }

    public function store(ItemRequest $request) {
        $id = $request->input('id');
        if (!$request->barcode) {
            if ($request->internal_id) {
                $request->merge(['barcode' => $request->internal_id]);
            }
        }
        $item = Item::firstOrNew(['id' => $id]);
        $item->item_type_id = '01';
        $item->amount_plastic_bag_taxes = Configuration::firstOrFail()->amount_plastic_bag_taxes;
        if ($request->has('date_of_due')) {
            $time = $request->date_of_due;
            $date = null;
            if (isset($time['date'])) {
                $date = $time['date'];
                if (!empty($date)) {
                    $request->merge(['date_of_due' => Carbon::createFromFormat('Y-m-d H:i:s.u', $date)]);
                }
            }
        }

        $item->fill($request->all());

        $temp_path = $request->input('temp_path');
        if($temp_path) {

            $directory = 'public'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'items'.DIRECTORY_SEPARATOR;

            $slug_name = Str::slug($item->description);
            if($item->internal_id){
                $slug_name = Str::slug($item->internal_id);
            }
            $prefix_name = Str::limit($slug_name, 20, '');

            $file_name_old = $request->input('image');
            $file_name_old_array = explode('.', $file_name_old);
            $file_content = file_get_contents($temp_path);
            $datenow = date('YmdHis');
            $file_name = $prefix_name.'-'.$datenow.'.'.$file_name_old_array[1];

            UploadFileHelper::checkIfValidFile($file_name, $temp_path, true);

            Storage::put($directory.$file_name, $file_content);
            $item->image = $file_name;

            //--- IMAGE SIZE MEDIUM
            $image = \Image::make($temp_path);
            $file_name = $prefix_name.'-'.$datenow.'_medium'.'.'.$file_name_old_array[1];
            $image->resize(512, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            Storage::put($directory.$file_name,  (string) $image->encode('jpg', 30));
            $item->image_medium = $file_name;

              //--- IMAGE SIZE SMALL
            $image = \Image::make($temp_path);
            $file_name = $prefix_name.'-'.$datenow.'_small'.'.'.$file_name_old_array[1];
            $image->resize(256, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            Storage::put($directory.$file_name,  (string) $image->encode('jpg', 20));
            $item->image_small = $file_name;



        }else if(!$request->input('image') && !$request->input('temp_path') && !$request->input('image_url')){
            $item->image = 'imagen-no-disponible.jpg';
        }

        $item->save();

        $processedLots = [];
        if ($request->lots_enabled) {
            
            foreach ($request->lots as $lotData) {
                if (isset($lotData['id'])) {
                    $lot = ItemLotsGroup::find($lotData['id']);
                    if ($lot) {
                        $lot->update([
                            'code' => $lotData['code'],
                            'quantity' => $lotData['quantity'],
                            'date_of_due' => $lotData['date_of_due'],
                            'status' => $lotData['status'],
                            'warehouse_id' => $request->warehouse_id,
                        ]);
                        $processedLots[$lotData['code']] = $lot->id;
                    }
                } else {
                    $lot = ItemLotsGroup::create([
                        'code' => $lotData['code'],
                        'quantity' => $lotData['quantity'],
                        'date_of_due' => $lotData['date_of_due'],
                        'status' => $lotData['status'],
                        'item_id' => $item->id,
                        'warehouse_id' => $request->warehouse_id,
                    ]);
                    $processedLots[$lotData['code']] = $lot->id;
                }
            }
            
            if(isset($request->positions_selected)) {
                
                ItemPosition::where('item_id', $item->id)
                    ->whereNull('lots_group_id')
                    ->where('warehouse_id', $request->warehouse_id)
                    ->delete();
                    
                foreach ($request->positions_selected as $position) {
                    $warehouseLocationPosition = WarehouseLocationPosition::where('location_id', $request->location_id)
                            ->where('row', $position['row'])
                            ->where('column', $position['column'])
                            ->first();
                            
                    if ($warehouseLocationPosition) {
                        $inventoryWarehouseLocation = InventoryWarehouseLocation::find($warehouseLocationPosition->location_id);
                        
                        if($inventoryWarehouseLocation) {

                            foreach ($position['lots'] as $lot) {
                                $lotId = $processedLots[$lot['code']] ?? null;
                                
                                if ($lotId) {
                                    $countItemPositions = ItemPosition::where('item_id', $item->id)->where('position_id', $warehouseLocationPosition->id)->count();
                                    
                                    ItemPosition::updateOrCreate(
                                        [
                                            'item_id' => $item->id,
                                            'position_id' => $warehouseLocationPosition->id,
                                            'warehouse_id' => $inventoryWarehouseLocation->warehouse_id,
                                            'location_id' => $request->location_id,
                                            'lots_group_id' => $lotId,
                                        ],
                                        [
                                            'stock' => $lot['stock'],
                                        ]
                                    );

                                    if($countItemPositions==0){
                                        WarehouseLocationPosition::where('id', $warehouseLocationPosition->id)
                                        ->increment('quantity_used');
                                    }
                                    
                                }
                            }
                        }
                    }
                }
            }
        } else {
            ItemPosition::whereNotNull('lots_group_id')
                ->where('item_id', $item->id)
                ->where('warehouse_id', $request->warehouse_id)
                ->delete();
                
            ItemLotsGroup::where('item_id', $item->id)
                ->where('warehouse_id', $request->warehouse_id)
                ->delete();
                
            // Procesar posiciones sin lotes
            if(isset($request->positions_selected)) {
                foreach ($request->positions_selected as $position) {
                    $warehouseLocationPosition = WarehouseLocationPosition::where('location_id', $request->location_id)
                            ->where('row', $position['row'])
                            ->where('column', $position['column'])
                            ->first();
                            
                    if ($warehouseLocationPosition) {
                        $inventoryWarehouseLocation = InventoryWarehouseLocation::find($warehouseLocationPosition->location_id);
                        
                        if($inventoryWarehouseLocation) {
                            $itemPosition = ItemPosition::updateOrCreate(
                                [
                                    'item_id' => $item->id,
                                    'position_id' => $warehouseLocationPosition->id,
                                    'lots_group_id' => null
                                ],
                                [
                                    'warehouse_id' => $inventoryWarehouseLocation->warehouse_id,
                                    'location_id' => $inventoryWarehouseLocation->id,
                                    'stock' => $position['stock'],
                                ]
                            );
                            
                            // Actualizar cantidad usada en la posición
                            WarehouseLocationPosition::where('id', $warehouseLocationPosition->id)
                                ->increment('quantity_used');
                        }
                    }
                }
            }
        }

        foreach ($request->item_unit_types as $value) {

            $item_unit_type = ItemUnitType::firstOrNew(['id' => $value['id']]);
            $item_unit_type->item_id = $item->id;
            $item_unit_type->description = $value['description'];
            $item_unit_type->unit_type_id = $value['unit_type_id'];
            $item_unit_type->quantity_unit = $value['quantity_unit'];
            $item_unit_type->price1 = $value['price1'];
            $item_unit_type->price2 = $value['price2'];
            $item_unit_type->price3 = $value['price3'];
            $item_unit_type->price_default = $value['price_default'];
            $item_unit_type->save();

            // migracion desarrollo sin terminar #1401
            if(!$value['barcode']) {
                $item_unit_type->barcode = $item_unit_type->id.$item_unit_type->unit_type_id.$item_unit_type->quantity_unit;
                $item_unit_type->save();
            }
            else {
                $item_unit_type->barcode = $value['barcode'];
                $item_unit_type->save();
            }
        }
        if (isset($request->supplies)) {
            foreach($request->supplies as $value){

                if(!isset($value['item_id'])) $value['item_id'] = $item->id;
                $itemSupply = ItemSupply::firstOrCreate(['id' => $value['id']],$value);
                $itemSupply->fill($value);
                $itemSupply->save();
            }
        }

        $configuration = Configuration::first();
        if($configuration->isShowExtraInfoToItem()){
            // Extra data
            if($request->has('colors')){
                $item->setItemColor($request->colors);
            }
            if($request->has('CatItemUnitsPerPackage')){
                $item->setItemUnitsPerPackage($request->CatItemUnitsPerPackage);
            }
            if($request->has('CatItemMoldCavity')){
                $item->setItemMoldCavity($request->CatItemMoldCavity);
            }
            if($request->has('CatItemMoldProperty')){
                $item->setItemMoldProperty($request->CatItemMoldProperty);
            }
            if($request->has('CatItemUnitBusiness')){
                $item->setItemUnitBusiness($request->CatItemUnitBusiness);
            }
            if($request->has('CatItemStatus')){
                $item->setItemStatus($request->CatItemStatus);
            }
            if($request->has('CatItemPackageMeasurement')){
                $item->setItemPackageMeasurement($request->CatItemPackageMeasurement);
            }
            if($request->has('CatItemProductFamily')){
                $item->setItemProductFamily($request->CatItemProductFamily);
            }
            if($request->has('CatItemSize')){
                $item->setItemSize($request->CatItemSize);
            }
            // Extra data
        }



        if ($request->tags_id) {
            ItemTag::destroy(   ItemTag::where('item_id', $item->id)->pluck('id'));
            foreach ($request->tags_id as $value) {
                ItemTag::create(['item_id' => $item->id,  'tag_id' => $value]);
                //$tag = ItemTag::where('item_id', $item->id)->where('tag_id', $value)->first();
            }
        }
        $item->lots_enabled = isset($request->lots_enabled) ? $request->lots_enabled:false;



        if (!$id) {

            // $item->lots()->delete();
            $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
            $warehouse = Warehouse::where('establishment_id',$establishment->id)->first();

            //$warehouse = WarehouseModule::find(auth()->user()->establishment_id);
            if($warehouse && !isset($request->warehouse_id)){
                $item->warehouse_id = $warehouse->id;
                $item->save();
            }
        } else {
            /****************************** SECCION PARA EDITAR ITEM_LOTS_GROUP **********************************************/
            $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
            $warehouse = Warehouse::where('establishment_id',$establishment->id)->first();
            //Eliminar lotes que ya no estan
            /* $v_lots = isset($request->lots) ? $request->lots:[];
            $lots = ItemLotsGroup::where('item_id', $item->id)->get();
            $v_lot_ids = array_column($v_lots, 'id');
            foreach ($lots as $lot) {
                if (!in_array($lot->id, $v_lot_ids)){
                    ItemPosition::where('lots_group_id', $lot->id)->delete();
                    $lot->delete();
                }
            }

            foreach ($v_lots as $lot) {
                $lot_id = isset($lot['id'])? (int) $lot['id']:0;
                if($lot_id != 0){
                    $temp_lot = ItemLotsGroup::find($lot_id);
                    if(!empty($temp_lot)){
                        $temp_lot
                            ->setDateOfDue($lot['date_of_due'])
                            ->setCode($lot['code'])
                            ->setStatus($lot['status'])
                            ->setQuantity($lot['quantity'])
                            ->push();
                    }
                }else{
                    $temp_lot = new ItemLotsGroup([
                        'date_of_due' => $lot['date_of_due'],
                        'code' => $lot['code'],
                        'item_id' => $item->id,
                        'quantity' => $lot['quantity'],
                        'status' => $lot['status'],
                    ]);
                    $temp_lot->push();
                }
            } */
        }


        $directory = 'public'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'items'.DIRECTORY_SEPARATOR;

        $multi_images = isset($request->multi_images) ? $request->multi_images:[];

        foreach ($multi_images as $im) {

            $file_name = $im['filename'];
            UploadFileHelper::checkIfValidFile($file_name, $im['temp_path'], true);

            $file_content = file_get_contents($im['temp_path']);
            Storage::put($directory.$file_name, $file_content);

            ItemImage::create(['item_id'=> $item->id, 'image' => $file_name]);
        }

        if (!$item->barcode) {
            $item->barcode = str_pad($item->id, 12, '0', STR_PAD_LEFT);
        }

        $item->update();

        // migracion desarrollo sin terminar #1401
        // $inventory_configuration = InventoryConfiguration::firstOrFail();

        // if($inventory_configuration->generate_internal_id == 1) {
        //     if(!$item->internal_id) {
        //         $items = Item::count();
        //         $item->internal_id = (string)($items + 1);
        //         $item->save();
        //     }
        // }

        $this->generateInternalId($item);

        /********************************* SECCION PARA PRECIO POR ALMACENES ******************************************/

        // Precios por almacenes
        // $warehouses = $request->warehouses;

        $this->createItemWarehousePrices($request, $item);

        // if ($warehouses) {
            // /** @var ItemWarehousePrice $price */

            // foreach ($warehouses as $warehouse) {
            //     $price = ItemWarehousePrice::where([
            //         'item_id' => $item->id,
            //         'warehouse_id' => $warehouse['id'],
            //     ])->first();
            //     if(empty($price)){
            //         $price = new ItemWarehousePrice([
            //             'item_id' => $item->id,
            //             'warehouse_id' => $warehouse['id'],
            //         ]) ;
            //     }
            //     $price
            //         ->setPrice($warehouse['price'])
            //         ->push();
            // }

            /*
            ItemWarehousePrice::where('item_id', $item->id)
                ->delete();

            foreach ($warehouses as $warehousePrice) {
                try {
                    $price = $warehousePrice['price'];
					if (is_numeric($warehousePrice['price'])) {
						ItemWarehousePrice::query()->insert([
							'item_id'      => $item->id,
							'warehouse_id' => $warehousePrice['id'],
							'price'        => $price,
						]);
					}
                } catch (\Throwable $th) {
                    \Log::error('No se pudo agregar el precio del producto al almacén ' . $warehousePrice['id']);
                }
            }
            */
        // }

        return [
            'success' => true,
            'message' => ($id)?'Producto editado con éxito':'Producto registrado con éxito',
            'id' => $item->id
        ];
    }

    public function visibleMassive(Request $request)
    {
        $type_product = $request->input('resource');
        $column = $type_product === 'restaurant' ? 'apply_restaurant' : 'apply_store';

        try {
            Item::whereNotNull('internal_id')->where($column, '=', 0)->update([
                $column => true
            ]);
            return [
                'success' => true,
                'message' => 'Todo los productos son visible en el restaurante'
            ];
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => $th->getMessage()
            ];
        }

    }
    /**
     *
     * Generar codigo interno de forma automatica
     *
     * @param  Item $item
     * @return void
     */
    public function generateInternalId(Item &$item)
    {
        $inventory_configuration = InventoryConfiguration::select('generate_internal_id')->firstOrFail();

        if($inventory_configuration->generate_internal_id && !$item->internal_id)
        {
            $item->internal_id = str_pad($item->id, 5, '0', STR_PAD_LEFT);
            $item->save();
        }
    }



    /**
     * @param ItemRequest|null $request
     * @param null $item
     * @throws Exception
     */
    private function createItemWarehousePrices(ItemRequest $request = null, Item $item = null)
    {
        if ($request !== null && $request->has('item_warehouse_prices') && $item !== null) {
            foreach ($request->item_warehouse_prices as $item_warehouse_price) {
                if ($item_warehouse_price['price'] && $item_warehouse_price['price'] != '') {
                    ItemWarehousePrice::updateOrCreate([
                        'item_id' => $item->id,
                        'warehouse_id' => $item_warehouse_price['warehouse_id'],
                    ], [
                        'price' => $item_warehouse_price['price'],
                    ]);
                } else {
                    if ($item_warehouse_price['id']) {
                        ItemWarehousePrice::findOrFail($item_warehouse_price['id'])->delete();
                    }
                }
            }
        }
    }


    /**
     * Eliminar item
     *
     * Usado en:
     * Modules\MobileApp\Http\Controllers\Api\ItemController
     *
     * @param  int $id
     * @return array
     *
     */
    public function destroy($id)
    {
        try {

            $item = Item::findOrFail($id);
            ItemLotsGroup::where('item_id', $id)->delete();
            ItemPosition::where('item_id', $id)->delete();
            $this->deleteRecordInitialKardex($item);
            $this->deleteRecordInitialWeightedCosts($item);
            $item->delete();

            return [
                'success' => true,
                'message' => 'Producto eliminado con éxito'
            ];

        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false,'message' => 'El producto esta siendo usado por otros registros, no puede eliminar'] : ['success' => false,'message' => 'Error inesperado, no se pudo eliminar el producto'];

        }


    }



    public function destroyItemUnitType($id)
    {
        $item_unit_type = ItemUnitType::findOrFail($id);
        $item_unit_type->delete();

        return [
            'success' => true,
            'message' => 'Registro eliminado con éxito'
        ];
    }


    public function import(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|numeric|min:1'
        ]);
        if ($request->hasFile('file')) {
            try {
                $import = new ItemsImport();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                return [
                    'success' => true,
                    'message' =>  __('app.actions.upload.success'),
                    'data' => $data
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' =>  $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    public function importRestaurant(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|numeric|min:1'
        ]);
        if ($request->hasFile('file')) {
            try {
                $import = new ItemsImportRestaurant();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                return [
                    'success' => true,
                    'message' =>  __('app.actions.upload.success'),
                    'data' => $data
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' =>  $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    public function catalog(Request $request)
    {
        $request->validate([
            'catalog_id' => 'required|numeric|min:1'
        ]);
        if ($request->hasFile('file')) {
            try {
                $old_digemid = CatDigemid::setInactiveMassive();
                $import = new CatalogImport();
                $import->import($request->file('file'), null, Excel::XLSX);
                $updated  = $import->getUpdated();
                return [
                    'success' => true,
                    'message' =>  __('app.actions.upload.success'),
                    'data' => count($updated),
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' =>  $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    public function upload(Request $request)
    {

        $validate_upload = UploadFileHelper::validateUploadFile($request, 'file', 'jpg,jpeg,png,gif,svg');

        if(!$validate_upload['success']){
            return $validate_upload;
        }

        if ($request->hasFile('file')) {
            $new_request = [
                'file' => $request->file('file'),
                'type' => $request->input('type'),
            ];

            return $this->upload_image($new_request);
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    function upload_image($request)
    {
        $file = $request['file'];
        $type = $request['type'];

        $temp = tempnam(sys_get_temp_dir(), $type);
        file_put_contents($temp, file_get_contents($file));

        $mime = mime_content_type($temp);
        $data = file_get_contents($temp);

        return [
            'success' => true,
            'data' => [
                'filename' => $file->getClientOriginalName(),
                'temp_path' => $temp,
                'temp_image' => 'data:' . $mime . ';base64,' . base64_encode($data)
            ]
        ];
    }

    private function deleteRecordInitialKardex($item){

        if($item->kardex->count() == 1){
            ($item->kardex[0]->type == null) ? $item->kardex[0]->delete() : false;
        }

    }


    /**
     *
     * @param  Item $item
     * @return void
     */
    private function deleteRecordInitialWeightedCosts($item)
    {
        if($item->weighted_average_costs()->count() == 1)
        {
            $item->weighted_average_cost()->delete();
        }
    }


    public function visibleStore(Request $request)
    {
        $item = Item::find($request->id);

        if(!$item->internal_id && $request->apply_store){
            return [
                'success' => false,
                'message' =>'Para habilitar la visibilidad, debe asignar un codigo interno al producto',
            ];
        }

        $visible = $request->apply_store == true ? 1 : 0 ;
        $item->apply_store = $visible;
        $item->save();

        return [
            'success' => true,
            'message' => ($visible > 0 )?'El Producto ya es visible en tienda virtual' : 'El Producto ya no es visible en tienda virtual',
            'id' => $request->id
        ];

    }

    public function duplicate(Request $request)
    {
        // return $request->id;
        $obj = Item::find($request->id);

        if($obj->lots_enabled){
            $obj->date_of_due = null;
            $obj->lot_code = null;
            $obj->stock = 0;
        }

        $new = $obj->setDescription($obj->getDescription().' (Duplicado)')->replicate();
        $new->save();

        return [
            'success' => true,
            'data' => [
                'id' => $new->id,
            ],
        ];

    }

    public function disable($id)
    {
        try {

            $item = Item::findOrFail($id);
            $item->active = 0;
            $item->save();

            return [
                'success' => true,
                'message' => 'Producto inhabilitado con éxito'
            ];

        } catch (Exception $e) {

            return  ['success' => false, 'message' => 'Error inesperado, no se pudo inhabilitar el producto'];

        }
    }

    public function images($item)
    {
        $records = ItemImage::where('item_id', $item)->get()->transform(function($row){
            return [
                'id' => $row->id,
                'item_id' => $row->item_id,
                'image' => $row->image,
                'name' => $row->image,
                'url'=> asset('storage'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'items'.DIRECTORY_SEPARATOR.$row->image)
            ];
        });
        return [
            'success' => true,
            'data' => $records
        ];
    }

    public function delete_images($id)
    {
        $record = ItemImage::findOrFail($id);
        $record->delete();

        return [
            'success' => true,
            'message' => 'Imagen eliminada con éxito'
        ];
    }


    public function enable($id)
    {
        try {

            $item = Item::findOrFail($id);
            $item->active = 1;
            $item->save();

            return [
                'success' => true,
                'message' => 'Producto habilitado con éxito'
            ];

        } catch (Exception $e) {

            return  ['success' => false, 'message' => 'Error inesperado, no se pudo habilitar el producto'];

        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $d_start = null;
        $d_end = null;
        $period = $request->period;

        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($request->month_start.'-01')->format('Y-m-d');
                $d_end = Carbon::parse($request->month_start.'-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($request->month_start.'-01')->format('Y-m-d');
                $d_end = Carbon::parse($request->month_end.'-01')->endOfMonth()->format('Y-m-d');
                break;
        }

        // $date = $request->month_start.'-01';
        // $start_date = Carbon::parse($date);
        // $end_date = Carbon::parse($date)->addMonth()->subDay();

        $items = Item::whereTypeUser()->whereNotIsSet();
        $extradata = [];
        $isPharmacy = false;
        if($request->has('isPharmacy') ){
            $isPharmacy = ($request->isPharmacy==='true')?true:false;
        }
        if($isPharmacy == true){
            $extradata[]='sanitary';
            $extradata[]='cod_digemid';
            $items->Pharmacy();
        }

        if($period !== 'all'){
            $items->whereBetween('items.created_at', [$d_start, $d_end]);
        }

        $records =  $items->get();
        return (new ItemExport())
            ->setExtraData($extradata)
            ->records($records)
            ->download('Reporte_Items_'.Carbon::now().'.xlsx');

    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportWp(Request $request) {
        $date = $request->month_start.'-01';
        $start_date = Carbon::parse($date);
        $end_date = Carbon::parse($date)->addMonth()->subDay();

        $records = Item::whereBetween('created_at', [$start_date, $end_date]);
        $extradata = [];
        $isPharmacy = $request->isPharmacy == 'true' ? true : false;
        if ($request->has('isPharmacy') && $isPharmacy == true) {
            $extradata[] = 'sanitary';
            $extradata[] = 'cod_digemid';
            $records->Pharmacy();
        }
        $records = $records->get();
        return (new ItemExportWp())
            ->setExtraData($extradata)
            ->records($records)
            ->download('Reporte_Items_'.Carbon::now().'.csv', Excel::CSV);

    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadExtraDataPdf(Request $request){
        $field ='';
        $records = $this->exportExtraItem($request,$field);


        $pdf = PDF::loadView('tenant.items.exports.items_extra_data',
            compact("records", "field"))
            ->setPaper('a4', 'landscape');

        $filename = 'Reporte_Items_Extra_Data_'.Carbon::now().'.xlsx';

        return $pdf->download($filename.'.pdf');
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|mixed|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadExtraDataItemsExcel(Request $request){
        $field ='';
        $items = $this->exportExtraItem($request,$field);
        $excel = new ItemExtraDataExport();
        $excel->setRecords($items)->setField($field);
        $filename = 'Reporte_Items_Extra_Data_'.Carbon::now().'.xlsx';

        return $excel->download($filename);
        return $excel->view();

    }

    /**
     * Obtiene lo smovimientos de inventario para la categoria correspondiente,
     * se implementa en pdf y excel por igual
     *
     * @param Request $request
     * @param         $field
     *
     * @return Item[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    public function exportExtraItem(Request $request, &$field){

        $stockByAttribute = ItemMovement::getQueryToStockWithOutItemId(auth()->user()->establishment_id)->distinct();
        $field = $request->fields ?? '';
        if($field == 'colors'){
            $stockByAttribute->where('item_movement_rel_extra.item_color_id','!=',0);
        }elseif($field == 'CatItemMoldProperty'){
            $stockByAttribute->where('item_movement_rel_extra.item_mold_properties_id','!=',0);
        }elseif($field == 'CatItemUnitBusiness'){
            $stockByAttribute->where('item_movement_rel_extra.item_unit_business_id','!=',0);
        }elseif($field == 'CatItemStatus'){
            $stockByAttribute->where('item_movement_rel_extra.item_status_id','!=',0);
        }
        elseif($field == 'CatItemPackageMeasurement'){
            $stockByAttribute->where('item_movement_rel_extra.item_package_measurements_id','!=',0);
        }
        elseif($field == 'CatItemProductFamily'){
            $stockByAttribute->where('item_movement_rel_extra.item_product_family_id','!=',0);
        }
        elseif($field == 'CatItemSize'){
            $stockByAttribute->where('item_movement_rel_extra.item_size_id','!=',0);
        }
        elseif($field == 'CatItemUnitsPerPackage'){
            $stockByAttribute->where('item_movement_rel_extra.item_units_per_package_id','!=',0);
        }
        elseif($field == 'CatItemMoldCavity'){
            $stockByAttribute->where('item_movement_rel_extra.item_mold_cavities_id','!=',0);
        }
        $itemsIds =$stockByAttribute->get()->pluck('item_id')->unique();
        $items = Item::wherein('id',$itemsIds)->get()->transform(function (Item $row){
           return $row->getCollectionData();
        });
        return $items;

    }
    public function exportBarCode(Request $request){

        ini_set("pcre.backtrack_limit", "50000000");

        $start = $request[0];
        $end = $request[1];

        $records = Item::whereBetween('id', [$start, $end]);
        $extradata = [];
        $isPharmacy = false;
        if($request->has('isPharmacy') ){
            $isPharmacy = ($request->isPharmacy==='true')?true:false;
        }
        if($isPharmacy == true){
            $extradata[]='sanitary';
            $extradata[]='cod_digemid';
            $records->Pharmacy();
        }
        $extra_data = $extradata;
        $records = $records->get();
        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [
                104.1,
                101.6
            ],
            'margin_top' => 2,
            'margin_right' => 2,
            'margin_bottom' => 0,
            'margin_left' => 2
        ]);
        $html = view('tenant.items.exports.items-barcode', compact('records','extra_data'))->render();

        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        $pdf->output('etiquetas_'.now()->format('Y_m_d').'.pdf', 'I');
    }

    /**
     * Genera los codigos de barra por archivo para los items que tengan internal_id o barcode
     * Se prioriza barcode, sino se genera internal_id
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Mpdf\MpdfException
     * @throws \Throwable
     */
    public function exportBarCodeFull(Request $request)
    {
        ini_set("pcre.backtrack_limit", "50000000");

        $start = $request[0];
        $end = $request[1];

        $records = Item::whereBetween('id', [$start, $end])
            ->where(function($q){
                $q->orwhere('barcode','!=','');
                $q->orwhere('internal_id','!=','');
            })
            // ->wherenotnull('barcode')
        ;
        $extradata = [];
        $establishment = \Auth::user()->establishment;
        $isPharmacy = false;
        if($request->has('isPharmacy') ){
            $isPharmacy = ($request->isPharmacy==='true')?true:false;
        }
        if($isPharmacy == true){
            $extradata[]='sanitary';
            $extradata[]='cod_digemid';
            $records->Pharmacy();
        }
        $extra_data = $extradata;
        $records = $records->get();
        $height = 23;

        $width = 48;
        $pdfj = new Fpdi();
        /** @var Item $item */
        foreach($records as $item){
            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    $width,
                    $height
                ],
                'margin_top' => 2,
                'margin_right' => 2,
                'margin_bottom' => 0,
                'margin_left' => 2
            ]);
            $html = view('tenant.items.exports.items-barcode-full', compact('item','extra_data','establishment'))->render();
            $pdf->AddPage();
            $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);
            PdfUnionController::addFpi($pdfj, $pdf);
        }

        return PdfUnionController::ResponseAsFile($pdfj,'bar_code_full');

    }
    /**
     * Exporta items al formato de DIGEMID
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportDigemid(Request $request)
    {
        ini_set('max_execution_time', 0);
        $company = Company::first();
        $company_cod_digemid = $company->cod_digemid;
        $records = CatDigemid::where('active',1);
        $max_prices = $records->max('max_prices');
            $records = $records->get();
        $export = new DigemidItemExport();
        $export->setRecords($records)->setCompanyCodDigemid($company_cod_digemid)->setMaxPrice($max_prices);

        return $export->download('Reporte_Items_Digemid_'.Carbon::now().'.xlsx');
    }

    public function printBarCode(Request $request)
    {
        ini_set("pcre.backtrack_limit", "50000000");
        $id = $request->id;

        $record = Item::find($id);
        $item_warehouse = ItemWarehouse::where([['item_id', $id], ['warehouse_id', auth()->user()
            ->establishment->warehouse->id]])->first();

        if(!$item_warehouse){
            return [
                'success' => false,
                'message' => "El producto seleccionado no esta disponible en su almacen!"
            ];
        }

        if($item_warehouse->stock < 1){
            return [
                'success' => false,
                'message' => "El producto seleccionado no tiene stock disponible en su almacen, no puede generar etiquetas!"
            ];
        }

        $stock = $item_warehouse->stock;

        $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    104.1,
                    24
                    ],
                'margin_top' => 2,
                'margin_right' => 2,
                'margin_bottom' => 0,
                'margin_left' => 2
            ]);
        $html = view('tenant.items.exports.items-barcode-id', compact('record', 'stock'))->render();

        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        $pdf->output('etiquetas_'.now()->format('Y_m_d').'.pdf', 'I');

    }

    public function printBarCodeX(Request $request)
    {
        ini_set("pcre.backtrack_limit", "50000000");
        $id = $request->input('id');
        $format = $request->input('format');

        $record = Item::find($id);
        $item_warehouse = ItemWarehouse::where([['item_id', $id], ['warehouse_id', auth()->user()
            ->establishment->warehouse->id]])->first();

        if(!$item_warehouse){
            return [
                'success' => false,
                'message' => "El producto seleccionado no esta disponible en su almacen!"
            ];
        }

        if($item_warehouse->stock < 1){
            return [
                'success' => false,
                'message' => "El producto seleccionado no tiene stock disponible en su almacen, no puede generar etiquetas!"
            ];
        }

        $stock = $item_warehouse->stock;

        $width = ($format == 1) ? 80 : 104.1;
        $height = ($format == 1) ? 26 : 24;

        $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    $width,
                    $height
                    ],
                'margin_top' => 2,
                'margin_right' => 2,
                'margin_bottom' => 0,
                'margin_left' => 2
            ]);
        $html = view('tenant.items.exports.items-barcode-x', compact('record', 'stock', 'format'))->render();

        // return $html;

        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        $pdf->output('etiquetas_1x'.$format.'_'.now()->format('Y_m_d').'.pdf', 'I');

    }

    public function itemLast()
    {
        $record = Item::latest()->first();
        return json_encode(['data' => $record->id]);
    }

    public function tablesImport()
    {
        $user = auth()->user();
        $warehouses = Warehouse::select('id', 'description');
        if ($user->type !== 'admin') {
            $warehouses = $warehouses->where('id', $user->establishment_id);
        }

        return response()->json([
            'warehouses' => $warehouses->get(),
        ], 200);
    }

    /**
     * Obtiene una lista de items del sistema
     *
     * @param \Illuminate\Http\Request $r
     *
     * @return \App\Http\Resources\Tenant\ItemCollection
     */
    public function getAllItems(Request $r){
        $records = $this->getRecords($r);
        return new ItemCollection($records->paginate(5000));

    }


    public function searchItemById($id)
    {
        // $items = SearchItemController::searchByIdToModal($id);
        $items = SearchItemController::getItemsToSupply(null, $id);
        return compact('items');
    }


    public function searchItems(Request $request)
    {

        $items = SearchItemController::getItemsToSupply($request);

        return compact('items');

    }

    public function item_tables()
    {
        // $items = $this->table('items');
        $items = SearchItemController::getItemsToDocuments();
        $categories = [];
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $system_isc_types = SystemIscType::whereActive()->get();
        $price_types = PriceType::whereActive()->get();
        $operation_types = OperationType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $attribute_types = AttributeType::whereActive()->orderByDescription()->get();
        $is_client = $this->getIsClient();

        $configuration= Configuration::first();

        /** Informacion adicional */
        $colors = collect([]);
        $CatItemSize=$colors;
        $CatItemStatus=$colors;
        $CatItemUnitBusiness = $colors;
        $CatItemMoldCavity = $colors;
        $CatItemPackageMeasurement =$colors;
        $CatItemUnitsPerPackage = $colors;
        $CatItemMoldProperty = $colors;
        $CatItemProductFamily= $colors;
        if($configuration->isShowExtraInfoToItem()){

            $colors = CatColorsItem::all();
            $CatItemSize= CatItemSize::all();
            $CatItemStatus= CatItemStatus::all();
            $CatItemUnitBusiness = CatItemUnitBusiness::all();
            $CatItemMoldCavity = CatItemMoldCavity::all();
            $CatItemPackageMeasurement = CatItemPackageMeasurement::all();
            $CatItemUnitsPerPackage = CatItemUnitsPerPackage::all();
            $CatItemMoldProperty = CatItemMoldProperty::all();
            $CatItemProductFamily= CatItemProductFamily::all();
        }


        /** Informacion adicional */

        return compact(
            'items',
            'categories',
            'affectation_igv_types',
            'system_isc_types',
            'price_types',
            'operation_types',
            'discount_types',
            'charge_types',
            'attribute_types',
            'is_client',
            'colors',
            'CatItemSize',
            'CatItemMoldCavity',
            'CatItemMoldProperty',
            'CatItemUnitBusiness',
            'CatItemStatus',
            'CatItemPackageMeasurement',
            'CatItemProductFamily',
            'CatItemUnitsPerPackage');
    }

    public function exportTxtBartender(Request $request)
    {
        ini_set("pcre.backtrack_limit", "50000000");

        $items = $request->items;
        $columns = $request->columns;

        $columnSelected = $this->getColumnsToBartender($columns);
        $columnsKey = array_keys($columnSelected);

        $itemCollect = collect($items)->map(function($item){

            if(sizeof($item['size']) > 0){
                $sizes = CatItemSize::whereIn('id',$item['size'])->get();
                $item['size'] = $sizes->pluck('name')->implode('-');
            }else{
                $item['size'] = " ";
            }

            if(sizeof($item['color']) > 0){
                $sizes = CatColorsItem::whereIn('id',$item['color'])->get();
                $item['color'] = $sizes->pluck('name')->implode('-');
            }else{
                $item['color'] = " ";
            }

            if(sizeof($item['status']) > 0){
                $sizes = CatItemStatus::whereIn('id',$item['status'])->get();
                $item['status'] = $sizes->pluck('name')->implode('-');
            }else{
                $item['status'] = " ";
            }

            $price_formated = $item['sale_unit_price'];
            $price_formated = $item['currency_type_symbol'].number_format($item['sale_unit_price'], 2);
            $item['sale_unit_price'] = $price_formated;

            return $item;
        });

        $dataItems = $itemCollect->flatMap(function ($item) use ($columnsKey)  {
            return array_map(function () use ($item,$columnsKey) {
                $item = array_intersect_key($item, array_flip($columnsKey));
                $orderedItem = array_replace(array_flip($columnsKey), $item);
                return $orderedItem ;
            }, range(1, $item['quantity_printer']));
        });

        $nombre_archivo = "TxtBartender".Carbon::now();

        $response = new StreamedResponse(function () use ($dataItems,$columnSelected) {
            $handle = fopen('php://output', 'w');

            $headers = array_values($columnSelected);

            fwrite($handle, implode(',', $headers) . "\n");

            foreach ($dataItems as $item) {
                $data = array_values($item);
                fwrite($handle, implode(',', $data) . "\n");
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$nombre_archivo.'"');

        return $response;

    }

    private function getColumnsToBartender($columns){

        $optionalColumns = [
            'internal_id' => 'Código Interno',
            'description' => 'Nombre',
            'barcode' => 'Código de barras',
            'category' => 'Categoría',
            'unit_type_id' => 'Unidad',
            'brand' => 'Marca',
            'sale_unit_price' => 'Precio',
            'size' => 'Talla',
            'color' => 'Colores',
            'status' => 'Status'
        ];

        $selected = array_intersect_key($optionalColumns, array_flip($columns));

        return $selected;
    }
 


}
