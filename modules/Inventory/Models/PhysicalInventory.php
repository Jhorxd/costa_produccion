<?php

namespace Modules\Inventory\Models;

use App\Models\Tenant\Establishment;
use App\Models\Tenant\ModelTenant;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class PhysicalInventory extends ModelTenant
{   
    use UsesTenantConnection;

    //
    protected $table = 'physical_inventories'; // Nombre de la tabla

    protected $fillable = [
        'date',
        'adjustment_type_id',
        'establishment_id',
        'warehouse_id',
        'comment',
        'series',
        'number',
        'confirmed'
    ];

    // Relación con el tipo de ajuste
    public function adjustmentType()
    {
        return $this->belongsTo(PhysicalInventoryAdjustmentType::class, 'adjustment_type_id');
    }

    // Relación con el establecimiento (sucursal)
    public function establishment()
    {
        return $this->belongsTo(Establishment::class, 'establishment_id');
    }

    // Relación con el almacén
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
    public function details()
    {
        return $this->hasMany(PhysicalInventoryDetail::class, 'physical_inventory_id');
    }
}
