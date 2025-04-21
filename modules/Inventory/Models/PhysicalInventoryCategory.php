<?php

namespace Modules\Inventory\Models;


use App\Models\Tenant\ModelTenant;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class PhysicalInventoryCategory extends ModelTenant
{
    //
    use UsesTenantConnection;

    protected $table = 'physical_inventory_categories';

    protected $fillable = ['name'];

}
