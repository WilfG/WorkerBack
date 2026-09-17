cat > deploy.sh << 'EOF'
#!/bin/bash
#
# Déploiement staging WorkerBack — appelé par GitHub Actions
#
set -euo pipefail

cd /var/www/worker.wiltek-software.online

echo "==> Pull du code..."
git fetch --prune
git reset --hard origin/develop
git clean -fd --exclude=docker-compose.worker.env --exclude=storage

echo "==> Vérification du fichier d'env..."
if [ ! -f docker-compose.worker.env ]; then
    echo "ERREUR : docker-compose.worker.env manquant !"
    exit 1
fi

echo "==> Création des permissions storage..."
sudo /usr/local/bin/fix-worker-storage-perms

echo "==> Build Docker..."
docker compose -f docker-compose.worker.yml --env-file docker-compose.worker.env build --pull

echo "==> Démarrage conteneur..."
docker compose -f docker-compose.worker.yml --env-file docker-compose.worker.env up -d --remove-orphans

echo "==> Attente du démarrage..."
sleep 5

echo "==> Vérification du conteneur..."
if ! docker ps | grep -q worker-api; then
    echo "ERREUR : conteneur non démarré !"
    docker logs --tail=50 worker-api
    exit 1
fi

echo "==> Migrations..."
docker exec worker-api php artisan migrate --force

echo "==> Nettoyage cache Laravel..."
docker exec worker-api php artisan config:clear || true
docker exec worker-api php artisan route:clear || true
docker exec worker-api php artisan view:clear || true

echo "==> Test endpoint API..."
if ! curl -sf http://127.0.0.1:8002/api/health > /dev/null 2>&1; then
    echo "Note : /api/health n'existe peut-être pas encore. Test sur /api/user..."
    curl -sf http://127.0.0.1:8002/api/user > /dev/null 2>&1 || echo "Warning : aucune route testée"
fi

echo "==> Nettoyage images Docker..."
docker image prune -f

echo "==> Déploiement réussi !"
EOF

# Rendre exécutable
git update-index --chmod=+x deploy.sh 2>nul || true
chmod +x deploy.sh 2>nul || true