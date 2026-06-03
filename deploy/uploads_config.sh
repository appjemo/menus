#!/usr/bin/env bash
# Sube los límites de subida (PHP-FPM y Nginx) para permitir videos hasta ~200 MB.
set -euo pipefail

echo "==> PHP-FPM límites de subida"
sudo tee /etc/php/8.4/fpm/conf.d/99-jemo-uploads.ini >/dev/null <<'INI'
upload_max_filesize = 200M
post_max_size = 205M
memory_limit = 256M
max_execution_time = 180
max_input_time = 180
INI
sudo systemctl restart php8.4-fpm

echo "==> Nginx client_max_body_size"
if grep -q 'client_max_body_size' /etc/nginx/sites-available/menus; then
  sudo sed -i 's/client_max_body_size .*/client_max_body_size 210M;/' /etc/nginx/sites-available/menus
else
  sudo sed -i '/root \/var\/www\/menus\/public;/a\    client_max_body_size 210M;' /etc/nginx/sites-available/menus
fi
sudo nginx -t && sudo systemctl reload nginx

echo "UPLOADS_CONFIG_DONE"
php -i 2>/dev/null | grep -E 'upload_max_filesize|post_max_size' || true
