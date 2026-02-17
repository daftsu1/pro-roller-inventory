mmmh #!/bin/sh
# Deploy pro-roller-inventory en VPS
# Siempre: pull, build app/worker, recrear solo app y worker.
# DB y Redis no se recrean (sus datos están en volúmenes; recrearlos no borra datos, pero evitas tocarlos).
# Uso: ./deploy.sh   (ejecutar desde ~/pro-roller-inventory)
set -e

cd "$(dirname "$0")"

echo ">>> Pull..."
git pull

echo ">>> Build app y worker..."
docker compose -f docker-compose.production.yml build --no-cache app worker

echo ">>> Recrear solo app y worker (db y redis siguen; sus datos están en volúmenes)..."
docker compose -f docker-compose.production.yml up -d --force-recreate app worker

echo ">>> Listo."
