<?php

namespace Modules\Inventory\Models;

use App\Models\Tenant\ModelTenant;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use App\Models\Tenant\Item;

class PhysicalInventoryDetail extends ModelTenant
{   

    use UsesTenantConnection;
    //
    protected $table = 'inventory_physical_details';

    protected $fillable = [
        'physical_inventory_id',
        'item_id',
        'counted_quantity',
        'system_quantity',
        'difference',
        'category_id',
        'cost'
    ];

    public function physicalInventory()
    {
        return $this->belongsTo(PhysicalInventory::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function category()
    {
        return $this->belongsTo(PhysicalInventoryCategory::class, 'category_id');
    }
}
