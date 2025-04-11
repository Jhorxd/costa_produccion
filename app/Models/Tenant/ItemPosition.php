<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Item;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Modules\Inventory\Models\InventoryWarehouseLocation;
use Modules\Inventory\Models\WarehouseLocationPosition;
use Modules\Item\Models\ItemLotsGroup;

class ItemPosition extends ModelTenant
{
    use UsesTenantConnection;
    
    protected $fillable = [
        'id',
        'item_id',
        'lots_group_id',
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

    public function lots_group()
    {
        return $this->belongsTo(ItemLotsGroup::class, 'lots_group_id');
    }

    public function get_code_lots_group()
    {
        return $this->belongsTo(ItemLotsGroup::class, 'lots_group_id')->select('id','code');
    }
}