<?php

namespace App\CoreFacturalo\Requests\Inputs;

use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentNumberSequence;
use App\Models\Tenant\Series;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Document\Models\SeriesConfiguration;

class Functions
{
    public static function newNumber($soap_type_id, $document_type_id, $series, $number, $model)
    {
        if ($number === '#') {
            return DB::transaction(function () use ($soap_type_id, $document_type_id, $series, $model) {

                // Bloqueamos la secuencia para esta serie y tipo de documento
                $sequence = DocumentNumberSequence::where('type', $document_type_id)
                                ->where('serie', $series)
                                ->lockForUpdate()
                                ->first();

                if (!$sequence) {
                    // Si la secuencia no existe, buscamos el último número en los documentos existentes
                    $document = $model::select('number')
                                    ->where('soap_type_id', $soap_type_id)
                                    ->where('document_type_id', $document_type_id)
                                    ->where('series', $series)
                                    ->orderBy('number', 'desc')
                                    ->first();

                    $lastNumber = ($document) ? (int)$document->number : 0;

                    // Creamos la secuencia con el siguiente número
                    $sequence = DocumentNumberSequence::create([
                        'type' => $document_type_id,
                        'serie' => $series,
                        'next_number' => $lastNumber + 1,
                    ]);
                }

                // Obtenemos el número actual y actualizamos la secuencia
                $nextNumber = (int)$sequence->next_number;
                $sequence->next_number = $nextNumber + 1;
                $sequence->save();

                return $nextNumber;
            });
        }

        return $number;
    }

    public static function filename($company, $document_type_id, $series, $number)
    {
        return join('-', [$company->number, $document_type_id, $series, $number]);
    }

    public static function validateUniqueDocument($soap_type_id, $document_type_id, $series, $number, $model)
    {
        $document = $model::where('soap_type_id', $soap_type_id)
                        ->where('document_type_id', $document_type_id)
                        ->where('series', $series)
                        ->where('number', $number)
                        ->first();
        if($document) {
            throw new Exception("El documento: {$document_type_id} {$series}-{$number} ya se encuentra registrado.");
        }
    }

    public static function identifier($soap_type_id, $date_of_issue, $model)
    {
        $documents = $model::where('soap_type_id', $soap_type_id)
                        ->where('date_of_issue', $date_of_issue)
                        ->get();
        $numeration = count($documents) + 1;
        $path = explode('\\', $model);
        switch (array_pop($path)) {
            case 'Voided':
                $prefix = 'RA';
                break;
            default:
                $prefix = 'RC';
                break;
        }

        return join('-', [$prefix, Carbon::parse($date_of_issue)->format('Ymd'), $numeration]);
    }

    /**
     * @param      $inputs
     * @param      $key
     * @param null $default
     *
     * @return mixed|null
     */
    public static function valueKeyInArray($inputs, $key, $default = null)
    {
        return (isset($inputs[$key]) && null !== $inputs[$key]) ? $inputs[$key] : $default;
    }
}
