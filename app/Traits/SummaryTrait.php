<?php

namespace App\Traits;

use App\CoreFacturalo\Facturalo;
use App\Models\Tenant\Summary;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant\Document;
use App\Models\Tenant\Tokens;
use App\Models\Tenant\DocumentResponse;
use DB;

trait SummaryTrait
{
public function save($request)
{
    $fact = DB::connection('tenant')->transaction(function () use ($request) {

        $facturalo = new Facturalo();
        $facturalo->save($request->all());

        return $facturalo;
    });

    $document = $fact->getDocument();

    // --- Enviar baja a SUNAT vía NubeFact ---
    $motivo = $request->input('motivo', 'ANULACION DE COMPROBANTE');

    foreach ($document->documents as $doc) {
        $docId = $doc->document_id;
        $resultado = $this->bajaSunat($docId, $motivo);

        $resultadoData = json_decode($resultado->getContent(), true);

        if (!$resultadoData['success']) {
            \Log::warning("bajaSunat falló para documento ID $docId: " . $resultadoData['message']);

            return [
                'success' => false,
                'message' => $resultadoData['message'],
            ];
        }

        // ✅ Solo actualiza si SUNAT confirmó la anulación (no si está pendiente)
        if ($resultadoData['aceptada_por_sunat'] === true) {
            $doc->document->update([
                'state_type_id' => '11'
            ]);
        }
    }

    return [
        'success' => true,
        'message' => "La anulación {$document->identifier} fue creada y enviada a SUNAT correctamente",
    ];
}

     public function bajaSunat($id, $motivo = 'ANULACION DE COMPROBANTE')
    {
        // si llega como Request (desde ruta HTTP), extraer el motivo
        if ($motivo instanceof \Illuminate\Http\Request) {
            $motivo = $motivo->input('motivo', 'ANULACION DE COMPROBANTE');
        }

        // --- Obtener documento ---
        $document = Document::with(['customer'])->findOrFail($id);

        // Obtener tipo de documento
        $tipo_documento_id = is_object($document->document_type) ? $document->document_type->id : $document->document_type;

        // Mapear tipo de comprobante según NubeFact
        switch ($tipo_documento_id) {
            case '01': // Factura
                $tipo_comprobante = 1;
                break;
            case '03': // Boleta
                $tipo_comprobante = 2;
                break;
            case '07': // Nota de crédito
                $tipo_comprobante = 3;
                break;
            case '08': // Nota de débito
                $tipo_comprobante = 4;
                break;
            default:
                $tipo_comprobante = 0;
                break;
        }

        // Log para depuración
        \Log::info("bajaSunat: Documento ID $id, tipo original: ".$tipo_documento_id.", tipo mapeado: $tipo_comprobante");

        // --- Construir JSON de anulación ---
        $data = [
            "operacion"          => "generar_anulacion",
            "tipo_de_comprobante" => $tipo_comprobante,
            "serie"              => $document->series,
            "numero"             => ltrim($document->number, "0"),
            "motivo"             => $motivo,
            "codigo_unico"       => ""
        ];

        $data_json = json_encode($data);

        // --- Obtener token del tenant activo ---
        $tokenRecord = Tokens::first();

        if (!$tokenRecord) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró configuración de token/ruta para NubeFact.',
                'document_id' => $id
            ]);
        }

        $ruta  = $tokenRecord->ruta;
        $token = $tokenRecord->token;

        // --- CURL ---
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ruta);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Token token="'.$token.'"',
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $respuesta = curl_exec($ch);
        curl_close($ch);

        $leer_respuesta = json_decode($respuesta, true);

        // --- Si hay errores en la respuesta ---
        if (isset($leer_respuesta['errors'])) {
            return response()->json([
                'success'     => false,
                'message'     => is_array($leer_respuesta['errors'])
                    ? implode(", ", $leer_respuesta['errors'])
                    : $leer_respuesta['errors'],
                'document_id' => $id
            ]);
        }

        // --- Guardar o actualizar la respuesta en DocumentResponse ---
        DocumentResponse::updateOrCreate(
            ['document_id' => $document->id],
            [
                'tipo_de_comprobante' => $leer_respuesta['tipo_de_comprobante'] ?? null,
                'serie'               => $leer_respuesta['serie'] ?? null,
                'numero'              => $leer_respuesta['numero'] ?? null,
                'enlace'              => $leer_respuesta['enlace'] ?? null,
                'aceptada_por_sunat'  => isset($leer_respuesta['aceptada_por_sunat']) ? (int)$leer_respuesta['aceptada_por_sunat'] : null,
                'sunat_description'   => $leer_respuesta['sunat_description'] ?? null,
                'sunat_note'          => $leer_respuesta['sunat_note'] ?? null,
                'sunat_responsecode'  => $leer_respuesta['sunat_responsecode'] ?? null,
                'sunat_soap_error'    => $leer_respuesta['sunat_soap_error'] ?? null,
                'pdf_zip_base64'      => $leer_respuesta['pdf_zip_base64'] ?? null,
                'xml_zip_base64'      => $leer_respuesta['xml_zip_base64'] ?? null,
                'cdr_zip_base64'      => $leer_respuesta['cdr_zip_base64'] ?? null,
                'enlace_del_pdf'      => $leer_respuesta['enlace_del_pdf'] ?? null,
                'enlace_del_xml'      => $leer_respuesta['enlace_del_xml'] ?? null,
                'enlace_del_cdr'      => $leer_respuesta['enlace_del_cdr'] ?? null,
                'codigo_hash'         => $leer_respuesta['codigo_hash'] ?? null,
                'cadena_para_codigo_qr' => $leer_respuesta['cadena_para_codigo_qr'] ?? null,
                'respuesta_json'      => $respuesta
            ]
        );

        // --- Actualizar estado SUNAT del documento ---
        if (isset($leer_respuesta['aceptada_por_sunat']) && (int)$leer_respuesta['aceptada_por_sunat'] === 1) {
            $document->update(['state_sunat' => 'ANULADO']);

            return response()->json([
                'success'            => true,
                'message'            => $leer_respuesta['sunat_description'] ?? 'Comprobante anulado correctamente',
                'document_id'        => $id,
                'enlace'             => $leer_respuesta['enlace'] ?? '',
                'aceptada_por_sunat' => true,
                'codigo_hash'        => $leer_respuesta['codigo_hash'] ?? ''
            ]);

        } else {
            // NubeFact a veces responde sin error pero aún está en proceso
            $document->update(['state_sunat' => 'BAJA_PENDIENTE']);

            return response()->json([
                'success'            => true,
                'message'            => 'La baja fue enviada pero está pendiente de confirmación por SUNAT. Consulte en unos minutos.',
                'document_id'        => $id,
                'aceptada_por_sunat' => false
            ]);
        }
    }


    public function query($id) {
        $document = Summary::find($id);

        $fact = DB::connection('tenant')->transaction(function () use($document) {
            $facturalo = new Facturalo();
            $facturalo->setDocument($document);
            $facturalo->setType('summary');
            $hasPseSend = $facturalo->hasPseSend();
            if($hasPseSend){
                $facturalo->pseQuerySummary();
            } else {
                $facturalo->statusSummary($document->ticket);
            }
            return $facturalo;
        });

        $response = $fact->getResponse();

        return [
            'success' => ($response['status_code'] === 99) ? false : true,
            'message' => $response['description'],
        ];
    }


    public function getCustomErrorMessage($message, $exception) {

        $this->setCustomErrorLog($exception);

        return [
            'success' => false,
            'message' => $message
        ];

    }

    public function setCustomErrorLog($exception)
    {
        Log::error("Code: {$exception->getCode()} - Line: {$exception->getLine()} - Message: {$exception->getMessage()} - File: {$exception->getFile()}");
    }

    public function updateUnknownErrorStatus($id, $exception) {

        Summary::findOrFail($id)->update([
            'unknown_error_status_response' => true,
            'error_manually_regularized' => [
                'message' => $exception->getMessage(),
            ],
        ]);

    }


}
