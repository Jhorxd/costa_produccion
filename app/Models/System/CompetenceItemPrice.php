<?php

namespace App\Models\System;
use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Model;

class CompetenceItemPrice extends Model
{
    protected $fillable = [
        'id'
    ];

    public function competence_item() {
        return $this->belongsTo(CompetenceItem::class, 'competence_item_id');
    }
}