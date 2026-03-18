<?php

// ================================================================
// PDV MASTER — Front Controller Principal
// ================================================================

// ---- Controle de erros -----------------------------------------
$dotenvPath = __DIR__ . '/.env';

if (!file_exists($dotenvPath)) {
    require_once __DIR__ . '/System/Bootstrap/install.php';
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

if (
    ($_ENV['APP_ENV'] ?? 'local') !== 'production' &&
    ($_ENV['APP_DISPLAY_ERRORS'] ?? 'true') === 'true'
) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// ---- Headers ---------------------------------------------------
header('Content-Type: text/html; charset=UTF-8');

// ---- Sessão ----------------------------------------------------
System\Session\Session::start();
System\Session\Token::verify();

// ---- Timezone --------------------------------------------------
date_default_timezone_set($_ENV['TIMEZONE'] ?? 'America/Sao_Paulo');

// ---- Constantes globais ----------------------------------------
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
define('BASEURL', $protocol . '://' . $host . $basePath);
if (!defined('BASE_PATH')) {
    define('BASE_PATH',  __DIR__);
}

// Extrair controller e method para helpers de menu ativo
$uri   = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$parts = explode('/', $uri);
define('CONTROLLER_NAME', ucfirst($parts[0] ?? 'Login') . 'Controller');
define('METHOD_NAME',     $parts[1] ?? 'index');
define('TOKEN', System\Session\Token::generate());

// ---- Roteador --------------------------------------------------
use System\Route\GetRoute;
use System\Route\SelectController;

$route = new SelectController(new GetRoute());

require_once __DIR__ . '/routes/routes.php';
