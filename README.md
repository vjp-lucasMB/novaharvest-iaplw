# NovaHarvest - UT1

Infraestructura web de NovaHarvest: nginx, PHP-FPM, MariaDB y Adminer mediante Docker Compose.

## 1. Arranque

Requisitos: Docker con Docker Compose. Clona el repositorio y entra en su carpeta.

```bash
git clone <URL_DEL_REPOSITORIO> novaharvest-iaplw
cd novaharvest-iaplw
cp .env.example .env
```

Edita `.env` y sustituye `DB_PASS` y `DB_ROOT_PASSWORD` por contraseñas propias. No subas `.env` al repositorio.

Genera el certificado autofirmado para `CN=localhost`:

```bash
mkdir -p nginx/certs
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout nginx/certs/novaharvest.key \
  -out nginx/certs/novaharvest.crt \
  -subj "/CN=localhost"
```

Comprueba la configuración y construye/arranca los servicios:

```bash
docker compose config
docker compose up -d --build
docker compose ps
docker compose logs db
```

Espera a que MariaDB aparezca como `healthy`. En la primera inicialización, carga el SQL una sola vez como `root`. En una terminal Bash:

```bash
set -a
. ./.env
set +a
docker compose exec -T db mariadb -u root -p"$DB_ROOT_PASSWORD" < db/novaharvest.sql
```

Comprueba las páginas:

- HTTP redirige a HTTPS: `http://localhost:8080/`
- Portada: `https://localhost:8443/`
- Excedentes: `https://localhost:8443/excedentes.php`
- Formulario: `https://localhost:8443/avisos.html`
- Estado: `https://localhost:8443/estado.php`
- Temporada: `https://localhost:8443/temporada.md`
- Adminer (solo equipo local): `http://localhost:8081`

El navegador avisará de que el certificado es autofirmado. Es normal en este entorno de prácticas. En Adminer selecciona sistema MySQL, servidor `db`, base de datos `novaharvest` y las credenciales de la aplicación.

Para detener sin borrar los datos:

```bash
docker compose down
```

Para volver a arrancar:

```bash
docker compose up -d
```

Para empezar desde cero y borrar también los datos persistidos (¡destructivo!):

```bash
docker compose down -v
docker compose up -d --build
```

Después de borrar el volumen hay que volver a cargar `db/novaharvest.sql`.

## 2. Arquitectura

```text
Navegador
  | HTTP localhost:8080 (301) / HTTPS localhost:8443
  v
web (nginx:1.27-alpine)
  |-- estáticos: ./web -> /usr/share/nginx/html (solo lectura)
  |-- configuración: nginx/default.conf
  |-- certificados: nginx/certs -> /etc/nginx/certs
  |
  | FastCGI por red Compose: php:9000
  v
php (PHP-FPM 8.3)
  |-- comparte ./web en /usr/share/nginx/html
  |-- configuración www.conf y php.ini
  |-- PDO / protocolo MariaDB hacia db:3306
  v
db (MariaDB 11)
  |-- volumen Docker dbdata -> /var/lib/mysql

adminer:5 -- red Compose hacia db:3306
  |
  +-- publicado solo en 127.0.0.1:8081
```

Recorrido de `/excedentes.php`: el navegador solicita HTTPS a nginx; nginx localiza el bloque PHP y reenvía la petición por FastCGI a `php:9000`; PHP-FPM ejecuta el fichero, que está montado en la misma ruta dentro de ambos contenedores; PDO conecta con `db` por el puerto interno 3306, ejecuta el `SELECT` y devuelve el HTML a nginx, que responde al navegador.

## 3. Configuración

| Fichero | Servicio | Ruta dentro del contenedor | Función |
|---|---|---|---|
| `compose.yaml` | Docker Compose | - | Define servicios, puertos, dependencias, variables y volumen |
| `nginx/default.conf` | web | `/etc/nginx/conf.d/default.conf` | HTTP→HTTPS, raíz web, errores, gzip, Markdown, FastCGI y límite de petición |
| `php/Dockerfile` | php | - | Construye PHP-FPM con `pdo_mysql` y `mysqli` |
| `php/www.conf` | php | `/usr/local/etc/php-fpm.d/www.conf` | Pool `[www]`, usuario, puerto y número de workers |
| `php/php.ini` | php | `/usr/local/etc/php/conf.d/novaharvest.ini` | Memoria, errores y zona horaria |
| `db/novaharvest.sql` | db (cargado manualmente) | - | Crea tablas, inserta datos y limita privilegios |
| `.env.example` | Compose | - | Plantilla de variables; no contiene secretos reales |

## 4. Base de datos

La base de datos se llama `novaharvest` y contiene:

- `excedentes`: `id`, `producto`, `productor`, `pueblo`, `kilos` y `precio_kg`.
- `avisos`: `id`, `nombre`, `email`, `producto` y `fecha`.

El usuario de la aplicación es `novaharvest_app`. Solo tiene `SELECT` e `INSERT` sobre `novaharvest.*`: la web necesita consultar los excedentes y registrar avisos, pero no necesita modificar la estructura ni borrar datos.

Comprobación de privilegios:

```bash
docker compose exec db mariadb -u root -p
```

Dentro del cliente MariaDB:

```sql
SHOW GRANTS FOR 'novaharvest_app'@'%';
```

Comprobaciones adicionales:

```bash
docker compose exec php php -m
docker compose exec php env | grep DB_
docker compose logs php
docker compose logs db
```

## Notas de seguridad

- `.env` y `nginx/certs/` están excluidos por `.gitignore`.
- Solo nginx publica los puertos de la web; PHP-FPM y MariaDB no publican puertos.
- Adminer se publica en `127.0.0.1:8081`, no en todas las interfaces.
- PHP recibe únicamente `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASS`.
- `display_errors` está desactivado en la configuración final.
- El usuario SQL de la web tiene privilegios mínimos para las operaciones del proyecto.
