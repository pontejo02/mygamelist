<?php
// modelos/Session.php — compatible PHP 7.2+

class Session {

    public static function start() {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public static function usuario() {
        return isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
    }

    public static function idUsuario() {
        return isset($_SESSION['id']) ? (int)$_SESSION['id'] : null;
    }

    public static function esAdmin() {
        return (isset($_SESSION['usuario']) && $_SESSION['usuario'] === '@admin');
    }

    public static function login($u) {
        $_SESSION['usuario'] = $u['nombreUsuario'];
        $_SESSION['id']      = $u['id'];
    }

    public static function logout() {
        session_unset();
        session_destroy();
    }

    public static function requireLogin($redir = '../index.php') {
        if (!self::usuario()) { header("Location: $redir"); exit; }
    }

    public static function requireAdmin($redir = '../index.php') {
        if (!self::esAdmin()) { header("Location: $redir"); exit; }
    }
}
?>
