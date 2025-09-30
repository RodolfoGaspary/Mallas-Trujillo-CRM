<?php
// pdf.php
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

// Load items
$items = [];
try {
    $stmtItems = $pdo->prepare("SELECT i.id_items, i.detalle AS item_detalle, i.ancho, i.alto, i.area
        FROM proformas_items pi
        JOIN items i ON pi.id_item = i.id_items
        WHERE pi.id_proforma = :id
        GROUP BY i.id_items, i.detalle, i.ancho, i.alto, i.area
        ORDER BY i.id_items");
    $stmtItems->execute([':id' => $id]);
    $items = $stmtItems->fetchAll();
} catch (Throwable $e) {
    $items = [];
}

if (empty($items)) {
    $items_rows = [[
        'detalle' => $row['item_detalle'] ?? '-',
        'ancho' => $row['ancho'] ?? '',
        'alto' => $row['alto'] ?? '',
        'area' => $row['area'] ?? '',
    ]];
} else {
    $items_rows = [];
    foreach ($items as $it) {
        $items_rows[] = [
            'detalle' => $it['item_detalle'] ?? '-',
            'ancho' => $it['ancho'] ?? '',
            'alto' => $it['alto'] ?? '',
            'area' => $it['area'] ?? '',
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Proforma #<?php echo $row['id_proformas']; ?></title>
    <link rel="stylesheet" href="../CSS/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <style>
        /* Print-specific styles */
        @media print {
            @page {
                size: A4;
                margin: 0.5cm;
            }
            body {
                margin: 0;
                padding: 0;
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .container-fluid {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
        }
        
        /* Screen styles */
        @media screen {
            body {
                background: white;
                padding: 20px;
            }
            .container-fluid {
                max-width: 210mm;
                margin: 0 auto;
                background: white;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
        }
        
        /* Common styles */
        .card {
            border: 1px solid #000;
            margin-bottom: 15px;
            background: white !important;
        }
        .table th {
            background-color: #f8f9fa !important;
        }
        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="no-print text-center mb-3">
        <button onclick="window.print()" class="btn btn-success">Imprimir/Guardar como PDF</button>
        <button onclick="window.close()" class="btn btn-secondary">Cerrar</button>
        <p class="text-muted mt-2">Use el botón "Imprimir" y seleccione "Guardar como PDF" como destino</p>
    </div>
    <div class="container-fluid py-0">
        <!-- Print button for manual printing -->
        <!-- <div class="no-print text-center mb-3">
            <button onclick="window.print()" class="btn btn-success">Imprimir PDF</button>
            <button onclick="window.close()" class="btn btn-secondary">Cerrar</button>
        </div> -->
        <div class="card mb-2">
            <div class="row">
                <div class="card-body col-6">
                    <div class="row">
                        <div class="text-center">
                            <h3>
                            <img src="../Assets/mt_logo.jpg" alt="Company Logo" style="max-height: 50px;">
                             MALLAS TRUJILLO</h3>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col">
                            <p>
                            <img src="../Assets/facebook.png" alt="Facebook" style="max-height: 20px;">
                             MALLAS TRUJILLO</p>
                        </div>
                        <div class="col">
                            <p>
                            <img src="../Assets/instagram.png" alt="Instagram" style="max-height: 20px;">
                             MALLASTRUJILLO</p>
                        </div>
                    </div>
                    <div class="row text-center">
                        <h5>
                        <img src="../Assets/whatsapp.png" alt="Whatsapp" style="max-height: 20px;">
                         Whatsapp: 976 309 660</h5>
                    </div>
                </div>
                <div class="col-6 mb-0 row text-center align-items-center d-flex">
                    <h4 class="card-title"><strong>Proforma </strong>#<?php echo $row['id_proformas']; ?></h4>
                </div>
            </div>
        </div>
        <div class="card mb-2">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p class="mb-1"><strong>Cliente:</strong> <?php echo htmlspecialchars($row['cliente_nombre'] ?? ''); ?></p>
                        <p class="mb-1"><strong>Teléfono:</strong> <?php echo htmlspecialchars($row['telefono'] ?? ''); ?></p>
                    </div>
                    <div class="col-6 text-start">
                        <p class="mb-1"><strong>Fecha:</strong> <?php echo htmlspecialchars($row['fecha'] ?? ''); ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($row['email'] ?? ''); ?></p>
                    </div>
                </div>
                <div class="row col-12">
                    <p class="mb-1"><strong>Dirección: </strong><?php echo htmlspecialchars($row['direccion_proformas'] ?? ''); ?></p>
                </div>
            </div>
        </div>
        <div class="card mb-2">
            <div class="card-body">
                <div class="row">
                    <?php if (!empty($items_rows)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Detalle</th>
                                    <th class="text-end">Área</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items_rows as $index => $item): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($item['detalle'] ?? '-'); ?></td>
                                    <td class="text-end"><?php echo htmlspecialchars(number_format($item['area'] ?? '', 2)); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-6 row">
                        <div class="col-12 mb=0">
                            <p><strong>Precio por MT2: </strong>S/.<?php echo htmlspecialchars($row['precio'] ?? ''); ?></p>
                            <p><strong>Descuento: </strong><?php echo htmlspecialchars($row['descuento'] ?? ''); ?>%</p>
                        </div>
                    <div class='align-top col-12 row mb-0'>
                        <div class="col">
                            <p><strong>Arn:</strong> <?php echo htmlspecialchars($row['arnes'] ?? ''); ?></p>
                        </div>
                        <div class="col">
                            <p><strong>Esc:</strong> <?php echo htmlspecialchars($row['escaleras'] ?? ''); ?></p>
                        </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <p><strong>Total MT2: </strong><?php echo htmlspecialchars(number_format($row['area_total'] ?? '',2)); ?>mt2</p>
                        <p><strong>Subtotal: </strong>S/.<?php echo htmlspecialchars(number_format($row['subtotal'] ?? '',2)); ?></p>
                        <p><strong>Subtotal con Descuento: </strong>S/.<?php echo htmlspecialchars(number_format($row['subtotal_desc'] ?? '',2)); ?></p>
                        <p><strong>Costo adicional: </strong>S/.<?php echo htmlspecialchars(number_format($row['costo_adicional'] ?? '',2)); ?></p>
                        <p><strong>Total: </strong>S/.<?php echo htmlspecialchars(number_format($row['total'] ?? '',2)); ?></p>
                    </div>
                </div>
                <div class="col row">
                    <div class="col text-start">
                        <p><strong>Detalle costo adicional: </strong><?php echo htmlspecialchars($row['descripcion_costo_adicional'] ?? ''); ?></p>
                        <p><strong>Observaciones: </strong><?php echo nl2br(htmlspecialchars($row['detalles'] ?? '')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../CSS/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
        <script>
        // Auto-print on load for better UX
        window.onload = function() {
            // Don't auto-print on mobile
            if (!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                setTimeout(() => {
                    window.print();
                }, 1000);
            }
        };
    </script>
</body>
</html>