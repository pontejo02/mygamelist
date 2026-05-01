<?php
require_once '../config.php';
require_once '../modelos/Session.php';
require_once '../modelos/Lista.php';
require_once '../modelos/Config.php';
Session::start();
$usuario = Session::usuario();
$base = '..'; $pag = 'resenas';
$lista   = new Lista();
$resenas = $lista->ultimas(30);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="base" content="..">
<title>Reseñas — MyGameList</title>
<link rel="stylesheet" href="../css/global.css">
<style><?= Config::cssVars() ?>
.resenas-grid { display:flex; flex-direction:column; gap:0; }
.resena-card { padding:1.25rem 0; border-bottom:1px solid var(--line); }
.resena-card:first-child { border-top:1px solid var(--line); }
.resena-head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:.5rem; gap:1rem; }
.resena-juego { font-family:var(--font-display); font-size:1.05rem; font-weight:700; }
.resena-body { font-size:.875rem; color:var(--grey); line-height:1.65; margin-bottom:.5rem; }
.resena-foot { font-size:.72rem; color:var(--grey2); }
.resena-foot strong { color:var(--gold); }
.estado-chip { font-size:.65rem; font-weight:600; letter-spacing:.08em; text-transform:uppercase; padding:.15rem .5rem; border-radius:2px; }
</style>
</head>
<body>
<?php include '_navbar.php'; ?>
<main>
  <div class="container page-section">
    <div style="margin-bottom:2rem">
      <p class="section-label">La comunidad opina</p>
      <h1 class="section-heading">Reseñas <em>recientes</em></h1>
    </div>
    <?php if ($resenas): ?>
    <div class="resenas-grid">
      <?php foreach ($resenas as $r):
        $chips = ['completado'=>'badge-done','jugando'=>'badge-playing','abandonado'=>'badge-dropped','pendiente'=>'badge-pending'];
        $chip  = $chips[$r['estado']] ?? 'badge-pending';
      ?>
      <div class="resena-card">
        <div class="resena-head">
          <div>
            <div class="resena-juego"><?= htmlspecialchars($r['titulo']) ?></div>
            <div style="font-size:.72rem;color:var(--grey);margin-top:.2rem"><?= htmlspecialchars($r['genero']) ?></div>
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.4rem;flex-shrink:0">
            <span class="score-tag">★ <?= $r['nota'] ?></span>
            <span class="badge <?= $chip ?>"><?= ucfirst($r['estado']) ?></span>
          </div>
        </div>
        <p class="resena-body">"<?= htmlspecialchars($r['resenia']) ?>"</p>
        <div class="resena-foot">— <strong><?= htmlspecialchars($r['nombreUsuario']) ?></strong> · <?= date('d/m/Y', strtotime($r['actualizado'])) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <p style="color:var(--grey)">Aún no hay reseñas. ¡Sé el primero!</p>
    <?php endif; ?>
  </div>
</main>
<?php include '_footer.php'; ?>
<script src="../js/global.js"></script>
</body></html>
