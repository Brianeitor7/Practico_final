#!/bin/bash
# ============================================================
# deploy.sh — Despliegue de la aplicación desde GitHub (rama main)
#
# Instalación:  sudo cp scripts/deploy.sh /usr/local/bin/deploy.sh
#               sudo chmod +x /usr/local/bin/deploy.sh
# Uso:          sudo deploy.sh
# Documentación: git pull desde la rama main del repositorio,
#               ajusta permisos y recarga Apache.
# ============================================================
set -euo pipefail

APP_DIR="/var/www/miproyecto"          # Repositorio local de la aplicación
RAMA="main"                            # Rama de producción
LOG="/var/log/deploy.log"

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" >> "$LOG"; }

echo "=== Despliegue iniciado: $(date '+%Y-%m-%d %H:%M:%S') ==="
log "=== Despliegue iniciado ==="

if [ ! -d "$APP_DIR/.git" ]; then
    echo "ERROR: $APP_DIR no es un repositorio Git." >&2
    log "ERROR: $APP_DIR no es un repositorio Git"
    exit 1
fi

cd "$APP_DIR"

# ---------- 1) Guardar revisión actual ----------
ANTES=$(git rev-parse --short HEAD)

# ---------- 2) Actualizar desde GitHub ----------
# fetch + reset garantiza que producción sea EXACTAMENTE la rama main
# remota (evita conflictos por cambios locales accidentales).
git fetch origin "$RAMA" >> "$LOG" 2>&1
git reset --hard "origin/${RAMA}" >> "$LOG" 2>&1
git clean -fd -e .env >> "$LOG" 2>&1   # limpia archivos no versionados, conservando .env

DESPUES=$(git rev-parse --short HEAD)

if [ "$ANTES" = "$DESPUES" ]; then
    echo "• No hay cambios nuevos (revisión $ANTES — ya está en la última versión)."
    log "Sin cambios. Revisión: $ANTES"
else
    echo "• Aplicación actualizada: $ANTES → $DESPUES"
    log "Actualización: $ANTES → $DESPUES"
    git log --oneline -1 >> "$LOG" 2>&1
fi

# ---------- 3) Permisos ----------
# Grupo desarrolladores puede editar el código; Apache (www-data) lee.
sudo chgrp -R desarrolladores "$APP_DIR"
sudo chmod -R g+rwX,o+rX "$APP_DIR"
sudo chmod 640 "$APP_DIR/.env" 2>/dev/null || true
sudo chgrp www-data "$APP_DIR/.env" 2>/dev/null || true

# ---------- 4) Recargar Apache ----------
sudo systemctl reload apache2
echo "• Apache recargado."
log "Apache recargado. Despliegue finalizado."

echo "=== Despliegue finalizado correctamente ==="
