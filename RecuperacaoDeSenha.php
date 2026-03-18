<?php

namespace App\Models;

use System\Model\Model;

class RecuperacaoDeSenha extends Model
{
    protected string $table = 'recuperacao_de_senha';
    protected bool $timestamps = true;

    public function __construct()
    {
        parent::__construct();
    }
}
