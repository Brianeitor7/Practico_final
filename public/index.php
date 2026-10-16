<?php
/**
 * index.php — Página principal: listado de productos y estadísticas.
 * Proyecto: Gestión de Productos (miproyecto.com)
 */
declare(strict_types=1);
require __DIR__ . '/db.php';

/* ---------- Estadísticas del inventario ---------- */
$stats = $conexion
    ->query('SELECT COUNT(*) AS total,
                     COALESCE(SUM(cantidad), 0) AS unidades,
                     COALESCE(SUM(precio * cantidad), 0) AS valor,
                     COUNT(DISTINCT categoria) AS categorias
              FROM productos')
    ->fetch_assoc();

/* ---------- Listado de productos ---------- */
$productos = $conexion
    ->query('SELECT id, nombre, descripcion, categoria, precio, cantidad
             FROM productos
             ORDER BY id DESC');

/* ---------- Mensajes de retroalimentación ---------- */
$mensajes = [
    'creado'   => ['tipo' => 'exito', 'texto' => 'Producto creado correctamente.'],
    'editado'  => ['tipo' => 'exito', 'texto' => 'Producto actualizado correctamente.'],
    'eliminado'=> ['tipo' => 'exito', 'texto' => 'Producto eliminado correctamente.'],
    'noexiste' => ['tipo' => 'error', 'texto' => 'El producto solicitado no existe.'],
];
$mensaje = $_GET['msg'] ?? null;
if (isset($mensajes[$mensaje])) {
    $m = $mensajes[$mensaje];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Productos — miproyecto.com</title>
<link rel="stylesheet" href="assets/estilos.css">
</head>
<body>

<header class="navbar">
    <div class="marca">📦 Gestión de Productos <span>— miproyecto.com</span></div>
    <nav>
        <a href="index.php" class="activo">Inicio</a>
        <a href="crear.php">＋ Nuevo producto</a>
    </nav>
</header>

<main class="contenedor">

    <?php if (isset($m)): ?>
        <div class="msg <?= htmlspecialchars($m['tipo']) ?>">
            <?= htmlspecialchars($m['texto']) ?>
        </div>
    <?php endif; ?>

    <section class="estadisticas">
        <div class="tarjeta">
            <div class="etiqueta">Productos registrados</div>
            <div class="valor"><?= (int) $stats['total'] ?></div>
        </div>
        <div class="tarjeta">
            <div class="etiqueta">Unidades en stock</div>
            <div class="valor"><?= (int) $stats['unidades'] ?></div>
        </div>
        <div class="tarjeta">
            <div class="etiqueta">Valor del inventario</div>
            <div class="valor">$<?= number_format((float) $stats['valor'], 2, ',', '.') ?></div>
        </div>
        <div class="tarjeta">
            <div class="etiqueta">Categorías</div>
            <div class="valor"><?= (int) $stats['categorias'] ?></div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-cabecera">
            <h2>Listado de productos</h2>
            <a href="crear.php" class="btn btn-primario">＋ Agregar producto</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($productos->num_rows === 0): ?>
                <tr><td colspan="6" class="sin-datos">
                    No hay productos registrados todavía.
                </td></tr>
            <?php else: ?>
                <?php while ($p = $productos->fetch_assoc()): ?>
                <tr>
                    <td><?= (int) $p['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($p['nombre']) ?></strong><br>
                        <small style="color:#64748b">
                            <?= htmlspecialchars(mb_strimwidth($p['descripcion'] ?? '', 0, 70, '…')) ?>
                        </small>
                    </td>
                    <td><span class="insignia"><?= htmlspecialchars($p['categoria']) ?></span></td>
                    <td class="precio">$<?= number_format((float) $p['precio'], 2, ',', '.') ?></td>
                    <td>
                        <?php if ((int) $p['cantidad'] === 0): ?>
                            <span class="insignia baja">Agotado</span>
                        <?php elseif ((int) $p['cantidad'] <= 5): ?>
                            <span class="insignia baja"><?= (int) $p['cantidad'] ?> (bajo)</span>
                        <?php else: ?>
                            <?= (int) $p['cantidad'] ?> uds.
                        <?php endif; ?>
                    </td>
                    <td>
                        <a class="btn btn-neutro btn-mini" href="editar.php?id=<?= (int) $p['id'] ?>">✏️ Editar</a>
                        <a class="btn btn-peligro btn-mini" href="eliminar.php?id=<?= (int) $p['id'] ?>"
                           onclick="return confirm('¿Eliminar el producto «<?= htmlspecialchars($p['nombre']) ?>»?')">🗑 Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </section>

    <footer class="pie">
        Gestión de Productos · <strong>Versión 1.0</strong> · PHP + MariaDB + Apache2 · miproyecto.com
    </footer>
</main>
</body>
</html>
