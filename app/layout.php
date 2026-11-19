<?php
function cabecera(string $titulo, bool $nav = true): void
{
    $actual = basename($_SERVER['SCRIPT_NAME']);
    $menu = [
        'index.php' => 'Inicio',
        'alumnos.php' => 'Alumnos',
        'materias.php' => 'Materias',
        'calificaciones.php' => 'Calificaciones',
        'usuarios.php' => 'Usuarios',
    ];
    $flash = get_flash();
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titulo) ?> · Control escolar</title>
<style>
  :root { --bg:#f4f6f9; --card:#fff; --text:#1f2937; --muted:#6b7280; --accent:#2563eb; --line:#e5e7eb; --nav:#1e3a8a; }
  @media (prefers-color-scheme: dark) {
    :root { --bg:#111827; --card:#1f2937; --text:#f3f4f6; --muted:#9ca3af; --accent:#60a5fa; --line:#374151; --nav:#0b1220; }
  }
  * { box-sizing: border-box; }
  body { margin:0; font-family: system-ui, sans-serif; background:var(--bg); color:var(--text); }
  nav { background:var(--nav); display:flex; flex-wrap:wrap; align-items:center; gap:4px; padding:8px 16px; }
  nav a, nav button { color:#fff; text-decoration:none; padding:8px 12px; border-radius:6px; background:none; border:0; font:inherit; cursor:pointer; }
  nav a.on, nav a:hover, nav button:hover { background:rgba(255,255,255,.18); }
  nav .brand { font-weight:700; margin-right:12px; }
  nav form { margin:0 0 0 auto; }
  main { max-width:1050px; margin:0 auto; padding:24px 16px; }
  h1 { margin-top:0; } h2 { margin-top:0; }
  .card { background:var(--card); border:1px solid var(--line); border-radius:10px; padding:20px; margin-bottom:24px; }
  .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:14px; }
  label { display:flex; flex-direction:column; font-size:.85rem; color:var(--muted); gap:4px; }
  input, select { padding:8px 10px; border:1px solid var(--line); border-radius:6px; background:var(--bg); color:var(--text); font-size:1rem; }
  button.btn, a.btn { display:inline-block; padding:9px 16px; border:0; border-radius:6px; background:var(--accent); color:#fff; font-size:1rem; cursor:pointer; text-decoration:none; }
  .btn.sec { background:var(--muted); } .btn.danger { background:#dc2626; }
  .btn.sm { padding:5px 10px; font-size:.85rem; }
  .full { grid-column:1/-1; }
  .msg { padding:10px 14px; border-radius:6px; margin-bottom:16px; }
  .msg.ok { background:#dcfce7; color:#166534; } .msg.error { background:#fee2e2; color:#991b1b; }
  .tabla { overflow-x:auto; } table { width:100%; border-collapse:collapse; }
  th, td { text-align:left; padding:10px; border-bottom:1px solid var(--line); white-space:nowrap; }
  th { color:var(--muted); font-size:.8rem; text-transform:uppercase; }
  td form { display:inline; margin:0; }
  .stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; margin-bottom:24px; }
  .stat { background:var(--card); border:1px solid var(--line); border-radius:10px; padding:18px; }
  .stat b { display:block; font-size:2rem; color:var(--accent); }
  .reprobado { color:#dc2626; font-weight:600; }
  .login { max-width:380px; margin:12vh auto; }
  .inline { display:flex; gap:8px; align-items:end; flex-wrap:wrap; margin-bottom:16px; }
</style>
</head>
<body>
<?php if ($nav): ?>
<nav>
  <span class="brand">🎓 Control escolar</span>
  <?php foreach ($menu as $url => $txt): ?>
    <a href="<?= $url ?>" class="<?= $actual === $url ? 'on' : '' ?>"><?= $txt ?></a>
  <?php endforeach; ?>
  <form method="post" action="logout.php">
    <?= csrf_campo() ?>
    <button type="submit">Salir (<?= e($_SESSION['nombre'] ?? '') ?>)</button>
  </form>
</nav>
<?php endif; ?>
<main>
<?php if ($flash): ?>
  <div class="msg <?= e($flash['tipo']) ?>"><?= e($flash['msg']) ?></div>
<?php endif;
}

function pie(): void
{
    echo "</main>\n</body>\n</html>";
}
