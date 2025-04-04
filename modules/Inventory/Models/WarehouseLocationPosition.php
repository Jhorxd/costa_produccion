<?php

namespace Modules\Inventory\Models;

use App\Models\Tenant\Item;
use App\Models\Tenant\ItemPosition;
use App\Models\Tenant\ModelTenant;
use Modules\Inventory\Models\InventoryKardex;
use Carbon\Carbon;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Builder;
use Modules\Item\Models\ItemLot;


/**
 * Class Inventory
 *
 * @property int $id
 * @property string|null $type
 * @property string $description
 * @property string|null $detail
 * @property int $item_id
 * @property int $warehouse_id
 * @property int|null $warehouse_destination_id
 * @property string|null $inventory_transaction_id
 * @property float $quantity
 * @property string|null $lot_code
 * @property int|null $inventories_transfer_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property \Modules\Inventory\Models\InventoryTransfer|null $inventories_transfer
 * @property InventoryTransaction|null $inventory_transaction
 * @property Item $item
 * @property Warehouse $warehouse
 * @property-read \Illuminate\Database\Eloquent\Collection|InventoryKardex[] $inventory_kardex
 * @package Modules\Inventory\Models
 * @mixin ModelTenant
 * @property-read int|null $inventory_kardex_count
 * @property-read \Illuminate\Database\Eloquent\Collection|ItemLot[] $lots
 * @property-read int|null $lots_count
 * @property-read \Modules\Inventory\Models\InventoryTransaction $transaction
 * @property-read \Modules\Inventory\Models\Warehouse $warehouse_destination
 * @mixin \Eloquent
 * @method static Builder|Inventory newModelQuery()
 * @method static Builder|Inventory newQuery()
 * @method static Builder|Inventory query()
 */
class WarehouseLocationPosition extends ModelTenant
{
    use UsesTenantConnection;

    protected $table = 'warehouse_location_positions';

    protected $with = [
        //
    ];

    /* protected $casts = [
        'store_id' => 'int',
        'type_id' => 'int',
        'rows' => 'int',
        'columns' => 'float',
        'maximum_stock' => 'int',
    ]; */
    protected $fillable = [
        'row',
        'quantity_used',
        'column',
        'status',
        'location_id',
    ];

    public function lots()
    {
        return $this->hasMany(ItemPosition::class, 'position_id')
            ->whereNotNull('lots_group_id')->select('id','stock','lots_group_id','position_id');
    }

    public function itemPositions()
    {
        return $this->hasMany(ItemPosition::class, 'position_id');
    }

    public function updateQuantityUsed() //$itemPosition->position->updateQuantityUsed();
    {
        $uniqueItemsCount = $this->hasMany(ItemPosition::class, 'position_id')
            ->distinct('item_id')
            ->count('item_id');
        
        $this->quantity_used = $uniqueItemsCount;
        $this->save();
    }
}
