<?php
require_once '../config.php';
require_once '../modelos/Session.php';
require_once '../modelos/RAWG.php';
require_once '../modelos/Config.php';
Session::start();
$id      = (int)($_GET['id'] ?? 0);
$j       = $id ? RAWG::detalle($id) : null;
$usuario = Session::usuario();
$base = '..'; $pag = '';
if (!$j) { header('Location: ../index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="base" content="..">
<title><?= htmlspecialchars($j['titulo']) ?> — MyGameList</title>
<link rel="stylesheet" href="../css/global.css">
<style><?= Config::cssVars() ?>
.juego-hero { position:relative; height:380px; background:var(--surface) center/cover no-repeat; overflow:hidden; }
.juego-hero img { width:100%; height:100%; object-fit:cover; }
.juego-hero-grad { position:absolute; inset:0; background:linear-gradient(to top,rgba(8,8,8,1) 0%,rgba(8,8,8,.4) 60%,transparent 100%); }
.juego-main { display:grid; grid-template-columns:1fr 280px; gap:3rem; margin-top:2rem; }
.juego-titulo { font-family:var(--font-display); font-size:clamp(1.8rem,4vw,2.8rem); font-weight:900; line-height:1.1; margin-bottom:.75rem; }
.juego-chips { display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:1.5rem; }
.juego-chip { background:var(--surface2); border:1px solid var(--line2); border-radius:var(--radius); font-size:.72rem; font-weight:600; letter-spacing:.06em; text-transform:uppercase; padding:.25rem .65rem; color:var(--grey); }
.juego-desc { font-size:.9rem; color:var(--grey); line-height:1.75; }
.juego-sidebar { background:var(--surface); border:1px solid var(--line2); border-radius:var(--radius2); padding:1.5rem; height:fit-content; }
.sidebar-score { font-family:var(--font-display); font-size:3rem; font-weight:900; color:var(--gold); line-height:1; margin-bottom:.25rem; }
.sidebar-row { display:flex; justify-content:space-between; padding:.6rem 0; border-bottom:1px solid var(--line); font-size:.82rem; }
.sidebar-row:last-of-type { border-bottom:none; }
.sidebar-row dt { color:var(--grey); }
.sidebar-row dd { font-weight:500; text-align:right; }
.screenshots { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:.75rem; margin-top:2rem; }
.screenshots img { width:100%; border-radius:var(--radius); aspect-ratio:16/9; object-fit:cover; }
@media(max-width:768px){ .juego-main{grid-template-columns:1fr;} .juego-hero{height:240px;} }
</style>
</head>
<body>
<?php include '_navbar.php'; ?>
<main>
  <!-- Imagen de fondo -->
  <?php if ($j['imagen']): ?>
  <div class="juego-hero">
    <img src="<?= htmlspecialchars($j['imagen']) ?>" alt="">
    <div class="juego-hero-grad"></div>
  </div>
  <?php endif; ?>

  <div class="container" style="margin-top:<?= $j['imagen'] ? '-80px' : '2rem' ?>;position:relative;z-index:2;padding-bottom:4rem">
    <div class="juego-main">
      <!-- Info principal -->
      <div>
        <h1 class="juego-titulo"><?= htmlspecialchars($j['titulo']) ?></h1>
        <div class="juego-chips">
          <?php foreach (explode(', ', $j['generos']) as $g): ?>
            <span class="juego-chip"><?= htmlspecialchars(trim($g)) ?></span>
          <?php endforeach; ?>
          <?php if ($j['anio']): ?>
            <span class="juego-chip"><?= $j['anio'] ?></span>
          <?php endif; ?>
        </div>
        <?php if ($j['descripcion']): ?>
          <p class="juego-desc"><?= htmlspecialchars($j['descripcion']) ?>…</p>
        <?php endif; ?>

        <!-- Screenshots -->
        <?php if ($j['screenshots']): ?>
        <div class="screenshots">
          <?php foreach ($j['screenshots'] as $ss): ?>
            <img src="<?= htmlspecialchars($ss) ?>" loading="lazy" alt="">
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Sidebar -->
      <div class="juego-sidebar">
        <p class="section-label" style="margin-bottom:.25rem">Puntuación</p>
        <div class="sidebar-score">★ <?= $j['nota'] ?></div>
        <?php if ($j['metacritic']): ?>
          <p style="font-size:.72rem;color:var(--grey);margin-bottom:1rem">Metacritic: <?= $j['metacritic'] ?></p>
        <?php endif; ?>

        <?php if ($usuario): ?>
          <a href="perfil.php?rawg_id=<?= $j['id'] ?>&rawg_titulo=<?= urlencode($j['titulo']) ?>"
             class="btn btn-primary" style="width:100%;margin-bottom:1rem;justify-content:center">+ Añadir a mi lista</a>
        <?php else: ?>
          <button class="btn btn-primary" style="width:100%;margin-bottom:1rem"
                  onclick="window.abrirLogin()">+ Añadir a mi lista</button>
        <?php endif; ?>

        <dl>
          <?php if ($j['desarrollador']): ?>
          <div class="sidebar-row"><dt>Desarrollador</dt><dd><?= htmlspecialchars($j['desarrollador']) ?></dd></div>
          <?php endif; ?>
          <?php if ($j['anio']): ?>
          <div class="sidebar-row"><dt>Año</dt><dd><?= $j['anio'] ?></dd></div>
          <?php endif; ?>
          <?php if ($j['plataformas']): ?>
          <div class="sidebar-row"><dt>Plataformas</dt><dd style="font-size:.72rem"><?= htmlspecialchars(mb_substr($j['plataformas'],0,60)) ?></dd></div>
          <?php endif; ?>
          <?php if ($j['web']): ?>
          <div class="sidebar-row"><dt>Web</dt><dd><a href="<?= htmlspecialchars($j['web']) ?>" target="_blank" rel="noopener" style="color:var(--gold)">Visitar →</a></dd></div>
          <?php endif; ?>
        </dl>
      </div>
    </div>
  </div>
</main>
<?php include '_footer.php'; ?>
<script src="../js/global.js"></script>
</body></html>
