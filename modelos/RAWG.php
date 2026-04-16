<?php
// modelos/RAWG.php — compatible PHP 7.2+
require_once __DIR__ . '/../config.php';

class RAWG {

    private static function fetch($endpoint, $params = array()) {
        $params['key'] = RAWG_KEY;
        $url = RAWG_URL . $endpoint . '?' . http_build_query($params);
        $ctx = stream_context_create(array('http' => array(
            'timeout'       => 8,
            'ignore_errors' => true,
            'header'        => "User-Agent: MyGameList/2.0\r\n"
        )));
        $raw = @file_get_contents($url, false, $ctx);
        return $raw ? json_decode($raw, true) : null;
    }

    private static function map($j) {
        return array(
            'id'          => $j['id'],
            'titulo'      => $j['name'],
            'imagen'      => isset($j['background_image']) ? $j['background_image'] : null,
            'nota'        => round((isset($j['rating']) ? $j['rating'] : 0) * 2, 1),
            'votos'       => number_format(isset($j['ratings_count']) ? $j['ratings_count'] : 0),
            'anio'        => isset($j['released']) ? substr($j['released'], 0, 4) : null,
            'genero'      => isset($j['genres'][0]['name']) ? $j['genres'][0]['name'] : 'Sin género',
            'generos'     => isset($j['genres']) ? implode(', ', array_column($j['genres'], 'name')) : '',
            'plataformas' => isset($j['platforms']) ? implode(', ', array_column(array_column($j['platforms'], 'platform'), 'name')) : '',
        );
    }

    public static function top($n = 10) {
        $d = self::fetch('/games', array('page_size' => $n, 'ordering' => '-rating', 'metacritic' => '80,100'));
        if (!isset($d['results'])) return array();
        $out = array();
        foreach ($d['results'] as $j) $out[] = self::map($j);
        return $out;
    }

    public static function trending($n = 6) {
        $d = self::fetch('/games', array(
            'page_size' => $n, 'ordering' => '-added',
            'dates' => date('Y-m-d', strtotime('-30 days')) . ',' . date('Y-m-d')
        ));
        if (!isset($d['results'])) return array();
        $out = array();
        foreach ($d['results'] as $j) $out[] = self::map($j);
        return $out;
    }

    public static function novedades($n = 6) {
        $d = self::fetch('/games', array(
            'page_size' => $n, 'ordering' => '-released',
            'dates' => date('Y-m-d', strtotime('-90 days')) . ',' . date('Y-m-d')
        ));
        if (!isset($d['results'])) return array();
        $out = array();
        foreach ($d['results'] as $j) $out[] = self::map($j);
        return $out;
    }

    public static function buscar($q, $n = 8) {
        $d = self::fetch('/games', array('search' => $q, 'page_size' => $n, 'search_precise' => 'true'));
        if (!isset($d['results'])) return array();
        $out = array();
        foreach ($d['results'] as $j) $out[] = self::map($j);
        return $out;
    }

    public static function porGenero($slug, $n = 12) {
        $d = self::fetch('/games', array('genres' => $slug, 'page_size' => $n, 'ordering' => '-rating'));
        if (!isset($d['results'])) return array();
        $out = array();
        foreach ($d['results'] as $j) $out[] = self::map($j);
        return $out;
    }

    public static function detalle($id) {
        $d = self::fetch('/games/' . (int)$id);
        if (!$d || isset($d['detail'])) return null;
        return array(
            'id'           => $d['id'],
            'titulo'       => $d['name'],
            'imagen'       => isset($d['background_image']) ? $d['background_image'] : null,
            'nota'         => round((isset($d['rating']) ? $d['rating'] : 0) * 2, 1),
            'metacritic'   => isset($d['metacritic']) ? $d['metacritic'] : null,
            'anio'         => isset($d['released']) ? substr($d['released'], 0, 4) : null,
            'descripcion'  => isset($d['description_raw']) ? mb_substr(strip_tags($d['description_raw']), 0, 600) : '',
            'generos'      => implode(', ', array_column(isset($d['genres']) ? $d['genres'] : array(), 'name')),
            'desarrollador'=> isset($d['developers'][0]['name']) ? $d['developers'][0]['name'] : '',
            'plataformas'  => isset($d['platforms']) ? implode(', ', array_column(array_column($d['platforms'], 'platform'), 'name')) : '',
            'web'          => isset($d['website']) ? $d['website'] : null,
            'screenshots'  => array_column(array_slice(isset($d['short_screenshots']) ? $d['short_screenshots'] : array(), 0, 4), 'image'),
        );
    }

    public static function rankingPaginado($page = 1, $n = 20) {
        $d = self::fetch('/games', array(
            'page' => $page, 'page_size' => $n,
            'ordering' => '-rating', 'metacritic' => '70,100'
        ));
        $out = array();
        if (isset($d['results'])) foreach ($d['results'] as $j) $out[] = self::map($j);
        return array('juegos' => $out, 'total' => isset($d['count']) ? $d['count'] : 0);
    }
}
?>
