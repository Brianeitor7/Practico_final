#!/bin/bash
# ============================================================
# backup.sh — Respaldo semanal de la aplicación y la base de datos
#
# Instalación:  sudo cp scripts/backup.sh /usr/local/bin/backup.sh
#               sudo chmod +x /usr/local/bin/backup.sh
# Uso manual:   sudo /usr/local/bin/backup.sh
# Cron (root):  0 0 * * 6 /usr/local/bin/backup.sh
#               (todos los sábados a las 00:00)
#
# Qué hace:
#   1. Empaqueta los archivos de la aplicación (sin .git)
#   2. Volca la base de datos con mysqldump
#   3. Genera UN paquete con fecha/hora: respaldo_completo_AAAAMMDD_HHMM.tar.gz
#   4. Guarda el respaldo en /var/www/backups (fuera del DocumentRoot público)
#   5. Registra todo en /var/log/backup.log
#   6. Rota: conserva los últimos 4 respaldos locales
#   7. Sube el respaldo a Backblaze B2 (rclone) y rota en la nube (≥ 4 semanas)
# ============================================================
set -euo pipefail

FECHA=$(date +%Y%m%d_%H%M)
BACKUP_DIR="/var/www/backups"                  # Fuera de /var/www/miproyecto/public
APP_DIR="/var/www/miproyecto"
DB_NAME="productos_db"
MYSQL_CNF="/etc/mysql/backup.cnf"              # Credenciales de mysqldump (root:600)
LOG="/var/log/backup.log"
RETENER=4                                      # Resguardos semanales a conservar
RCLONE_CONF="/etc/rclone/rclone.conf"          # Configuración de Backblaze B2
REMOTO="b2practico:BucketPractico"             # Destino en la nube

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" >> "$LOG"; }

log "===== Inicio del respaldo ====="
echo "Backup iniciado: $FECHA"

mkdir -p "$BACKUP_DIR"

# ---------- 1) Respaldo de archivos de la aplicación ----------
APP_TAR="${BACKUP_DIR}/respaldo_app_${FECHA}.tar.gz"
tar --exclude="${APP_DIR}/.git" \
    --exclude="${BACKUP_DIR}" \
    -czf "$APP_TAR" -C "$(dirname "$APP_DIR")" "$(basename "$APP_DIR")"
log "Aplicación empaquetada: $(basename "$APP_TAR") ($(du -h "$APP_TAR" | cut -f1))"

# ---------- 2) Volcado de la base de datos ----------
DB_SQL="${BACKUP_DIR}/respaldo_db_${FECHA}.sql.gz"
mysqldump --defaults-extra-file="$MYSQL_CNF" "$DB_NAME" | gzip > "$DB_SQL"
log "Base de datos volcada: $(basename "$DB_SQL") ($(du -h "$DB_SQL" | cut -f1))"

# ---------- 3) Paquete único con fecha y hora ----------
PAQUETE="${BACKUP_DIR}/respaldo_completo_${FECHA}.tar.gz"
tar -czf "$PAQUETE" -C "$BACKUP_DIR" \
    "$(basename "$APP_TAR")" "$(basename "$DB_SQL")"
rm -f "$APP_TAR" "$DB_SQL"
log "Paquete completo: $(basename "$PAQUETE") ($(du -h "$PAQUETE" | cut -f1))"

# ---------- Verificación de integridad ----------
if tar -tzf "$PAQUETE" > /dev/null 2>&1; then
    log "Verificación de integridad: OK ($(tar -tzf "$PAQUETE" | wc -l) archivos)"
else
    log "ERROR: el paquete $PAQUETE está corrupto"
    echo "ERROR: respaldo corrupto" >&2
    exit 1
fi

# ---------- 4) Rotación local: conservar los últimos 4 ----------
ls -1t "${BACKUP_DIR}"/respaldo_completo_*.tar.gz 2>/dev/null \
    | tail -n +$((RETENER + 1)) \
    | xargs -r rm -f --
LOCALES=$(ls -1 "${BACKUP_DIR}"/respaldo_completo_*.tar.gz 2>/dev/null | wc -l)
log "Rotación local: ${LOCALES} respaldo(s) en disco (política: ${RETENER})"

# ---------- 5) Copia a Backblaze B2 (rclone) ----------
if [ -f "$RCLONE_CONF" ]; then
    rclone --config "$RCLONE_CONF" copy "$PAQUETE" "$REMOTO" \
           --log-file "$LOG" --log-level INFO
    log "Respaldo copiado a la nube: ${REMOTO}"

    # Rotación en la nube: elimina objetos con más de 28 días
    # (4 semanas ≈ 4 respaldos semanales conservados)
    rclone --config "$RCLONE_CONF" delete "$REMOTO" --min-age 28d >> "$LOG" 2>&1
    NUBEOSOS=$(rclone --config "$RCLONE_CONF" lsf "$REMOTO" 2>/dev/null | wc -l)
    log "Rotación en la nube: ${NUBEOSOS} objeto(s) en ${REMOTO}"
else
    log "AVISO: no existe $RCLONE_CONF — se omite la copia a la nube"
fi

log "===== Respaldo finalizado correctamente ====="
echo "Backup finalizado: $(basename "$PAQUETE")"
