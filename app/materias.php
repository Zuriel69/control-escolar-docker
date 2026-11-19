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
            $clave = strtoupper(trim($_POST['clave'] ?? ''));
            $nombre = trim($_POST['nombre'] ?? '');
            $grado = (int)($_POST['grado'] ?? 0);
            if ($clave === '' || $nombre === '' || $grado < 1 || $grado > 12) {
                set_flash('error', 'Clave, nombre y grado (1-12) son obligatorios.');
                redirect('materias.php' . ($id ? "?editar=$id" : ''));
            }
            if ($id) {
                $st = $db->prepare('UPDATE materias SET clave=?, nombre=?, grado=? WHERE id=?');
                $st->bind_param('ssii', $clave, $nombre, $grado, $id);
                set_flash('ok', 'Materia actualizada.');
            } else {
                $st = $db->prepare('INSERT INTO materias (clave, nombre, grado) VALUES (?,?,?)');
                $st->bind_param('ssi', $clave, $nombre, $grado);
                set_flash('ok', 'Materia registrada.');
            }
            $st->execute();
        } elseif ($accion === 'eliminar') {
            $id = (int)($_POST['id'] ?? 0);
            $st = $db->prepare('DELETE FROM materias WHERE id = ?');
            $st->bind_param('i', $id);
            $st->execute();
            set_flash('ok', 'Materia eliminada (junto con sus calificaciones).');
        }
    } catch (mysqli_sql_exception $ex) {
        set_flash('error', $ex->getCode() === 1062 ? 'Ya existe una materia con esa clave.' : 'Error en la base de datos.');
    }
    redirect('materias.php');
}

$editar = null;
if (isset($_GET['editar'])) {
    $st = $db->prepare('SELECT * FROM materias WHERE id = ?');
    $eid = (int)$_GET['editar'];
    $st->bind_param('i', $eid);
    $editar = filas($st)[0] ?? null;
}
$v = $editar ?? [];
$materias = $db->query('SELECT * FROM materias ORDER BY grado, nombre')->fetch_all(MYSQLI_ASSOC);

cabecera('Materias');
?>
<h1>Materias</h1>
<section class="card">
  <h2><?= $editar ? 'Editar materia' : 'Registrar materia' ?></h2>
  <form method="post" class="grid">
    <?= csrf_campo() ?>
    <input type="hidden" name="accion" value="guardar">
    <input type="hidden" name="id" value="<?= (int)($v['id'] ?? 0) ?>">
    <label>Clave <input name="clave" maxlength="20" required value="<?= e($v['clave'] ?? '') ?>"></label>
    <label>Nombre <input name="nombre" maxlength="100" required value="<?= e($v['nombre'] ?? '') ?>"></label>
    <label>Grado <input type="number" name="grado" min="1" max="12" required value="<?= e($v['grado'] ?? '') ?>"></label>
    <div class="full">
      <button class="btn" type="submit">Guardar</button>
      <?php if ($editar): ?><a class="btn sec" href="materias.php">Cancelar</a><?php endif; ?>
    </div>
  </form>
</section>

<section class="card">
  <h2>Listado (<?= count($materias) ?>)</h2>
  <div class="tabla"><table>
    <tr><th>Clave</th><th>Nombre</th><th>Grado</th><th></th></tr>
    <?php if (!$materias): ?><tr><td colspan="4">Aún no hay materias.</td></tr><?php endif; ?>
    <?php foreach ($materias as $m): ?>
      <tr>
        <td><?= e($m['clave']) ?></td><td><?= e($m['nombre']) ?></td><td><?= (int)$m['grado'] ?></td>
        <td>
          <a class="btn sm sec" href="?editar=<?= (int)$m['id'] ?>">Editar</a>
          <form method="post" onsubmit="return confirm('¿Eliminar materia y sus calificaciones?');">
            <?= csrf_campo() ?><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
            <button class="btn sm danger" type="submit">Eliminar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table></div>
</section>
<?php pie();
