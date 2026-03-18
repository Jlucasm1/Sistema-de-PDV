<?php
require '/var/www/html/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('/var/www/html');
$dotenv->safeLoad();

// Simula o createHash
$hash     = $_ENV['HASH'] ?? getenv('HASH') ?? 'pdvmaster_default_salt_2024';
$salt     = $hash;
$unicSalt = strrev($hash);
$password = 'admin123';
$hashGerado = sha1("{$password}{$salt}{$unicSalt}");

echo "=== HASH ===" . PHP_EOL;
echo "HASH do .env: " . $hash . PHP_EOL;
echo "Hash gerado:  " . $hashGerado . PHP_EOL . PHP_EOL;

// Conecta ao banco
$pdo = new PDO(
    'mysql:host=' . ($_ENV['HOST_NAME'] ?? 'pdv_master_db') . ';dbname=' . ($_ENV['HOST_DBNAME'] ?? 'pdvmaster') . ';charset=utf8',
    $_ENV['HOST_USERNAME'] ?? 'pdvmaster',
    $_ENV['HOST_PASSWORD'] ?? 'pdvmaster@2024'
);

// Busca usuário
$stmt = $pdo->prepare("SELECT id, email, password, status, deleted_at FROM usuarios WHERE email = ? LIMIT 1");
$stmt->execute(['admin@pdvmaster.com.br']);
$user = $stmt->fetch(PDO::FETCH_OBJ);

echo "=== BANCO ===" . PHP_EOL;
echo "Usuário encontrado: " . ($user ? 'SIM' : 'NÃO') . PHP_EOL;
if ($user) {
    echo "Password no banco: " . $user->password . PHP_EOL;
    echo "Status: " . $user->status . PHP_EOL;
    echo "Deleted at: " . ($user->deleted_at ?? 'NULL') . PHP_EOL;
    echo "Hash bate: " . ($user->password === $hashGerado ? 'SIM ✓' : 'NÃO ✗') . PHP_EOL;
}

// Simula a query do Auth
echo PHP_EOL . "=== QUERY DO AUTH ===" . PHP_EOL;
$stmt2 = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND password = ? LIMIT 1");
$stmt2->execute(['admin@pdvmaster.com.br', $hashGerado]);
echo "Rows encontrados: " . $stmt2->rowCount() . PHP_EOL;
