<?php
require_once '../config.php';
require_once '../modelos/Session.php';
require_once '../modelos/Usuarios.php';
require_once '../modelos/Config.php';
Session::start();
if (Session::usuario()) { header('Location: ../index.php'); exit; }
$usuarios = new Usuarios();
$err = []; $data = ['nombreUsuario'=>'','email'=>''];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $data['nombreUsuario'] = trim($_POST['nombreUsuario'] ?? '');
    $data['email']         = trim($_POST['email'] ?? '');
    $pass    = trim($_POST['contrasenia']        ?? '');
    $confirm = trim($_POST['confirmar']          ?? '');
    if (!preg_match('/^@[A-Za-z0-9._-]{3,20}$/', $data['nombreUsuario']))
        $err['nombreUsuario'] = 'Debe empezar con @ y tener 3-20 caracteres.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
        $err['email'] = 'Email no válido.';
    if (strlen($pass) < 6)
        $err['contrasenia'] = 'Mínimo 6 caracteres.';
    elseif ($pass !== $confirm)
        $err['confirmar'] = 'Las contraseñas no coinciden.';
    if (!$err) {
        $r = $usuarios->registrar(['nombreUsuario'=>$data['nombreUsuario'],'email'=>$data['email'],'contrasenia'=>$pass]);
        if ($r === 'duplicado') $err['global'] = 'Ese usuario o email ya está registrado.';
        elseif ($r) {
            Session::login(['nombreUsuario'=>$data['nombreUsuario'],'id'=>$r]);
            header('Location: ../vistas/perfil.php'); exit;
        } else $err['global'] = 'Error al crear la cuenta.';
    }
}
$base = '..'; $pag = '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="base" content="..">
<title>Registro — MyGameList</title>
<link rel="stylesheet" href="../css/global.css">
<style><?= Config::cssVars() ?>
.auth-wrap { min-height:calc(100vh - 64px - 50px); display:flex; align-items:center; justify-content:center; padding:2rem 1rem; }
.auth-box { background:var(--surface); border:1px solid var(--line2); border-top:2px solid var(--gold); border-radius:var(--radius2); padding:2.5rem 2rem; width:100%; max-width:420px; }
.auth-title { font-family:var(--font-display); font-size:1.75rem; font-weight:700; margin-bottom:.3rem; }
.auth-title em { color:var(--gold); font-style:italic; }
.auth-sub { font-size:.82rem; color:var(--grey); margin-bottom:1.75rem; }
.auth-sub a { color:var(--gold); }
</style>
</head>
<body>
<?php include '_navbar.php'; ?>
<div class="auth-wrap">
  <div class="auth-box">
    <h1 class="auth-title">Crea tu <em>cuenta</em></h1>
    <p class="auth-sub">¿Ya tienes cuenta? <a href="../index.php">Inicia sesión</a></p>
    <?php if (isset($err['global'])): ?><div class="form-error-global"><?= $err['global'] ?></div><?php endif; ?>
    <form method="POST">
      <div class="form-field">
        <label class="form-label">Nombre de usuario</label>
        <input class="form-input" type="text" name="nombreUsuario" placeholder="@tunombre" value="<?= htmlspecialchars($data['nombreUsuario']) ?>">
        <?php if (isset($err['nombreUsuario'])): ?><p class="form-error"><?= $err['nombreUsuario'] ?></p><?php endif; ?>
      </div>
      <div class="form-field">
        <label class="form-label">Email</label>
        <input class="form-input" type="email" name="email" placeholder="tu@email.com" value="<?= htmlspecialchars($data['email']) ?>">
        <?php if (isset($err['email'])): ?><p class="form-error"><?= $err['email'] ?></p><?php endif; ?>
      </div>
      <div class="form-field">
        <label class="form-label">Contraseña</label>
        <input class="form-input" type="password" name="contrasenia" placeholder="Mínimo 6 caracteres">
        <?php if (isset($err['contrasenia'])): ?><p class="form-error"><?= $err['contrasenia'] ?></p><?php endif; ?>
      </div>
      <div class="form-field">
        <label class="form-label">Confirmar contraseña</label>
        <input class="form-input" type="password" name="confirmar">
        <?php if (isset($err['confirmar'])): ?><p class="form-error"><?= $err['confirmar'] ?></p><?php endif; ?>
      </div>
      <button class="btn btn-primary" type="submit" style="width:100%">Crear cuenta</button>
    </form>
  </div>
</div>
<?php include '_footer.php'; ?>
<script src="../js/global.js"></script>
</body></html>
