# MyGameList 🎮

Plataforma web para gestionar y valorar videojuegos. Lleva un registro de los juegos que has jugado, puntúalos, escribe reseñas y descubre qué están jugando otros usuarios.

---

## ¿Qué es MyGameList?

MyGameList es una aplicación web desarrollada como Proyecto Final de Ciclo del Grado Superior de Desarrollo de Aplicaciones Web (DAW) en FP Ibaiondo.

La idea es simple: tener en un solo sitio todos los juegos que has jugado, los que tienes pendientes y los que estás jugando ahora mismo, con la posibilidad de puntuarlos y leer las opiniones de otros jugadores.

---

## Funcionalidades principales

- Registro e inicio de sesión seguros
- Lista personal de juegos con estados: Jugando, Completado, Pendiente y Abandonado
- Puntuación del 1 al 10 y reseñas por juego
- Ranking global de los juegos mejor valorados
- Buscador con autocompletado conectado a la API de RAWG
- Filtro por género
- Ficha detallada de cada juego con screenshots
- Actividad reciente de la comunidad
- Panel de administración para gestionar usuarios y personalizar colores

---

## Tecnologías usadas

| Capa | Tecnología |
|------|-----------|
| Frontend | HTML5, CSS3, JavaScript ES6 |
| Backend | PHP 7.2+ |
| Base de datos | MySQL / MariaDB |
| API externa | RAWG Video Games Database |
| Iconos | Lucide Icons (SVG) |
| Tipografía | DM Sans, Playfair Display, DM Mono (Google Fonts) |
| Control de versiones | Git + GitHub |

---

## Instalación y configuración

### Requisitos previos
- XAMPP (o cualquier servidor con Apache + MySQL + PHP 7.2+)
- Cuenta gratuita en [rawg.io](https://rawg.io/apidocs) para obtener una API key

### Pasos

**1. Clonar el repositorio**
```bash
git clone https://github.com/tuusuario/mygamelist.git
```

**2. Copiar la carpeta en htdocs**

Mueve la carpeta `mygamelist` dentro de `C:\xampp\htdocs\`

**3. Crear la base de datos**

- Abre phpMyAdmin en `http://localhost/phpmyadmin`
- Crea una base de datos llamada `mygamelist`
- Importa el archivo `mygamelist_v2.sql`

**4. Configurar las credenciales**

Crea un archivo `config.php` en la raíz del proyecto con este contenido:

```php
<?php
define('RAWG_KEY',  'tu_api_key_aqui');
define('RAWG_URL',  'https://api.rawg.io/api');
define('DB_HOST',   'localhost');
define('DB_USER',   'root');
define('DB_PASS',   '');
define('DB_NAME',   'mygamelist');
define('SITE_NAME', 'MyGameList');
?>
```

**5. Crear el usuario administrador**

- Abre `http://localhost/mygamelist/crearAdmin.php` en el navegador
- Verás el mensaje de confirmación
- **Borra el archivo `crearAdmin.php` inmediatamente después**

**6. Abrir la web**

Ve a `http://localhost/mygamelist/` y ya está lista.

Credenciales del admin: `@admin` / `admin1234`

---

## Estructura del proyecto

```
mygamelist/
├── index.php               # Página de inicio
├── mygamelist_v2.sql       # Script de base de datos
├── modelos/                # Clases PHP (Conexion, RAWG, Usuarios, Lista, Config, Session)
├── vistas/                 # Páginas PHP (ranking, géneros, reseñas, juego, perfil, admin...)
├── usuario/                # Login y logout
├── api/                    # Endpoint JSON del buscador
├── css/                    # Hojas de estilo
└── js/                     # Scripts JavaScript
```

---

## Autor

**Luken Alava**  
2.º DAW — FP Ibaiondo  
Curso 2025-2026
