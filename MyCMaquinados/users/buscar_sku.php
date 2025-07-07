<?php
require_once './db_conexion.php';

$term = isset($_GET['term']) ? trim($_GET['term']) : '';

if ($term !== '') {
    $sql = "SELECT id_productos AS id, sku, nombre, clase, descripcion, existencia
            FROM productos
            WHERE sku LIKE :term OR nombre LIKE :term
            ORDER BY nombre ASC LIMIT 10";
    $stmt = $cnnPDO->prepare($sql);
    $stmt->execute([':term' => "%$term%"]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
} else {
    echo json_encode([]);
}

