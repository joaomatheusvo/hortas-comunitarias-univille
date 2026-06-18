#!/bin/sh

echo "Iniciando frontend..."

if [ ! -d "node_modules" ]; then
    echo "Instalando dependencias do Node.js..."
    npm install
else
    echo "Dependencias ja instaladas"
fi

echo "Iniciando servidor de desenvolvimento..."
exec npm run serve
