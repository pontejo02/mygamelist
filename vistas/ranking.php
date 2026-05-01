<?php
require_once '../config.php';
require_once '../modelos/Session.php';
require_once '../modelos/RAWG.php';
require_once '../modelos/Config.php';
Session::start();
$pagina  = max(1, (int)($_GET['p'] ?? 1));
$datos   = RAWG::rankingPaginado($pagina, 20);
$juegos  = $datos['juegos'];
$usuario = Session::usuario();
$base = '..'; $pag = 'ranking';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="base" content="..">
<title>Ranking — MyGameList</title>
<link rel="stylesheet" href="../css/global.css">
<style><?= Config::cssVars() ?>
.ranking-table-row { display:flex; align-items:center; gap:1rem; padding:.85rem 0; border-bottom:1px solid var(--line); text-decoration:none; color:inherit; transition:background var(--transition); }
.ranking-table-row:first-child { border-top:1px solid var(--line); }
.ranking-table-row:hover { padding-left:.5rem; background:rgba(255,255,255,.02); }
.rk-num { font-family:var(--font-mono); font-size:.85rem; color:var(--grey2); width:32px; flex-shrink:0; text-align:right; }
.rk-cover { width:48px; height:64px; object-fit:cover; border-radius:var(--radius); flex-shrink:0; background:var(--surface2); display:flex; align-items:center; justify-content:center; font-size:1.2rem; color:var(--grey2); }
.rk-cover img { width:100%; height:100%; object-fit:cover; border-radius:var(--radius); }
.rk-info { flex:1; min-width:0; }
.rk-titulo { font-weight:500; font-size:.9rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.rk-meta { font-size:.72rem; color:var(--grey); margin-top:.15rem; }
.paginacion { display:flex; gap:.5rem; justify-content:center; margin-top:2.5rem; flex-wrap:wrap; }
.pag-btn { padding:.45rem .9rem; border-radius:var(--radius); font-size:.8rem; font-weight:500; background:var(--surface2); border:1px solid var(--line2); color:var(--grey); text-decoration:none; transition:var(--transition); }
.pag-btn:hover { border-color:var(--grey); color:var(--white); }
.pag-btn.activo { background:var(--gold); color:var(--bg); border-color:var(--gold); }
</style>
</head>
<body>
<?php include '_navbar.php'; ?>
<main>
  <div class="container page-section">
    <div class="section-header">
      <div>
        <p class="section-label">Los mejores del mundo</p>
        <h1 class="section-heading">Ranking <em>global</em></h1>
      </div>
    </div>
    <div>
      <?php foreach ($juegos as $i => $j): $n = ($pagina-1)*20 + $i + 1; ?>
      <a href="juego.php?id=<?= $j['id'] ?>" class="ranking-table-row">
        <span class="rk-num"><?= $n ?></span>
        <div class="rk-cover">
          <?= $j['imagen'] ? '<img src="'.htmlspecialchars($j['imagen']).'" loading="lazy">' : '🎮' ?>
        </div>
        <div class="rk-info">
          <div class="rk-titulo"><?= htmlspecialchars($j['titulo']) ?></div>
          <div class="rk-meta"><?= htmlspecialchars($j['genero']) ?> · <?= $j['anio'] ?? '—' ?> · <?= $j['votos'] ?> votos</div>
        </div>
        <span class="score-tag">★ <?= $j['nota'] ?></span>
      </a>
      <?php endforeach; ?>
      <?php if (empty($juegos)): ?>
        <p style="color:var(--grey);padding:2rem 0">No se pudieron cargar los juegos.</p>
      <?php endif; ?>
    </div>
    <div class="paginacion">
      <?php for ($p=1; $p<=5; $p++): ?>
        <a href="ranking.php?p=<?= $p ?>" class="pag-btn <?= $p===$pagina?'activo':'' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
  </div>
</main>
<?php include '_footer.php'; ?>
<script src="../js/global.js"></script>
</body></html>
