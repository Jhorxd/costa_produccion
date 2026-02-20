<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Tokens extends Model
{
    /**
     * La conexión que usa el modelo (tenant activo).
     *
     * @var string
     */
    protected $connection = 'tenant';

    /**
     * La tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'tokens';

    /**
     * Los campos que se pueden asignar masivamente.
     *
     * @var array
     */
    protected $fillable = ['ruta', 'token'];
}
