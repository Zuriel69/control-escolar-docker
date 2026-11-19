<?php
require __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $_SESSION = [];
    session_destroy();
}
header('Location: login.php');
exit;
