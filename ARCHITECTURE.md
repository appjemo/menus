# JEMO Menus — Arquitectura Técnica

> Sistema web multicompañía para gestión de menús digitales en pantallas de
> restaurantes, con actualización de precios en tiempo real sobre video.
>
> Dominio de producción: **menus.wearejemo.com**

---

## 1. Objetivo del sistema

Reemplazar el flujo actual (videos actualizados manualmente vía USB) por una
plataforma web donde:

1. Cada restaurante (compañía) entra a su panel, edita los precios de sus productos
   y los guarda.
2. Las pantallas físicas en el local **reflejan el cambio al instante**, sin que
   nadie toque el hardware.
3. El contenido visual es un video diseñado en After Effects (con espacios
   reservados) y los precios se dibujan dinámicamente encima.

### Principios de diseño

- **Usar el stack que el equipo domina** (Laravel + Filament) en lugar de adoptar
  tecnología nueva por moda.
- **Las pantallas nunca se quedan en negro**: si se cae internet, siguen mostrando
  los últimos precios conocidos.
- **El cliente solo edita valores** (precios, nombres); nunca toca el diseño ni las
  posiciones (eso lo controla JEMO).
- Empezar simple (single-database multi-tenant) y escalar solo cuando se necesite.

---

## 2. Stack tecnológico

| Capa | Tecnología | Por qué |
|---|---|---|
| Backend / Admin | **Laravel 11 + Filament 3** | CRUD, multi-tenant, roles y permisos casi sin código |
| Tiempo real | **Laravel Reverb** | Servidor WebSocket oficial, self-hosted en la misma VM, gratis |
| Multi-tenant | Single DB + `tenant_id` + **spatie/laravel-permission** | Simple de operar, suficiente para el volumen inicial |
| Player (pantalla) | **Blade + Alpine.js + Laravel Echo**, empaquetado como **PWA** | Ligero, cacheable offline, sin framework pesado |
| Reproductor físico | **Raspberry Pi** + Chromium en modo kiosko | Dispositivo controlado, fiable, con cache local |
| Almacenamiento video | **Google Cloud Storage** | Barato, escalable, descarga el peso de la VM |
| Servidor | VM Google Cloud: **Nginx + PHP-FPM + Supervisor** | Stack estándar de producción Laravel |
| Entorno local | **Laravel Sail (Docker)** | Idéntico a producción, no ensucia Windows |
| Base de datos | **MySQL 8** (o PostgreSQL) | Estándar, bien soportado por Filament |

### ¿Por qué NO React/Next + Supabase/Firebase?

No es peor ni mejor, es *otro* camino. Para este equipo implicaría:
- Aprender un stack nuevo en un proyecto comercial con clientes reales.
- Reconstruir a mano lo que Filament regala (panel admin, auth, permisos).
- El "tiempo real" no requiere Node: Laravel Reverb lo resuelve nativamente.

---

## 3. Arquitectura general

```
┌──────────────────────────────────┐          ┌────────────────────────────────────┐
│        VM — Google Cloud         │          │   Raspberry Pi (en cada restaurante)│
│                                  │  wss://   │                                    │
│  ┌────────────────────────────┐  │◄─────────►│  Chromium (modo kiosko)            │
│  │ Laravel + Filament (panel) │  │           │   └─ Player PWA                    │
│  │ Laravel Reverb (WebSocket) │  │   https   │       ├─ video de fondo (.mp4)     │
│  │ MySQL                      │  │◄─────────►│       ├─ overlay de precios        │
│  └────────────────────────────┘  │           │       └─ Service Worker (cache)    │
│            │                     │           │           ├─ cachea video         │
└────────────┼─────────────────────┘          │           └─ cachea último menú    │
             │                                 └────────────────────────────────────┘
             ▼
  Google Cloud Storage  ──── sirve los .mp4 de After Effects ────►
```

### Flujo de actualización de precio (el corazón del sistema)

```
1. Dueño edita "Hamburguesa = $9.99" en Filament y guarda.
2. Laravel persiste el cambio en MySQL.
3. Un Event (PriceUpdated) se dispara hacia el canal privado de esa pantalla.
4. Reverb (WebSocket) empuja el cambio a todos los Players suscritos.
5. El Player actualiza SOLO el texto del precio en el overlay (sin recargar el video).
6. El Service Worker guarda el nuevo menú en cache (para el modo offline).
```

Latencia objetivo: **< 1 segundo** desde "Guardar" hasta verlo en pantalla.

---

## 4. Multi-tenancy (multicompañía)

**Estrategia: single database, discriminada por `tenant_id`.**

- Cada `company` es un tenant (un restaurante o cadena).
- Todas las tablas de negocio llevan `company_id`.
- Un **Global Scope** de Eloquent filtra automáticamente por la compañía del
  usuario autenticado → un usuario nunca ve datos de otra compañía.
- **Filament** se configura con su sistema de tenancy para aislar paneles por
  compañía.
- Un rol especial **Super Admin (JEMO)** puede ver y administrar todas las
  compañías (es quien sube los videos y configura plantillas).

### Roles y permisos (spatie/laravel-permission)

| Rol | Permisos típicos |
|---|---|
| **Super Admin (JEMO)** | Todo: crear compañías, subir videos, definir plantillas y posiciones, gestionar pantallas |
| **Admin de compañía** | Gestionar usuarios de su empresa, editar productos, precios, promos, ver sus pantallas |
| **Editor** | Solo editar productos y precios |
| **Visor** | Solo lectura |

---

## 5. Modelo de datos

```
companies (tenants)
  └─ users (pertenecen a una compañía, con roles)
  └─ products (nombre, precio, categoría)
  └─ promotions (promos dinámicas con vigencia)
  └─ templates (un video + layout de huecos)
       └─ screens (pantalla física, token único, ligada a una plantilla)
       └─ slots (posición de cada precio sobre el video)
```

### Tablas

**companies**
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| name | string | nombre del restaurante |
| slug | string unique | para URLs |
| is_active | boolean | suspender clientes morosos |
| created_at / updated_at | timestamp | |

**users**
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| company_id | FK → companies | nullable solo para Super Admin |
| name, email, password | | auth estándar Laravel |
| (roles/permisos) | | vía tablas de spatie |

**products**
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| company_id | FK | tenant scope |
| name | string | "Hamburguesa Clásica" |
| price | decimal(10,2) | el valor que el cliente edita |
| category | string nullable | "Hamburguesas", "Bebidas" |
| is_active | boolean | mostrar/ocultar |
| sort_order | int | orden en pantalla |

**templates** (el diseño de After Effects)
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| company_id | FK | |
| name | string | "Menú Almuerzo" |
| video_url | string | ruta en Google Cloud Storage |
| video_width / video_height | int | resolución base para posicionar huecos |
| duration_seconds | int nullable | |

**slots** (dónde va cada precio sobre el video)
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| template_id | FK | |
| product_id | FK nullable | qué producto se muestra aquí |
| label | string nullable | texto fijo opcional |
| pos_x / pos_y | int | coordenadas (px sobre el video base) |
| font_size | int | |
| font_color | string | hex |
| font_family | string nullable | |
| align | enum(left,center,right) | |

**screens** (cada pantalla física)
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| company_id | FK | |
| template_id | FK | qué plantilla muestra |
| name | string | "Pantalla caja 1" |
| token | uuid unique | identifica la pantalla en la URL del Player |
| last_seen_at | timestamp nullable | heartbeat para saber si está online |

**promotions**
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| company_id | FK | |
| product_id | FK nullable | |
| title | string | |
| promo_price | decimal nullable | |
| starts_at / ends_at | timestamp | vigencia automática |
| is_active | boolean | |

---

## 6. El Player (la pantalla)

URL única por pantalla: **`menus.wearejemo.com/play/{token}`**

El Raspberry Pi abre esa URL en Chromium kiosko al encender. La página:

1. Descarga el `video_url` (cacheado por el Service Worker) y lo reproduce en loop,
   a pantalla completa, como fondo.
2. Pinta una capa HTML absoluta encima. Por cada `slot`, posiciona el precio del
   producto en sus coordenadas `(pos_x, pos_y)`, escalando si la TV tiene otra
   resolución que el video base.
3. Se conecta vía **Laravel Echo** al canal privado `screen.{token}`.
4. Al recibir un evento `PriceUpdated` / `MenuUpdated`, actualiza solo el texto del
   precio afectado — el video nunca se interrumpe.

### Escalado de posiciones

Las coordenadas se guardan respecto a `video_width × video_height` (ej. 1920×1080).
El Player calcula un factor de escala según el tamaño real de la pantalla, así el
mismo layout funciona en TVs de distinta resolución.

---

## 7. Modo offline (resiliencia)

Requisito crítico: **si se cae internet, la pantalla sigue mostrando los últimos
precios.** Se logra con la PWA:

- **Service Worker** cachea:
  - el archivo `.mp4` (cache-first, se actualiza en segundo plano si cambia),
  - el último JSON del menú (productos + slots + precios).
- Al arrancar sin red, el Player carga todo desde cache y muestra el último estado.
- Cuando vuelve la red:
  - reconecta automáticamente al WebSocket (Echo reintenta),
  - hace un *fetch* del menú actual por si hubo cambios mientras estuvo offline,
  - refresca el cache.
- **Heartbeat**: el Player envía un ping periódico → `screens.last_seen_at`, para
  que en el panel JEMO se vea qué pantallas están online/offline.

---

## 8. Raspberry Pi (reproductor físico)

Configuración estándar para cada unidad:

- **SO**: Raspberry Pi OS Lite + Chromium.
- **Arranque automático** en modo kiosko apuntando a la URL del Player:
  `chromium-browser --kiosk --noerrordialogs --disable-infobars https://menus.wearejemo.com/play/{token}`
- Ocultar cursor, deshabilitar protector de pantalla y gestión de energía.
- Reinicio nocturno opcional (cron) para limpieza.
- **Provisión**: cada Pi se entrega pre-configurado con el token de su pantalla.

> Más adelante se puede crear una imagen base de SD reutilizable para clonar Pis
> rápidamente.

---

## 9. Almacenamiento de video (Google Cloud Storage)

- Los `.mp4` exportados de After Effects se suben a un bucket de GCS.
- Filament sube el archivo y guarda la URL pública (o firmada) en `templates.video_url`.
- Ventajas: no ocupa disco de la VM, escala a muchos clientes, mejor ancho de banda.
- El Service Worker del Player cachea el video en el Pi tras la primera descarga.

---

## 10. Despliegue en producción

VM en Google Cloud (Ubuntu):

- **Nginx** como servidor web + proxy.
- **PHP-FPM** para Laravel.
- **MySQL 8** (en la VM o Cloud SQL más adelante).
- **Supervisor** mantiene vivos:
  - `php artisan reverb:start` (servidor WebSocket),
  - `php artisan queue:work` (colas para eventos/jobs).
- **Certbot / Let's Encrypt** para SSL en `menus.wearejemo.com`.
- Reverb expuesto vía Nginx con WebSocket upgrade (wss).
- Deploy: git pull + `composer install` + `migrate` + `build` (o pipeline simple).

---

## 11. Seguridad

- Canales WebSocket **privados**: cada Player solo puede suscribirse al canal de su
  propio token (autorización por canal).
- Tenant isolation por Global Scope → imposible leer datos de otra compañía.
- Roles/permisos en cada acción de Filament.
- HTTPS/WSS obligatorio.
- Tokens de pantalla como UUID no adivinables.
- Rate limiting en endpoints públicos del Player.

---

## 12. Roadmap de implementación

### Fase 0 — Entorno (Docker/Sail)
- [ ] Crear proyecto Laravel 11 con Sail
- [ ] Levantar contenedores (app, mysql) y verificar que corre
- [ ] Instalar Filament 3 + crear usuario admin

### Fase 1 — Base de datos y multi-tenant
- [ ] Migraciones de todas las tablas (sección 5)
- [ ] Modelos Eloquent + relaciones
- [ ] Global Scope por `company_id`
- [ ] spatie/laravel-permission + roles base
- [ ] Tenancy de Filament

### Fase 2 — Panel de administración (CRUD)
- [ ] Recurso Filament: Compañías (solo Super Admin)
- [ ] Recurso: Usuarios + asignación de roles
- [ ] Recurso: Productos / Precios
- [ ] Recurso: Plantillas (subida de video a GCS + editor de slots)
- [ ] Recurso: Pantallas (con token y URL del Player)
- [ ] Recurso: Promociones

### Fase 3 — Player + tiempo real
- [ ] Página del Player `/play/{token}`
- [ ] Render del video + overlay de slots con escalado
- [ ] Instalar y configurar Laravel Reverb
- [ ] Eventos `MenuUpdated` / `PriceUpdated` broadcasting a canal privado
- [ ] Laravel Echo en el Player suscrito al canal
- [ ] Autorización de canal por token

### Fase 4 — Offline / PWA
- [ ] Manifest PWA
- [ ] Service Worker: cache de video y menú
- [ ] Reconexión automática + refetch al volver online
- [ ] Heartbeat de pantallas (online/offline en panel)

### Fase 5 — Despliegue
- [ ] Configurar VM: Nginx, PHP-FPM, MySQL, Supervisor
- [ ] Dominio menus.wearejemo.com + SSL
- [ ] Reverb tras Nginx (wss)
- [ ] Bucket GCS + credenciales
- [ ] Imagen/configuración del Raspberry Pi en kiosko

---

## 13. Decisiones tomadas (registro)

| Decisión | Elección | Fecha |
|---|---|---|
| Stack principal | Laravel 11 + Filament 3 | 2026-06-03 |
| Tiempo real | Laravel Reverb (self-hosted) | 2026-06-03 |
| Multi-tenancy | Single DB + tenant_id | 2026-06-03 |
| Reproductor físico | Smart TV + Raspberry Pi (Chromium kiosko) | 2026-06-03 |
| Comportamiento offline | Mostrar últimos precios cacheados | 2026-06-03 |
| Origen del diseño | Video por cliente en After Effects (JEMO define posiciones) | 2026-06-03 |
| Entorno local | Laravel Sail (Docker) | 2026-06-03 |
| Almacenamiento video | Google Cloud Storage | 2026-06-03 |

---

## 14. Cuestiones abiertas / futuras

- ¿Programación de menús por horario? (ej. menú de desayuno vs almuerzo automático).
- ¿Editor visual de slots arrastrando sobre el video, en vez de coordenadas a mano?
- ¿Múltiples pantallas con plantillas distintas en un mismo local?
- ¿Métricas/analítica de uptime de pantallas?
- ¿Migrar a Cloud SQL si crece el número de clientes?
- ¿Imagen de SD clonable para aprovisionar Raspberry Pis en masa?
