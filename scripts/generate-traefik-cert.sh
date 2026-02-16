#!/bin/bash
# Genera certificado autofirmado para Traefik (HTTPS por IP).
# Ejecutar en el servidor, en la carpeta donde está docker-compose.traefik.yml.

set -e
mkdir -p certs
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout certs/key.pem \
  -out certs/cert.pem \
  -subj "/CN=localhost"
chmod 600 certs/key.pem certs/cert.pem
echo "Listo. Certificados en ./certs/"
