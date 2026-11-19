<?php
require __DIR__ . '/config.php';
require __DIR__ . '/layout.php';

if (!empty($_SESSION['uid'])) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $usuario = trim($_POST['usuario'] ?? '');
    $pass = (string)($_POST['password'] ?? '');

    $st = db()->prepare('SELECT id, nombre, password_hash FROM usuarios WHERE usuario = ?');
    $st->bind_param('s', $usuario);
    $u = filas($st)[0] ?? null;

    if ($u && password_verify($pass, $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$u['id'];
        $_SESSION['nombre'] = $u['nombre'];
        redirect('index.php');
    }
    usleep(400000);
    set_flash('error', 'Usuario o contraseña incorrectos.');
    redirect('login.php');
}

cabecera('Iniciar sesión', false);
?>
<div class="card login">
  <h1>🎓 Control escolar</h1>
  <form method="post" class="grid">
    <?= csrf_campo() ?>
    <label class="full">Usuario <input name="usuario" required autofocus></label>
    <label class="full">Contraseña <input type="password" name="password" required></label>
    <div class="full"><button class="btn" type="submit">Entrar</button></div>
  </form>
  <p style="color:var(--muted);font-size:.85rem">Primer acceso: <b>admin</b> / <b>admin123</b></p>
</div>
<?php pie();
