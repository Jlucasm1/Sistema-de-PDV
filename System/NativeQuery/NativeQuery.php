<?php

namespace System\NativeQuery;

use PDO;

/**
 * PDV MASTER - ORM Nativo com PDO
 * Implementa CRUD completo, soft-delete e queries preparadas
 */
class NativeQuery
{
    protected PDO $db;
    protected string $table    = '';
    protected bool   $timestamps = false;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    // ----------------------------------------------------------------
    // SELECT helpers
    // ----------------------------------------------------------------

    public function query(string $sql): array
    {
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function queryGetOne(string $sql): object
    {
        $stmt = $this->db->query($sql);
        $row  = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ?: (object)[];
    }

    public function prepare(string $sql, array $data = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function prepareGetOne(string $sql, array $data = []): ?object
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ?: null;
    }

    public function execute(string $sql): bool
    {
        return $this->db->exec($sql) !== false;
    }

    public function all(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function find(int $id): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ?: null;
    }

    public function findBy(string $field, $value): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$field} = ? LIMIT 1");
        $stmt->execute([$value]);
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        return $row ?: null;
    }

    // ----------------------------------------------------------------
    // INSERT
    // ----------------------------------------------------------------

    public function save(array $data)
    {
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $fields      = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(array_values($data));

        return $result ? $this->db->lastInsertId() : false;
    }

    // ----------------------------------------------------------------
    // UPDATE
    // ----------------------------------------------------------------

    public function update(array $data, int $id, string $primaryKey = 'id'): bool
    {
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $setParts = array_map(fn($field) => "{$field} = ?", array_keys($data));
        $values   = array_values($data);
        $values[] = $id;

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            $this->table,
            implode(', ', $setParts),
            $primaryKey
        );

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    // ----------------------------------------------------------------
    // DELETE (soft delete via deleted_at)
    // ----------------------------------------------------------------

    public function softDelete(int $id): bool
    {
        return $this->update(['deleted_at' => date('Y-m-d H:i:s')], $id);
    }

    public function hardDelete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    public function lastId(): string
    {
        return $this->db->lastInsertId();
    }

    public function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }

    public function commit(): void
    {
        $this->db->commit();
    }

    public function rollback(): void
    {
        $this->db->rollBack();
    }
}
