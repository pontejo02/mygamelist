<?php
require_once '../config.php';
require_once '../modelos/Session.php';
require_once '../modelos/Usuarios.php';
require_once '../modelos/Lista.php';
require_once '../modelos/Config.php';
include_once '_iconos.php';

Session::start();
Session::requireAdmin('../index.php');

$usuarios = new Usuarios();
$lista    = new Lista();
$ok = $err = '';

// ── Eliminar usuario ──────────────────────────────────────
if (isset($_GET['del_user'])) {
    $usuarios->eliminar((int)$_GET['del_user'])
        ? ($ok = 'Usuario eliminado correctamente.')
        : ($err = 'Error al eliminar el usuario.');
}

// ── Guardar edición de usuario ────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['accion']) && $_POST['accion']==='editar_usuario') {
    $id  = (int)$_POST['id_usuario'];
    $d   = array(
        'nombreUsuario' => trim(isset($_POST['nombreUsuario']) ? $_POST['nombreUsuario'] : ''),
        'email'         => trim(isset($_POST['email'])         ? $_POST['email']         : ''),
        'contrasenia'   => trim(isset($_POST['contrasenia'])   ? $_POST['contrasenia']   : ''),
    );
    $errVal = '';
    if (!preg_match('/^@[A-Za-z0-9._-]{3,20}$/', $d['nombreUsuario']))
        $errVal = 'El nombre debe empezar con @ y tener 3-20 caracteres.';
    elseif (!filter_var($d['email'], FILTER_VALIDATE_EMAIL))
        $errVal = 'El email no es válido.';
    elseif (!empty($d['contrasenia']) && strlen($d['contrasenia']) < 6)
        $errVal = 'La contraseña debe tener al menos 6 caracteres.';

    if ($errVal) {
        $err = $errVal;
    } elseif ($usuarios->editar($id, $d)) {
        $ok = 'Usuario actualizado correctamente.';
    } else {
        $err = 'Error al actualizar. Puede que el nombre o email ya existan.';
    }
}

// ── Guardar config colores ────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['accion']) && $_POST['accion']==='config') {
    $permitidos = array('color_gold', 'color_bg');
    foreach ($permitidos as $k) {
        if (isset($_POST[$k]) && preg_match('/^#[0-9a-fA-F]{6}$/', $_POST[$k])) {
            Config::set($k, $_POST[$k]);
        }
    }
    $ok = 'Colores guardados. Los cambios ya son visibles en toda la web.';
}

$seccion = isset($_GET['s']) ? $_GET['s'] : 'dashboard';
$todosU  = $usuarios->todos();
$stats   = $lista->stats();
$cfg     = Config::todos();
$base    = '..'; $pag = 'admin';

// Usuario a editar
$usuarioEditar = null;
if (isset($_GET['edit_user'])) {
    $usuarioEditar = $usuarios->obtener((int)$_GET['edit_user']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Panel Admin — MyGameList</title>
<link rel="stylesheet" href="../css/global.css">
<style>
<?= Config::cssVars() ?>
body { display:flex; min-height:100vh; }

/* ── Sidebar ── */
.admin-sidebar {
  width:230px; flex-shrink:0;
  background:var(--surface); border-right:1px solid var(--line);
  display:flex; flex-direction:column;
  position:sticky; top:0; height:100vh; overflow-y:auto;
}
.sidebar-logo {
  padding:1.5rem 1.25rem 1rem;
  font-family:var(--font-display); font-size:1.15rem; font-weight:700;
  border-bottom:1px solid var(--line);
}
.sidebar-logo em { color:var(--gold); font-style:italic; }
.sidebar-logo small {
  display:block; font-family:var(--font-body); font-size:.6rem;
  letter-spacing:.2em; text-transform:uppercase;
  color:var(--grey2); font-weight:400; margin-top:.2rem;
}
.sidebar-group { padding:.75rem 0; border-bottom:1px solid var(--line); }
.sidebar-label {
  font-size:.62rem; font-weight:600; letter-spacing:.15em;
  text-transform:uppercase; color:var(--grey2);
  padding:.2rem 1.25rem .5rem;
}
.sidebar-link {
  display:flex; align-items:center; gap:.6rem;
  padding:.55rem 1.25rem; font-size:.82rem; font-weight:500;
  color:var(--grey); transition:var(--transition); text-decoration:none;
  border-left:2px solid transparent;
}
.sidebar-link:hover { color:var(--white); background:rgba(255,255,255,.03); }
.sidebar-link.activo { color:var(--gold); border-color:var(--gold); background:var(--gold-bg); }
.sidebar-footer {
  margin-top:auto; padding:1rem 1.25rem;
  border-top:1px solid var(--line);
}
.sidebar-footer-user {
  display:flex; align-items:center; gap:.6rem;
  font-size:.8rem; color:var(--grey); margin-bottom:.6rem;
}
.sidebar-footer-user strong { color:var(--gold); }

/* ── Main ── */
.admin-main { flex:1; overflow-x:auto; padding:2rem 2.5rem; min-width:0; }
.admin-title {
  font-family:var(--font-display); font-size:1.75rem; font-weight:700;
  margin-bottom:.3rem;
}
.admin-title em { color:var(--gold); font-style:italic; }
.admin-subtitle { color:var(--grey); font-size:.875rem; margin-bottom:2rem; }

/* ── Stats ── */
.stats-row {
  display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr));
  gap:1rem; margin-bottom:2.5rem;
}
.stat-card {
  background:var(--surface); border:1px solid var(--line);
  border-top:2px solid var(--gold); border-radius:var(--radius2); padding:1.25rem;
}
.stat-card-icon { color:var(--gold); margin-bottom:.5rem; }
.stat-card-n { font-family:var(--font-mono); font-size:2rem; color:var(--gold); line-height:1; }
.stat-card-l { font-size:.68rem; font-weight:600; letter-spacing:.1em; text-transform:uppercase; color:var(--grey); margin-top:.3rem; }

/* ── Tabla usuarios ── */
.user-actions { display:flex; gap:.4rem; }

/* ── Panel editar usuario ── */
.edit-panel {
  background:var(--surface); border:1px solid var(--gold-dim);
  border-radius:var(--radius2); padding:1.75rem;
  margin-bottom:2rem;
}
.edit-panel-title {
  font-family:var(--font-display); font-size:1.2rem; font-weight:700;
  margin-bottom:1.25rem; display:flex; align-items:center; gap:.5rem;
}
.edit-panel-title em { color:var(--gold); font-style:italic; }
.form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }

/* ── Color picker ── */
.color-row { display:flex; align-items:center; gap:1rem; margin-bottom:1.25rem; }
.color-swatch {
  width:44px; height:44px; border-radius:var(--radius);
  border:1px solid var(--line2); cursor:pointer; flex-shrink:0;
  transition:transform var(--transition);
}
.color-swatch:hover { transform:scale(1.05); }
.color-info { flex:1; }
.color-label { font-size:.82rem; font-weight:500; margin-bottom:.2rem; }
.color-hex { font-family:var(--font-mono); font-size:.78rem; color:var(--grey); }
</style>
</head>
<body>

<!-- ── SIDEBAR ─────────────────────────────────────────── -->
<aside class="admin-sidebar">
  <div class="sidebar-logo">
    My<em>GameList</em>
    <small>Panel de administración</small>
  </div>

  <div class="sidebar-group">
    <div class="sidebar-label">Principal</div>
    <a href="admin.php?s=dashboard" class="sidebar-link <?= $seccion==='dashboard'?'activo':'' ?>">
      <?= icono('bar-chart', 16) ?> Dashboard
    </a>
  </div>

  <div class="sidebar-group">
    <div class="sidebar-label">Gestión</div>
    <a href="admin.php?s=usuarios" class="sidebar-link <?= $seccion==='usuarios'?'activo':'' ?>">
      <?= icono('users', 16) ?> Usuarios
    </a>
  </div>

  <div class="sidebar-group">
    <div class="sidebar-label">Configuración</div>
    <a href="admin.php?s=apariencia" class="sidebar-link <?= $seccion==='apariencia'?'activo':'' ?>">
      <?= icono('palette', 16) ?> Apariencia
    </a>
  </div>

  <div class="sidebar-footer">
    <div class="sidebar-footer-user">
      <?= icono('user', 15) ?>
      <span>Conectado como <strong><?= htmlspecialchars(Session::usuario()) ?></strong></span>
    </div>
    <a href="../usuario/logout.php" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center">
      <?= icono('log-out', 14) ?> Cerrar sesión
    </a>
  </div>
</aside>

<!-- ── CONTENIDO PRINCIPAL ─────────────────────────────── -->
<main class="admin-main">

  <?php if ($ok): ?>
    <div class="msg msg-ok" style="margin-bottom:1.5rem">
      <?= icono('check', 16) ?> <?= $ok ?>
    </div>
  <?php endif; ?>
  <?php if ($err): ?>
    <div class="msg msg-error" style="margin-bottom:1.5rem">
      <?= icono('info', 16) ?> <?= $err ?>
    </div>
  <?php endif; ?>

  <!-- ══ DASHBOARD ══════════════════════════════════════ -->
  <?php if ($seccion==='dashboard'): ?>

  <h1 class="admin-title">Panel de <em>control</em></h1>
  <p class="admin-subtitle">Resumen general de MyGameList</p>

  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-card-icon"><?= icono('users', 22) ?></div>
      <div class="stat-card-n"><?= $stats['usuarios'] ?></div>
      <div class="stat-card-l">Usuarios</div>
    </div>
    <div class="stat-card">
      <div class="stat-card-icon"><?= icono('gamepad', 22) ?></div>
      <div class="stat-card-n"><?= $stats['entradas'] ?></div>
      <div class="stat-card-l">Juegos en listas</div>
    </div>
    <div class="stat-card">
      <div class="stat-card-icon"><?= icono('message', 22) ?></div>
      <div class="stat-card-n"><?= $stats['resenas'] ?></div>
      <div class="stat-card-l">Reseñas escritas</div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
    <a href="admin.php?s=usuarios" class="stat-card" style="text-decoration:none;cursor:pointer;display:flex;align-items:center;gap:1rem;border-top-color:var(--gold)">
      <?= icono('users', 28) ?>
      <div><div style="font-weight:600">Gestionar usuarios</div><div style="font-size:.78rem;color:var(--grey)">Ver, editar y eliminar cuentas</div></div>
    </a>
    <a href="admin.php?s=apariencia" class="stat-card" style="text-decoration:none;cursor:pointer;display:flex;align-items:center;gap:1rem;border-top-color:var(--gold)">
      <?= icono('palette', 28) ?>
      <div><div style="font-weight:600">Cambiar colores</div><div style="font-size:.78rem;color:var(--grey)">Personalizar la apariencia</div></div>
    </a>
  </div>

  <!-- ══ USUARIOS ═══════════════════════════════════════ -->
  <?php elseif ($seccion==='usuarios'): ?>

  <h1 class="admin-title"><em>Usuarios</em> registrados</h1>
  <p class="admin-subtitle"><?= count($todosU) ?> usuarios en total</p>

  <!-- Panel de edición (si hay usuario seleccionado) -->
  <?php if ($usuarioEditar): ?>
  <div class="edit-panel">
    <div class="edit-panel-title">
      <?= icono('edit', 18) ?>
      Editando a <em><?= htmlspecialchars($usuarioEditar['nombreUsuario']) ?></em>
    </div>
    <form method="POST" action="admin.php?s=usuarios">
      <input type="hidden" name="accion"      value="editar_usuario">
      <input type="hidden" name="id_usuario"  value="<?= $usuarioEditar['id'] ?>">
      <div class="form-grid-2">
        <div class="form-field">
          <label class="form-label"><?= icono('user', 13) ?> Nombre de usuario</label>
          <input class="form-input" type="text" name="nombreUsuario"
                 value="<?= htmlspecialchars($usuarioEditar['nombreUsuario']) ?>"
                 placeholder="@nombreusuario">
        </div>
        <div class="form-field">
          <label class="form-label"><?= icono('mail', 13) ?> Email</label>
          <input class="form-input" type="email" name="email"
                 value="<?= htmlspecialchars($usuarioEditar['email']) ?>">
        </div>
        <div class="form-field">
          <label class="form-label"><?= icono('lock', 13) ?> Nueva contraseña</label>
          <input class="form-input" type="password" name="contrasenia"
                 placeholder="Dejar vacío para no cambiarla">
        </div>
        <div class="form-field" style="display:flex;align-items:flex-end;gap:.75rem">
          <button class="btn btn-primary" type="submit" style="flex:1">
            <?= icono('save', 15) ?> Guardar cambios
          </button>
          <a href="admin.php?s=usuarios" class="btn btn-ghost">Cancelar</a>
        </div>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Usuario</th>
          <th>Email</th>
          <th>Registro</th>
          <th>Juegos</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($todosU as $u): ?>
        <tr>
          <td style="color:var(--grey2);font-family:var(--font-mono);font-size:.78rem"><?= $u['id'] ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:.5rem">
              <?= icono('user', 14) ?>
              <span style="font-weight:500"><?= htmlspecialchars($u['nombreUsuario']) ?></span>
              <?php if ($u['nombreUsuario']==='@admin'): ?>
                <span class="badge badge-gold">Admin</span>
              <?php endif; ?>
            </div>
          </td>
          <td style="color:var(--grey);font-size:.82rem"><?= htmlspecialchars($u['email']) ?></td>
          <td style="color:var(--grey);font-size:.78rem"><?= date('d/m/Y', strtotime($u['creado'])) ?></td>
          <td style="font-family:var(--font-mono);font-size:.85rem"><?= $u['totalJuegos'] ?></td>
          <td>
            <div class="user-actions">
              <a href="admin.php?s=usuarios&edit_user=<?= $u['id'] ?>"
                 class="btn btn-ghost btn-sm" title="Editar usuario">
                <?= icono('edit', 14) ?> Editar
              </a>
              <?php if ($u['nombreUsuario'] !== '@admin'): ?>
              <a href="admin.php?s=usuarios&del_user=<?= $u['id'] ?>"
                 onclick="return confirm('¿Eliminar a <?= htmlspecialchars($u['nombreUsuario']) ?>? Esta acción no se puede deshacer.')"
                 class="btn btn-danger btn-sm" title="Eliminar usuario">
                <?= icono('trash', 14) ?> Eliminar
              </a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- ══ APARIENCIA ════════════════════════════════════ -->
  <?php elseif ($seccion==='apariencia'): ?>

  <h1 class="admin-title">Apariencia <em>del sitio</em></h1>
  <p class="admin-subtitle">Los cambios se aplican en toda la web al instante.</p>

  <form method="POST" action="admin.php?s=apariencia" style="max-width:500px">
    <input type="hidden" name="accion" value="config">

    <?php
    $valGold = isset($cfg['color_gold']) ? $cfg['color_gold'] : '#f0b429';
    $valBg   = isset($cfg['color_bg'])   ? $cfg['color_bg']   : '#080808';
    $camposAdmin = array(
        'color_gold' => array('Color dorado', 'Acento principal, botones, notas y destacados.', $valGold),
        'color_bg'   => array('Color de fondo', 'Color de fondo general de todas las páginas.', $valBg),
    );
    foreach ($camposAdmin as $k => $info):
        $label = $info[0]; $desc = $info[1]; $val = $info[2];
    ?>
    <div class="color-row">
      <div id="prev_<?= $k ?>" class="color-swatch"
           style="background:<?= htmlspecialchars($val) ?>"
           onclick="document.getElementById('inp_<?= $k ?>').click()"
           title="Haz clic para cambiar el color">
      </div>
      <input type="color" id="inp_<?= $k ?>" name="<?= $k ?>"
             value="<?= htmlspecialchars($val) ?>"
             style="position:absolute;opacity:0;pointer-events:none;width:1px;height:1px"
             oninput="document.getElementById('prev_<?= $k ?>').style.background=this.value;
                      document.getElementById('hex_<?= $k ?>').textContent=this.value;">
      <div class="color-info">
        <div class="color-label"><?= $label ?></div>
        <div style="font-size:.78rem;color:var(--grey);margin-bottom:.2rem"><?= $desc ?></div>
        <div class="color-hex" id="hex_<?= $k ?>"><?= htmlspecialchars($val) ?></div>
      </div>
    </div>
    <?php endforeach; ?>

    <div style="margin-top:1.5rem;display:flex;gap:.75rem;align-items:center">
      <button class="btn btn-primary" type="submit">
        <?= icono('save', 15) ?> Guardar cambios
      </button>
      <span style="font-size:.75rem;color:var(--grey)">
        <?= icono('info', 13) ?> Colores muy oscuros pueden afectar la legibilidad.
      </span>
    </div>
  </form>

  <?php endif; ?>

</main>
</body>
</html>
