#!/bin/bash

# Script para hacer commit de cambios en el tema y actualizar la referencia en el proyecto base
# Uso: ./theme-commit.sh "mensaje del commit"

set -e

THEME_DIR="themes/themeCulturaenlinea"
THEME_NAME="themeCulturaenlinea"

# Verificar que se proporcionó un mensaje de commit
if [ -z "$1" ]; then
    echo "❌ Error: Debes proporcionar un mensaje de commit"
    echo "Uso: ./theme-commit.sh \"mensaje del commit\""
    exit 1
fi

COMMIT_MSG="$1"

echo "🎨 Procesando cambios en el tema $THEME_NAME..."
echo ""

# Ir al directorio del tema
cd "$THEME_DIR"

# Verificar si hay cambios
if git diff-index --quiet HEAD --; then
    echo "ℹ️  No hay cambios en el tema para commitear"
    cd ../..
else
    echo "📝 Cambios detectados en el tema:"
    git status --short
    echo ""
    
    # Agregar todos los cambios
    git add .
    
    # Hacer commit
    git commit -m "$COMMIT_MSG"
    echo "✅ Commit realizado en el tema"
    
    # Obtener el hash del commit
    COMMIT_HASH=$(git rev-parse --short HEAD)
    echo "📌 Commit hash: $COMMIT_HASH"
    echo ""
    
    # Preguntar si hacer push
    read -p "¿Hacer push al repositorio remoto del tema? (s/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[SsYy]$ ]]; then
        CURRENT_BRANCH=$(git branch --show-current)
        git push origin "$CURRENT_BRANCH"
        echo "✅ Push realizado al tema (rama: $CURRENT_BRANCH)"
    else
        echo "⏭️  Push omitido"
    fi
    
    # Volver al directorio raíz
    cd ../..
    
    echo ""
    echo "🔄 Actualizando referencia del submódulo en el proyecto base..."
    
    # Agregar la nueva referencia del submódulo
    git add "$THEME_DIR"
    
    # Hacer commit en el proyecto base
    git commit -m "Actualizar $THEME_NAME a commit $COMMIT_HASH"
    echo "✅ Referencia del submódulo actualizada en el proyecto base"
    echo ""
    
    # Preguntar si hacer push del proyecto base
    read -p "¿Hacer push al repositorio del proyecto base? (s/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[SsYy]$ ]]; then
        CURRENT_BRANCH=$(git branch --show-current)
        git push origin "$CURRENT_BRANCH"
        echo "✅ Push realizado al proyecto base (rama: $CURRENT_BRANCH)"
    else
        echo "⏭️  Push omitido"
    fi
fi

echo ""
echo "✨ ¡Proceso completado!"
