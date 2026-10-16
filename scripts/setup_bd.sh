#!/bin/bash
# ============================================================
# setup_bd.sh — Crea la base de datos y los usuarios de MariaDB
# Uso (como root):  sudo bash setup_bd.sh
# Las contraseñas se leen del archivo .env (fuera de Git).
# ============================================================
set -euo pipefail
cd "$(dirname "$0")/.."

# ---------- Cargar credenciales desde .env ----------
if [ ! -f .env ]; then
    echo "ERROR: no se encontró .env. Copie config/env.example a .env y complete las claves."
    exit 1
fi
set -a
# shellcheck disable=SC1091
. ./.env
set +a

DB_NAME="${DB_NAME:-productos_db}"
DB_USER="${DB_USER:-appuser}"
DB_PASS="${DB_PASS:?Falta DB_PASS en .env}"
BCK_USER="${BCK_USER:-bckuser}"
BCK_PASS="${BCK_PASS:?Falta BCK_PASS en .env}"

# ---------- Crear base de datos, tabla y datos ----------
mysql < sql/productos_db.sql

# ---------- Crear usuarios con privilegios mínimos ----------
# appuser  → solo CRUD sobre la base de la aplicación
# bckuser  → solo lectura (mysqldump) para los respaldos
mysql <<SQL
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT SELECT, INSERT, UPDATE, DELETE ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';

CREATE USER IF NOT EXISTS '${BCK_USER}'@'localhost' IDENTIFIED BY '${BCK_PASS}';
ALTER USER '${BCK_USER}'@'localhost' IDENTIFIED BY '${BCK_PASS}';
GRANT SELECT, LOCK TABLES ON \`${DB_NAME}\`.* TO '${BCK_USER}'@'localhost';

FLUSH PRIVILEGES;
SQL

echo "✔ Base de datos '${DB_NAME}' lista (usuarios: ${DB_USER}, ${BCK_USER})"
