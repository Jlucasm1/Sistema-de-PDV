<?php

namespace App\Models;

use System\Model\Model;

class ClienteEndereco extends Model
{
    protected string $table = 'clientes_enderecos';
    protected bool $timestamps = true;

    public function __construct()
    {
        parent::__construct();
    }

    public function enderecos($idCliente)
    {
        if ((int) $idCliente <= 0) {
            return [];
        }

        return $this->query("SELECT * FROM clientes_enderecos WHERE id_cliente = {$idCliente}");
    }
}
