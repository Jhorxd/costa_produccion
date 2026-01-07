<?php

namespace App\Models\System;
use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Model;

class CompetenceItem extends Model
{
    protected $with = ['item_unit_types'];

    protected $fillable = [
        'id',
        'cod_digemid',
        'sanitary',
        'description'
    ];

    public function item_unit_types()
    {
        return $this->hasMany(CompetenceItemPrice::class);
    }
    
}