<?php
require_once __DIR__ . '/../Backend/db_connect.php';
header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
$sort = trim($_GET['sort'] ?? 'fecha_desc');

try {
    $pdo = get_pdo_connection();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed']);
    exit;
}

// Validate sort parameter against a whitelist to avoid SQL injection.
$orderBy = 'p.created_at DESC';
switch ($sort) {
    case 'fecha_asc':
        $orderBy = 'p.fecha ASC, p.id_proformas DESC';
        break;
    case 'fecha_desc':
        $orderBy = 'p.fecha DESC, p.id_proformas DESC';
        break;
    case 'cliente_asc':
        $orderBy = 'c.nombre COLLATE utf8mb4_general_ci ASC, p.fecha DESC';
        break;
    case 'cliente_desc':
        $orderBy = 'c.nombre COLLATE utf8mb4_general_ci DESC, p.fecha DESC';
        break;
    default:
        $orderBy = 'p.fecha DESC, p.id_proformas DESC';
}

$sql = "SELECT p.id_proformas AS id, c.nombre AS cliente_nombre, c.telefono, p.direccion_proformas, p.fecha, p.precio, p.created_at
        FROM proformas p
        LEFT JOIN clientes c ON p.id_c_p = c.id_clientes
        WHERE (:q = '' OR c.nombre LIKE :like_q)
        ORDER BY " . $orderBy . "\n        LIMIT 10";

$stmt = $pdo->prepare($sql);
$like = '%' . $q . '%';
$stmt->bindValue(':q', $q, PDO::PARAM_STR);
$stmt->bindValue(':like_q', $like, PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll();

echo json_encode($rows, JSON_UNESCAPED_UNICODE);
exit;
