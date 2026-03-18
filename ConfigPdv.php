<?php

namespace App\Models;

use System\Model\Model;

class ConfigPdv extends Model
{
    protected string $table = 'config_pdv';
    protected bool $timestamps = true;

    public function __construct()
    {
        parent::__construct();
    }

    public function configPdv($idEmpresa)
    {
        return $this->queryGetOne(
            "SELECT config_pdv.id AS idConfigPdv, tipos_pdv.descricao, config_pdv.id_tipo_pdv FROM config_pdv
    		INNER JOIN tipos_pdv ON config_pdv.id_tipo_pdv = tipos_pdv.id
            WHERE config_pdv.id_empresa = {$idEmpresa}"
        );
    }
}
