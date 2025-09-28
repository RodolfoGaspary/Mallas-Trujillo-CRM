<?php
require_once __DIR__ . '/../Backend/db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "Invalid proforma id";
    exit;
}

try {
    $pdo = get_pdo_connection();
} catch (Throwable $e) {
    http_response_code(500);
    echo "DB connection failed";
    exit;
}

$sql = "SELECT p.*, c.nombre AS cliente_nombre, c.telefono, c.email, pi.id_item, i.detalle AS item_detalle, i.ancho, i.alto, i.area
        FROM proformas p
        LEFT JOIN clientes c ON p.id_c_p = c.id_clientes
        LEFT JOIN proformas_items pi ON p.id_proformas = pi.id_proforma
        LEFT JOIN items i ON pi.id_item = i.id_items
        WHERE p.id_proformas = :id LIMIT 1;";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();
if (!$row) {
    echo "Proforma not found";
    exit;
}

// Prepare header-level values
$costo_adicional_header = (float)($row['costo_adicional'] ?? 0);

// Try to load per-item lines from proformas_items (multi-item support)
$items = [];
try {
  // proformas_items now only links items to proforma. Count duplicates as quantity.
  $stmtItems = $pdo->prepare("SELECT i.id_items, i.detalle AS item_detalle, i.ancho, i.alto, i.area
    FROM proformas_items pi
    JOIN items i ON pi.id_item = i.id_items
    WHERE pi.id_proforma = :id
    GROUP BY i.id_items, i.detalle, i.ancho, i.alto, i.area
    ORDER BY i.id_items");
  $stmtItems->execute([':id' => $id]);
  $items = $stmtItems->fetchAll();
} catch (Throwable $e) {
  // If the table doesn't exist or other error, fall back to single-item behavior below
  $items = [];
}

// Treat proforma `descuento` as a percentage (e.g. 10 for 10%)
$descuento_pct = (float)($row['descuento'] ?? 0);

if (!empty($items)) {
  $items_rows = [];
  foreach ($items as $it) {
    $items_rows[] = [
      'detalle' => $it['item_detalle'] ?? '-',
      'ancho' => $it['ancho'] ?? '',
      'alto' => $it['alto'] ?? '',
      'area' => $it['area'] ?? '',
    ];
  }

  // Summary calculations per your updated spec:
  $precio = (float)($row['precio'] ?? 0);
  $area = (float)($row['area_total'] ?? 0);
  $subtotal = (float)($row['subtotal'] ?? 0);
  $descuento_frac = $descuento_pct / 100.0;
  $subtotal_desc = (float)($row['subtotal_desc'] ?? 0);
  $costo_adicional = (float)($row['costo_adicional'] ?? 0);
  $descripcion_costo_adicional = $row['descripcion_costo_adicional'] ?? '';

} else {
  // Fallback single-item behavior (older schema: proformas.id_i_p)
  $precio = (float)($row['precio'] ?? 0);
  $area = (float)($row['area_total'] ?? 0);
  $subtotal = (float)($row['subtotal'] ?? 0);
  $descuento_frac = $descuento_pct / 100.0;
  $subtotal_desc = (float)($row['subtotal_desc'] ?? 0);
  $costo_adicional = (float)($row['costo_adicional'] ?? 0);
  $descripcion_costo_adicional = $row['descripcion_costo_adicional'] ?? '';

  // build single-item representation for the template
  $items_rows = [[
    'detalle' => $row['item_detalle'] ?? '-',
    'ancho' => $row['ancho'] ?? '',
    'alto' => $row['alto'] ?? '',
  ]];
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Proforma #<?php echo $row['id_proformas']; ?></title>
  <link rel="stylesheet" href="../CSS/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <style>
    .dt{font-weight:bold}
  </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Proforma #<?php echo $row['id_proformas']; ?></h1>
    <a class="btn btn-secondary" href="historial.php">Volver al Historial</a>
  </div>
  <div class="row">
    <div class="col-md-7">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Cliente</h5>
          <p class="mb-1"><strong>Nombre:</strong> <?php echo htmlspecialchars($row['cliente_nombre'] ?? ''); ?></p>
          <p class="mb-1"><strong>Teléfono:</strong> <?php echo htmlspecialchars($row['telefono'] ?? ''); ?></p>
          <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($row['email'] ?? ''); ?></p>
          <p class="mb-1"><strong>Dirección:</strong> <?php echo htmlspecialchars($row['direccion_proformas'] ?? ''); ?></p>
        </div>
      </div>
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Información adicional</h5>
          <p class="mb-1"><strong>Fecha:</strong> <?php echo htmlspecialchars($row['fecha'] ?? ''); ?></p>
          <p class="mb-1"><strong>Arnes:</strong> <?php echo htmlspecialchars($row['arnes'] ?? ''); ?></p>
          <p class="mb-1"><strong>Escaleras:</strong> <?php echo htmlspecialchars($row['escaleras'] ?? ''); ?></p>
          <p class="mb-1"><strong>Detalles:</strong> <?php echo htmlspecialchars($row['detalles'] ?? ''); ?></p>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Items</h5>
          <div class="table-responsive">
          <table class="table table-sm">
            <thead>
              <tr>
                <th>#</th>
                <th>Detalle</th>
                <th class="text-end">Alto</th>
                <th class="text-end">Ancho</th>
                <th class="text-end">Área</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items_rows as $idx => $it): ?>
                <tr>
                  <td><?php echo $idx+1; ?></td>
                  <td><?php echo htmlspecialchars($it['detalle']); ?></td>
                  <td class="text-end"><?php echo htmlspecialchars(number_format($it['alto'],4)); ?></td>
                  <td class="text-end"><?php echo htmlspecialchars(number_format($it['ancho'],4)); ?></td>
                  <td class="text-end"><?php echo htmlspecialchars(number_format($it['area'],4)); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-md-5">
      <div class="card mb-3">
        <div class="card-body">
          <h5 class="card-title">Resumen de precios</h5>
          <p class="mb-1"><strong>Precio:</strong> <span class="text-end d-flex"><?php echo htmlspecialchars(number_format($row['precio'],2)); ?></span></p><hr>
          <p class="mb-1"><strong>Area Total:</strong> <span class="text-end d-flex"><?php echo htmlspecialchars(number_format($row['area_total'],2)); ?></span></p><hr>
          <p class="mb-1"><strong>Subtotal:</strong> <span class="text-end d-flex"><?php echo htmlspecialchars(number_format($row['subtotal'],2)); ?></span></p><hr>
          <p class="mb-1"><strong>Descuento:</strong> <span class="text-end d-flex"><?php echo htmlspecialchars(number_format($row['descuento'],2)); ?>%</span></p><hr>
          <p class="mb-1"><strong>Subtotal con descuento:</strong> <span class="text-end d-flex"><?php echo htmlspecialchars(number_format($subtotal_desc,2)); ?></span></p><hr>
          <p class="mb-1"><strong>Costo adicional:</strong> <span class="text-end d-flex"><?php echo htmlspecialchars(number_format($costo_adicional,2)); ?></span></p><hr>
          <p class="mb-1"><strong>Descripción costo adicional:</strong> <span class="text-start d-flex overflow-auto"><?php echo htmlspecialchars($descripcion_costo_adicional ?: '-'); ?></span></p><hr>
          <p class="mb-1"><strong>Total:</strong> <span class="text-end d-flex"><?php echo htmlspecialchars(number_format($row['total'],2)); ?></span></p>
        </div>
      </div>
    </div>
  <script src="../CSS/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</div>
</body>
</html>