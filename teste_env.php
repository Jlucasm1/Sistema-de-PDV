<?php
require '/var/www/html/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable('/var/www/html');
$dotenv->safeLoad();

$hash     = $_ENV['HASH'] ?? getenv('HASH') ?? 'pdvmaster_default_salt_2024';
$salt     = $hash;
$unicSalt = strrev($hash);
$password = 'admin123';

$result = sha1("{$password}{$salt}{$unicSalt}");

echo "HASH do .env: " . $hash . PHP_EOL;
echo "Hash gerado:  " . $result . PHP_EOL;
echo "Esperado:     31436dce8d474f3108f8d7f1c4fc0a080e4af915" . PHP_EOL;
echo "Bate? " . ($result === '31436dce8d474f3108f8d7f1c4fc0a080e4af915' ? 'SIM ✓' : 'NAO ✗') . PHP_EOL;