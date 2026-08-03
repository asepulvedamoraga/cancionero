# Cancionero

Aplicación Laravel privada e independiente para administrar canciones y repertorios. PortalEmprendedor se utilizó solo como referencia de separación por capas, Eloquent, Bootstrap y criterios de seguridad; no comparte código, configuración ni base de datos.

## Requisitos

- Laravel 12.64 o compatible.
- PHP 8.2 o superior y extensiones habituales de Laravel (`ctype`, `curl`, `dom`, `fileinfo`, `filter`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`).
- Composer 2.
- MySQL 8 o MariaDB compatible.
- Node.js y npm para compilar Vite.
- Imagick no es un requisito: el sistema funciona conservando los PDF originales sin convertirlos.

## Instalación local

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Crear una base MySQL exclusiva y completar:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cancionero
DB_USERNAME=cancionero_user
DB_PASSWORD=una_clave_segura
```

Configurar el administrador inicial:

```env
ADMIN_NAME="Administrador"
ADMIN_EMAIL="admin@example.com"
ADMIN_PASSWORD="una-clave-segura"
```

Después ejecutar:

```bash
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Ingresar en `/login` con `ADMIN_EMAIL` y `ADMIN_PASSWORD`. El seeder crea el administrador solo si no existe y no modifica una cuenta existente. Sin `ADMIN_PASSWORD`, desarrollo muestra una advertencia y producción detiene el seeding. En producción la contraseña exige 12 caracteres, mayúscula, minúscula, número y símbolo.

## Configuración

`config/cancionero.php` centraliza el nombre, límites de carga, MIME/extensiones permitidos, calidad de imagen, resolución PDF, miniaturas y preferencias de exportación. Las variables principales se documentan en `.env.example`.

## Base de datos

Las migraciones crean `users`, `categories`, `liturgical_moments`, `liturgical_seasons`, `songs`, `song_liturgical_season`, `song_files`, `repertoires` y `repertoire_song`, además de tablas estándar de sesiones, cache y colas de Laravel.

`database/setup/create_database.sql.example` es solo una referencia para servidores con acceso SQL suficiente. En cPanel se deben crear la base y el usuario desde **MySQL Databases**, considerar el prefijo automático de la cuenta, asignar privilegios y copiar los nombres resultantes al `.env`. Nunca guardar la contraseña real en Git.

## Producción

La guía operativa completa para instalación, actualización, respaldos, rollback, cron y solución de problemas está en [docs/DEPLOYMENT_CPANEL.md](docs/DEPLOYMENT_CPANEL.md).

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --seed --force
npm ci
npm run build
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

El usuario del servidor web necesita escritura en `storage/` y `bootstrap/cache/`. El Document Root del dominio debe apuntar a la carpeta `public/` del proyecto.

### cPanel

1. Crear una base y usuario exclusivos en **MySQL Databases** y asignar todos los privilegios.
2. Subir el proyecto fuera de cualquier carpeta pública cuando sea posible, por ejemplo `/home/usuario/cancionero`.
3. Apuntar el dominio o subdominio a `/home/usuario/cancionero/public`.
4. Crear `.env`, generar `APP_KEY`, configurar MySQL, `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false` y el administrador inicial.
5. Ejecutar Composer, migraciones, seeders y build de assets mediante Terminal/SSH o preparar `vendor` y `public/build` en un entorno compatible.
6. Conceder escritura solo a `storage/` y `bootstrap/cache/`.
7. No es necesario instalar Imagick ni Ghostscript.

Si el hosting no permite cambiar el Document Root, la opción segura es mantener toda la aplicación fuera de `public_html` y publicar únicamente el contenido de `public/`, ajustando de forma explícita y documentada las rutas de `index.php`. No copiar `.env`, `app`, `config`, `database`, `storage` ni `vendor` a una ubicación navegable. Si tampoco puede garantizarse esta separación, cambiar de plan de hosting antes de publicar.

## Pruebas

```bash
php artisan test
```

Las pruebas usan SQLite en memoria según `phpunit.xml`; no deben apuntar a la base de desarrollo o producción.

## Módulo de canciones

La Etapa 2 incorpora CRUD, búsqueda, filtros, paginación, categorías, momentos y tiempos litúrgicos, carga múltiple de imágenes, un PDF por carga, miniaturas GD, archivos privados, reemplazo, eliminación, reordenamiento táctil, descarga y modo lectura básico.

Los originales se guardan en `storage/app/songs/{song_id}/originals`; páginas convertidas y miniaturas usan `storage/app/songs/{song_id}/pages` y `storage/app/songs/{song_id}/thumbnails`. Nunca se sirven directamente desde `public/`: las rutas protegidas verifican autenticación, visibilidad de la canción y permisos de su propietario.

La conversión automática está desactivada por defecto mediante `SONG_PDF_CONVERSION_ENABLED=false`. Los PDF originales se conservan y pueden abrirse o descargarse, por lo que el hosting no necesita Imagick ni Ghostscript.
## Módulo de repertorios

La Etapa 3 incorpora CRUD de repertorios, búsqueda y filtros, selección de canciones activas, notas particulares, conteo y advertencia de páginas, ordenamiento mediante arrastrar y soltar, eliminación de canciones y duplicación completa como borrador. No agrega dependencias frontend: el ordenamiento usa APIs nativas del navegador.





## Repertorios públicos

La Etapa 9 habilita enlaces públicos directos con el formato `/r/{slug}`. No existe todavía un directorio público: solo las personas que reciben el enlace pueden descubrirlo. Las páginas incluyen `noindex,nofollow` para evitar indexación accidental.

Un repertorio público ofrece una vista de solo lectura y presentación sin iniciar sesión. Nunca expone notas del pivote, controles administrativos, correos ni rutas físicas. Cada archivo se sirve mediante un endpoint que vuelve a comprobar que el repertorio continúa público, que la canción está activa y adjunta al repertorio, y que el archivo pertenece a esa canción. Cambiar el repertorio a privado revoca inmediatamente la vista, presentación, archivos y descarga.

La descarga pública del PDF requiere activar explícitamente **Permitir descarga pública del PDF**. La generación está limitada por solicitudes y usa el mismo almacenamiento temporal y limpieza programada de las exportaciones privadas. El propietario puede copiar o abrir el enlace público desde el detalle administrativo del repertorio.
## Biblioteca compartida

La Etapa 8 separa el módulo de canciones en tres ámbitos:

- **Biblioteca:** canciones activas de todas las cuentas verificadas, con filtro por contenido propio o compartido.
- **Mis canciones:** canciones activas e inactivas pertenecientes a la cuenta actual.
- **Archivadas:** canciones eliminadas lógicamente que el propietario puede restaurar con sus archivos y relaciones. Los administradores pueden revisar y restaurar cualquier canción archivada.

La creación de canciones consulta posibles coincidencias por título o autor mientras se escribe. La advertencia es informativa y abre la canción existente en otra pestaña; no impide registrar versiones, arreglos o documentos diferentes. El endpoint devuelve solamente información pública dentro de la biblioteca y nunca correos u otros datos privados del propietario.

En la selección de canciones de un repertorio se muestra quién subió cada canción. Utilizar una canción compartida no transfiere su propiedad ni permite editarla; el usuario solo controla su posición y notas dentro de su propio repertorio.
## Registro y cuentas

La Etapa 7 habilita registro público en `/register`, verificación de correo, recuperación de contraseña, perfil y cambio de contraseña. Las cuentas nuevas se crean como usuarios normales, activas y sin correo verificado. Deben verificar su correo antes de acceder a canciones y repertorios. Las cuentas que existían antes de la migración se conservan activas y verificadas.

El administrador puede consultar las cuentas en `/admin/users` y activarlas o desactivarlas. No puede desactivar su propia cuenta desde la interfaz. Desactivar una cuenta no elimina sus canciones, archivos ni repertorios.

En desarrollo, `MAIL_MAILER=log` escribe los enlaces en `storage/logs/laravel.log`. En producción se debe configurar SMTP o un proveedor compatible y definir correctamente `APP_URL`, `MAIL_FROM_ADDRESS` y `MAIL_FROM_NAME`; de lo contrario, los enlaces generados pueden apuntar al dominio equivocado. Nunca guardar credenciales SMTP en Git.

Las rutas de registro y recuperación tienen limitación de solicitudes. El inicio de sesión rechaza cuentas desactivadas y el middleware cierra sesiones existentes si la cuenta fue desactivada posteriormente.
## Propiedad y visibilidad multiusuario

La Etapa 6 incorpora la base multiusuario. Cada canción y repertorio pertenece a un usuario mediante `user_id`. Las canciones activas forman una biblioteca compartida entre usuarios autenticados, pero solo su propietario o un administrador puede editar sus datos y archivos. Las canciones inactivas quedan restringidas a propietario y administradores.

Los repertorios son privados por defecto. Los usuarios autenticados pueden consultar repertorios públicos de otras cuentas, pero solo el propietario o un administrador puede editarlos, ordenar canciones, duplicarlos o exportarlos. `allow_public_download` queda preparado para la futura vista pública sin autenticación; todavía no habilita rutas públicas.

Al aplicar la migración sobre una instalación existente, las canciones y repertorios previos se asignan al primer administrador y los repertorios permanecen privados. La migración se detiene si existe contenido pero no hay ningún usuario al cual asignarlo.
## Presentación y exportación

La Etapa 4 agrega el modo presentación protegido para tablet en `/repertoires/{repertoire}/presentation`, con secuencia plana, controles táctiles, teclado, índice, pantalla completa y zoom básico. Los PDF originales se muestran embebidos sin conversión cuando no existen páginas generadas.

La Etapa 5 genera un PDF consolidado privado mediante FPDF y FPDI, respetando el orden de canciones y archivos. La portada y el índice se controlan con `REPERTOIRE_EXPORT_COVER` y `REPERTOIRE_EXPORT_INDEX`. Los archivos temporales se guardan bajo `storage/app/exports` y vencen según `REPERTOIRE_EXPORT_TTL_HOURS`.

Programar diariamente en cron, usando la ruta absoluta real del proyecto:

```bash
php artisan repertoire:cleanup-exports
```

FPDI gratuito no puede importar algunos PDF cifrados o con estructuras avanzadas. En ese caso la exportación informa el problema y elimina cualquier archivo parcial.

## Administración de catálogos

La Etapa 10 incorpora una sección administrativa única en `/admin/settings`. Desde ella se gestionan usuarios, categorías, momentos litúrgicos y tiempos litúrgicos sin agregar cada catálogo al menú principal.

Los catálogos permiten crear y editar nombre, slug, descripción, orden y estado. El slug se genera desde el nombre cuando se deja vacío y debe ser único dentro de cada catálogo. Las opciones activas se muestran en los formularios de canciones. Una opción asociada a canciones no puede desactivarse hasta retirar esas asociaciones; no existe eliminación permanente desde la interfaz.

Esta etapa no requiere migraciones ni dependencias nuevas.
## Papelera de repertorios

La Etapa 11 completa el ciclo de vida de los repertorios con una papelera en `/repertoires/trashed`. El propietario puede restaurar sus repertorios eliminados sin perder canciones, orden ni notas; los administradores pueden recuperar cualquier repertorio. Los demás usuarios no pueden ver ni restaurar contenido ajeno.

Enviar un repertorio público a la papelera revoca inmediatamente su vista, presentación, archivos y descarga pública. La revocación y el borrado lógico se ejecutan dentro de una transacción. Por seguridad, el repertorio se restaura como privado y con la descarga pública desactivada; su propietario puede publicarlo nuevamente desde la edición.

Esta etapa no requiere migraciones ni dependencias nuevas.
## Preparación técnica para producción

La Etapa 12 agrega el comando de diagnóstico de solo lectura:

```bash
php artisan cancionero:production-check
```

Debe ejecutarse con la configuración real del servidor antes de abrir el sitio. Comprueba entorno, debug, clave, URL HTTPS, cookie segura, MySQL/MariaDB, correo real, assets compilados, permisos de escritura, almacenamiento privado, extensiones PHP, conexión y tablas. No muestra contraseñas ni detalles internos de las excepciones. Un resultado fallido impide considerar listo el despliegue.

La limpieza de exportaciones está programada diariamente a las 03:00. En producción se debe configurar una única tarea cron que ejecute el scheduler cada minuto:

```bash
* * * * * cd /ruta/absoluta/cancionero && php artisan schedule:run >> /dev/null 2>&1
```

La ruta y el binario PHP deben reemplazarse por los valores reales del hosting. `schedule:list` permite verificar que `repertoire:cleanup-exports` esté registrado.

Antes de cada migración o actualización se debe generar un respaldo de la base MySQL y de `storage/app/songs`, descargar una copia fuera del hosting y realizar al menos una prueba de restauración. Cancionero no genera automáticamente backups porque cPanel, proveedores y políticas de retención varían; el respaldo debe configurarse mediante la herramienta del hosting o un mecanismo operacional aprobado.

`.env.example` utiliza `utf8mb4_unicode_ci`, compatible con MySQL y MariaDB, y documenta valores recomendados para HTTPS, logs diarios, cookies seguras y correo. No se modifica automáticamente el `.env` de instalaciones existentes.

Esta etapa no requiere migraciones ni dependencias nuevas.
## Estado

Etapas 1 a 13 implementadas: base Laravel, canciones, repertorios, presentación, exportación, propiedad/visibilidad multiusuario, gestión de cuentas, biblioteca compartida, repertorios públicos seguros, administración de catálogos, recuperación segura de repertorios, preparación técnica y guía definitiva de despliegue en cPanel.

## Estabilización Laravel 12

El proyecto fue actualizado desde Laravel 11 a Laravel 12.64, con PHPUnit 11, Collision 8.9 y Carbon 3. El cambio no requiere migraciones nuevas. En despliegues existentes ejecutar `composer install --no-dev --optimize-autoloader`, reconstruir cachés y realizar una prueba de inicio de sesión, archivos privados, presentación y exportación.

Antes de actualizar una instalación existente, respaldar `composer.json`, `composer.lock`, `.env`, la base de datos y `storage/app`. Para volver a la versión anterior del código, restaurar los archivos Composer correspondientes al mismo release y ejecutar nuevamente `composer install`; no existe rollback de base de datos asociado a esta actualización.