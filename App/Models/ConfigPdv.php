<?php

namespace App\Models;

use System\Model\Model;

class ConfigPdv extends Model
{
    protected string $table = 'config_pdv';
    protected bool $timestamps = true;
    public const TIPO_PADRAO = 1;
    public const TIPO_DIFERENCIAL = 2;

    public function __construct()
    {
        parent::__construct();
    }

    public function configPdv($idEmpresa)
    {
        if (empty($idEmpresa)) {
            return (object) [];
        }

        return $this->queryGetOne(
            "SELECT config_pdv.id AS idConfigPdv, tipos_pdv.descricao, config_pdv.id_tipo_pdv FROM config_pdv
    		INNER JOIN tipos_pdv ON config_pdv.id_tipo_pdv = tipos_pdv.id
            WHERE config_pdv.id_empresa = {$idEmpresa}"
        );
    }

    public static function nomeTipo(int $idTipoPdv): string
    {
        return $idTipoPdv === self::TIPO_DIFERENCIAL ? 'PDV Diferencial' : 'PDV Padrão';
    }

    public static function descricaoTipo(int $idTipoPdv): string
    {
        if ($idTipoPdv === self::TIPO_DIFERENCIAL) {
            return 'Catálogo visual com carrinho, quantidade por item e fechamento completo da venda.';
        }

        return 'Lançamento rápido de vendas por valor, pagamento e vendedor responsável.';
    }

    public static function iconeTipo(int $idTipoPdv): string
    {
        return $idTipoPdv === self::TIPO_DIFERENCIAL ? 'fas fa-layer-group' : 'fas fa-bolt';
    }
}
