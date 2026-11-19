<?php
require __DIR__ . '/config.php';
require __DIR__ . '/layout.php';
requiere_login();
$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();
    $accion = $_POST['accion'] ?? '';
    $volver = 'calificaciones.php' . (!empty($_POST['filtro']) ? '?alumno=' . (int)$_POST['filtro'] : '');
    try {
        if ($accion === 'guardar') {
            $alumno = (int)($_POST['alumno_id'] ?? 0);
            $materia = (int)($_POST['materia_id'] ?? 0);
            $parcial = (int)($_POST['parcial'] ?? 0);
            $cal = str_replace(',', '.', trim($_POST['calificacion'] ?? ''));
            if (!$alumno || !$materia || $parcial < 1 || $parcial > 3 || !is_numeric($cal) || $cal < 0 || $cal > 10) {
                set_flash('error', 'Completa todos los campos. La calificación va de 0 a 10.');
                redirect($volver);
            }
            $cal = (float)$cal;
            $st = $db->prepare('INSERT INTO calificaciones (alumno_id, materia_id, parcial, calificacion) VALUES (?,?,?,?)
                                ON DUPLICATE KEY UPDATE calificacion = VALUES(calificacion)');
            $st->bind_param('iiid', $alumno, $materia, $parcial, $cal);
            $st->execute();
            set_flash('ok', 'Calificación guardada.');
        } elseif ($accion === 'eliminar') {
            $id = (int)($_POST['id'] ?? 0);
            $st = $db->prepare('DELETE FROM calificaciones WHERE id = ?');
            $st->bind_param('i', $id);
            $st->execute();
            set_flash('ok', 'Calificación eliminada.');
        }
    } catch (mysqli_sql_exception $ex) {
        set_flash('error', 'Error en la base de datos.');
    }
    redirect($volver);
}

$filtro = (int)($_GET['alumno'] ?? 0);
$alumnos = $db->query('SELECT id, matricula, nombre, apellidos FROM alumnos ORDER BY apellidos, nombre')->fetch_all(MYSQLI_ASSOC);
$materias = $db->query('SELECT id, clave, nombre, grado FROM materias ORDER BY grado, nombre')->fetch_all(MYSQLI_ASSOC);

$sql = "SELECT c.id, a.matricula, CONCAT(a.nombre, ' ', a.apellidos) AS alumno, m.clave, m.nombre AS materia, c.parcial, c.calificacion
        FROM calificaciones c
        JOIN alumnos a ON a.id = c.alumno_id
        JOIN materias m ON m.id = c.materia_id";
if ($filtro) {
    $st = $db->prepare($sql . ' WHERE c.alumno_id = ? ORDER BY m.nombre, c.parcial');
    $st->bind_param('i', $filtro);
} else {
    $st = $db->prepare($sql . ' ORDER BY a.apellidos, a.nombre, m.nombre, c.parcial');
}
$rows = filas($st);
$promedio = $rows ? array_sum(array_column($rows, 'calificacion')) / count($rows) : null;

cabecera('Calificaciones');
?>
<h1>Calificaciones</h1>
<section class="card">
  <h2>Capturar calificación</h2>
  <?php if (!$alumnos || !$materias): ?>
    <p>Primero registra al menos un <a href="alumnos.php">alumno</a> y una <a href="materias.php">materia</a>.</p>
  <?php else: ?>
  <form method="post" class="grid">
    <?= csrf_campo() ?>
    <input type="hidden" name="accion" value="guardar">
    <input type="hidden" name="filtro" value="<?= $filtro ?>">
    <label>Alumno
      <select name="alumno_id" required>
        <?php foreach ($alumnos as $a): ?>
          <option value="<?= (int)$a['id'] ?>" <?= $filtro === (int)$a['id'] ? 'selected' : '' ?>><?= e($a['matricula'] . ' · ' . $a['apellidos'] . ' ' . $a['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Materia
      <select name="materia_id" required>
        <?php foreach ($materias as $m): ?>
          <option value="<?= (int)$m['id'] ?>"><?= e($m['clave'] . ' · ' . $m['nombre'] . ' (grado ' . $m['grado'] . ')') ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Parcial
      <select name="parcial"><option>1</option><option>2</option><option>3</option></select>
    </label>
    <label>Calificación (0-10) <input type="number" name="calificacion" min="0" max="10" step="0.1" required></label>
    <div class="full"><button class="btn" type="submit">Guardar</button> <small style="color:var(--muted)">Si ya existe esa combinación, se actualiza.</small></div>
  </form>
  <?php endif; ?>
</section>

<section class="card">
  <h2>Registros (<?= count($rows) ?>)</h2>
  <form method="get" class="inline">
    <label>Filtrar por alumno
      <select name="alumno" onchange="this.form.submit()">
        <option value="0">Todos</option>
        <?php foreach ($alumnos as $a): ?>
          <option value="<?= (int)$a['id'] ?>" <?= $filtro === (int)$a['id'] ? 'selected' : '' ?>><?= e($a['apellidos'] . ' ' . $a['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <noscript><button class="btn" type="submit">Filtrar</button></noscript>
  </form>
  <?php if ($promedio !== null): ?>
    <p>Promedio: <b class="<?= $promedio < 6 ? 'reprobado' : '' ?>"><?= number_format($promedio, 2) ?></b></p>
  <?php endif; ?>
  <div class="tabla"><table>
    <tr><th>Matrícula</th><th>Alumno</th><th>Materia</th><th>Parcial</th><th>Calificación</th><th></th></tr>
    <?php if (!$rows): ?><tr><td colspan="6">Sin calificaciones.</td></tr><?php endif; ?>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= e($r['matricula']) ?></td><td><?= e($r['alumno']) ?></td>
        <td><?= e($r['clave'] . ' · ' . $r['materia']) ?></td><td><?= (int)$r['parcial'] ?></td>
        <td class="<?= $r['calificacion'] < 6 ? 'reprobado' : '' ?>"><?= number_format((float)$r['calificacion'], 1) ?></td>
        <td>
          <form method="post" onsubmit="return confirm('¿Eliminar esta calificación?');">
            <?= csrf_campo() ?><input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="filtro" value="<?= $filtro ?>">
            <button class="btn sm danger" type="submit">Eliminar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table></div>
</section>
<?php pie();
