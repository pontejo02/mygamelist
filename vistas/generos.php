<?php
require_once '../config.php';
require_once '../modelos/Session.php';
require_once '../modelos/RAWG.php';
require_once '../modelos/Config.php';
Session::start();
$usuario = Session::usuario();
$base = '..'; $pag = 'generos';
$slugActivo = $_GET['g'] ?? '';
$generos = [
  'action'=>['⚔️','Action'],'rpg'=>['🧙','RPG'],'shooter'=>['🔫','Shooter'],
  'adventure'=>['🗺️','Adventure'],'strategy'=>['♟️','Strategy'],'puzzle'=>['🧩','Puzzle'],
  'sports'=>['⚽','Sports'],'racing'=>['🏎️','Racing'],'simulation'=>['🏗️','Simulation'],
  'indie'=>['💡','Indie'],'horror'=>['👻','Horror'],'fighting'=>['🥊','Fighting'],
];
$juegos = $slugActivo ? RAWG::porGenero($slugActivo, 12) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="base" content="..">
<title>Géneros — MyGameList</title>
<link rel="stylesheet" href="../css/global.css">
<style><?= Config::cssVars() ?>
.generos-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(110px,1fr)); gap:.75rem; margin-bottom:3rem; }
.genero-btn { background:var(--surface); border:1px solid var(--line2); border-radius:var(--radius2); padding:1.25rem .5rem; text-align:center; cursor:pointer; text-decoration:none; color:inherit; transition:var(--transition); display:flex; flex-direction:column; align-items:center; gap:.4rem; }
.genero-btn:hover,.genero-btn.activo { border-color:var(--gold-dim); background:var(--gold-bg); }
.genero-btn.activo .g-nombre { color:var(--gold); }
.g-icono { font-size:1.6rem; line-height:1; }
.g-nombre { font-size:.7rem; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:var(--grey); }
</style>
</head>
<body>
<?php include '_navbar.php'; ?>
<main>
  <div class="container page-section">
    <div style="margin-bottom:2rem">
      <p class="section-label">Explora por categoría</p>
      <h1 class="section-heading">Géneros</h1>
    </div>
    <div class="generos-grid">
      <?php foreach ($generos as $slug => [$icono, $nombre]): ?>
      <a href="generos.php?g=<?= $slug ?>" class="genero-btn <?= $slugActivo===$slug?'activo':'' ?>">
        <span class="g-icono"><?= $icono ?></span>
        <span class="g-nombre"><?= $nombre ?></span>
      </a>
      <?php endforeach; ?>
    </div>
    <?php if ($slugActivo && isset($generos[$slugActivo])): ?>
      <div class="section-header" style="margin-bottom:1.5rem">
        <div>
          <p class="section-label">Resultados</p>
          <h2 class="section-heading"><?= $generos[$slugActivo][0] ?> <?= $generos[$slugActivo][1] ?></h2>
        </div>
      </div>
      <?php if ($juegos): ?>
        <div class="cards-grid">
          <?php foreach ($juegos as $i => $j): ?>
          <a href="juego.php?id=<?= $j['id'] ?>" class="game-card">
            <span class="card-rank-badge">#<?= $i+1 ?></span>
            <?= $j['imagen'] ? '<img class="card-cover" src="'.htmlspecialchars($j['imagen']).'" loading="lazy">' : '<div class="card-cover-placeholder">🎮</div>' ?>
            <div class="card-info">
              <div class="card-title"><?= htmlspecialchars($j['titulo']) ?></div>
              <div class="card-sub">
                <span class="card-genre"><?= $j['anio'] ?? '—' ?></span>
                <span class="card-score">★ <?= $j['nota'] ?></span>
              </div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p style="color:var(--grey)">No se encontraron juegos para este género.</p>
      <?php endif; ?>
    <?php elseif (!$slugActivo): ?>
      <p style="color:var(--grey);font-size:.875rem">Selecciona un género para ver los juegos.</p>
    <?php endif; ?>
  </div>
</main>
<?php include '_footer.php'; ?>
<script src="../js/global.js"></script>
</body></html>
