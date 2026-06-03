# Raspberry Pi — Configuración en modo kiosko (JEMO Menus)

Guía para dejar un Raspberry Pi mostrando una pantalla del menú a pantalla completa,
arrancando solo al encender. Cada Pi muestra **una** pantalla (un `token`).

> URL del Player: `https://menus.wearejemo.com/play/{TOKEN}`
> El `{TOKEN}` se obtiene en el panel → **Screens** (columna *Token*, botón *View*).

---

## 1. Grabar el sistema operativo

1. Descarga **Raspberry Pi Imager** (https://www.raspberrypi.com/software/).
2. Elige **Raspberry Pi OS (64-bit)** *con escritorio* (no la Lite).
3. En el engranaje ⚙ de opciones avanzadas, preconfigura:
   - **Hostname**: ej. `jemo-pantalla-01`
   - **Usuario/clave** (ej. usuario `jemo`)
   - **Wi-Fi** (SSID + clave del restaurante) y país
   - **Zona horaria**
   - Activar **SSH** (para mantenimiento remoto)
4. Graba la microSD y arranca el Pi.

---

## 2. Ajustes base (una sola vez por Pi)

Abre una terminal en el Pi (o por SSH) y actualiza:

```bash
sudo apt update && sudo apt full-upgrade -y
sudo apt install -y chromium-browser unclutter
```

### Autologin al escritorio y sesión X11

```bash
sudo raspi-config
```
- **System Options → Boot / Auto Login → Desktop Autologin**
- **Advanced Options → Wayland → X11** (el modo kiosko de abajo está probado en X11)
- Reinicia si lo pide.

---

## 3. Script de arranque del kiosko

Crea el script que lanza Chromium a pantalla completa con el token de esta pantalla.
**Reemplaza `PEGA_AQUI_EL_TOKEN`** por el token real de la pantalla.

```bash
mkdir -p ~/.config/jemo
cat > ~/.config/jemo/kiosk.sh <<'EOF'
#!/usr/bin/env bash
TOKEN="PEGA_AQUI_EL_TOKEN"
URL="https://menus.wearejemo.com/play/${TOKEN}"

# Evitar que se apague la pantalla / ahorro de energía
xset s off
xset s noblank
xset -dpms

# Ocultar el cursor cuando no se mueve
unclutter -idle 0.5 -root &

# Limpiar banderas de cierre sucio para que no salga el aviso de "restaurar"
sed -i 's/"exited_cleanly":false/"exited_cleanly":true/' ~/.config/chromium/Default/Preferences 2>/dev/null || true
sed -i 's/"exit_type":"Crashed"/"exit_type":"Normal"/' ~/.config/chromium/Default/Preferences 2>/dev/null || true

chromium-browser \
  --kiosk \
  --incognito \
  --noerrordialogs \
  --disable-infobars \
  --disable-session-crashed-bubble \
  --disable-features=Translate \
  --autoplay-policy=no-user-gesture-required \
  --check-for-update-interval=31536000 \
  --start-fullscreen \
  "$URL"
EOF
chmod +x ~/.config/jemo/kiosk.sh
```

---

## 4. Lanzarlo automáticamente al iniciar sesión

```bash
mkdir -p ~/.config/autostart
cat > ~/.config/autostart/jemo-kiosk.desktop <<'EOF'
[Desktop Entry]
Type=Application
Name=JEMO Menus Kiosk
Exec=/home/jemo/.config/jemo/kiosk.sh
X-GNOME-Autostart-enabled=true
EOF
```

> Si tu usuario no es `jemo`, ajusta la ruta en `Exec=`.

Reinicia para probar:

```bash
sudo reboot
```

Al volver, el Pi debe abrir Chromium en pantalla completa con el menú. La primera vez
descarga y cachea el video (Service Worker); a partir de ahí **funciona aunque se caiga
internet** (muestra el último video y precios).

---

## 5. (Opcional) Reinicio nocturno

Para limpieza/estabilidad, reinicia cada noche a las 4:00 AM:

```bash
sudo crontab -e
# agrega:
0 4 * * * /sbin/reboot
```

---

## 6. Mantenimiento

- **Cambiar de plantilla/precios**: se hace desde el panel; la pantalla se actualiza sola
  (no hay que tocar el Pi).
- **Cambiar el token de la pantalla**: edita `~/.config/jemo/kiosk.sh` y reinicia.
- **Ver si está online**: en el panel → **Dashboard / Screens**, columna de estado
  (verde = vista hace < 2 min).
- **Acceso remoto**: por SSH al hostname/IP del Pi (SSH activado en el paso 1).

---

## 7. Checklist por dispositivo

- [ ] SO grabado con Wi-Fi, zona horaria y SSH
- [ ] `chromium-browser` y `unclutter` instalados
- [ ] Autologin a escritorio + sesión X11
- [ ] `kiosk.sh` con el **token correcto**
- [ ] Autostart creado
- [ ] Reinicio: abre el menú a pantalla completa
- [ ] Prueba offline: desconecta el Wi-Fi → la pantalla sigue mostrando
- [ ] (Opcional) cron de reinicio nocturno
