<?php
require_once './db_conexion.php';
session_start();

// Procesar actualización de precio si se recibe por POST
if (isset($_POST['id_productos'], $_POST['precio'], $_POST['old_price'], $_POST['nombre'], $_POST['sku'], $_POST['clase'], $_POST['descripcion'], $_POST['unidad_medida'])) {
    $id_productos = htmlspecialchars(trim($_POST['id_productos']), ENT_QUOTES, 'UTF-8');
    $nuevo_precio = htmlspecialchars(trim($_POST['precio']), ENT_QUOTES, 'UTF-8');
    $viejo_precio = htmlspecialchars(trim($_POST['old_price']), ENT_QUOTES, 'UTF-8');
    $nombre = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
    $sku = htmlspecialchars(trim($_POST['sku']), ENT_QUOTES, 'UTF-8');
    $clase = htmlspecialchars(trim($_POST['clase']), ENT_QUOTES, 'UTF-8');
    $descripcion = htmlspecialchars(trim($_POST['descripcion']), ENT_QUOTES, 'UTF-8');
    $unidad_medida = htmlspecialchars(trim($_POST['unidad_medida']), ENT_QUOTES, 'UTF-8');

    if ($nuevo_precio === '' || !is_numeric($nuevo_precio) || $nuevo_precio < 0) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Precio inválido'
        ];
        header('Location: productos.php');
        exit();
    }

    if ($nuevo_precio == $viejo_precio) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'El nuevo precio debe ser diferente al actual.'
        ];
        header('Location: productos.php');
        exit();
    }

    try {
        // Guardar historial
        $sql_insert = "INSERT INTO precio_productos_historial (id_productos, old_price, fecha) VALUES (?, ?, NOW())";
        $query_insert = $cnnPDO->prepare($sql_insert);
        $query_insert->execute([$id_productos, $viejo_precio]);

        // Actualizar producto
        $sql = "UPDATE productos SET precio = ?, nombre = ?, sku = ?, clase = ?, descripcion = ?, unidad_medida = ?  WHERE id_productos = ?";
        $query = $cnnPDO->prepare($sql);
        $query->execute([
            $nuevo_precio,
            $nombre,
            $sku,
            $clase,
            $descripcion,
            $unidad_medida,
            $id_productos
        ]);

        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Precio actualizado correctamente.'
        ];
    } catch (PDOException $e) {
        error_log($e->getMessage(), 3, 'errors.log');
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Error al actualizar el precio.'
        ];
    }
    header('Location: productos.php');
    exit();
}

// Si no se recibe POST, solo muestra acceso autorizado
$_SESSION['toastr'] = [
    'type' => 'error',
    'message' => 'Acceso no autorizado'
];
header('Location: productos.php');
exit();
