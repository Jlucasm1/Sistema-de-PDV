<?php

namespace App\Models;

use System\Model\Model;

class TipoPdv extends Model
{
    protected string $table = 'tipos_pdv';
    protected bool $timestamps = true;

    public function __construct()
    {
        parent::__construct();
    }

    public function tiposPdv()
    {
        return $this->all();
    }
}
