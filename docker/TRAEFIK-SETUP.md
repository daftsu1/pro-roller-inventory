# Conectar la app a tu Traefik existente

Esta app está preparada para conectarse a un **Traefik que ya tengas corriendo** en el VPS. No incluye configuración de Traefik; solo lo necesario para que Traefik enrute el tráfico a esta aplicación.

## Requisitos

- Traefik ya instalado y corriendo en el VPS.
- Red Docker `traefik` creada (si tu Traefik la usa con otro nombre, cambia `traefik` en `docker-compose.production.yml` por ese nombre).

## Pasos

### 1. Crear la red (si aún no existe)

Si tu Traefik usa una red llamada `traefik`:

```bash
docker network create traefik
```

Si tu red tiene otro nombre, edita en `docker-compose.production.yml` la sección `networks` y el `traefik.docker.network` en las labels del servicio `app`.

### 2. Configurar dominio en la app

En `docker-compose.production.yml`, en las **labels** del servicio `app`, cambia el Host por tu dominio:

```yaml
- "traefik.http.routers.pro-roller.rule=Host(`tu-dominio.com`)"
```

Ajusta también el nombre del router, entrypoints y certresolver si en tu Traefik usas otros (por ejemplo otro entrypoint o otro resolver).

### 3. Variables de entorno

En tu `.env`:

```env
APP_URL=https://tu-dominio.com
APP_ENV=production
APP_DEBUG=false
```

### 4. Levantar la aplicación

```bash
docker-compose -f docker-compose.production.yml up -d --build
```

Traefik debería detectar el servicio por las labels y enrutar el tráfico al contenedor `pro_roller_app` en el puerto 80 interno.

## Qué hace este compose

- **app**: solo `expose: 80` (no publica puertos en el host). Traefik se conecta por la red `traefik` y enruta según las labels.
- **db**: sin `ports`, solo accesible desde la red interna de la app.
- **networks**: la app usa `pro_roller_network` (interna) y `traefik` (externa, la de tu Traefik).

## Si algo no enruta

- Que Traefik esté en la misma red que indica la label `traefik.docker.network=traefik`.
- Revisar logs: `docker logs pro_roller_app` y los logs de Traefik.
- Comprobar que el nombre del router y la regla `Host(...)` no entren en conflicto con otras apps en el mismo Traefik.
