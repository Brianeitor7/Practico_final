<?php
/**
 * db.php — Conexión a la base de datos MariaDB.
 *
 * Las credenciales se cargan desde el archivo .env ubicado en la raíz
 * del proyecto (fuera de public/ y excluido del repositorio Git para
 * no exponer contraseñas).
 */
declare(strict_types=1);

$envFile = dirname(__DIR__) . '/.env';

$env = [];
if (is_readable($envFile)) {
    $env = parse_ini_file($envFile, false, INI_SCANNER_RAW) ?: [];
}

$DB_HOST = $env['DB_HOST'] ?? 'localhost';
$DB_PORT = (int) ($env['DB_PORT'] ?? 3306);
$DB_NAME = $env['DB_NAME'] ?? 'productos_db';
$DB_USER = $env['DB_USER'] ?? 'appuser';
$DB_PASS = $env['DB_PASS'] ?? '';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
    $conexion->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    error_log('Error de conexión BD: ' . $e->getMessage());
    exit('Error de conexión a la base de datos. Verifique la configuración del archivo .env');
}
