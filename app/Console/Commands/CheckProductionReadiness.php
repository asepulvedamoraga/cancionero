<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CheckProductionReadiness extends Command
{
    protected $signature = 'cancionero:production-check';

    protected $description = 'Comprueba la configuración mínima antes de publicar Cancionero';

    public function handle(): int
    {
        $checks = [
            $this->check('Entorno de producción', app()->environment('production'), 'APP_ENV debe ser production.'),
            $this->check('Modo debug desactivado', ! config('app.debug'), 'APP_DEBUG debe ser false.'),
            $this->check('Clave de aplicación', filled(config('app.key')), 'APP_KEY no está configurada.'),
            $this->check('URL pública HTTPS', $this->hasSecurePublicUrl(), 'APP_URL debe usar https y el dominio público real.'),
            $this->check('Cookies seguras', (bool) config('session.secure'), 'SESSION_SECURE_COOKIE debe ser true con HTTPS.'),
            $this->check('Base MySQL/MariaDB', in_array(config('database.default'), ['mysql', 'mariadb'], true), 'DB_CONNECTION debe ser mysql o mariadb.'),
            $this->check('Correo de producción', $this->hasProductionMailer(), 'Configura SMTP u otro mailer real y un remitente válido.'),
            $this->check('Assets compilados', is_file(public_path('build/manifest.json')), 'Falta public/build/manifest.json; ejecuta npm run build.'),
            $this->check('Storage escribible', is_writable(storage_path()), 'El servidor web necesita escritura en storage/.'),
            $this->check('Cache escribible', is_writable(base_path('bootstrap/cache')), 'El servidor web necesita escritura en bootstrap/cache/.'),
            $this->check('Archivos privados protegidos', $this->privateStorageIsOutsidePublic(), 'El disco local no puede estar dentro de public/.'),
            $this->check('Extensiones PHP', $this->hasRequiredExtensions(), 'Falta una extensión requerida: ctype, curl, dom, fileinfo, mbstring, openssl, PDO o pdo_mysql.'),
            $this->databaseCheck(),
        ];

        $this->table(['Comprobación', 'Resultado', 'Detalle'], array_map(fn (array $check) => [
            $check['name'],
            $check['passed'] ? '<fg=green>OK</>' : '<fg=red>FALLO</>',
            $check['passed'] ? 'Correcto' : $check['message'],
        ], $checks));

        $failed = collect($checks)->where('passed', false)->count();

        if ($failed > 0) {
            $this->error("La aplicación no está lista para producción: {$failed} comprobación(es) pendientes.");

            return self::FAILURE;
        }

        $this->info('La configuración técnica mínima está lista para producción. Verifica además backups, cron y correo con pruebas reales.');

        return self::SUCCESS;
    }

    private function check(string $name, bool $passed, string $message): array
    {
        return compact('name', 'passed', 'message');
    }

    private function hasSecurePublicUrl(): bool
    {
        $url = (string) config('app.url');
        $host = parse_url($url, PHP_URL_HOST);

        return str_starts_with($url, 'https://') && filled($host) && ! in_array($host, ['localhost', '127.0.0.1'], true);
    }

    private function hasProductionMailer(): bool
    {
        $mailer = config('mail.default');
        $address = (string) config('mail.from.address');
        $host = strtolower((string) substr(strrchr($address, '@') ?: '', 1));

        return ! in_array($mailer, ['log', 'array'], true)
            && filter_var($address, FILTER_VALIDATE_EMAIL)
            && ! in_array($host, ['example.com', 'example.org', 'example.net'], true);
    }

    private function privateStorageIsOutsidePublic(): bool
    {
        $root = realpath((string) config('filesystems.disks.local.root')) ?: (string) config('filesystems.disks.local.root');
        $public = realpath(public_path()) ?: public_path();

        return ! str_starts_with(strtolower(str_replace('\\', '/', $root)), rtrim(strtolower(str_replace('\\', '/', $public)), '/').'/');
    }

    private function hasRequiredExtensions(): bool
    {
        foreach (['ctype', 'curl', 'dom', 'fileinfo', 'mbstring', 'openssl', 'PDO', 'pdo_mysql'] as $extension) {
            if (! extension_loaded($extension)) {
                return false;
            }
        }

        return true;
    }

    private function databaseCheck(): array
    {
        if (! in_array(config('database.default'), ['mysql', 'mariadb'], true)) {
            return $this->check('Conexión y tablas', false, 'No se comprobó la base porque DB_CONNECTION no es MySQL/MariaDB.');
        }

        try {
            DB::connection()->getPdo();
            $missing = collect(['migrations', 'users', 'songs', 'song_files', 'repertoires', 'sessions', 'cache', 'jobs'])
                ->reject(fn (string $table) => Schema::hasTable($table));

            return $this->check(
                'Conexión y tablas',
                $missing->isEmpty(),
                $missing->isEmpty() ? '' : 'Faltan tablas requeridas: '.$missing->implode(', ').'. Ejecuta las migraciones.'
            );
        } catch (Throwable) {
            return $this->check('Conexión y tablas', false, 'No fue posible conectar o consultar la base configurada. Revisa las credenciales y permisos.');
        }
    }
}
