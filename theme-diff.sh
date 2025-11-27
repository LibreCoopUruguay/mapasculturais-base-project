#!/bin/bash

# Script para ver las diferencias en el tema
# Uso: ./theme-diff.sh

THEME_DIR="themes/themeCulturaenlinea"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📝 CAMBIOS EN EL TEMA (themeCulturaenlinea)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

cd "$THEME_DIR"

# Verificar si hay cambios
if git diff-index --quiet HEAD --; then
    echo "✅ No hay cambios en el tema"
else
    echo "Archivos modificados:"
    echo ""
    git status --short
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Diferencias detalladas:"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    git diff
fi

cd ../..
