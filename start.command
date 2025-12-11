#!/bin/bash
cd "$(dirname "$0")"

echo "=================================================="
echo "   Démarrage de l'application Pagaplie Web"
echo "=================================================="
echo ""
echo "Le serveur démarre à l'adresse : http://localhost:8000"
echo "Fermez cette fenêtre pour arrêter le serveur."
echo ""

# Open the browser after a slight delay to ensure server is ready
(sleep 1 && open "http://localhost:8000") &

# Start the PHP server
php -S localhost:8000
