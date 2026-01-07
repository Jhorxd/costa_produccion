<?php
namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Models\System\Configuration;
use Illuminate\Support\Facades\DB;
use App\Models\System\Client;
use Hyn\Tenancy\Environment;
use App\Models\System\CompetenceItem;
use App\Models\System\CompetenceItemPrice;
use Modules\Finance\Helpers\UploadFileHelper;
use App\Http\Resources\System\ItemCollection;
use App\Http\Resources\System\ItemResource;
use App\Imports\ItemsImportCompetence;
use Maatwebsite\Excel\Excel;
use App\Models\System\Competitor;


class ItemConfigurationController extends Controller
{
    public function itemsIndex(){
        return view('system.configuration.items');
    }

    public function records(Request $request){
        $records = CompetenceItem::paginate(config('tenant.items_per_page'));
        return new ItemCollection($records);
        //return new ItemCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function store(Request $request) {

        $data = $request->all();

        $id = $request->input('id');

        $exist = CompetenceItem::where('cod_digemid', $data['cod_digemid'])
            ->when($id, function($query) use ($id) {
                return $query->where('id', '!=', $id);
            })
            ->first();

        if($exist){
            return [
                'success' => false,
                'message' => 'El código digemid ya se encuentra registrado'
            ];    
        }
        
        $item = CompetenceItem::firstOrNew(['id' => $id]);
        $item->fill($request->all());
        $item->save();


        foreach ($request->item_unit_types as $value) {

            $item_unit_type = CompetenceItemPrice::firstOrNew(['id' => $value['id']]);
            $item_unit_type->competence_item_id = $item->id;
            $item_unit_type->unit_type_id = $value['unit_type_id'];
            $item_unit_type->factor = $value['factor'];
            $item_unit_type->competence_label1 = $value['competence_label1'];
            $item_unit_type->competence_id1 = $value['competence_id1'];
            $item_unit_type->competence_unit_price1 = $value['competence_unit_price1'];
            $item_unit_type->competence_label2 = $value['competence_label2'];
            $item_unit_type->competence_id2 = $value['competence_id2'];
            $item_unit_type->competence_unit_price2 = $value['competence_unit_price2'];
            $item_unit_type->competence_label3 = $value['competence_label3'];
            $item_unit_type->competence_id3 = $value['competence_id3'];
            $item_unit_type->competence_unit_price3 = $value['competence_unit_price3'];
            $item_unit_type->competence_label4 = $value['competence_label4'];
            $item_unit_type->competence_id4 = $value['competence_id4'];
            $item_unit_type->competence_unit_price4 = $value['competence_unit_price4'];
            
            $item_unit_type->save();
        }
        $item->update();

        return [
            'success' => true,
            'message' => ($id)?'Producto editado con éxito':'Producto registrado con éxito',
            'id' => $item->id
        ];
    }

    public function record($id)
    {
        $record = new ItemResource(CompetenceItem::findOrFail($id));

        return $record;
    }

    public function importItems(Request $request)
    {
        //Log::debug("INgreso");
        if ($request->hasFile('file')) {
            try {
                $import = new ItemsImportCompetence();
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

    public function tables(){
        $competitors = Competitor::all();
        return compact('competitors');
    }

    public function destroyItemUnitType($id)
    {
        $item_unit_type = CompetenceItemPrice::findOrFail($id);
        $item_unit_type->delete();

        return [
            'success' => true,
            'message' => 'Registro eliminado con éxito'
        ];
    }
}
