-- ═══════════════════════════════════════════════════
-- MyGameList v2 — Base de datos
-- ═══════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS mygamelist
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
USE mygamelist;

-- ── Usuarios ───────────────────────────────────────
CREATE TABLE usuarios (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  nombreUsuario VARCHAR(50)  NOT NULL UNIQUE,
  email         VARCHAR(100) NOT NULL UNIQUE,
  contrasenia   VARCHAR(255) NOT NULL,
  creado        DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Catálogo de juegos (caché local de RAWG) ──────
CREATE TABLE juegos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  rawgId      INT          NOT NULL UNIQUE,
  titulo      VARCHAR(200) NOT NULL,
  genero      VARCHAR(100),
  imagen      VARCHAR(300),
  anio        CHAR(4),
  desarrollador VARCHAR(150),
  creado      DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Lista de juegos de cada usuario ────────────────
CREATE TABLE lista (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  idUsuario  INT  NOT NULL,
  idJuego    INT  NOT NULL,
  estado     ENUM('jugando','completado','abandonado','pendiente') NOT NULL DEFAULT 'pendiente',
  nota       TINYINT CHECK (nota BETWEEN 1 AND 10),
  horas      SMALLINT DEFAULT 0,
  resenia    TEXT,
  actualizado DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_usuario_juego (idUsuario, idJuego),
  FOREIGN KEY (idUsuario) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (idJuego)   REFERENCES juegos(id)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Configuración del sitio (admin) ────────────────
CREATE TABLE config (
  clave  VARCHAR(60)  NOT NULL PRIMARY KEY,
  valor  VARCHAR(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Valores por defecto
INSERT INTO config (clave, valor) VALUES
  ('color_gold', '#f0b429'),
  ('color_bg',   '#080808');

-- ── Usuario admin (contraseña: admin1234) ──────────
-- Ejecuta primero este archivo, luego abre:
-- http://localhost/mygamelist2/crearAdmin.php
-- y bórralo después.
