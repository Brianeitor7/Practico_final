#!/bin/bash
# ============================================================
# demo_usuario.sh — Demostración del ciclo de vida de un usuario
# (Parte 2: crear / modificar / bloquear / eliminar)
#
# Uso:  sudo bash demo_usuario.sh   (o desde /usr/local/bin)
# Cada paso se muestra en pantalla y queda registrado en
# /var/log/demo_usuario.log
# ============================================================
set -uo pipefail
LOG="/var/log/demo_usuario.log"
USUARIO="usuario_temporal"

paso() {
    echo ""
    echo "════════════════════════════════════════════════════════"
    echo "  $1"
    echo "════════════════════════════════════════════════════════"
    echo "[$(date '+%H:%M:%S')] === $1 ===" >> "$LOG"
}

{
paso "PASO 1 — Crear el usuario «${USUARIO}»"
useradd -m -s /bin/bash -c "Usuario de prueba temporal" "$USUARIO" 2>&1
echo "usuario_temporal:Temporal*2026" | chpasswd
id "$USUARIO"

paso "PASO 2 — Modificar el usuario (comentario y grupo)"
usermod -c "Usuario temporal — demostración modificada" "$USUARIO"
usermod -aG desarrolladores "$USUARIO" 2>/dev/null || usermod -aG users "$USUARIO"
grep "^${USUARIO}:" /etc/passwd

paso "PASO 3 — Bloquear la cuenta (usermod -L)"
usermod -L "$USUARIO"
passwd -S "$USUARIO"
grep "^${USUARIO}:" /etc/shadow | cut -d: -f1-2

paso "PASO 4 — Eliminar el usuario y su directorio personal (userdel -r)"
userdel -r "$USUARIO" 2>&1
id "$USUARIO" || echo "(el usuario ya no existe — eliminado correctamente)"

paso "DEMO FINALIZADA ✔"
} 2>&1 | tee -a "$LOG"
