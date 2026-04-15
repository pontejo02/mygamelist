<?php
// modelos/Lista.php — compatible PHP 7.2+
require_once __DIR__ . '/Conexion.php';

class Lista {
    private $bd;

    public function __construct() { $this->bd = Conexion::get(); }

    public function porEstado($idUsuario, $estado) {
        $s = $this->bd->prepare(
            "SELECT l.*, j.titulo, j.genero, j.imagen, j.anio, j.desarrollador
             FROM lista l JOIN juegos j ON l.idJuego=j.id
             WHERE l.idUsuario=? AND l.estado=?
             ORDER BY l.actualizado DESC");
        $s->bind_param('is', $idUsuario, $estado);
        $s->execute();
        return $s->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function contarEstados($idUsuario) {
        $s = $this->bd->prepare("SELECT estado, COUNT(*) as n FROM lista WHERE idUsuario=? GROUP BY estado");
        $s->bind_param('i', $idUsuario);
        $s->execute();
        $rows = $s->get_result()->fetch_all(MYSQLI_ASSOC);
        $out  = ['jugando'=>0,'completado'=>0,'abandonado'=>0,'pendiente'=>0];
        foreach ($rows as $r) $out[$r['estado']] = (int)$r['n'];
        return $out;
    }

    public function obtener($idLista, $idUsuario) {
        $s = $this->bd->prepare(
            "SELECT l.*, j.titulo FROM lista l JOIN juegos j ON l.idJuego=j.id
             WHERE l.id=? AND l.idUsuario=?");
        $s->bind_param('ii', $idLista, $idUsuario);
        $s->execute();
        $res = $s->get_result()->fetch_assoc();
        return $res ? $res : null;
    }

    public function añadir($idUsuario, $d) {
        // Buscar o crear juego en BD local
        $s = $this->bd->prepare("SELECT id FROM juegos WHERE rawgId=?");
        $s->bind_param('i', $d['rawgId']);
        $s->execute();
        $fila = $s->get_result()->fetch_assoc();

        if ($fila) {
            $idJuego = $fila['id'];
        } else {
            $s = $this->bd->prepare("INSERT INTO juegos (rawgId,titulo,genero,imagen,anio,desarrollador) VALUES (?,?,?,?,?,?)");
            $s->bind_param('isssss', $d['rawgId'], $d['titulo'], $d['genero'], $d['imagen'], $d['anio'], $d['dev']);
            $s->execute();
            $idJuego = $this->bd->insert_id;
        }

        // Comprobar duplicado
        $s = $this->bd->prepare("SELECT id FROM lista WHERE idUsuario=? AND idJuego=?");
        $s->bind_param('ii', $idUsuario, $idJuego);
        $s->execute();
        if ($s->get_result()->num_rows > 0) return 'duplicado';

        $nota  = ($d['nota']  !== '') ? (int)$d['nota']  : null;
        $horas = ($d['horas'] !== '') ? (int)$d['horas'] : 0;
        $s = $this->bd->prepare(
            "INSERT INTO lista (idUsuario,idJuego,estado,nota,horas,resenia) VALUES (?,?,?,?,?,?)");
        $s->bind_param('iisiss', $idUsuario, $idJuego, $d['estado'], $nota, $horas, $d['resenia']);
        return $s->execute();
    }

    public function editar($idLista, $idUsuario, $d) {
        $nota  = ($d['nota']  !== '') ? (int)$d['nota']  : null;
        $horas = ($d['horas'] !== '') ? (int)$d['horas'] : 0;
        $s = $this->bd->prepare(
            "UPDATE lista SET estado=?,nota=?,horas=?,resenia=? WHERE id=? AND idUsuario=?");
        $s->bind_param('siisii', $d['estado'], $nota, $horas, $d['resenia'], $idLista, $idUsuario);
        return $s->execute();
    }

    public function eliminar($idLista, $idUsuario) {
        $s = $this->bd->prepare("DELETE FROM lista WHERE id=? AND idUsuario=?");
        $s->bind_param('ii', $idLista, $idUsuario);
        return $s->execute();
    }

    public function ultimas($n = 5) {
        $s = $this->bd->prepare(
            "SELECT l.nota, l.resenia, l.estado, l.actualizado,
                    u.nombreUsuario, j.titulo, j.genero
             FROM lista l
             JOIN usuarios u ON l.idUsuario=u.id
             JOIN juegos   j ON l.idJuego=j.id
             WHERE l.resenia IS NOT NULL AND l.resenia != ''
             ORDER BY l.actualizado DESC LIMIT ?");
        $s->bind_param('i', $n);
        $s->execute();
        return $s->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function actividad($n = 8) {
        $s = $this->bd->prepare(
            "SELECT l.estado, l.nota, l.actualizado, u.nombreUsuario, j.titulo
             FROM lista l
             JOIN usuarios u ON l.idUsuario=u.id
             JOIN juegos   j ON l.idJuego=j.id
             ORDER BY l.actualizado DESC LIMIT ?");
        $s->bind_param('i', $n);
        $s->execute();
        return $s->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function stats() {
        $bd = $this->bd;
        return [
            'usuarios' => $bd->query("SELECT COUNT(*) FROM usuarios")->fetch_row()[0],
            'entradas' => $bd->query("SELECT COUNT(*) FROM lista")->fetch_row()[0],
            'resenas'  => $bd->query("SELECT COUNT(*) FROM lista WHERE resenia!='' AND resenia IS NOT NULL")->fetch_row()[0],
        ];
    }
}
?>
