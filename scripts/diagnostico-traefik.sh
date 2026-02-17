#!/bin/bash
# Diagnóstico rápido de Traefik. Ejecutar en el servidor.

echo "=== Contenedores Traefik ==="
docker ps -a --filter "name=traefik" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

echo ""
echo "=== Últimas 30 líneas de logs de Traefik ==="
docker logs traefik 2>&1 | tail -30

echo ""
echo "=== Si certs-init falló ==="
docker logs traefik_certs_init 2>&1 | tail -20

echo ""
echo "=== Red traefik_default ==="
docker network inspect traefik_default 2>/dev/null | grep -E '"Name"|"IPv4Address"' || echo "Red no existe. Crear con: docker network create traefik_default"

echo ""
echo "=== App en la red? ==="
docker ps --filter "network=traefik_default" --format "table {{.Names}}\t{{.Status}}"
