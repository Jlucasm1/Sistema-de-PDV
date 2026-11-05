<?php

namespace App\Models;

use System\Model\Model;

class Caixa extends Model
{
    protected string $table = 'caixas';
    protected bool $timestamps = true;
    protected static bool $schemaReady = false;

    public function __construct()
    {
        parent::__construct();
        $this->ensureSchema();
    }

    public function ensureSchema(): void
    {
        if (self::$schemaReady) {
            return;
        }

        $this->execute(
            "CREATE TABLE IF NOT EXISTS caixas (
                id INT NOT NULL AUTO_INCREMENT,
                id_empresa INT NOT NULL,
                nome VARCHAR(60) NOT NULL,
                descricao VARCHAR(120) DEFAULT NULL,
                status INT NOT NULL DEFAULT 1,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY FK_caixas_empresas (id_empresa),
                CONSTRAINT FK_caixas_empresas FOREIGN KEY (id_empresa) REFERENCES empresas (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        $this->execute(
            "CREATE TABLE IF NOT EXISTS caixa_sessoes (
                id INT NOT NULL AUTO_INCREMENT,
                id_empresa INT NOT NULL,
                id_caixa INT NOT NULL,
                id_usuario INT NOT NULL,
                valor_abertura DOUBLE DEFAULT 0,
                valor_fechamento DOUBLE DEFAULT NULL,
                observacao_abertura VARCHAR(255) DEFAULT NULL,
                observacao_fechamento VARCHAR(255) DEFAULT NULL,
                aberto_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                ultima_atividade_em TIMESTAMP NULL DEFAULT NULL,
                fechado_em TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                KEY FK_caixa_sessoes_empresas (id_empresa),
                KEY FK_caixa_sessoes_caixas (id_caixa),
                KEY FK_caixa_sessoes_usuarios (id_usuario),
                CONSTRAINT FK_caixa_sessoes_empresas FOREIGN KEY (id_empresa) REFERENCES empresas (id),
                CONSTRAINT FK_caixa_sessoes_caixas FOREIGN KEY (id_caixa) REFERENCES caixas (id),
                CONSTRAINT FK_caixa_sessoes_usuarios FOREIGN KEY (id_usuario) REFERENCES usuarios (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        self::$schemaReady = true;
    }

    public function ativos(int $idEmpresa): array
    {
        return $this->query(
            "SELECT *
            FROM caixas
            WHERE id_empresa = {$idEmpresa}
              AND status = 1
              AND deleted_at IS NULL
            ORDER BY nome"
        );
    }

    public function livres(int $idEmpresa): array
    {
        if ($idEmpresa <= 0) {
            return [];
        }

        return $this->query(
            "SELECT c.*
            FROM caixas c
            LEFT JOIN caixa_sessoes cs
                ON cs.id_caixa = c.id
               AND cs.id_empresa = c.id_empresa
               AND cs.fechado_em IS NULL
               AND cs.deleted_at IS NULL
            WHERE c.id_empresa = {$idEmpresa}
              AND c.status = 1
              AND c.deleted_at IS NULL
              AND cs.id IS NULL
            ORDER BY c.nome"
        );
    }

    public function painelOperacional(int $idEmpresa): array
    {
        return $this->query(
            "SELECT
                c.id,
                c.nome,
                c.descricao,
                cs.id AS id_caixa_sessao,
                cs.id_usuario,
                cs.aberto_em,
                cs.ultima_atividade_em,
                u.nome AS usuario_nome,
                COALESCE((
                    SELECT SUM(v.valor)
                    FROM vendas v
                    WHERE v.id_empresa = cs.id_empresa
                      AND v.id_usuario = cs.id_usuario
                      AND v.deleted_at IS NULL
                      AND v.created_at BETWEEN cs.aberto_em AND COALESCE(cs.fechado_em, NOW())
                ), 0) AS faturamento,
                COALESCE((
                    SELECT COUNT(DISTINCT CASE
                        WHEN v.codigo_venda IS NOT NULL AND v.codigo_venda <> '' THEN v.codigo_venda
                        ELSE CONCAT('VENDA-', v.id)
                    END)
                    FROM vendas v
                    WHERE v.id_empresa = cs.id_empresa
                      AND v.id_usuario = cs.id_usuario
                      AND v.deleted_at IS NULL
                      AND v.created_at BETWEEN cs.aberto_em AND COALESCE(cs.fechado_em, NOW())
                ), 0) AS quantidade_vendas,
                CASE
                    WHEN cs.id IS NOT NULL
                     AND cs.fechado_em IS NULL
                     AND cs.ultima_atividade_em >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1
                    ELSE 0
                END AS online
            FROM caixas c
            LEFT JOIN caixa_sessoes cs
                ON cs.id_caixa = c.id
               AND cs.id_empresa = c.id_empresa
               AND cs.fechado_em IS NULL
               AND cs.deleted_at IS NULL
            LEFT JOIN usuarios u ON u.id = cs.id_usuario
            WHERE c.id_empresa = {$idEmpresa}
              AND c.deleted_at IS NULL
            ORDER BY online DESC, c.nome ASC"
        );
    }
}
