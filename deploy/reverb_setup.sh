#!/usr/bin/env bash
# Configura Nginx (proxy WebSocket /app) y el servicio systemd de Laravel Reverb.
# Idempotente.
set -euo pipefail

APP_DIR=/var/www/menus
RUN_USER="$(whoami)"

echo "==> [1/3] Nginx vhost con proxy WebSocket para Reverb"
sudo tee /etc/nginx/sites-available/menus >/dev/null <<'NGINX'
server {
    listen 80;
    server_name menus.wearejemo.com 35.232.83.116;
    return 301 https://$host$request_uri;
}
server {
    listen 443 ssl;
    server_name menus.wearejemo.com 35.232.83.116;

    ssl_certificate /etc/letsencrypt/live/menus.wearejemo.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/menus.wearejemo.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    root /var/www/menus/public;
    index index.php;
    charset utf-8;
    client_max_body_size 100M;

    # WebSocket de Laravel Reverb (clientes se conectan a wss://host/app/{key})
    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 600s;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

sudo nginx -t
sudo systemctl reload nginx

echo "==> [2/3] Servicio systemd de Reverb"
sudo tee /etc/systemd/system/reverb.service >/dev/null <<UNIT
[Unit]
Description=Laravel Reverb WebSocket Server (JEMO Menus)
After=network.target

[Service]
Type=simple
User=${RUN_USER}
WorkingDirectory=${APP_DIR}
ExecStart=/usr/bin/php ${APP_DIR}/artisan reverb:start --host=127.0.0.1 --port=8080
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
UNIT

echo "==> [3/3] Habilitar y arrancar Reverb"
sudo systemctl daemon-reload
sudo systemctl enable reverb
sudo systemctl restart reverb
sleep 2
sudo systemctl is-active reverb && echo "Reverb activo" || (sudo journalctl -u reverb --no-pager | tail -20; exit 1)

echo "REVERB_SETUP_DONE"
