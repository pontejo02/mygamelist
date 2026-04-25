<?php
require_once 'config.php';
require_once 'modelos/Session.php';
require_once 'modelos/RAWG.php';
require_once 'modelos/Lista.php';
require_once 'modelos/Config.php';

Session::start();

$top      = RAWG::top(10);
$trending = RAWG::trending(6);
$hero     = array_slice($top, 0, 5);

$lista    = new Lista();
$resenas  = $lista->ultimas(4);
$actividad = $lista->actividad(6);

$usuario  = Session::usuario();
$base     = '.';
$pag      = 'inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="base" content=".">
<title>MyGameList — Tu historial de videojuegos</title>
<link rel="stylesheet" href="css/global.css">
<link rel="stylesheet" href="css/inicio.css">
<style><?= Config::cssVars() ?></style>
</head>
<body>

<?php include 'vistas/_navbar.php'; ?>

<!-- ── MODAL LOGIN ─────────────────────────────────────── -->
<?php if (!$usuario): ?>
<div class="modal-bg" id="modalLogin">
  <div class="modal">
    <button class="modal-close" id="cerrarLogin">✕</button>
    <h2 class="modal-title">Accede a tu <em>cuenta</em></h2>
    <form method="POST" action="usuario/login.php" id="formLogin">
      <div class="form-field">
        <label class="form-label">Nombre de usuario</label>
        <input class="form-input" type="text" name="nombreUsuario" placeholder="@tunombre" required>
      </div>
      <div class="form-field">
        <label class="form-label">Contraseña</label>
        <input class="form-input" type="password" name="contrasenia" required>
      </div>
      <button class="btn btn-primary" type="submit" style="width:100%">Entrar</button>
      <p style="text-align:center;margin-top:1rem;font-size:.82rem;color:var(--grey)">
        ¿No tienes cuenta? <a href="vistas/registro.php" style="color:var(--gold)">Regístrate</a>
      </p>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- ── HERO CARRUSEL ───────────────────────────────────── -->
<section class="hero" id="hero">
  <?php foreach ($hero as $i => $j): ?>
  <div class="hero-slide <?= $i===0 ? 'activo':'' ?>"
       <?= $j['imagen'] ? 'style="background-image:url('.htmlspecialchars($j['imagen']).')"' : '' ?>>
    <div class="hero-grad"></div>
    <div class="container hero-content">
      <p class="section-label">#<?= $i+1 ?> en el ranking global</p>
      <h1 class="hero-titulo"><?= htmlspecialchars($j['titulo']) ?></h1>
      <div class="hero-meta">
        <span class="badge badge-gold">★ <?= $j['nota'] ?></span>
        <span class="hero-meta-item"><?= htmlspecialchars($j['genero']) ?></span>
        <?php if ($j['anio']): ?>
          <span class="hero-meta-item"><?= $j['anio'] ?></span>
        <?php endif; ?>
      </div>
      <div class="hero-actions">
        <a href="vistas/juego.php?id=<?= $j['id'] ?>" class="btn btn-ghost">Ver detalles</a>
        <?php if ($usuario): ?>
          <a href="vistas/perfil.php?rawg_id=<?= $j['id'] ?>&rawg_titulo=<?= urlencode($j['titulo']) ?>" class="btn btn-primary">+ Mi lista</a>
        <?php else: ?>
          <button class="btn btn-primary" onclick="window.abrirLogin()">+ Mi lista</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>

  <button class="hero-arrow hero-prev" id="heroPrev">&#8249;</button>
  <button class="hero-arrow hero-next" id="heroNext">&#8250;</button>
  <div class="hero-dots" id="heroDots">
    <?php foreach ($hero as $i => $j): ?>
      <span class="hero-dot <?= $i===0?'activo':'' ?>" data-i="<?= $i ?>"></span>
    <?php endforeach; ?>
  </div>
</section>

<!-- ── TOP 10 ──────────────────────────────────────────── -->
<section class="page-section">
  <div class="container">
    <div class="section-header">
      <div>
        <p class="section-label">Los mejores</p>
        <h2 class="section-heading">Top <em>Ranking</em></h2>
      </div>
      <a href="vistas/ranking.php" class="section-link">Ver ranking completo →</a>
    </div>
    <div class="cards-grid">
      <?php foreach ($top as $i => $j): ?>
      <a href="vistas/juego.php?id=<?= $j['id'] ?>" class="game-card">
        <span class="card-rank-badge">#<?= $i+1 ?></span>
        <?php if ($j['imagen']): ?>
          <img class="card-cover" src="<?= htmlspecialchars($j['imagen']) ?>" alt="<?= htmlspecialchars($j['titulo']) ?>" loading="lazy">
        <?php else: ?>
          <div class="card-cover-placeholder">🎮</div>
        <?php endif; ?>
        <div class="card-overlay">
          <div class="card-overlay-title"><?= htmlspecialchars($j['titulo']) ?></div>
          <div class="card-overlay-meta">
            <span>🎮 <?= htmlspecialchars($j['genero']) ?></span>
            <span>★ <?= $j['nota'] ?>/10 · <?= $j['votos'] ?> votos</span>
          </div>
          <div class="card-overlay-btn">+ Añadir a mi lista</div>
        </div>
        <div class="card-info">
          <div class="card-title"><?= htmlspecialchars($j['titulo']) ?></div>
          <div class="card-sub">
            <span class="card-genre"><?= htmlspecialchars($j['genero']) ?></span>
            <span class="card-score">★ <?= $j['nota'] ?></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── TRENDING ────────────────────────────────────────── -->
<section class="page-section">
  <div class="container">
    <div class="section-header">
      <div>
        <p class="section-label">Esta semana</p>
        <h2 class="section-heading">🔥 <em>Trending</em></h2>
      </div>
    </div>
    <div class="trending-list">
      <?php foreach ($trending as $i => $j): ?>
      <a href="vistas/juego.php?id=<?= $j['id'] ?>" class="trending-row">
        <span class="trending-num"><?= str_pad($i+1, 2, '0', STR_PAD_LEFT) ?></span>
        <?php if ($j['imagen']): ?>
          <img class="trending-img" src="<?= htmlspecialchars($j['imagen']) ?>" alt="" loading="lazy">
        <?php else: ?>
          <div class="trending-img-ph">🎮</div>
        <?php endif; ?>
        <div class="trending-info">
          <div class="trending-title"><?= htmlspecialchars($j['titulo']) ?></div>
          <div class="trending-meta"><?= htmlspecialchars($j['genero']) ?> · <?= $j['anio'] ?? '—' ?></div>
        </div>
        <span class="card-score" style="flex-shrink:0">★ <?= $j['nota'] ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── ACTIVIDAD + RESEÑAS ─────────────────────────────── -->
<section class="page-section">
  <div class="container dos-col">

    <!-- Actividad -->
    <div>
      <div class="section-header" style="margin-bottom:1.25rem">
        <div>
          <p class="section-label">Comunidad</p>
          <h2 class="section-heading">Actividad <em>reciente</em></h2>
        </div>
      </div>
      <?php if ($actividad): ?>
        <div class="actividad-list">
          <?php
          $verbos = ['jugando'=>'está jugando','completado'=>'completó','abandonado'=>'abandonó','pendiente'=>'marcó como pendiente'];
          foreach ($actividad as $a):
            $verbo = $verbos[$a['estado']] ?? 'actualizó';
          ?>
          <div class="actividad-row">
            <div class="act-avatar">👤</div>
            <div class="act-text">
              <strong><?= htmlspecialchars($a['nombreUsuario']) ?></strong>
              <span class="act-verb"> <?= $verbo ?> </span>
              <span class="act-game"><?= htmlspecialchars($a['titulo']) ?></span>
            </div>
            <?php if ($a['nota']): ?>
              <span class="score-tag"><?= $a['nota'] ?></span>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p style="color:var(--grey);font-size:.875rem">¡Sé el primero en añadir juegos!</p>
      <?php endif; ?>
    </div>

    <!-- Últimas reseñas -->
    <div>
      <div class="section-header" style="margin-bottom:1.25rem">
        <div>
          <p class="section-label">La comunidad opina</p>
          <h2 class="section-heading">Últimas <em>reseñas</em></h2>
        </div>
        <a href="vistas/resenas.php" class="section-link">Ver todas →</a>
      </div>
      <?php if ($resenas): ?>
        <div class="resenas-mini">
          <?php foreach ($resenas as $r): ?>
          <div class="resena-mini-row">
            <div class="resena-mini-head">
              <span class="resena-mini-juego"><?= htmlspecialchars($r['titulo']) ?></span>
              <span class="score-tag"><?= $r['nota'] ?></span>
            </div>
            <p class="resena-mini-texto"><?= htmlspecialchars(mb_substr($r['resenia'], 0, 110)) ?>…</p>
            <span class="resena-mini-autor">— <?= htmlspecialchars($r['nombreUsuario']) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p style="color:var(--grey);font-size:.875rem">Aún no hay reseñas.</p>
      <?php endif; ?>
    </div>

  </div>
</section>

<?php include 'vistas/_footer.php'; ?>

<script src="js/global.js"></script>
<script src="js/inicio.js"></script>
</body>
</html>
