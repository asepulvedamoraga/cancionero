# Despliegue de Cancionero en cPanel

Esta guía describe la instalación inicial, actualización, verificación y rollback de Cancionero en un hosting cPanel. Está basada en la aplicación real: Laravel 12.64, PHP 8.2+, MySQL/MariaDB, Blade, Vite y archivos privados servidos por controladores.

No copies secretos de desarrollo. Sustituye rutas, dominios, usuarios y nombres de base de los ejemplos por los valores reales del hosting.

## 1. Requisitos del hosting

- PHP 8.2 o superior.
- Composer 2, ya sea en el servidor o en un entorno de construcción compatible.
- MySQL 8 o MariaDB con `utf8mb4_unicode_ci`.
- Apache con `mod_rewrite` y archivos `.htaccess` permitidos.
- Extensiones PHP: `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `gd`, `iconv`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `session`, `tokenizer` y `xml`.
- GD debe incluir soporte JPEG, PNG y WebP.
- Escritura para el usuario web en `storage/` y `bootstrap/cache/`.
- Cron Jobs para ejecutar el scheduler de Laravel.
- SMTP u otro mailer real para verificación de cuentas y recuperación de contraseña.

Imagick y Ghostscript no son requisitos. Los PDF originales se conservan y pueden presentarse o exportarse sin convertirlos a imágenes.

Valores PHP sugeridos como punto de partida:

```ini
memory_limit = 256M
upload_max_filesize = 25M
post_max_size = 64M
max_file_uploads = 40
max_execution_time = 120
```

`upload_max_filesize` y `post_max_size` deben superar `SONG_UPLOAD_MAX_MB`. Ajusta los valores según los límites contratados y el tamaño real de los repertorios.

## 2. Respaldo previo obligatorio

Para una actualización, activa mantenimiento y crea un respaldo consistente de:

1. La base MySQL completa.
2. `storage/app/songs`, que contiene originales, páginas y miniaturas.
3. El archivo `.env` sin incorporarlo al paquete ni a Git.
4. El código desplegado, `composer.lock` y `public/build` de la versión vigente.

Las exportaciones de `storage/app/exports` son temporales y normalmente no necesitan respaldo. Descarga al menos una copia de la base y de los archivos fuera del hosting. Antes del primer despliegue real, prueba que ambos respaldos puedan restaurarse juntos.

No uses una copia de base de una hora y una copia de archivos de otra: podrían quedar registros sin archivo o archivos sin registro.

## 3. Preparar el paquete

El paquete debe incluir, como mínimo:

```text
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/ (estructura y .gitignore; no reemplazar archivos existentes al actualizar)
artisan
composer.json
composer.lock
package.json
package-lock.json
vite.config.js
```

No incluir:

```text
.env
node_modules/
storage/logs/*.log
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
```

Si cPanel no ofrece Composer, genera `vendor/` en un entorno Linux compatible con la misma versión de PHP y súbelo. Si no ofrece Node.js, ejecuta localmente o en CI:

```bash
npm ci
npm run build
```

y sube `public/build/`. `node_modules/` nunca se sube.

Cuando Composer esté disponible en el servidor, se prefiere:

```bash
composer install --no-dev --optimize-autoloader
```

No ejecutar `composer update` en producción.

## 4. Ubicación y Document Root

### Opción recomendada

Mantén la aplicación fuera de la carpeta pública:

```text
/home/USUARIO/cancionero
```

Configura el dominio o subdominio para que su Document Root sea:

```text
/home/USUARIO/cancionero/public
```

Así Apache solo expone `public/`; `.env`, `vendor/`, `storage/`, migraciones y código PHP quedan fuera de acceso web.

### Si cPanel no permite apuntar a `public/`

La aplicación debe seguir fuera de `public_html`. Publica únicamente el contenido de `public/` en el Document Root y ajusta las tres referencias de `index.php` hacia la ubicación real de la aplicación:

```php
if (file_exists($maintenance = '/home/USUARIO/cancionero/storage/framework/maintenance.php')) {
    require $maintenance;
}

require '/home/USUARIO/cancionero/vendor/autoload.php';

(require_once '/home/USUARIO/cancionero/bootstrap/app.php')
    ->handleRequest(Illuminate\Http\Request::capture());
```

Copia también `public/.htaccess` y `public/build` al Document Root. Usa rutas absolutas reales y prueba el procedimiento después de cada cambio de cuenta o directorio.

Nunca copies `.env`, `app`, `bootstrap`, `config`, `database`, `resources`, `routes`, `storage` o el proyecto completo dentro de una carpeta navegable. Si no puede garantizarse esta separación, el hosting no es adecuado para publicar la aplicación.

## 5. Crear la base en cPanel

Desde **MySQL Databases**:

1. Crea una base exclusiva.
2. Crea un usuario con contraseña aleatoria y segura.
3. Asigna el usuario a la base con todos los privilegios necesarios.
4. Conserva los nombres completos generados por cPanel; normalmente incluyen el prefijo de la cuenta.

Configuración esperada:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=USUARIO_cancionero
DB_USERNAME=USUARIO_cancionero
DB_PASSWORD=REEMPLAZAR
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

Algunos proveedores indican un host distinto de `localhost`. Usa el dato entregado por cPanel. `database/setup/create_database.sql.example` es solo una referencia para servidores con privilegios SQL; no contiene una contraseña utilizable.

## 6. Crear el `.env` de producción

Copia `.env.example` como `.env` únicamente en el servidor y completa, como mínimo:

```env
APP_NAME="Cancionero"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=America/Santiago
APP_URL=https://cancionero.example.com
APP_LOCALE=es
APP_FALLBACK_LOCALE=es

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning
LOG_DAILY_DAYS=14

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=REEMPLAZAR
MAIL_PASSWORD=REEMPLAZAR
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=cancionero@example.com
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_NAME="Administrador"
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=REEMPLAZAR_SOLO_DURANTE_INSTALACION

SONG_UPLOAD_MAX_MB=20
SONG_IMAGE_QUALITY=85
SONG_PDF_CONVERSION_ENABLED=false
SONG_PDF_RESOLUTION=150
SONG_GENERATE_THUMBNAILS=true
REPERTOIRE_EXPORT_COVER=true
REPERTOIRE_EXPORT_INDEX=false
REPERTOIRE_EXPORT_TTL_HOURS=24
```

Reglas importantes:

- `APP_URL` debe coincidir exactamente con el dominio y usar HTTPS; no alternar entre `www` y sin `www`.
- Mantén `SESSION_DOMAIN=null` salvo que exista una necesidad comprobada de compartir la cookie entre subdominios.
- No uses mailers `log` o `array` en producción.
- `QUEUE_CONNECTION=sync` evita exigir un worker permanente en cPanel; la aplicación actual no necesita colas asíncronas.
- No agregues comillas a valores booleanos.
- Nunca envíes ni registres el contenido completo de `.env`.

Genera `APP_KEY` una sola vez:

```bash
php artisan key:generate
```

No regeneres la clave durante actualizaciones: invalidaría sesiones y cualquier dato cifrado con la clave anterior.

## 7. Instalación inicial

Desde la raíz privada de la aplicación, instala las dependencias bloqueadas:

```bash
composer install --no-dev --optimize-autoloader
```

Si `public/build` no venía incluido y Node está disponible, compila los assets antes del diagnóstico:

```bash
npm ci
npm run build
```

Limpia cachés anteriores y crea las tablas y datos iniciales:

```bash
php artisan optimize:clear
php artisan migrate --seed --force
```

El seeder necesita `ADMIN_PASSWORD`, exige una contraseña segura en producción y no sobrescribe una cuenta existente. Inmediatamente después del seeding, elimina `ADMIN_PASSWORD` del `.env`; la cuenta ya quedó almacenada con un hash. Luego construye las cachés y ejecuta el diagnóstico:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cancionero:production-check
```

Comprueba el acceso del administrador. Si necesitas repetir el seeder durante la instalación, vuelve a definir temporalmente `ADMIN_PASSWORD`, limpia la caché de configuración y retírala al terminar.

No vuelvas a ejecutar `db:seed` rutinariamente durante las actualizaciones. Las migraciones posteriores se aplican con `php artisan migrate --force`.

## 8. Permisos y almacenamiento

El servidor web necesita escritura recursiva en:

```text
storage/
bootstrap/cache/
```

En cPanel normalmente basta con directorios `775` o `755`, dependiendo del propietario y grupo configurados. No uses `777`. Verifica que los archivos pertenezcan al usuario correcto de la cuenta.

Los documentos privados se guardan en:

```text
storage/app/songs/{song_id}/originals
storage/app/songs/{song_id}/pages
storage/app/songs/{song_id}/thumbnails
```

Las exportaciones temporales se guardan bajo:

```text
storage/app/exports
```

No es necesario ejecutar `php artisan storage:link`: canciones y exportaciones se sirven mediante rutas que vuelven a comprobar autenticación, propiedad o acceso público. La carpeta `storage/app` nunca debe exponerse directamente.

## 9. Configurar cron

En **Cron Jobs**, configura una sola ejecución por minuto:

```cron
* * * * * cd /home/USUARIO/cancionero && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

La ruta de PHP puede ser diferente, por ejemplo:

```text
/opt/cpanel/ea-php82/root/usr/bin/php
```

Usa el binario PHP 8.2+ asociado al dominio. Verifica manualmente:

```bash
php artisan schedule:list
php artisan repertoire:cleanup-exports --hours=24
```

Debe aparecer `repertoire:cleanup-exports` programado diariamente a las 03:00. No programes además el comando de limpieza directamente, porque se ejecutaría dos veces.

## 10. Verificación antes de abrir el sitio

Ejecuta:

```bash
php artisan migrate:status
php artisan schedule:list
php artisan cancionero:production-check
```

El último comando debe finalizar sin fallos. Luego verifica manualmente:

1. `https://DOMINIO/up` responde HTTP 200.
2. El login carga sin mostrar errores internos.
3. El administrador puede iniciar y cerrar sesión.
4. Una cuenta nueva recibe el correo de verificación.
5. La recuperación de contraseña envía un enlace con el dominio correcto.
6. Se puede subir una imagen y un PDF.
7. Los archivos no son accesibles mediante una URL directa de `storage`.
8. Se puede crear, ordenar, presentar y exportar un repertorio.
9. Un repertorio privado devuelve 404 en sus rutas públicas.
10. Publicar un repertorio habilita el enlace; enviarlo a la papelera lo revoca.
11. El diseño y la presentación funcionan en teléfono y tablet.
12. `storage/logs/laravel.log` no contiene errores nuevos ni secretos.

## 11. Procedimiento de actualización

1. Informa una ventana de mantenimiento.
2. Activa mantenimiento:

```bash
php artisan down --secret="TOKEN_TEMPORAL_ALEATORIO"
```

3. Con la aplicación detenida, respalda base, `.env`, código vigente y `storage/app/songs` para que la base y los archivos correspondan al mismo instante.
4. Sube el nuevo código sin reemplazar `.env` ni `storage/app/songs`.
5. Instala exactamente las dependencias bloqueadas:

```bash
composer install --no-dev --optimize-autoloader
```

6. Instala assets desde el paquete o compílalos:

```bash
npm ci
npm run build
```

7. Limpia cachés anteriores y aplica migraciones:

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cancionero:production-check
```

8. Desactiva mantenimiento:

```bash
php artisan up
```

9. Ejecuta el checklist funcional de la sección anterior.

No uses `migrate:fresh`, `db:wipe`, `composer update` ni elimines `storage` durante una actualización.

## 12. Rollback

Si una actualización falla antes de migrar, restaura el código, `vendor/` y `public/build` del release anterior, reconstruye cachés y vuelve a probar.

Si ya se ejecutaron migraciones o hubo escrituras con la nueva versión:

1. Vuelve a mantenimiento.
2. Conserva una copia del estado fallido para diagnóstico.
3. Restaura conjuntamente la base y `storage/app/songs` del mismo respaldo.
4. Restaura el código, `composer.lock`, `vendor/` y `public/build` de la versión anterior.
5. Ejecuta `php artisan optimize:clear` y reconstruye cachés.
6. Ejecuta el diagnóstico y el checklist antes de `php artisan up`.

No ejecutes `php artisan migrate:rollback` a ciegas: una migración descendente puede eliminar datos y no restaura archivos físicos. El rollback debe seguir las instrucciones específicas del release o restaurar el respaldo completo.

## 13. Problemas frecuentes

### 419 Page Expired

- Confirma que `APP_URL` use el mismo dominio y esquema con que navega el usuario.
- Usa HTTPS y `SESSION_SECURE_COOKIE=true` en producción.
- Mantén `SESSION_DOMAIN=null` salvo una necesidad concreta.
- Confirma que exista la tabla `sessions` y que la base sea escribible.
- Ejecuta `php artisan optimize:clear` y luego reconstruye cachés.
- Borra las cookies antiguas del dominio después de cambiar URL o configuración de sesión.

### Unknown collation `utf8mb4_0900_ai_ci`

El servidor es probablemente MariaDB o una versión anterior de MySQL. Usa:

```env
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

Después ejecuta `php artisan config:clear`. Si la base ya fue creada con otra collation, corrígela desde cPanel/phpMyAdmin después de respaldarla.

### Vite manifest not found

Falta `public/build/manifest.json`. Ejecuta `npm ci && npm run build` en un entorno de construcción y sube la carpeta completa `public/build`.

### Error 500 o pantalla blanca

- Confirma `APP_DEBUG=false`; no lo actives públicamente.
- Revisa `storage/logs/laravel.log` desde cPanel sin copiar secretos.
- Revisa permisos de `storage/` y `bootstrap/cache/`.
- Ejecuta `composer check-platform-reqs --no-dev`.
- Limpia y reconstruye cachés.

### No llegan correos

- Comprueba host, puerto, cifrado, usuario y contraseña SMTP.
- Usa un remitente autorizado por el dominio.
- Confirma que `APP_URL` sea público y HTTPS.
- Revisa spam, logs del proveedor y límites de envío.
- Prueba tanto verificación de correo como recuperación de contraseña.

### Los archivos no suben

- Compara `SONG_UPLOAD_MAX_MB` con `upload_max_filesize` y `post_max_size`.
- Revisa `max_file_uploads`, `memory_limit` y permisos.
- Confirma que `fileinfo` y GD estén habilitados.
- No publiques ni muevas los archivos a `public/storage`.

### El cron no se ejecuta

- Usa rutas absolutas para PHP y el proyecto.
- Confirma la versión con el mismo binario: `/ruta/php -v`.
- Ejecuta manualmente `/ruta/php artisan schedule:list`.
- Durante el diagnóstico, redirige temporalmente la salida a un archivo privado; elimina ese log cuando funcione.

## 14. Registro de cada despliegue

Para cada release conserva fuera del sitio público:

- Fecha y responsable.
- Versión o identificador del paquete.
- Archivos relevantes modificados.
- Migraciones aplicadas.
- Resultado de `cancionero:production-check`.
- Resultado del checklist funcional.
- Ubicación y fecha de los respaldos.
- Incidencias y acciones de rollback.

Nunca registres contraseñas, `APP_KEY`, tokens SMTP ni el contenido completo de `.env`.