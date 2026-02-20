<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Tenant\DocumentResponse
 *
 * @property int $id
 * @property int $document_id
 * @property string|null $tipo_de_comprobante
 * @property string|null $serie
 * @property string|null $numero
 * @property string|null $enlace
 * @property int|null $aceptada_por_sunat
 * @property string|null $sunat_description
 * @property string|null $sunat_note
 * @property string|null $sunat_responsecode
 * @property string|null $sunat_soap_error
 * @property string|null $pdf_zip_base64
 * @property string|null $xml_zip_base64
 * @property string|null $cdr_zip_base64
 * @property string|null $enlace_del_pdf
 * @property string|null $enlace_del_xml
 * @property string|null $enlace_del_cdr
 * @property string|null $codigo_hash
 * @property string|null $cadena_para_codigo_qr
 * @property string|null $respuesta_json
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * 
 * @property Document $document
 */
class DocumentResponse extends ModelTenant
{
    protected $table = 'document_responses';

    protected $fillable = [
        'document_id',
        'tipo_de_comprobante',
        'serie',
        'numero',
        'enlace',
        'aceptada_por_sunat',
        'sunat_description',
        'sunat_note',
        'sunat_responsecode',
        'sunat_soap_error',
        'pdf_zip_base64',
        'xml_zip_base64',
        'cdr_zip_base64',
        'enlace_del_pdf',
        'enlace_del_xml',
        'enlace_del_cdr',
        'codigo_hash',
        'cadena_para_codigo_qr',
        'respuesta_json'
    ];

    /**
     * Relación inversa: cada respuesta pertenece a un documento
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
