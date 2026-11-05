<?php

namespace App\Models;

use System\Model\Model;

class ClienteTipo extends Model
{
    protected string $table = 'clientes_tipos';
    protected bool $timestamps = true;

    public function __construct()
    {
        parent::__construct();
    }
}
