<?php
// modelos/Usuarios.php — compatible PHP 7.2+
require_once __DIR__ . '/Conexion.php';

class Usuarios {
    private $bd;

    public function __construct() { $this->bd = Conexion::get(); }

    public function login($nombre, $pass) {
        $s = $this->bd->prepare("SELECT * FROM usuarios WHERE nombreUsuario=? LIMIT 1");
        $s->bind_param('s', $nombre);
        $s->execute();
        $u = $s->get_result()->fetch_assoc();
        return ($u && password_verify($pass, $u['contrasenia'])) ? $u : false;
    }

    public function registrar($d) {
        $s = $this->bd->prepare("SELECT id FROM usuarios WHERE nombreUsuario=? OR email=?");
        $s->bind_param('ss', $d['nombreUsuario'], $d['email']);
        $s->execute();
        if ($s->get_result()->num_rows > 0) return 'duplicado';

        $hash = password_hash($d['contrasenia'], PASSWORD_DEFAULT);
        $s = $this->bd->prepare("INSERT INTO usuarios (nombreUsuario,email,contrasenia) VALUES (?,?,?)");
        $s->bind_param('sss', $d['nombreUsuario'], $d['email'], $hash);
        return $s->execute() ? $this->bd->insert_id : false;
    }

    public function todos() {
        return $this->bd->query(
            "SELECT u.*, COUNT(l.id) as totalJuegos FROM usuarios u
             LEFT JOIN lista l ON u.id=l.idUsuario GROUP BY u.id ORDER BY u.creado DESC"
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function editar($id, $d) {
        // Cambiar nombre y email
        $s = $this->bd->prepare(
            "UPDATE usuarios SET nombreUsuario=?, email=? WHERE id=?");
        $s->bind_param('ssi', $d['nombreUsuario'], $d['email'], $id);
        if (!$s->execute()) return false;

        // Cambiar contraseña solo si se rellenó
        if (!empty($d['contrasenia'])) {
            $hash = password_hash($d['contrasenia'], PASSWORD_DEFAULT);
            $s2   = $this->bd->prepare("UPDATE usuarios SET contrasenia=? WHERE id=?");
            $s2->bind_param('si', $hash, $id);
            $s2->execute();
        }
        return true;
    }

    public function obtener($id) {
        $s = $this->bd->prepare("SELECT id,nombreUsuario,email,creado FROM usuarios WHERE id=?");
        $s->bind_param('i', $id);
        $s->execute();
        $res = $s->get_result()->fetch_assoc();
        return $res ? $res : null;
    }

    public function eliminar($id) {
        $s = $this->bd->prepare("DELETE FROM usuarios WHERE id=? AND nombreUsuario != '@admin'");
        $s->bind_param('i', $id);
        return $s->execute();
    }
}
?>
