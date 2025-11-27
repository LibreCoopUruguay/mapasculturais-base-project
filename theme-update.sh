#!/bin/bash

# Script para actualizar el tema desde el repositorio remoto
# Uso: ./theme-update.sh

set -e

THEME_DIR="themes/themeCulturaenlinea"

echo "🔄 Actualizando tema desde el repositorio remoto..."
echo ""

# Ir al directorio del tema
cd "$THEME_DIR"

# Obtener la rama actual
CURRENT_BRANCH=$(git branch --show-current)
echo "Rama actual del tema: $CURRENT_BRANCH"

# Verificar si hay cambios sin commitear
if ! git diff-index --quiet HEAD --; then
    echo "⚠️  Advertencia: Hay cambios sin commitear en el tema"
    echo ""
    git status --short
    echo ""
    read -p "¿Descartar estos cambios y actualizar? (s/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[SsYy]$ ]]; then
        git reset --hard HEAD
        echo "✅ Cambios descartados"
    else
        echo "❌ Actualización cancelada"
        exit 1
    fi
fi

# Hacer pull
echo ""
echo "📥 Descargando últimos cambios..."
git pull origin "$CURRENT_BRANCH"

# Obtener el nuevo hash
NEW_HASH=$(git rev-parse --short HEAD)
echo "✅ Tema actualizado a commit: $NEW_HASH"

# Volver al proyecto base
cd ../..

echo ""
echo "🔄 Actualizando referencia del submódulo en el proyecto base..."

# Agregar la nueva referencia
git add "$THEME_DIR"

# Verificar si realmente cambió
if git diff --cached --quiet; then
    echo "ℹ️  El tema ya estaba actualizado"
else
    git commit -m "Actualizar themeCulturaenlinea a commit $NEW_HASH"
    echo "✅ Referencia del submódulo actualizada"
    echo ""
    
    read -p "¿Hacer push al proyecto base? (s/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[SsYy]$ ]]; then
        CURRENT_BRANCH=$(git branch --show-current)
        git push origin "$CURRENT_BRANCH"
        echo "✅ Push realizado"
    fi
fi

echo ""
echo "✨ ¡Actualización completada!"
