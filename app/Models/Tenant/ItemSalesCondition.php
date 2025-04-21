<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Item;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class ItemSalesCondition extends ModelTenant
{
    use UsesTenantConnection;
    
    protected $fillable = [
        'id',
        'description'
    ];

    public function items(){
        return $this->hasMany(Item::class);
    }
}
