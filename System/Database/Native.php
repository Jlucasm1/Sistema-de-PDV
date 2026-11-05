<?php

namespace System\Database;

use PDO;
use PDOException;

/**
 * PDV MASTER - Conexão nativa com o banco de dados via PDO
 */
class Native
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $host     = $_ENV['HOST_NAME']     ?? getenv('HOST_NAME')     ?? 'db';
        $dbname   = $_ENV['HOST_DBNAME']   ?? getenv('HOST_DBNAME')   ?? 'pdvmaster';
        $username = $_ENV['HOST_USERNAME'] ?? getenv('HOST_USERNAME') ?? 'pdvmaster';
        $password = $_ENV['HOST_PASSWORD'] ?? getenv('HOST_PASSWORD') ?? '';

        try {
            self::$pdo = new PDO(
                "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::ATTR_PERSISTENT         => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]
            );
        } catch (PDOException $e) {
            self::handleConnectionError($e);
        }

        return self::$pdo;
    }

    /**
     * Trata erros de conexão de forma clara
     */
    private static function handleConnectionError(PDOException $e): void
    {
        $code = $e->getCode();
        $messages = [
            2002 => '<b>Erro de Conexão:</b> Host do banco não encontrado. Verifique se o serviço DB está rodando.',
            1049 => '<b>Erro de Conexão:</b> Banco de dados não existe. Verifique HOST_DBNAME no .env.',
            1044 => '<b>Erro de Conexão:</b> Usuário sem permissão no banco. Verifique HOST_USERNAME.',
            1045 => '<b>Erro de Conexão:</b> Senha incorreta. Verifique HOST_PASSWORD no .env.',
        ];

        $msg = $messages[$code] ?? "<b>Erro de Conexão ({$code}):</b> " . $e->getMessage();

        echo "<!DOCTYPE html><html><body style='font-family:sans-serif;padding:30px;background:#f8d7da;'>
            <div style='border:1px solid #f5c6cb;padding:20px;border-radius:8px;background:#fff;max-width:600px;margin:auto;'>
                <h2 style='color:#721c24;'>⚠️ PDV MASTER - Erro de Banco de Dados</h2>
                <p>{$msg}</p>
                <p><small>Verifique o arquivo <code>.env</code> e se o container Docker está rodando.</small></p>
            </div>
        </body></html>";
        exit;
    }

    /**
     * Reseta a conexão (útil para testes)
     */
    public static function reset(): void
    {
        self::$pdo = null;
    }
}
