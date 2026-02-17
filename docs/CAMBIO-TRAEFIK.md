# Cambio de Traefik: dar de baja el actual y levantar el del repo

Haz esto **en el servidor** (SSH).

---

## 1. Dar de baja el Traefik actual

### Si lo tienes con Docker Compose en otra carpeta

Entra en la carpeta donde está el compose de Traefik y baja el proyecto:

```bash
cd /ruta/donde/está/tu/traefik
docker compose down
```

(Usa el nombre del archivo si no es `docker-compose.yml`, por ejemplo:  
`docker compose -f docker-compose.traefik.yml down`.)

### Si no recuerdas la carpeta

Busca el contenedor y bájalo por nombre:

```bash
docker ps -a | grep traefik
docker stop traefik
docker rm traefik
```

Si el contenedor no se llama `traefik`, usa el nombre que salga en la primera línea.

---

## 2. Crear la red (por si el anterior la borró)

```bash
docker network create traefik_default
```

(Si dice que ya existe, no pasa nada.)

---

## 3. Preparar el Traefik nuevo (certificados)

Entra en la carpeta del proyecto (donde está `docker-compose.traefik.yml`):

```bash
cd /ruta/a/pro-roller-inventory
```

Genera el certificado autofirmado:

```bash
bash scripts/generate-traefik-cert.sh
```

O a mano:

```bash
mkdir -p certs
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout certs/key.pem -out certs/cert.pem -subj "/CN=localhost"
```

---

## 4. Levantar el Traefik nuevo

En la misma carpeta del proyecto:

```bash
docker compose -f docker-compose.traefik.yml up -d
```

Comprueba que esté corriendo:

```bash
docker ps | grep traefik
```

---

## 5. Levantar (o reiniciar) tu app

Si la app ya estaba levantada, Traefik la verá solo por estar en la red `traefik_default`. Si la bajaste antes, levántala:

```bash
docker compose -f docker-compose.production.yml up -d
```

---

## 6. Probar

- HTTP: `http://TU_IP`
- HTTPS: `https://TU_IP` (el navegador avisará; "Avanzado" → "Acceder")
- Dashboard Traefik: `http://TU_IP:8080`

---

## Resumen de comandos (todo seguido)

```bash
# 1. Bajar Traefik viejo (elige una opción)
docker stop traefik && docker rm traefik

# 2. Red
docker network create traefik_default 2>/dev/null || true

# 3. Ir al proyecto y generar cert
cd /ruta/a/pro-roller-inventory
bash scripts/generate-traefik-cert.sh

# 4. Levantar Traefik nuevo
docker compose -f docker-compose.traefik.yml up -d

# 5. Levantar app
docker compose -f docker-compose.production.yml up -d
```

Sustituye `/ruta/a/pro-roller-inventory` por la ruta real en tu servidor (por ejemplo `~/pro-roller-inventory` o `/var/www/pro-roller-inventory`).
