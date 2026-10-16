# Practico_final — Centro de cómputos Linux

Proyecto final: administración de un servidor Linux (Ubuntu 26.04 LTS)
con una aplicación web de **gestión de productos** (PHP + MariaDB + Apache2).

URL local de la aplicación: **http://miproyecto.com** (VirtualHost + /etc/hosts)

## Estructura del repositorio

```
Practico_final/
├── public/                  # DocumentRoot del VirtualHost (código de la app)
│   ├── index.php            # Listado de productos + estadísticas (READ)
│   ├── crear.php            # Alta de productos (CREATE)
│   ├── editar.php           # Modificación de productos (UPDATE)
│   ├── eliminar.php         # Borrado de productos (DELETE)
│   ├── db.php               # Conexión a MariaDB (lee credenciales de .env)
│   └── assets/estilos.css   # Hoja de estilos
├── sql/
│   └── productos_db.sql     # Esquema y datos iniciales de la base
├── scripts/
│   ├── deploy.sh            # Despliegue desde la rama main → /usr/local/bin
│   ├── backup.sh            # Respaldo app + BD (local y Backblaze B2) → /usr/local/bin
│   ├── setup_bd.sh          # Creación de la base y usuarios de MariaDB
│   └── demo_usuario.sh      # Demo: crear/modificar/bloquear/eliminar usuario
├── config/
│   ├── miproyecto.com.conf  # VirtualHost de Apache
│   └── env.example          # Plantilla de credenciales (.env NO se versiona)
└── README.md
```

## Seguridad

* **Ninguna contraseña se sube a este repositorio.**
* Las credenciales de la base de datos viven en `.env` (excluido con `.gitignore`).
* Las claves de Backblaze B2 viven solo en el servidor (`/etc/rclone/rclone.conf`).
* `mysqldump` usa `/etc/mysql/backup.cnf` con permisos `600` (solo root).

## Despliegue

```bash
sudo deploy.sh      # descarga la última versión de la rama main y recarga Apache
```

## Respaldo

```bash
sudo /usr/local/bin/backup.sh     # manual
crontab -l                         # 0 0 * * 6  (sábados 00:00)
```

Cada respaldo guarda archivos + base de datos en
`/var/www/backups/respaldo_completo_AAAAMMDD_HHMM.tar.gz`,
sube una copia a Backblaze B2 y conserva los últimos 4 respaldos
semanales (local y en la nube).
