<?php
// save_proforma.php
header('Content-Type: application/json');
require_once __DIR__ . '/../Backend/db_connect.php';

try {
    $pdo = get_pdo_connection();
    
    // Get JSON data from request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Insert proforma
    $stmt = $pdo->prepare('INSERT INTO proformas (direccion_proformas, fecha, precio, descuento, costo_adicional, descripcion_costo_adicional, total, detalles, arnes, escaleras, subtotal, subtotal_desc, area_total, id_c_p) VALUES (:direccion, :fecha, :precio, :descuento, :costo_adicional, :descripcion_costo_adicional, :total, :detalles, :arnes, :escaleras, :subtotal, :subtotal_descuento, :area_total, :id_cliente)');

    // Check if the client is already in the database
    $stmtClientes = $pdo->prepare('SELECT id_clientes FROM clientes WHERE nombre = :nombre');
    $stmtClientes->execute([':nombre' => $data['clienteNombre'] ?? '']);
    $cliente = $stmtClientes->fetch();

    // If the client is not in the database, insert it
    if (!$cliente) {
        $stmtClientes = $pdo->prepare('INSERT INTO clientes (nombre, telefono, email) VALUES (:nombre, :telefono, :email)');
        $stmtClientes->execute([
            ':nombre' => $data['clienteNombre'] ?? '',
            ':telefono' => $data['clienteTelefono'] ?? '',
            ':email' => $data['clienteEmail'] ?? ''
        ]);
        $id_cliente = $pdo->lastInsertId();
    } else {
        $id_cliente = $cliente['id_clientes'];
    }

    // Prepare the values for insertion
    $values = [
        ':direccion' => $data['direccion'] ?? '',
        ':fecha' => $data['fecha'] ?? date('Y-m-d'),
        ':precio' => $data['precio'] ?? 0,
        ':descuento' => $data['descuento'] ?? 0,
        ':costo_adicional' => $data['costo_adicional'] ?? 0,
        ':descripcion_costo_adicional' => $data['descripcion_costo_adicional'] ?? '',
        ':total' => $data['total'] ?? 0,
        ':detalles' => $data['detalles_adicionales'] ?? '',
        ':arnes' => $data['arnes'] ?? '',
        ':escaleras' => $data['escaleras'] ?? '',
        ':subtotal' => $data['subtotal'] ?? 0,
        ':subtotal_descuento' => $data['subtotal_desc'] ?? 0,
        ':area_total' => $data['area_total'] ?? 0,
        ':id_cliente' => $id_cliente
    ];

    // Execute the insert statement
    $stmt->execute($values);
    
    $proforma_id = $pdo->lastInsertId();
    
    // Insert items and proforma_items
    
    $area_total = 0.0;

    foreach ($data['items'] as $item) {
        if (!empty($item['detalle']) || !empty($item['alto']) || !empty($item['ancho'])) {
            // Insert into items table
            $stmt = $pdo->prepare('INSERT INTO items (detalle, ancho, alto, area) VALUES (:detalle, :ancho, :alto, :area)');
            $area = (float)$item['ancho'] * (float)$item['alto'];
            $stmt->execute([
                ':detalle' => $item['detalle'],
                ':ancho' => $item['ancho'],
                ':alto' => $item['alto'],
                ':area' => $area
            ]);

            $area_total += $area;

            $item_id = $pdo->lastInsertId();
            
            // Insert into proformas_items table
            $stmt = $pdo->prepare('INSERT INTO proformas_items (id_proforma, id_item) VALUES (:id_proforma, :id_item)');
            $stmt->execute([
                ':id_proforma' => $proforma_id,
                ':id_item' => $item_id
            ]);
        }
    }

    $subtotal = 0.0;
    $subtotal_desc = 0.0;
    $total = 0.0;

    $subtotal = (float)$area_total * (float)$data['precio'];
    $subtotal_desc = (float)$subtotal * (1 - (float)$data['descuento']/100);
    $total = (float)$subtotal_desc + (float)$data['costo_adicional'];

    $values = [
        ':subtotal' => $subtotal,
        ':subtotal_desc' => $subtotal_desc,
        ':total' => $total,
        ':area_total' => $area_total,
        ':id_proforma' => $proforma_id
    ];

    $stmt = $pdo->prepare('UPDATE proformas SET subtotal = :subtotal, subtotal_desc = :subtotal_desc, total = :total, area_total = :area_total WHERE id_proformas = :id_proforma');
    $stmt->execute($values);

    // Commit transaction
    $pdo->commit();
    
    echo json_encode(['success' => true, 'proforma_id' => $proforma_id]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
} 
?>