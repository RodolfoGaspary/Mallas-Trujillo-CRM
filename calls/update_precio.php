<?php
// update_precio.php
header('Content-Type: application/json');
require_once __DIR__ . '/../Backend/db_connect.php';

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Debug: Log received data
error_log("Received data: " . print_r($data, true));

if ($data && isset($data['precio']) && isset($data['descuento1']) && isset($data['descuento2']) && isset($data['descuento3'])) {
    
    // Retrieve form values from JSON
    $precio = $data['precio'];
    $descuento1 = $data['descuento1'];
    $descuento2 = $data['descuento2'];
    $descuento3 = $data['descuento3'];
    $m2_1 = $data['m2_1'];
    $m2_2 = $data['m2_2'];
    $m2_3 = $data['m2_3'];

    // Debug: Log values before update
    error_log("Updating - Precio: $precio, Desc1: $descuento1, Desc2: $descuento2, Desc3: $descuento3");

    try {
        // Update the precios table
        $pdo = get_pdo_connection();
        $stmt = $pdo->prepare('UPDATE precios SET precio = :precio, descuento_1 = :descuento1, descuento_2 = :descuento2, descuento_3 = :descuento3, m2_1 = :m2_1, m2_2 = :m2_2, m2_3 = :m2_3 WHERE id = :id');
        
        $result = $stmt->execute([
            'precio' => $precio,
            'descuento1' => $descuento1,
            'descuento2' => $descuento2,
            'descuento3' => $descuento3,
            'm2_1' => $m2_1,
            'm2_2' => $m2_2,
            'm2_3' => $m2_3,
            'id' => 1
        ]);

        // Debug: Log update result
        error_log("Update result: " . ($result ? 'true' : 'false'));
        error_log("Rows affected: " . $stmt->rowCount());

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            // Check if the values are actually different
            $checkStmt = $pdo->prepare('SELECT * FROM precios WHERE id = :id');
            $checkStmt->execute(['id' => 1]);
            $current = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Current values - Precio: {$current['precio']}, Desc1: {$current['descuento_1']}, Desc2: {$current['descuento_2']}, Desc3: {$current['descuento_3']}");
            
            if ($current['precio'] == $precio && 
                $current['descuento_1'] == $descuento1 && 
                $current['descuento_2'] == $descuento2 && 
                $current['descuento_3'] == $descuento3) {
                echo json_encode(['success' => true, 'message' => 'No changes detected']);
            } else {
                echo json_encode(['error' => 'Update failed - no rows affected']);
            }
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    error_log("Invalid data received: " . print_r($data, true));
    echo json_encode(['error' => 'Invalid or missing data']);
}