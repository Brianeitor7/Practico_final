<?php
/**
 * editar.php — Formulario de edición de producto (UPDATE).
 */
declare(strict_types=1);
require __DIR__ . '/db.php';

$categorias = ['Electrónica', 'Informática', 'Oficina', 'Accesorios', 'General'];
$errores = [];

/* ---------- Obtener el producto solicitado ---------- */
$id = $_GET['id'] ?? null;
if (!ctype_digit((string) $id)) {
    header('Location: index.php?msg=noexiste');
    exit;
}

$sentencia = $conexion->prepare('SELECT id, nombre, descripcion, categoria, precio, cantidad
                                 FROM productos WHERE id = ?');
$sentencia->bind_param('i', $id);
$sentencia->execute();
$resultado = $sentencia->get_result();
$producto = $resultado->fetch_assoc();
$sentencia->close();

if (!$producto) {
    header('Location: index.php?msg=noexiste');
    exit;
}

$nombre      = $producto['nombre'];
$descripcion = $producto['descripcion'] ?? '';
$categoria   = $producto['categoria'];
$precio      = $producto['precio'];
$cantidad    = $producto['cantidad'];

/* ---------- Procesar la actualización ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $categoria   = $_POST['categoria'] ?? $categoria;
    $precio      = $_POST['precio'] ?? '';
    $cantidad    = $_POST['cantidad'] ?? '';

    if ($nombre === '' || mb_strlen($nombre) > 100) {
        $errores[] = 'El nombre es obligatorio (máx. 100 caracteres).';
    }
    if (!in_array($categoria, $categorias, true)) {
        $errores[] = 'La categoría seleccionada no es válida.';
    }
    if ($precio === '' || !is_numeric($precio) || (float) $precio < 0) {
        $errores[] = 'El precio debe ser un número mayor o igual a 0.';
    }
    if ($cantidad === '' || !ctype_digit($cantidad) || (int) $cantidad < 0) {
        $errores[] = 'La cantidad debe ser un número entero mayor o igual a 0.';
    }

    if (empty($errores)) {
        $actualizar = $conexion->prepare(
            'UPDATE productos
             SET nombre = ?, descripcion = ?, categoria = ?, precio = ?, cantidad = ?
             WHERE id = ?'
        );
        // Precio como string ('s'): evita truncados de DECIMAL con 'd'/'i'
        $actualizar->bind_param('ssssii', $nombre, $descripcion, $categoria, $precio, $cantidad, $id);
        $actualizar->execute();
        $actualizar->close();

        header('Location: index.php?msg=editado');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar producto — miproyecto.com</title>
<link rel="stylesheet" href="assets/estilos.css">
</head>
<body>

<header class="navbar">
    <div class="marca">📦 Gestión de Productos <span>— miproyecto.com</span></div>
    <nav>
        <a href="index.php">← Volver</a>
        <a href="crear.php">＋ Nuevo producto</a>
    </nav>
</header>

<main class="contenedor">
    <section class="panel">
        <div class="panel-cabecera">
            <h2>Editar producto #<?= (int) $id ?></h2>
        </div>

        <div style="padding: 26px;">
            <?php if (!empty($errores)): ?>
                <div class="msg error">
                    <strong>No se pudo actualizar:</strong>
                    <ul style="margin:8px 0 0 20px">
                        <?php foreach ($errores as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="formulario">
                <div class="campo">
                    <label for="nombre">Nombre del producto *</label>
                    <input type="text" id="nombre" name="nombre" maxlength="100" required
                           value="<?= htmlspecialchars($nombre) ?>">
                </div>

                <div class="campo">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($descripcion) ?></textarea>
                </div>

                <div class="fila">
                    <div class="campo">
                        <label for="categoria">Categoría</label>
                        <select id="categoria" name="categoria">
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>" <?= $c === $categoria ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="precio">Precio (USD) *</label>
                        <input type="number" id="precio" name="precio" step="0.01" min="0" required
                               value="<?= htmlspecialchars((string) $precio) ?>">
                    </div>
                </div>

                <div class="campo">
                    <label for="cantidad">Cantidad en stock *</label>
                    <input type="number" id="cantidad" name="cantidad" min="0" step="1" required
                           value="<?= (int) $cantidad ?>">
                </div>

                <div class="acciones">
                    <button type="submit" class="btn btn-primario">🔄 Actualizar producto</button>
                    <a href="index.php" class="btn btn-neutro">Cancelar</a>
                </div>
            </form>
        </div>
    </section>

    <footer class="pie">
        Gestión de Productos · <strong>Versión 1.0</strong> · PHP + MariaDB + Apache2 · miproyecto.com
    </footer>
</main>
</body>
</html>
