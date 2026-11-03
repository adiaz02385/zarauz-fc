# Zaraúz FC — Sistema de tesorería

Starter funcional para construir el sistema de control de mensualidades de Zaraúz FC.

## Alcance inicial
- 20 jugadores como base de planificación.
- Cuota mensual de referencia: $10.000 CLP.
- Registro administrativo de pagos.
- Consulta de mensualidades para jugadores.
- Historial individual mediante enlace con token.
- Base de datos PostgreSQL.

## Estado del repositorio
Este repositorio contiene una **base inicial (starter/overlay)**, no una instalación Laravel completa ni una aplicación lista para producción. Incluye modelos, migraciones, controlador y vistas públicas. Aún falta completar el esqueleto Laravel, los recursos/paneles Filament, autenticación y autorización, pruebas y configuración final de despliegue.

## Stack objetivo
- PHP 8.2+
- Laravel 12
- Filament 5
- PostgreSQL
- Railway (hosting)

## Incorporar el starter a Laravel
1. Crear la aplicación: `composer create-project laravel/laravel zarauz-fc '^12.0'`
2. Instalar Filament: `composer require filament/filament:^5.0`
3. Copiar el contenido de este repositorio sobre el proyecto Laravel, respetando rutas.
4. Crear `config/zarauz.php` usando como referencia `config-zarauz.php`.
5. Configurar PostgreSQL en `.env`.
6. Ejecutar `php artisan migrate`.
7. Crear el usuario administrador y completar el panel Filament.

## Variables de entorno
Revisar `.env.example`. No subir secretos ni el archivo `.env` real.

## Railway
El proyecto Railway y un servicio PostgreSQL fueron creados previamente. El servicio web aún requiere una aplicación Laravel completa y configuración de build/start antes de desplegarse. Revisar `railway.toml` y `Dockerfile` antes de usar en producción.

## Pendientes de seguridad antes de producción
- Autenticación y autorización del panel administrativo.
- Restringir el registro de pagos al administrador.
- Definir qué datos quedan visibles en el resumen público.
- Proteger los enlaces individuales y evitar compartir información de otros jugadores.
- Validación, pruebas automatizadas, backups y HTTPS.
- Configurar correctamente migraciones y healthcheck para el servicio web.
