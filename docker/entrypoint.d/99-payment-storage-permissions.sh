#!/bin/sh
set -eu

APP_PATH="${APP_BASE_DIR:-/var/www/html}"
UPLOAD_PATH="$APP_PATH/storage/app/private/payment-proofs"

mkdir -p "$UPLOAD_PATH"
chown -R www-data:www-data "$APP_PATH/storage/app/private"
chmod 770 "$UPLOAD_PATH"

echo "Zaraúz FC: private payment-proof storage is ready."
