<?php
// vistas/_navbar.php
$base    = isset($base)    ? $base    : '.';
$usuario = Session::usuario();
$pag     = isset($pag)     ? $pag     : '';
include_once __DIR__ . '/_iconos.php';
?>
<nav class="navbar">
    <a href="<?= $base ?>/index.php" class="nav-logo">My<em>GameList</em></a>

    <div class="nav-search">
        <span class="nav-search-icon"><?= icono('search', 15) ?></span>
        <input type="text" id="navInput" placeholder="Buscar juego…" autocomplete="off">
        <div class="search-dropdown" id="navDropdown"></div>
    </div>

    <div class="nav-links">
        <a href="<?= $base ?>/vistas/ranking.php" class="nav-link <?= $pag==='ranking' ?'activo':'' ?>">
            <?= icono('trophy', 15) ?> Ranking
        </a>
        <a href="<?= $base ?>/vistas/generos.php" class="nav-link <?= $pag==='generos' ?'activo':'' ?>">
            <?= icono('layers', 15) ?> Géneros
        </a>
        <a href="<?= $base ?>/vistas/resenas.php" class="nav-link <?= $pag==='resenas' ?'activo':'' ?>">
            <?= icono('message', 15) ?> Reseñas
        </a>

        <?php if ($usuario): ?>
            <a href="<?= $base ?>/vistas/perfil.php" class="nav-link <?= $pag==='perfil' ?'activo':'' ?>">
                <?= icono('list', 15) ?> Mi Lista
            </a>
            <?php if (Session::esAdmin()): ?>
                <a href="<?= $base ?>/vistas/admin.php" class="nav-link nav-link-admin <?= $pag==='admin' ?'activo':'' ?>">
                    <?= icono('settings', 15) ?> Admin
                </a>
            <?php endif; ?>
            <span class="nav-user"><?= icono('user', 14) ?> <strong><?= htmlspecialchars($usuario) ?></strong></span>
            <a href="<?= $base ?>/usuario/logout.php" class="nav-btn nav-btn-outline">
                <?= icono('log-out', 14) ?> Salir
            </a>
        <?php else: ?>
            <button class="nav-btn nav-btn-outline" id="btnLogin">
                <?= icono('log-in', 14) ?> Entrar
            </button>
            <a href="<?= $base ?>/vistas/registro.php" class="nav-btn nav-btn-fill">
                <?= icono('user-plus', 14) ?> Registro
            </a>
        <?php endif; ?>
    </div>
</nav>
