<?php
require_once '../config.php';
require_once '../modelos/Session.php';
require_once '../modelos/Lista.php';
require_once '../modelos/Config.php';
Session::start();
Session::requireLogin('../index.php');

$idUsuario = Session::idUsuario();
$usuario   = Session::usuario();
$lista     = new Lista();
$conteo    = $lista->contarEstados($idUsuario);
$base = '..'; $pag = 'perfil';

$ok = $err = '';

// ── Añadir desde RAWG ──
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['accion']??'')==='añadir') {
    $d = [
        'rawgId'  => (int)$_POST['rawg_id'],
        'titulo'  => trim($_POST['rawg_titulo']  ?? ''),
        'imagen'  => trim($_POST['rawg_imagen']  ?? ''),
        'genero'  => trim($_POST['rawg_genero']  ?? ''),
        'anio'    => trim($_POST['rawg_anio']    ?? ''),
        'dev'     => trim($_POST['rawg_dev']     ?? ''),
        'estado'  => trim($_POST['estado']       ?? 'pendiente'),
        'nota'    => trim($_POST['nota']         ?? ''),
        'horas'   => trim($_POST['horas']        ?? ''),
        'resenia' => trim($_POST['resenia']      ?? ''),
    ];
    if (!$d['rawgId'] || !$d['titulo']) { $err = 'Selecciona un juego.'; }
    else {
        $r = $lista->añadir($idUsuario, $d);
        if ($r === 'duplicado') $err = 'Ya tienes ese juego en tu lista.';
        elseif ($r) { $ok = '"'.htmlspecialchars($d['titulo']).'" añadido.'; $conteo = $lista->contarEstados($idUsuario); }
        else $err = 'Error al guardar. Inténtalo de nuevo.';
    }
}

// ── Editar ──
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['accion']??'')==='editar') {
    $r = $lista->editar((int)$_POST['idLista'], $idUsuario, [
        'estado'  => $_POST['estado']  ?? 'pendiente',
        'nota'    => $_POST['nota']    ?? '',
        'horas'   => $_POST['horas']   ?? '',
        'resenia' => $_POST['resenia'] ?? '',
    ]);
    if ($r) { $ok = 'Lista actualizada.'; $conteo = $lista->contarEstados($idUsuario); }
    else $err = 'Error al actualizar.';
}

// ── Eliminar ──
if (isset($_GET['del'])) {
    $lista->eliminar((int)$_GET['del'], $idUsuario);
    header('Location: perfil.php?tab='.($_GET['tab']??'jugando')); exit;
}

$tabActual = $_GET['tab'] ?? 'jugando';
if (!in_array($tabActual, ['jugando','completado','abandonado','pendiente'])) $tabActual = 'jugando';
$juegos = $lista->porEstado($idUsuario, $tabActual);

// Editar: cargar entrada
$editando = null;
if (isset($_GET['edit'])) $editando = $lista->obtener((int)$_GET['edit'], $idUsuario);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="base" content="..">
<title>Mi Lista — MyGameList</title>
<link rel="stylesheet" href="../css/global.css">
<style><?= Config::cssVars() ?>
.perfil-header { background:var(--surface); border-bottom:1px solid var(--line); padding:2rem 0; margin-bottom:0; }
.perfil-top { display:flex; align-items:center; gap:1.5rem; }
.perfil-avatar { width:56px; height:56px; border-radius:50%; background:var(--surface2); border:1px solid var(--line2); display:flex; align-items:center; justify-content:center; font-size:1.5rem; flex-shrink:0; }
.perfil-nombre { font-family:var(--font-display); font-size:1.5rem; font-weight:700; }
.perfil-stats { display:flex; gap:1.5rem; margin-top:.4rem; flex-wrap:wrap; }
.pstat { text-align:center; }
.pstat-n { font-family:var(--font-mono); font-size:1.2rem; font-weight:500; color:var(--gold); }
.pstat-l { font-size:.65rem; letter-spacing:.08em; text-transform:uppercase; color:var(--grey); }
.btn-add-float { margin-left:auto; }
.panel-flotante { display:none; position:fixed; inset:0; z-index:400; background:rgba(0,0,0,.8); backdrop-filter:blur(6px); align-items:center; justify-content:center; padding:1rem; }
.panel-flotante.open { display:flex; }
.panel-box { background:var(--surface); border:1px solid var(--line2); border-radius:var(--radius2); width:100%; max-width:440px; padding:2rem; position:relative; max-height:90vh; overflow-y:auto; }
.buscador-wrap { position:relative; }
.buscador-drop { display:none; position:absolute; top:calc(100% + 4px); left:0; right:0; background:var(--surface); border:1px solid var(--line2); border-radius:var(--radius); max-height:260px; overflow-y:auto; z-index:10; }
.buscador-drop.open { display:block; }
.drop-item { display:flex; align-items:center; gap:.6rem; padding:.6rem .8rem; cursor:pointer; border-bottom:1px solid var(--line); transition:background var(--transition); }
.drop-item:hover { background:var(--surface2); }
.drop-item img { width:32px; height:44px; object-fit:cover; border-radius:2px; flex-shrink:0; }
.drop-item-info { flex:1; min-width:0; }
.drop-item-title { font-size:.82rem; font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.drop-item-meta { font-size:.7rem; color:var(--grey); }
.juego-selected { background:var(--gold-bg); border:1px solid var(--gold-dim); border-radius:var(--radius); padding:.6rem .9rem; display:flex; align-items:center; gap:.6rem; margin-top:.5rem; }
.juego-selected img { width:28px; height:38px; object-fit:cover; border-radius:2px; }
.juego-selected-name { flex:1; font-size:.85rem; font-weight:500; }
.lista-tabla { width:100%; border-collapse:collapse; font-size:.875rem; }
.lista-tabla th { font-size:.65rem; font-weight:600; letter-spacing:.1em; text-transform:uppercase; color:var(--grey); padding:.5rem 1rem; border-bottom:1px solid var(--line2); text-align:left; }
.lista-tabla td { padding:.75rem 1rem; border-bottom:1px solid var(--line); vertical-align:middle; }
.lista-tabla tr:last-child td { border-bottom:none; }
.lista-tabla tr:hover td { background:rgba(255,255,255,.02); }
.lista-vacia { text-align:center; padding:3rem 1rem; color:var(--grey); }
</style>
</head>
<body>
<?php include '_navbar.php'; ?>

<div class="perfil-header">
  <div class="container">
    <div class="perfil-top">
      <div class="perfil-avatar">👤</div>
      <div>
        <div class="perfil-nombre"><?= htmlspecialchars($usuario) ?></div>
        <div class="perfil-stats">
          <div class="pstat"><div class="pstat-n"><?= array_sum($conteo) ?></div><div class="pstat-l">Total</div></div>
          <div class="pstat"><div class="pstat-n" style="color:var(--gold)"><?= $conteo['jugando'] ?></div><div class="pstat-l">Jugando</div></div>
          <div class="pstat"><div class="pstat-n" style="color:var(--success)"><?= $conteo['completado'] ?></div><div class="pstat-l">Completados</div></div>
          <div class="pstat"><div class="pstat-n" style="color:var(--danger)"><?= $conteo['abandonado'] ?></div><div class="pstat-l">Abandonados</div></div>
          <div class="pstat"><div class="pstat-n" style="color:var(--grey)"><?= $conteo['pendiente'] ?></div><div class="pstat-l">Pendientes</div></div>
        </div>
      </div>
      <button class="btn btn-primary btn-add-float" id="btnAbrirPanel">+ Añadir juego</button>
    </div>
  </div>
</div>

<main>
  <div class="container page-section">
    <?php if ($ok): ?><div class="msg msg-ok"><?= $ok ?></div><?php endif; ?>
    <?php if ($err): ?><div class="msg msg-error"><?= $err ?></div><?php endif; ?>

    <div class="tabs">
      <?php foreach(['jugando'=>'Jugando','completado'=>'Completados','abandonado'=>'Abandonados','pendiente'=>'Pendientes'] as $k=>$lbl): ?>
        <a href="perfil.php?tab=<?= $k ?>" class="tab-btn <?= $tabActual===$k?'activo':'' ?>">
          <?= $lbl ?> <span class="tab-count"><?= $conteo[$k] ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($juegos)): ?>
      <div class="lista-vacia">
        <p style="font-size:1.1rem;margin-bottom:1rem">No tienes juegos aquí todavía.</p>
        <button class="btn btn-primary" id="btnVacioPanel">+ Añadir uno</button>
      </div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="lista-tabla">
        <thead><tr><th>#</th><th>Juego</th><th>Género</th><th>Nota</th><th>Horas</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($juegos as $i => $jj): ?>
          <tr>
            <td style="color:var(--grey2);font-family:var(--font-mono)"><?= $i+1 ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:.65rem">
                <?php if ($jj['imagen']): ?>
                  <img src="<?= htmlspecialchars($jj['imagen']) ?>" style="width:32px;height:44px;object-fit:cover;border-radius:2px;flex-shrink:0" loading="lazy">
                <?php endif; ?>
                <span style="font-weight:500"><?= htmlspecialchars($jj['titulo']) ?></span>
              </div>
            </td>
            <td style="color:var(--grey);font-size:.8rem"><?= htmlspecialchars($jj['genero']) ?></td>
            <td><?= $jj['nota'] ? '<span class="score-tag">'.$jj['nota'].'</span>' : '<span style="color:var(--grey2)">—</span>' ?></td>
            <td style="color:var(--grey)"><?= $jj['horas'] ?>h</td>
            <td>
              <div style="display:flex;gap:.4rem">
                <a href="perfil.php?edit=<?= $jj['id'] ?>&tab=<?= $tabActual ?>" class="btn btn-ghost btn-sm btn-icon" title="Editar">✏️</a>
                <a href="perfil.php?del=<?= $jj['id'] ?>&tab=<?= $tabActual ?>"
                   onclick="return confirm('¿Eliminar <?= htmlspecialchars($jj['titulo']) ?>?')"
                   class="btn btn-danger btn-sm btn-icon" title="Eliminar">🗑</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</main>

<!-- ── PANEL AÑADIR ──────────────────────────────────── -->
<div class="panel-flotante" id="panelAñadir">
  <div class="panel-box">
    <button class="modal-close" id="cerrarPanel">✕</button>
    <h2 class="modal-title" style="margin-bottom:1.25rem">Añadir <em>juego</em></h2>
    <form method="POST" id="formAñadir">
      <input type="hidden" name="accion" value="añadir">
      <input type="hidden" name="rawg_id"     id="f_rawg_id">
      <input type="hidden" name="rawg_titulo" id="f_rawg_titulo">
      <input type="hidden" name="rawg_imagen" id="f_rawg_imagen">
      <input type="hidden" name="rawg_genero" id="f_rawg_genero">
      <input type="hidden" name="rawg_anio"   id="f_rawg_anio">
      <input type="hidden" name="rawg_dev"    id="f_rawg_dev">
      <div class="form-field">
        <label class="form-label">Buscar juego *</label>
        <div class="buscador-wrap">
          <input class="form-input" type="text" id="busqInput" placeholder="Escribe el nombre…" autocomplete="off">
          <div class="buscador-drop" id="busqDrop"></div>
        </div>
        <div id="juegoSel" style="display:none" class="juego-selected"></div>
      </div>
      <div class="form-field">
        <label class="form-label">Estado *</label>
        <select class="form-select" name="estado">
          <option value="jugando">Jugando</option>
          <option value="completado">Completado</option>
          <option value="pendiente" selected>Pendiente</option>
          <option value="abandonado">Abandonado</option>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-field">
          <label class="form-label">Nota (1-10)</label>
          <input class="form-input" type="number" name="nota" min="1" max="10" placeholder="Opcional">
        </div>
        <div class="form-field">
          <label class="form-label">Horas jugadas</label>
          <input class="form-input" type="number" name="horas" min="0" placeholder="0">
        </div>
      </div>
      <div class="form-field">
        <label class="form-label">Reseña</label>
        <textarea class="form-textarea" name="resenia" placeholder="¿Qué te pareció?"></textarea>
      </div>
      <button class="btn btn-primary" type="submit" style="width:100%">Guardar en mi lista</button>
    </form>
  </div>
</div>

<!-- ── PANEL EDITAR ──────────────────────────────────── -->
<?php if ($editando): ?>
<div class="panel-flotante open" id="panelEditar">
  <div class="panel-box">
    <button class="modal-close" onclick="document.getElementById('panelEditar').classList.remove('open')">✕</button>
    <h2 class="modal-title" style="margin-bottom:1.25rem">Editar <em><?= htmlspecialchars($editando['titulo']) ?></em></h2>
    <form method="POST">
      <input type="hidden" name="accion"  value="editar">
      <input type="hidden" name="idLista" value="<?= $editando['id'] ?>">
      <div class="form-field">
        <label class="form-label">Estado</label>
        <select class="form-select" name="estado">
          <?php foreach(['jugando','completado','pendiente','abandonado'] as $e): ?>
            <option value="<?= $e ?>" <?= $editando['estado']===$e?'selected':'' ?>><?= ucfirst($e) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="form-field">
          <label class="form-label">Nota (1-10)</label>
          <input class="form-input" type="number" name="nota" min="1" max="10" value="<?= $editando['nota'] ?? '' ?>">
        </div>
        <div class="form-field">
          <label class="form-label">Horas jugadas</label>
          <input class="form-input" type="number" name="horas" min="0" value="<?= $editando['horas'] ?? 0 ?>">
        </div>
      </div>
      <div class="form-field">
        <label class="form-label">Reseña</label>
        <textarea class="form-textarea" name="resenia"><?= htmlspecialchars($editando['resenia'] ?? '') ?></textarea>
      </div>
      <div style="display:flex;gap:.75rem">
        <button class="btn btn-primary" type="submit" style="flex:1">Guardar cambios</button>
        <a href="perfil.php?tab=<?= $tabActual ?>" class="btn btn-ghost">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php include '_footer.php'; ?>
<script src="../js/global.js"></script>
<script src="../js/perfil.js"></script>
</body></html>
