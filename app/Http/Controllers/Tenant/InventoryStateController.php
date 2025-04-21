<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\InventoryState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class InventoryStateController extends Controller
{
    public function getData(){
        try {
            $data = InventoryState::all();
            if(!$data->isEmpty()){
                return Response::json([
                    'success' => true,
                    'message' => 'Estados encontrados',
                    'data' => $data
                ],200);
            }else{
                return Response::json([
                    'success' => false,
                    'message' => 'No se encontraron estados'
                ],404);
            }
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Error al obtener los estados'
            ],500);
        }
    }
}
