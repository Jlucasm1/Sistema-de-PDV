<?php

// ================================================================
// PDV MASTER - Helpers Globais
// ================================================================

function createHash(?string $password = null): string
{
    $hash = $_ENV['HASH'] ?? getenv('HASH');
    if (empty($hash)) {
        throw new \RuntimeException('Variável HASH não definida no .env');
    }

    return legacySha1PasswordHash((string) $password, $hash);
}

function createPasswordHash(?string $password = null): string
{
    return password_hash((string) $password, PASSWORD_BCRYPT);
}

function passwordMatches(?string $password, ?string $storedHash): bool
{
    $hash = $_ENV['HASH'] ?? getenv('HASH');
    $password = (string) $password;
    $storedHash = (string) $storedHash;

    if ($storedHash === '') {
        return false;
    }

    if (password_verify($password, $storedHash)) {
        return true;
    }

    foreach (legacyPasswordHashes($password) as $legacyHash) {
        if (hash_equals($legacyHash, $storedHash)) {
            return true;
        }
    }

    if (!empty($hash) && hash_equals(legacySha1PasswordHash($password, $hash), $storedHash)) {
        return true;
    }

    return false;
}

function normalizaTexto(?string $texto): string
{
    $texto = (string) $texto;

    if ($texto !== '' && preg_match('/(Ã.|Â.|�)/u', $texto)) {
        $corrigido = utf8_decode($texto);
        if (mb_check_encoding($corrigido, 'UTF-8')) {
            return $corrigido;
        }
    }

    return $texto;
}

function e(mixed $valor): string
{
    return htmlspecialchars(normalizaTexto((string) $valor), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function legacyPasswordHashes(string $password): array
{
    $salts = array_filter(array_unique([
        $_ENV['HASH'] ?? getenv('HASH') ?: null,
        'pdvmaster@2024#secure_salt_key',
    ]));

    return array_map(static function (string $salt) use ($password): string {
        return legacySha1PasswordHash($password, $salt);
    }, $salts);
}

function legacySha1PasswordHash(string $password, string $salt): string
{
    $unicSalt = strrev($salt);
    return sha1("{$password}{$salt}{$unicSalt}");
}

function dd(mixed $data): never
{
    if (!defined('IS_TERMINAL')) {
        echo "<style>body{background:#1a1a2e;} pre{background:#16213e;color:#0f3460;border:2px solid #e94560;padding:15px;border-radius:8px;font-size:13px;color:#a8dadc;}</style>";
        echo "<pre>";
    }
    print_r($data);
    exit;
}

function real(float|string|null $valor): string
{
    return number_format((float)$valor, 2, ',', '.');
}

function formataValorMoedaParaGravacao(string $valor): string
{
    $valor = str_replace('R$', '', $valor);
    $valor = trim($valor);
    if (str_contains($valor, '.')) {
        $valor = str_replace('.', '', $valor);
    }
    $valor = str_replace(',', '.', $valor);
    return $valor;
}

function timestamp(): string
{
    return date('Y-m-d H:i:s');
}

function in64(string $string): string
{
    return base64_encode("pdvmaster_secret||" . $string);
}

function out64(string $string): string|false
{
    $decoded = base64_decode($string);
    if (!$decoded) return false;
    $parts = explode('pdvmaster_secret||', $decoded);
    return $parts[1] ?? false;
}

function currentRouteFromMenu(string $route, string $extraClass = ''): void
{
    $routeParts  = explode('/', $route);
    $controller  = explode('Controller', CONTROLLER_NAME)[0];
    $method      = METHOD_NAME;

    if (
        ucfirst($routeParts[0]) === $controller &&
        (!isset($routeParts[1]) || $routeParts[1] === $method)
    ) {
        echo "currentRouteFromMenu {$extraClass}";
    }
}

function uploadImageHelper(object $uploadClass, string $folder, array $image): string|array
{
    $uploadClass->file($image);
    $uploadClass->folder($folder);
    $uploadClass->extensions(['png', 'jpg', 'jpeg', 'webp']);

    $error = match ($uploadClass->getErrors()) {
        1       => 'Formato de imagem não suportado',
        2       => 'Tamanho máximo permitido: 10MB',
        3       => 'Formato não identificado. Tente novamente.',
        4       => 'Diretório de upload não encontrado.',
        default => null
    };

    if ($error !== null) {
        return ['error' => $error];
    }

    $uploadClass->move();
    return $uploadClass->destinationPath();
}

// ✅ ADICIONADO — resolve o "Call to undefined function iconFilter()"
function iconFilter(): void
{
    echo '<i class="fas fa-filter"></i>';
}

function stringAbreviation(string $string, int $length, string $end): string
{
    if (strlen($string) <= $length) return $string;
    return substr($string, 0, $length) . $end;
}

function gerarCodigoDeBarrasEmPng(string $codigo): void
{
    $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
    $barcode   = $generator->getBarcode($codigo, $generator::TYPE_CODE_128);
    echo "data:image/png;base64," . base64_encode($barcode);
}

function fileGet(string $url): ?array
{
    $context = stream_context_create(['http' => ['ignore_errors' => true, 'method' => 'GET']]);
    $json    = @file_get_contents($url, false, $context);
    return $json ? json_decode($json, true) : null;
}
