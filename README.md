# Zaraúz FC — Sistema de tesorería

Aplicación web de control de mensualidades para Zaraúz FC.

## Funcionalidades incluidas
- Mantenedor de jugadores en Filament 5 (alta, edición y activación/desactivación).
- Registro y edición administrativa de pagos, incluyendo pagos parciales.
- Asociación de pagos a jugador y período mensual.
- Resumen público agregado por mes, sin publicar nombres ni saldos individuales.
- Consulta individual del historial mediante enlace UUID.
- PostgreSQL como base de datos.

## Reglas iniciales
- Cuota mensual de referencia: $10.000 CLP.
- Base de planificación: 20 jugadores.
- Solo usuarios autenticados pueden ingresar al panel /admin y registrar pagos.
- Los enlaces individuales se deben compartir únicamente con el jugador correspondiente.

## Stack
- PHP 8.2+
- Laravel 12
- Filament 5
- PostgreSQL
- Railway

## Despliegue en Railway
El Dockerfile genera una instalación limpia de Laravel 12 durante la construcción, instala las dependencias indicadas en composer.json e integra los archivos de este repositorio. Railway ejecuta las migraciones y el seeder en el paso pre-deploy.

Servicios creados en Railway:
- Aplicación web: se conecta desde el repositorio GitHub.
- Postgres: base de datos persistente.

Variables que debe tener el servicio web:
- APP_KEY: clave generada con `php artisan key:generate --show`.
- APP_ENV=production
- APP_DEBUG=false
- APP_URL: dominio público asignado por Railway.
- DB_CONNECTION=pgsql
- DB_URL: referencia privada `${{Postgres.DATABASE_URL}}`.
- SESSION_DRIVER=database
- CACHE_STORE=database
- QUEUE_CONNECTION=database
- ZARAUZ_MONTHLY_FEE=10000

### Primer usuario administrador
No se crea ninguna cuenta con credenciales predeterminadas. Para crear la cuenta durante el despliegue, configura ADMIN_EMAIL y ADMIN_PASSWORD como variables privadas del servicio web; ADMIN_NAME es opcional. El seeder crea/actualiza esa cuenta en cada despliegue. Usa una contraseña única y robusta.

Alternativamente, crea el usuario desde una consola del servicio con `php artisan make:filament-user`.

## Desarrollo local
1. Crear un proyecto Laravel 12: `composer create-project laravel/laravel zarauz-fc '^12.0'`.
2. Instalar Filament 5: `composer require filament/filament:^5.0`.
3. Copiar los archivos de este repositorio sobre el proyecto Laravel, respetando rutas.
4. Ejecutar `php artisan migrate --seed`.
5. Crear un administrador con `php artisan make:filament-user` o definir ADMIN_EMAIL y ADMIN_PASSWORD antes de ejecutar el seeder.
6. Iniciar con `php artisan serve`.

## Privacidad y seguridad
- El resumen público solo muestra cifras agregadas.
- La ruta individual usa un token UUID secreto. No compartir ese enlace en grupos.
- No existe registro público de usuarios.
- No subir archivos .env ni credenciales al repositorio.
- Activar backups de PostgreSQL y mantener APP_DEBUG desactivado en producción.
