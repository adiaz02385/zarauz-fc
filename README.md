# Zaraúz FC — Sistema de tesorería

Aplicación para controlar jugadores, cuotas mensuales y pagos del club.

## Incluye
- Panel administrativo Filament 5 en `/admin`.
- Mantenedor de jugadores.
- Registro de pagos, abonos y período de la mensualidad.
- Consulta individual por enlace privado.
- Resumen público con totales agregados y acceso para compartir por WhatsApp.
- Escudo oficial de Zaraúz FC en las pantallas públicas y el panel.

## Configuración inicial de tesorería
- Cuota mensual: $10.000 CLP.
- Nómina inicial: 16 jugadores.
- Octubre 2026: 15 pagos informados; pendiente Luis Salgado.
- Noviembre 2026: 9 pagos informados según los checks de la nómina recibida el 03-11-2026.
- La fecha exacta de los pagos históricos no venía indicada, por lo que se guardan sin fecha en vez de inventarla.

El seeder es idempotente: al ejecutarse de nuevo no duplica jugadores ni registros de carga inicial.

## Despliegue
- PHP 8.4
- Laravel 12
- Filament 5
- PostgreSQL
- Railway

El servicio web usa el Dockerfile del repositorio y conecta a PostgreSQL mediante `DB_URL`. Railway ejecuta migraciones y el seeder antes de publicar una nueva versión.

## Primer administrador
No hay credenciales predeterminadas. Para generar el usuario inicial desde variables de entorno privadas, configura `ADMIN_EMAIL`, `ADMIN_PASSWORD` y opcionalmente `ADMIN_NAME` en Railway. El seeder crea o actualiza ese usuario de forma idempotente. También se puede crear desde consola con `php artisan make:filament-user`.

## Seguridad
- No hay registro público de usuarios.
- La creación y edición de pagos está restringida al panel autenticado de Filament.
- El resumen público no expone nombres ni el detalle de las deudas.
- Compartir enlaces individuales solo con el jugador correspondiente.
- Nunca subir el archivo `.env` ni credenciales al repositorio.
