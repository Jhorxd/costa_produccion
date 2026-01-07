<?php

namespace App\Models\System;
use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Model;

class Competitor extends Model
{
    protected $fillable = [
        'description'
    ];
}