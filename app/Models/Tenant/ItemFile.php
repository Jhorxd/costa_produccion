<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Item;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class ItemFile extends ModelTenant
{
    use UsesTenantConnection;
    
    protected $fillable = [
        'id',
        'item_id',
        'filename',
        'route',
        'user_created_at'
    ];

    public function item(){
        return $this->belongsTo(Item::class);
    }
}