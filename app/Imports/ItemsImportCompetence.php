<?php

namespace App\Imports;

use App\Models\Tenant\Item;
use App\Models\Tenant\PharmaceuticalItemUnitType;
use App\Models\Tenant\Warehouse;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\Item\Models\Category;
use Modules\Item\Models\Brand;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Modules\Inventory\Models\InventoryTransaction;
use Modules\Inventory\Models\Inventory;
use Exception;
use Illuminate\Support\Facades\Log;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Finance\Helpers\UploadFileHelper;
use App\Models\System\CompetenceItem;
use App\Models\System\CompetenceItemPrice;


class ItemsImportCompetence implements ToCollection
{
    use Importable;

    protected $data;

    public function collection(Collection $rows)
    {
        $filteredRows = $rows->reject(function ($row) {
            return $row->every(function ($value) {
                return is_null($value) || $value === '';
            });
        });
        $total = count($filteredRows);
        
        $registered = 0;
        unset($filteredRows[0]);
        foreach ($filteredRows as $row) {

            $description = $row[0] ?? null;
            $codigoDigemid = $row[1] ?? null;
            $sanitaryRegister = $row[2] ?? null;
            $unidad = $row[3] ?? null;
            $factor = $row[4] ?? null;
            $competenceName1 = $row[5] ?? null;
            $competencePrice1 = $row[6] ?? 0;
            $competenceName2 = $row[7] ?? null;
            $competencePrice2 = $row[8] ?? 0;
            $competenceName3 = $row[9] ?? null;
            $competencePrice3 = $row[10] ?? 0;
            $competenceName4 = $row[11] ?? null;
            $competencePrice4 = $row[12] ?? 0;
            

            $item = CompetenceItem::where('cod_digemid', $codigoDigemid)->first();
            Log::debug("Step1");

            if($item){
                Log::debug("Step2");
                $competenceItemPrice = CompetenceItemPrice::where('unit_type_id', $unidad)->where('factor', $factor)->where('competence_item_id', $item->id)->first();

                if($competenceItemPrice){
                    Log::debug("Step3");
                    $competenceItemPrice->competence_unit_price1 = $competencePrice1;
                    $competenceItemPrice->competence_label2 = $competenceName1;
                    $competenceItemPrice->competence_unit_price2 = $competencePrice2;
                    $competenceItemPrice->competence_label3 = $competenceName2;
                    $competenceItemPrice->competence_unit_price3 = $competencePrice3;
                    $competenceItemPrice->competence_label3 = $competenceName3;
                    $competenceItemPrice->competence_unit_price4 = $competencePrice4;
                    $competenceItemPrice->competence_label4 = $competenceName4;
                    $competenceItemPrice->update();

                }else{
                    Log::debug("Step4");
                    $competenceItemPrice = new CompetenceItemPrice();
                    $competenceItemPrice->competence_item_id = $item->id;
                    $competenceItemPrice->unit_type_id = $unidad;
                    $competenceItemPrice->factor = $factor;
                    $competenceItemPrice->competence_unit_price1 = $competencePrice1;
                    $competenceItemPrice->competence_label2 = $competenceName1;
                    $competenceItemPrice->competence_unit_price2 = $competencePrice2;
                    $competenceItemPrice->competence_label3 = $competenceName2;
                    $competenceItemPrice->competence_unit_price3 = $competencePrice3;
                    $competenceItemPrice->competence_label3 = $competenceName3;
                    $competenceItemPrice->competence_unit_price4 = $competencePrice4;
                    $competenceItemPrice->competence_label4 = $competenceName4;
                    $competenceItemPrice->save();
                }
            }
            else{
                $competenceItem = new CompetenceItem();
                $competenceItem->description = $description;
                $competenceItem->cod_digemid = $codigoDigemid;
                $competenceItem->sanitary = $sanitaryRegister;
                $competenceItem->save();

                $competenceItemPrice = new CompetenceItemPrice();
                $competenceItemPrice->competence_item_id = $competenceItem->id;
                $competenceItemPrice->unit_type_id = $unidad;
                $competenceItemPrice->factor = $factor;
                $competenceItemPrice->competence_unit_price1 = $competencePrice1;
                $competenceItemPrice->competence_label2 = $competenceName1;
                $competenceItemPrice->competence_unit_price2 = $competencePrice2;
                $competenceItemPrice->competence_label3 = $competenceName2;
                $competenceItemPrice->competence_unit_price3 = $competencePrice3;
                $competenceItemPrice->competence_label3 = $competenceName3;
                $competenceItemPrice->competence_unit_price4 = $competencePrice4;
                $competenceItemPrice->competence_label4 = $competenceName4;
                $competenceItemPrice->update();
            }
        }
                
        //$this->data = compact('total', 'registered', 'warehouse_id_de');

    }

    public function getData()
    {
        return $this->data;
    }
}
