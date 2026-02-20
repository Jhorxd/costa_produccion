<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Facturalo;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\VoidedCollection;
use App\Models\Tenant\Voided;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Tenant\VoidedRequest;
use Carbon\Carbon;
use App\Models\Tenant\Tokens;
use App\Models\Tenant\DocumentResponse;
use App\Models\Tenant\{
    Document,
    Configuration,
    VoidedDocument
};


class VoidedController extends Controller
{
    use StorageDocument;

    public function __construct()
    {
        $this->middleware('input.request:voided,web', ['only' => ['store']]);
    }

    public function index()
    {
        return view('tenant.voided.index');
    }

    public function columns()
    {
        return [
            'date_of_issue' => 'Fecha de emisión'
        ];
    }

    public function records(Request $request)
    {
        $voided = DB::connection('tenant')
                    ->table('voided')
                    ->where($request->column, 'like', "%{$request->value}%")
                    ->select(DB::raw("id, external_id, date_of_reference, date_of_issue, ticket, identifier, state_type_id, 'voided' AS 'type'"));

        $summaries = DB::connection('tenant')
                        ->table('summaries')
                        ->select(DB::raw("id, external_id, date_of_reference, date_of_issue, ticket, identifier, state_type_id, 'summaries' AS 'type'"))
                        ->where($request->column, 'like', "%{$request->value}%")
                        ->where('summary_status_type_id', '3');

        return new VoidedCollection($voided->union($summaries)->orderBy('date_of_issue', 'DESC')->paginate(config('tenant.items_per_page')));
    }

public function store(VoidedRequest $request)
{
    $validate = $this->validateVoided($request);
    if(!$validate['success']) return $validate;

    $fact = DB::connection('tenant')->transaction(function () use($request) {
        $facturalo = new Facturalo();
        $inputs = $request->all();

        \Log::info('Iniciando store voided', $inputs);
        $facturalo->save($inputs);
        \Log::info('Save OK');
        $facturalo->createXmlUnsigned();
        \Log::info('createXmlUnsigned OK');
        $service_pse_xml = $facturalo->servicePseSendXml();
        \Log::info('servicePseSendXml OK', $service_pse_xml);
        $facturalo->signXmlUnsigned($service_pse_xml['xml_signed']);
        \Log::info('signXmlUnsigned OK');
        $facturalo->senderXmlSignedSummary();
        \Log::info('senderXmlSignedSummary OK');

        return $facturalo;
    });

    $document = $fact->getDocument();
    \Log::info('getDocument OK', ['identifier' => $document->identifier]);

    // Llamar bajaSunat por cada documento a anular
    foreach ($request->documents as $doc) {
        $docId = $doc['document_id'];
        $motivo = $doc['description'] ?? 'ANULACION DE COMPROBANTE';

        \Log::info("Llamando bajaSunat para documento ID $docId");
        $resultado = $this->bajaSunat($docId, $motivo);
        $resultadoData = json_decode($resultado->getContent(), true);
        \Log::info("Resultado bajaSunat ID $docId", $resultadoData);

        if (!$resultadoData['success']) {
            \Log::warning("bajaSunat falló para documento ID $docId: " . $resultadoData['message']);

            return [
                'success' => false,
                'message' => $resultadoData['message'],
            ];
        }

        // ✅ Solo actualiza si SUNAT confirmó la anulación (no si está pendiente)
        if ($resultadoData['aceptada_por_sunat'] === true) {
            $docModel = Document::find($docId);
            if ($docModel) {
                $docModel->update(['state_type_id' => '11']);
            }
        }
    }

    return [
        'success' => true,
        'message' => "La anulación {$document->identifier} fue creado correctamente",
    ];
}

    /**
     * Validaciones previas
     *
     * @param VoidedRequest $request
     * @return array
     */
    public function validateVoided($request)
    {

        $configuration = Configuration::select('restrict_voided_send', 'shipping_time_days_voided')->firstOrFail();
        $voided_date_of_issue = Carbon::parse($request->date_of_issue);

        if($configuration->restrict_voided_send)
        {
            foreach ($request->documents as $row)
            {
                $document = Document::whereFilterWithOutRelations()->select('date_of_issue')->findOrFail($row['document_id']);

                $difference_days = $configuration->shipping_time_days_voided - $document->getDiffInDaysDateOfIssue($voided_date_of_issue);

                if($difference_days < 0)
                {
                    return [
                        'success' => false,
                        'message' => "El documento excede los {$configuration->shipping_time_days_voided} días válidos para ser anulado."
                    ];
                }
            }
        }

        return [
            'success' => true,
            'message' => null
        ];

    }


    public function status($voided_id)
    {
        $document = Voided::find($voided_id);
        $documents_voided = VoidedDocument::where('voided_id', $voided_id)->first();

        $fact = DB::connection('tenant')->transaction(function () use($document, $documents_voided) {
            $facturalo = new Facturalo();
            $facturalo->updateStockForAnnulmentSale($documents_voided->document_id);
            $facturalo->setDocument($document);
            $facturalo->setType('voided');
            $facturalo->statusSummary($document->ticket);
            return $facturalo;
        });

        $response = $fact->getResponse();

        // Llamar a bajaSunat con el document_id y el description como motivo
        $motivo = $response['description'] ?? 'ANULACION DE COMPROBANTE';
        $bajaSunatResponse = $this->bajaSunat($documents_voided->document_id, $motivo);

        return [
            'success' => true,
            'message' => $response['description'],
            'sunat_baja' => $bajaSunatResponse
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

    public function status_masive()
    {

        $records = Voided::where('state_type_id', '03')->get();

        $fact = DB::connection('tenant')->transaction(function () use($records) {

            foreach ($records as $document) {

                $facturalo = new Facturalo();
                $facturalo->setDocument($document);
                $facturalo->setType('voided');
                $facturalo->statusSummary($document->ticket);
            }
        });

        return [
            'success' => true,
            'message' => "Consulta masiva ejecutada.",
        ];
    }

    public function destroy($voided_id)
    {
        $document = Voided::find($voided_id);
        foreach ($document->documents as $doc)
        {
            $doc->document->update([
                'state_type_id' => '05'
            ]);
        }
        $document->delete();

        return [
            'success' => true,
            'message' => 'Anulación eliminada con éxito'
        ];
    }
}
