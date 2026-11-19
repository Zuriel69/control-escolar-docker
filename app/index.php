<?php
require __DIR__ . '/config.php';
require __DIR__ . '/layout.php';
requiere_login();
$db = db();

$total = fn(string $t): int => (int)$db->query("SELECT COUNT(*) c FROM $t")->fetch_assoc()['c'];
$prom = $db->query('SELECT AVG(calificacion) p FROM calificaciones')->fetch_assoc()['p'];
$ultimos = $db->query('SELECT matricula, nombre, apellidos, grado, grupo FROM alumnos ORDER BY id DESC LIMIT 5')->fetch_all(MYSQLI_ASSOC);

cabecera('Inicio');
?>
<h1>Panel principal</h1>
<div class="stats">
  <div class="stat"><b><?= $total('alumnos') ?></b>Alumnos</div>
  <div class="stat"><b><?= $total('materias') ?></b>Materias</div>
  <div class="stat"><b><?= $total('calificaciones') ?></b>Calificaciones</div>
  <div class="stat"><b><?= $prom !== null ? number_format((float)$prom, 1) : '—' ?></b>Promedio general</div>
</div>
<section class="card">
  <h2>Últimos alumnos registrados</h2>
  <div class="tabla"><table>
    <tr><th>Matrícula</th><th>Nombre</th><th>Grado</th><th>Grupo</th></tr>
    <?php if (!$ultimos): ?><tr><td colspan="4">Aún no hay alumnos. <a href="alumnos.php">Registrar uno</a>.</td></tr><?php endif; ?>
    <?php foreach ($ultimos as $a): ?>
      <tr><td><?= e($a['matricula']) ?></td><td><?= e($a['nombre'] . ' ' . $a['apellidos']) ?></td><td><?= (int)$a['grado'] ?></td><td><?= e($a['grupo']) ?></td></tr>
    <?php endforeach; ?>
  </table></div>
</section>
<?php pie();
