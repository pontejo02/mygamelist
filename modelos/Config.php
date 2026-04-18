<?php
// modelos/Config.php — compatible PHP 7.2+
require_once __DIR__ . '/Conexion.php';

class Config {
    private static $cache = null;

    public static function get($clave, $defecto = '') {
        self::cargar();
        return isset(self::$cache[$clave]) ? self::$cache[$clave] : $defecto;
    }

    public static function set($clave, $valor) {
        $bd = Conexion::get();
        $s  = $bd->prepare("INSERT INTO config (clave,valor) VALUES (?,?) ON DUPLICATE KEY UPDATE valor=?");
        $s->bind_param('sss', $clave, $valor, $valor);
        $s->execute();
        self::$cache = null;
    }

    public static function todos() {
        self::cargar();
        return self::$cache;
    }

    private static function cargar() {
        if (self::$cache !== null) return;
        $bd   = Conexion::get();
        $rows = $bd->query("SELECT clave,valor FROM config")->fetch_all(MYSQLI_ASSOC);
        self::$cache = array_column($rows, 'valor', 'clave');
    }

    public static function cssVars() {
        $gold = self::get('color_gold', '#f0b429');
        $bg   = self::get('color_bg',   '#080808');
        return ":root { --gold: {$gold}; --gold-light: {$gold}; --bg: {$bg}; }";
    }
}
?>
