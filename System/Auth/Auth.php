<?php

namespace System\Auth;

use System\Session\Session;

/**
 * PDV MASTER - Trait de Autenticação
 */
trait Auth
{
    public function userExist(array $data): bool
    {
        if ($this->loginVerify($data)) {
            Session::set('logged', true);
            return true;
        }
        return false;
    }

    private function loginVerify(array $data): bool
    {
        $keys     = array_keys($data);
        $loginFld = $keys[0]; // email
        $passFld  = $keys[1]; // password

        $stmt = $this->db->prepare(
            "SELECT id, {$passFld} FROM {$this->table} WHERE {$loginFld} = ? LIMIT 1"
        );
        $stmt->execute([$data[$loginFld]]);
        $user = $stmt->fetch(\PDO::FETCH_OBJ);

        if (!$user || !passwordMatches($data[$passFld] ?? '', $user->{$passFld} ?? '')) {
            return false;
        }

        if (!password_verify((string) ($data[$passFld] ?? ''), (string) $user->{$passFld})) {
            $update = $this->db->prepare("UPDATE {$this->table} SET {$passFld} = ? WHERE id = ?");
            $update->execute([createPasswordHash($data[$passFld] ?? ''), $user->id]);
        }

        return true;
    }
}
