<?php
// modelos/Conexion.php
require_once __DIR__ . '/../config.php';

class Conexion {
    private static $bd = null;

    public static function get(): mysqli {
        if (self::$bd === null) {
            self::$bd = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if (self::$bd->connect_error) {
                die('<p style="color:red;font-family:monospace;padding:2rem">Error de conexión: ' . self::$bd->connect_error . '</p>');
            }
            self::$bd->set_charset('utf8mb4');
        }
        return self::$bd;
    }
}
?>
