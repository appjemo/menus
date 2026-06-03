#!/usr/bin/env bash
# Instala PHP 8.4 (el composer.lock requiere >=8.4.1) y lo deja como default.
set -euo pipefail
export DEBIAN_FRONTEND=noninteractive

echo "==> Instalando PHP 8.4 + extensiones"
sudo apt-get update -y
sudo apt-get install -y \
  php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring php8.4-xml \
  php8.4-curl php8.4-zip php8.4-bcmath php8.4-gd php8.4-intl php8.4-redis

echo "==> PHP CLI default -> 8.4"
sudo update-alternatives --set php /usr/bin/php8.4

echo "==> Habilitando php8.4-fpm"
sudo systemctl enable --now php8.4-fpm

echo "==> Apuntando Nginx al socket de php8.4-fpm"
sudo sed -i 's|php8.3-fpm.sock|php8.4-fpm.sock|' /etc/nginx/sites-available/menus
sudo nginx -t && sudo systemctl reload nginx

echo "PHP_84_DONE: $(php -v | head -1)"
