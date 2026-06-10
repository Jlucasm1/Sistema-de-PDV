<?php
/**
 * PDV MASTER - Entry Point
 * DocumentRoot aponta para esta pasta (/public)
 * SCRIPT_NAME será /index.php, então BASEURL = http://localhost:8080
 */
define('BASE_PATH', dirname(__DIR__));
chdir(BASE_PATH);
require BASE_PATH . '/index.php';
