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
- Las fechas exactas de los pagos históricos no venían indicadas; quedan vacías en lugar de inventarse.

El seeder es idempotente: al ejecutarse de nuevo no duplica jugadores ni registros de carga inicial.

## Despliegue
- PHP 8.4
- Laravel 12
- Filament 5
- PostgreSQL
- Railway

El servicio web usa el Dockerfile del repositorio y conecta a PostgreSQL mediante `DB_URL`. Railway ejecuta migraciones y el seeder antes de publicar una nueva versión.

## Primer administrador
No hay credenciales predeterminadas ni registro público de usuarios. Para crear el tesorero inicial configura las variables privadas `ADMIN_EMAIL`, `ADMIN_PASSWORD` y opcionalmente `ADMIN_NAME` en el servicio Web de Railway. El seeder crea/actualiza esa cuenta y la marca como administradora. Usa una contraseña robusta y no la guardes en GitHub.

Las demás cuentas quedan sin permiso para entrar al panel Filament. Si creas una cuenta manualmente con `php artisan make:filament-user`, debes asignarle `is_admin = true` desde Tinker o la consola de base de datos antes de que pueda ingresar.

## Seguridad
- Solo usuarios con `is_admin = true` pueden abrir el panel administrativo.
- La creación y edición de pagos se hace dentro del panel autenticado.
- El resumen público no expone nombres ni detalle de deudas.
- Los enlaces individuales deben compartirse únicamente con el jugador correspondiente.
- Nunca subir el archivo `.env` ni credenciales al repositorio.
