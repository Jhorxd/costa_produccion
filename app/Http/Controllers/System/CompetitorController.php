<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Models\System\Competitor;
use App\Http\Resources\System\CompetitorCollection;
use Modules\Item\Http\Resources\BrandResource;

class CompetitorController extends Controller
{
    public function columns()
    {
        return [
            'name' => 'Nombre',
        ];
    }

    public function records(Request $request)
    {
        /*$records = Competitor::where($request->column, 'like', "%{$request->value}%")
                            ->latest();*/
        

        //return new CompetitorCollection($records->paginate(config('tenant.items_per_page')));
        return new CompetitorCollection(Competitor::paginate(config('tenant.items_per_page')));
    }


    public function record($id)
    {
        $record = Competitor::findOrFail($id);

        return $record;
    }

    /**
     * Crea o edita una nueva marca.
     * El nombre de marca debe ser único, por lo tanto se valida cuando el nombre existe.
     *
     * @param BrandRequest $request
     *
     * @return array
     */
    //public function store(BrandRequest $request)//
    public function store(Request $request)
    {
        $id = (int)$request->input('id');
        $description = $request->input('description');
        $error = null;
        $competitor = null;
        if (!empty($description)) {
            $competitor = Competitor::where('description', $description);
            if (empty($id)) {
                $competitor = $competitor->first();
                if (!empty($competitor)) {
                    $error = 'El nombre de marca ya existe';
                }
            } else {
                $competitor = $competitor->where('id', '!=', $id)->first();
                if (!empty($competitor)) {
                    $error = 'El nombre de marca ya existe para otro registro';
                }
            }
        }
        $data = [
            'success' => false,
            'message' => $error,
            'data' => $competitor
        ];
        if (empty($error)) {
            $competitor = Competitor::firstOrNew(['id' => $id]);
            $competitor->fill($request->all());
            $competitor->save();
            $data = [
                'success' => true,
                'message' => ($id) ? 'Marca editada con éxito' : 'Marca registrada con éxito',
                'data' => $competitor
            ];
        }
        return $data;

    }

    public function destroy($id)
    {
        try {

            $competitor = Competitor::findOrFail($id);
            $competitor->delete();

            return [
                'success' => true,
                'message' => 'Marca eliminada con éxito'
            ];

        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false,'message' => "La Marca esta siendo usada por otros registros, no puede eliminar"] : ['success' => false,'message' => "Error inesperado, no se pudo eliminar la Marca"];

        }

    }




}
