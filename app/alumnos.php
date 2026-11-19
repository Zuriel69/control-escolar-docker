<?php
require __DIR__ . '/config.php';
require __DIR__ . '/layout.php';
requiere_login();
$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $accion = $_POST['accion'] ?? '';
    try {
        if ($accion === 'guardar') {
            $id = (int)($_POST['id'] ?? 0);
            $matricula = trim($_POST['matricula'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $apellidos = trim($_POST['apellidos'] ?? '');
            $fecha = trim($_POST['fecha_nacimiento'] ?? '');
            $grado = (int)($_POST['grado'] ?? 0);
            $grupo = strtoupper(trim($_POST['grupo'] ?? ''));
            $email = trim($_POST['email'] ?? '') ?: null;
            $tel = trim($_POST['telefono'] ?? '') ?: null;

            $err = [];
            if ($matricula === '' || $nombre === '' || $apellidos === '') $err[] = 'Matrícula, nombre y apellidos son obligatorios.';
            $d = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$d || $d->format('Y-m-d') !== $fecha) $err[] = 'Fecha de nacimiento inválida.';
            if ($grado < 1 || $grado > 12) $err[] = 'El grado debe estar entre 1 y 12.';
            if (!preg_match('/^[A-Z]$/', $grupo)) $err[] = 'El grupo debe ser una letra.';
            if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) $err[] = 'Correo inválido.';

            if ($err) {
                set_flash('error', implode(' ', $err));
                redirect('alumnos.php' . ($id ? "?editar=$id" : ''));
            }
            if ($id) {
                $st = $db->prepare('UPDATE alumnos SET matricula=?, nombre=?, apellidos=?, fecha_nacimiento=?, grado=?, grupo=?, email=?, telefono=? WHERE id=?');
                $st->bind_param('ssssisssi', $matricula, $nombre, $apellidos, $fecha, $grado, $grupo, $email, $tel, $id);
                set_flash('ok', 'Alumno actualizado.');
            } else {
                $st = $db->prepare('INSERT INTO alumnos (matricula, nombre, apellidos, fecha_nacimiento, grado, grupo, email, telefono) VALUES (?,?,?,?,?,?,?,?)');
                $st->bind_param('ssssisss', $matricula, $nombre, $apellidos, $fecha, $grado, $grupo, $email, $tel);
                set_flash('ok', 'Alumno registrado.');
            }
            $st->execute();
        } elseif ($accion === 'eliminar') {
            $id = (int)($_POST['id'] ?? 0);
            $st = $db->prepare('DELETE FROM alumnos WHERE id = ?');
            $st->bind_param('i', $id);
            $st->execute();
            set_flash('ok', 'Alumno eliminado (junto con sus calificaciones).');
        }
    } catch (mysqli_sql_exception $ex) {
        set_flash('error', $ex->getCode() === 1062 ? 'Ya existe un alumno con esa matrícula.' : 'Error en la base de datos.');
    }
    redirect('alumnos.php');
}

$editar = null;
if (isset($_GET['editar'])) {
    $st = $db->prepare('SELECT * FROM alumnos WHERE id = ?');
    $eid = (int)$_GET['editar'];
    $st->bind_param('i', $eid);
    $editar = filas($st)[0] ?? null;
}
$v = $editar ?? [];

$q = trim($_GET['q'] ?? '');
$like = '%' . $q . '%';
$st = $db->prepare('SELECT * FROM alumnos WHERE matricula LIKE ? OR nombre LIKE ? OR apellidos LIKE ? ORDER BY grado, grupo, apellidos, nombre');
$st->bind_param('sss', $like, $like, $like);
$alumnos = filas($st);

cabecera('Alumnos');
?>
<h1>Alumnos</h1>
<section class="card">
  <h2><?= $editar ? 'Editar alumno' : 'Registrar alumno' ?></h2>
  <form method="post" class="grid">
    <?= csrf_campo() ?>
    <input type="hidden" name="accion" value="guardar">
    <input type="hidden" name="id" value="<?= (int)($v['id'] ?? 0) ?>">
    <label>Matrícula <input name="matricula" maxlength="20" required value="<?= e($v['matricula'] ?? '') ?>"></label>
    <label>Nombre <input name="nombre" maxlength="80" required value="<?= e($v['nombre'] ?? '') ?>"></label>
    <label>Apellidos <input name="apellidos" maxlength="100" required value="<?= e($v['apellidos'] ?? '') ?>"></label>
    <label>Fecha de nacimiento <input type="date" name="fecha_nacimiento" required value="<?= e($v['fecha_nacimiento'] ?? '') ?>"></label>
    <label>Grado <input type="number" name="grado" min="1" max="12" required value="<?= e($v['grado'] ?? '') ?>"></label>
    <label>Grupo <input name="grupo" maxlength="1" pattern="[A-Za-z]" required value="<?= e($v['grupo'] ?? '') ?>"></label>
    <label>Correo (opcional) <input type="email" name="email" maxlength="120" value="<?= e($v['email'] ?? '') ?>"></label>
    <label>Teléfono (opcional) <input name="telefono" maxlength="20" value="<?= e($v['telefono'] ?? '') ?>"></label>
    <div class="full">
      <button class="btn" type="submit">Guardar</button>
      <?php if ($editar): ?><a class="btn sec" href="alumnos.php">Cancelar</a><?php endif; ?>
    </div>
  </form>
</section>

<section class="card">
  <h2>Listado (<?= count($alumnos) ?>)</h2>
  <form method="get" class="inline">
    <label>Buscar <input name="q" value="<?= e($q) ?>" placeholder="Matrícula o nombre"></label>
    <button class="btn" type="submit">Buscar</button>
    <?php if ($q !== ''): ?><a class="btn sec" href="alumnos.php">Limpiar</a><?php endif; ?>
  </form>
  <div class="tabla"><table>
    <tr><th>Matrícula</th><th>Nombre</th><th>Nacimiento</th><th>Grado</th><th>Grupo</th><th>Correo</th><th>Teléfono</th><th></th></tr>
    <?php if (!$alumnos): ?><tr><td colspan="8">Sin resultados.</td></tr><?php endif; ?>
    <?php foreach ($alumnos as $a): ?>
      <tr>
        <td><?= e($a['matricula']) ?></td>
        <td><?= e($a['nombre'] . ' ' . $a['apellidos']) ?></td>
        <td><?= e(date('d/m/Y', strtotime($a['fecha_nacimiento']))) ?></td>
        <td><?= (int)$a['grado'] ?></td>
        <td><?= e($a['grupo']) ?></td>
        <td><?= e($a['email'] ?? '—') ?></td>
        <td><?= e($a['telefono'] ?? '—') ?></td>
        <td>
          <a class="btn sm" href="calificaciones.php?alumno=<?= (int)$a['id'] ?>">Boleta</a>
          <a class="btn sm sec" href="?editar=<?= (int)$a['id'] ?>">Editar</a>
          <form method="post" onsubmit="return confirm('¿Eliminar alumno y sus calificaciones?');">
            <?= csrf_campo() ?><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
            <button class="btn sm danger" type="submit">Eliminar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table></div>
</section>
<?php pie();
