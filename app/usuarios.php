<?php
require __DIR__ . '/config.php';
require __DIR__ . '/layout.php';
requiere_login();
$db = db();
$yo = (int)$_SESSION['uid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $accion = $_POST['accion'] ?? '';
    try {
        if ($accion === 'crear') {
            $usuario = trim($_POST['usuario'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $pass = (string)($_POST['password'] ?? '');
            if ($usuario === '' || $nombre === '' || strlen($pass) < 8) {
                set_flash('error', 'Usuario y nombre son obligatorios; la contraseña debe tener al menos 8 caracteres.');
            } else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $st = $db->prepare('INSERT INTO usuarios (usuario, nombre, password_hash) VALUES (?,?,?)');
                $st->bind_param('sss', $usuario, $nombre, $hash);
                $st->execute();
                set_flash('ok', 'Usuario creado.');
            }
        } elseif ($accion === 'eliminar') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id === $yo) {
                set_flash('error', 'No puedes eliminar tu propio usuario.');
            } else {
                $st = $db->prepare('DELETE FROM usuarios WHERE id = ?');
                $st->bind_param('i', $id);
                $st->execute();
                set_flash('ok', 'Usuario eliminado.');
            }
        } elseif ($accion === 'password') {
            $actual = (string)($_POST['actual'] ?? '');
            $nueva = (string)($_POST['nueva'] ?? '');
            $conf = (string)($_POST['confirmar'] ?? '');
            $st = $db->prepare('SELECT password_hash FROM usuarios WHERE id = ?');
            $st->bind_param('i', $yo);
            $h = filas($st)[0]['password_hash'] ?? '';
            if (!password_verify($actual, $h)) {
                set_flash('error', 'La contraseña actual es incorrecta.');
            } elseif (strlen($nueva) < 8 || $nueva !== $conf) {
                set_flash('error', 'La nueva contraseña debe tener 8+ caracteres y coincidir con la confirmación.');
            } else {
                $nuevo = password_hash($nueva, PASSWORD_DEFAULT);
                $st = $db->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?');
                $st->bind_param('si', $nuevo, $yo);
                $st->execute();
                set_flash('ok', 'Contraseña actualizada.');
            }
        }
    } catch (mysqli_sql_exception $ex) {
        set_flash('error', $ex->getCode() === 1062 ? 'Ese usuario ya existe.' : 'Error en la base de datos.');
    }
    redirect('usuarios.php');
}

$usuarios = $db->query('SELECT id, usuario, nombre, creado_en FROM usuarios ORDER BY usuario')->fetch_all(MYSQLI_ASSOC);

cabecera('Usuarios');
?>
<h1>Usuarios</h1>
<div class="grid" style="align-items:start">
  <section class="card">
    <h2>Nuevo usuario</h2>
    <form method="post" class="grid">
      <?= csrf_campo() ?><input type="hidden" name="accion" value="crear">
      <label class="full">Usuario <input name="usuario" maxlength="50" required></label>
      <label class="full">Nombre completo <input name="nombre" maxlength="100" required></label>
      <label class="full">Contraseña (mín. 8) <input type="password" name="password" minlength="8" required></label>
      <div class="full"><button class="btn" type="submit">Crear</button></div>
    </form>
  </section>
  <section class="card">
    <h2>Cambiar mi contraseña</h2>
    <form method="post" class="grid">
      <?= csrf_campo() ?><input type="hidden" name="accion" value="password">
      <label class="full">Actual <input type="password" name="actual" required></label>
      <label class="full">Nueva (mín. 8) <input type="password" name="nueva" minlength="8" required></label>
      <label class="full">Confirmar <input type="password" name="confirmar" minlength="8" required></label>
      <div class="full"><button class="btn" type="submit">Actualizar</button></div>
    </form>
  </section>
</div>
<section class="card">
  <h2>Usuarios del sistema</h2>
  <div class="tabla"><table>
    <tr><th>Usuario</th><th>Nombre</th><th>Creado</th><th></th></tr>
    <?php foreach ($usuarios as $u): ?>
      <tr>
        <td><?= e($u['usuario']) ?></td><td><?= e($u['nombre']) ?></td>
        <td><?= e(date('d/m/Y', strtotime($u['creado_en']))) ?></td>
        <td>
          <?php if ((int)$u['id'] !== $yo): ?>
          <form method="post" onsubmit="return confirm('¿Eliminar usuario?');">
            <?= csrf_campo() ?><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <button class="btn sm danger" type="submit">Eliminar</button>
          </form>
          <?php else: ?><small style="color:var(--muted)">(tú)</small><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table></div>
</section>
<?php pie();
