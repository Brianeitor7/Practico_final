<?php
/**
 * crear.php — Formulario de alta de producto (CREATE).
 */
declare(strict_types=1);
require __DIR__ . '/db.php';

$categorias = ['Electrónica', 'Informática', 'Oficina', 'Accesorios', 'General'];
$errores = [];

$nombre      = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$categoria   = $_POST['categoria'] ?? 'General';
$precio      = $_POST['precio'] ?? '';
$cantidad    = $_POST['cantidad'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* ---------- Validación del lado del servidor ---------- */
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
        $sentencia = $conexion->prepare(
            'INSERT INTO productos (nombre, descripcion, categoria, precio, cantidad)
             VALUES (?, ?, ?, ?, ?)'
        );
        // Precio como string ('s'): evita truncados de DECIMAL con 'd'
        $sentencia->bind_param(
            'ssssi',
            $nombre,
            $descripcion,
            $categoria,
            $precio,
            $cantidad
        );
        $sentencia->execute();
        $sentencia->close();

        header('Location: index.php?msg=creado');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nuevo producto — miproyecto.com</title>
<link rel="stylesheet" href="assets/estilos.css">
</head>
<body>

<header class="navbar">
    <div class="marca">📦 Gestión de Productos <span>— miproyecto.com</span></div>
    <nav>
        <a href="index.php">← Volver</a>
        <a href="crear.php" class="activo">＋ Nuevo producto</a>
    </nav>
</header>

<main class="contenedor">
    <section class="panel">
        <div class="panel-cabecera">
            <h2>Registrar nuevo producto</h2>
        </div>

        <div style="padding: 26px;">
            <?php if (!empty($errores)): ?>
                <div class="msg error">
                    <strong>No se pudo guardar:</strong>
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
                           value="<?= htmlspecialchars($nombre) ?>" placeholder="Ej.: Teclado mecánico RGB">
                </div>

                <div class="campo">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="3"
                              placeholder="Breve descripción del producto…"><?= htmlspecialchars($descripcion) ?></textarea>
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
                               value="<?= htmlspecialchars($precio) ?>" placeholder="0.00">
                    </div>
                </div>

                <div class="campo">
                    <label for="cantidad">Cantidad en stock *</label>
                    <input type="number" id="cantidad" name="cantidad" min="0" step="1" required
                           value="<?= htmlspecialchars($cantidad) ?>" placeholder="0">
                    <small>Si el stock es 0 el producto aparecerá como «Agotado».</small>
                </div>

                <div class="acciones">
                    <button type="submit" class="btn btn-exito">💾 Guardar producto</button>
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
