<?php
declare(strict_types=1);
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* ---------- Conexión a MariaDB (contenedor "servidor-bd") ---------- */
// Deben coincidir con las variables MARIADB_* de docker-compose.yml
const DB_HOST = 'servidor-bd';
const DB_PORT = 3306;
const DB_USER = 'usuario';
const DB_PASS = 'clave123';
const DB_NAME = 'control_escolar';

/** Devuelve la conexión a la base de datos. */
function db(): mysqli
{
    static $db = null;
    if ($db instanceof mysqli) {
        return $db;
    }
    try {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        $db->set_charset('utf8mb4');
    } catch (mysqli_sql_exception $e) {
        http_response_code(500);
        exit('Error de conexión con la base de datos: ' . htmlspecialchars($e->getMessage()));
    }
    return $db;
}

/* ---------- Utilidades ---------- */
function e(mixed $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function requiere_login(): void
{
    if (empty($_SESSION['uid'])) {
        redirect('login.php');
    }
}

function set_flash(string $tipo, string $msg): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $msg];
}

function get_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function csrf(): string
{
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf() . '">';
}

function csrf_validar(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('Sesión expirada o token inválido. Regresa y recarga la página.');
    }
}

function filas(mysqli_stmt $st): array
{
    $st->execute();
    return $st->get_result()->fetch_all(MYSQLI_ASSOC);
}
