<?php

namespace App\Config;

class ConfigPerfil
{
    # Estes valores devem ser os mesmos do banco de dados
    public static function superAdmin()
    {
        return 1;
    }

    public static function adiministrador()
    {
        return 2;
    }

    public static function gerente()
    {
        return 5;
    }

    public static function vendedor()
    {
        return 4;
    }

    public static function isSuperAdmin(int $idPerfil): bool
    {
        return $idPerfil === self::superAdmin();
    }

    public static function isAdministrador(int $idPerfil): bool
    {
        return $idPerfil === self::adiministrador();
    }

    public static function isGerente(int $idPerfil): bool
    {
        return $idPerfil === self::gerente();
    }

    public static function isVendedor(int $idPerfil): bool
    {
        return $idPerfil === self::vendedor();
    }

    public static function perfisComAcessoGerencial(): array
    {
        return [
            self::superAdmin(),
            self::gerente(),
        ];
    }

    public static function perfisAdministrativos(): array
    {
        return [
            self::superAdmin(),
        ];
    }

    public static function perfisDisponiveisNoSistema(): array
    {
        return [
            self::superAdmin(),
            self::gerente(),
            self::vendedor(),
        ];
    }
}
