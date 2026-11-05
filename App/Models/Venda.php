<?php

namespace App\Models;

use System\Model\Model;

class Venda extends Model
{
    protected string $table      = 'vendas';
    protected bool   $timestamps = true;

    public function __construct()
    {
        parent::__construct();
    }

    public function seJaExisteCodigoDeVenda(string $codigo, int $idEmpresa): bool
    {
        $result = $this->prepare(
            "SELECT id FROM vendas WHERE codigo_venda = ? AND id_empresa = ? LIMIT 1",
            [$codigo, $idEmpresa]
        );
        return count($result) > 0;
    }
}
