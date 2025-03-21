<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Item;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Modules\Inventory\Models\InventoryWarehouseLocation;
use Modules\Inventory\Models\WarehouseLocationPosition;

class ItemPosition extends ModelTenant
{
    use UsesTenantConnection;
    
    protected $fillable = [
        'id',
        'item_id',
        'stock',
        'position_id',
        'location_id',
        'warehouse_id'
    ];

    public function item(){
        return $this->belongsTo(Item::class);
    }

    public function position(){
        return $this->belongsTo(WarehouseLocationPosition::class);
    }

    public function location(){
        return $this->belongsTo(InventoryWarehouseLocation::class);
    }
    
    public function warehouse(){
        return $this->belongsTo(Warehouse::class);
    }
}