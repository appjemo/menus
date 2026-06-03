#!/usr/bin/env bash
# JEMO Menus — Aprovisionamiento de sistema en la VM (Debian 12)
# Idempotente: se puede correr varias veces sin romper nada.
set -euo pipefail

echo "==> [1/8] Swap (2G) para evitar OOM con 2GB RAM"
if ! sudo swapon --show | grep -q /swapfile; then
  sudo fallocate -l 2G /swapfile
  sudo chmod 600 /swapfile
  sudo mkswap /swapfile
  sudo swapon /swapfile
  grep -q '/swapfile' /etc/fstab || echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
  echo "    swap creado"
else
  echo "    swap ya existe"
fi

echo "==> [2/8] Paquetes base"
export DEBIAN_FRONTEND=noninteractive
sudo apt-get update -y
sudo apt-get install -y ca-certificates apt-transport-https lsb-release gnupg curl unzip git zip

echo "==> [3/8] Repo Sury (PHP 8.3 en Debian 12)"
if [ ! -f /etc/apt/sources.list.d/php.list ]; then
  sudo curl -sSLo /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
  echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/php.list
fi
sudo apt-get update -y

echo "==> [4/8] PHP 8.3 + extensiones"
sudo apt-get install -y \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml \
  php8.3-curl php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl php8.3-redis

echo "==> [5/8] Composer"
if ! command -v composer >/dev/null 2>&1; then
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi
composer --version

echo "==> [6/8] MariaDB"
sudo apt-get install -y mariadb-server
sudo systemctl enable --now mariadb

echo "==> [7/8] Node.js 20 LTS"
if ! command -v node >/dev/null 2>&1; then
  curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
  sudo apt-get install -y nodejs
fi
node --version

echo "==> [8/8] Nginx"
sudo apt-get install -y nginx
sudo systemctl enable --now nginx

echo "===================================================="
echo "PROVISION_SYSTEM_DONE"
echo "PHP:   $(php -v | head -1)"
echo "Node:  $(node --version)"
echo "Mysql: $(mariadb --version)"
df -h / | tail -1
free -h | head -2
