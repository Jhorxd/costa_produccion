<?php

namespace App\Models\Tenant; 

use App\Models\Tenant\ModelTenant;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class InventoryState extends ModelTenant
{
    use UsesTenantConnection;

    protected $fillable = [
        'id',
        'name',
        'description',
        'is_available_to_sale'
    ];
}
