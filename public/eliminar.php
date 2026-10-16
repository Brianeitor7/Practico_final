<?php
/**
 * eliminar.php — Confirmación y borrado de producto (DELETE).
 */
declare(strict_types=1);
require __DIR__ . '/db.php';

/* ---------- Obtener el producto a eliminar ---------- */
$id = $_GET['id'] ?? null;
if (!ctype_digit((string) $id)) {
    header('Location: index.php?msg=noexiste');
    exit;
}

$sentencia = $conexion->prepare('SELECT id, nombre FROM productos WHERE id = ?');
$sentencia->bind_param('i', $id);
$sentencia->execute();
$producto = $sentencia->get_result()->fetch_assoc();
$sentencia->close();

if (!$producto) {
    header('Location: index.php?msg=noexiste');
    exit;
}

/* ---------- Confirmar y eliminar ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrar = $conexion->prepare('DELETE FROM productos WHERE id = ?');
    $borrar->bind_param('i', $id);
    $borrar->execute();
    $borrar->close();

    header('Location: index.php?msg=eliminado');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eliminar producto — miproyecto.com</title>
<link rel="stylesheet" href="assets/estilos.css">
</head>
<body>

<header class="navbar">
    <div class="marca">📦 Gestión de Productos <span>— miproyecto.com</span></div>
    <nav>
        <a href="index.php">← Volver</a>
    </nav>
</header>

<main class="contenedor">
    <section class="panel" style="max-width:560px;margin:0 auto;">
        <div class="panel-cabecera">
            <h2>⚠️ Confirmar eliminación</h2>
        </div>
        <div style="padding: 26px;">
            <p style="margin-bottom:10px">
                Está a punto de eliminar el siguiente producto:
            </p>
            <p style="font-size:1.15rem;font-weight:700;margin-bottom:20px">
                «<?= htmlspecialchars($producto['nombre']) ?>»
                <small style="color:#64748b;font-weight:400">(ID #<?= (int) $producto['id'] ?>)</small>
            </p>
            <p style="color:#64748b;margin-bottom:24px">
                Esta acción no se puede deshacer.
            </p>
            <form method="post" class="acciones">
                <button type="submit" class="btn btn-peligro">🗑 Sí, eliminar</button>
                <a href="index.php" class="btn btn-neutro">Cancelar</a>
            </form>
        </div>
    </section>

    <footer class="pie">
        Gestión de Productos · <strong>Versión 1.0</strong> · PHP + MariaDB + Apache2 · miproyecto.com
    </footer>
</main>
</body>
</html>
