<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;

class DocumentNumberSequence extends ModelTenant
{
    use UsesTenantConnection;
    
    protected $fillable = [
        'type',
        'serie',
        'next_number',
    ];
}