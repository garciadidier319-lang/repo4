<?php
declare(strict_types=1);
$dbHost = getenv('DB_HOST') ?: 'mysql-diboweb.alwaysdata.net';
$dbName = getenv('DB_NAME') ?: 'diboweb_articulosgit';
$dbUser = getenv('DB_USER') ?: 'diboweb';
$dbPass = getenv('DB_PASS') ?: 'Gol123456*';
try {
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec("CREATE TABLE IF NOT EXISTS articulos (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(180) NOT NULL,
        marca VARCHAR(120) NOT NULL,
        unidades INT UNSIGNED NOT NULL DEFAULT 0,
        bodega_ubicacion VARCHAR(180) NOT NULL,
        creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_nombre (nombre), INDEX idx_marca (marca), INDEX idx_bodega (bodega_ubicacion)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (PDOException $e) {
    error_log('InventarioPro - error DB: ' . $e->getMessage());
    http_response_code(500);
    exit('<!doctype html><html lang="es"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Error</title><body style="font:16px Arial;max-width:650px;margin:12vh auto;padding:24px;color:#19304d"><h1>No se pudo conectar a la base de datos</h1><p>Verifica las credenciales, permisos y que la base de datos exista en el hosting.</p></body></html>');
}
