<?php
namespace Modules\Inventory\Models;
use App\Models\Tenant\ModelTenant;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class PhysicalInventoryAdjustmentType extends ModelTenant
{   
    use UsesTenantConnection;

    //
    protected $table = 'physical_inventory_adjustment_types'; // Nombre de la tabla
    protected $fillable = ['name']; // Campos permitidos para asignación masiva

}
