<?php
require_once './db_conexion.php';

// Sanitizar entrada
$term = isset($_GET['term']) ? htmlspecialchars($_GET['term'], ENT_QUOTES, 'UTF-8') : '';

$response = [];

if ($term !== '') {
    $sql = "SELECT id_productos, sku, nombre, clase, descripcion, existencia 
            FROM productos 
            WHERE sku LIKE ? 
            ORDER BY sku ASC 
            LIMIT 10";
    $query = $cnnPDO->prepare($sql);
    $query->execute(["%$term%"]);

    $productos = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($productos as $producto) {
        $response[] = [
            'id' => $producto['id_productos'],
            'sku' => $producto['sku'],
            'nombre' => $producto['nombre'],
            'clase' => $producto['clase'],
            'descripcion' => $producto['descripcion'],
            'existencia' => $producto['existencia']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
