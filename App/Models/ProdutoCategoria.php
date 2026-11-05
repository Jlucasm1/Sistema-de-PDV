<?php

namespace App\Models;

use System\Model\Model;

class ProdutoCategoria extends Model
{
    protected string $table = 'produtos_categorias';
    protected bool $timestamps = true;

    public function __construct()
    {
        parent::__construct();
    }
}
