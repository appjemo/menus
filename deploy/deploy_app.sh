#!/usr/bin/env bash
# JEMO Menus — Despliegue de la app Laravel en la VM (Debian 12)
# Idempotente: clona o actualiza, instala deps, configura .env, migra y deja Nginx sirviendo.
set -euo pipefail

APP_DIR="/var/www/menus"
REPO="https://github.com/appjemo/menus.git"
DB_NAME="jemo_menus"
DB_USER="jemo"
RUN_USER="$(whoami)"
PHP_SOCK="/run/php/php8.4-fpm.sock"
SERVER_NAMES="35.232.83.116 menus.wearejemo.com"

echo "==> [1/9] Clonar o actualizar repo en $APP_DIR (owner: $RUN_USER)"
if [ ! -d "$APP_DIR/.git" ]; then
  sudo mkdir -p "$APP_DIR"
  sudo chown -R "$RUN_USER":"$RUN_USER" "$APP_DIR"
  git clone "$REPO" "$APP_DIR"
else
  git -C "$APP_DIR" pull --ff-only
fi
cd "$APP_DIR"

echo "==> [2/9] Base de datos MariaDB ($DB_NAME / $DB_USER)"
# Reutiliza el password del .env si ya existe; si no, genera uno nuevo.
if [ -f .env ] && grep -q '^DB_PASSWORD=' .env; then
  DB_PASS="$(grep '^DB_PASSWORD=' .env | head -1 | cut -d= -f2- | tr -d '"')"
fi
if [ -z "${DB_PASS:-}" ]; then
  DB_PASS="$(openssl rand -hex 16)"
fi
sudo mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "==> [3/9] composer install"
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "==> [4/9] Archivo .env"
if [ ! -f .env ]; then
  cp .env.example .env
fi
# Configurar valores clave (idempotente con sed)
set_env() { local k="$1" v="$2"; if grep -q "^${k}=" .env; then sudo sed -i "s|^${k}=.*|${k}=${v}|" .env; else echo "${k}=${v}" >> .env; fi; }
set_env APP_NAME "JEMO_Menus"
set_env APP_ENV "local"
set_env APP_DEBUG "true"
set_env APP_URL "http://35.232.83.116"
set_env DB_CONNECTION "mysql"
set_env DB_HOST "127.0.0.1"
set_env DB_PORT "3306"
set_env DB_DATABASE "${DB_NAME}"
set_env DB_USERNAME "${DB_USER}"
set_env DB_PASSWORD "${DB_PASS}"

echo "==> [5/9] App key + migraciones"
php artisan key:generate --force
php artisan migrate --force

echo "==> [6/9] Permisos (storage y bootstrap/cache para www-data)"
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache

echo "==> [7/9] Nginx vhost"
sudo tee /etc/nginx/sites-available/menus >/dev/null <<NGINX
server {
    listen 80;
    server_name ${SERVER_NAMES};
    root ${APP_DIR}/public;

    index index.php;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:${PHP_SOCK};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX
sudo ln -sf /etc/nginx/sites-available/menus /etc/nginx/sites-enabled/menus
sudo rm -f /etc/nginx/sites-enabled/default

echo "==> [8/9] Permitir a www-data leer la app (ACL en /var/www/menus)"
sudo chmod o+x /var/www /var/www/menus

echo "==> [9/9] Recargar Nginx"
sudo nginx -t
sudo systemctl reload nginx

echo "===================================================="
echo "DEPLOY_APP_DONE"
echo "URL: http://35.232.83.116"
echo "DB:  ${DB_NAME} / ${DB_USER}"
