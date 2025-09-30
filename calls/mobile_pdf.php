<?php
// mobile_pdf.php - Same PHP code as your pdf.php but with different styling
require_once __DIR__ . '/../Backend/db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "Invalid proforma id";
    exit;
}

// ... same database code as before ...

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Proforma #<?php echo $row['id_proformas']; ?></title>
    <link rel="stylesheet" href="../CSS/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <style>
        body {
            background: white;
            padding: 20px;
        }
        .container-fluid {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
        }
        .card {
            border: 1px solid #000;
            margin-bottom: 15px;
        }
        .table th {
            background-color: #f8f9fa !important;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mb-3">
        <button onclick="window.print()" class="btn btn-success">Imprimir/Guardar PDF</button>
        <button onclick="window.close()" class="btn btn-secondary">Cerrar</button>
    </div>

    <!-- Same content as your pdf.php -->
    <div class="container-fluid">
        <!-- Your existing PDF content here -->
        <div class="card mb-2">
            <div class="row">
                <div class="card-body col-6">
                    <!-- ... your header content ... -->
                </div>
                <div class="col-6 mb-0 row text-center align-items-center d-flex">
                    <h4 class="card-title"><strong>Proforma </strong>#<?php echo $row['id_proformas']; ?></h4>
                </div>
            </div>
        </div>
        <!-- ... rest of your content ... -->
    </div>

    <script>
        // Mobile-friendly print handling
        function checkMobilePrint() {
            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                // Mobile device - show instructions
                alert('Para guardar como PDF:\n1. Toque el botón "Imprimir/Guardar PDF"\n2. En el menú de impresión, seleccione "Guardar como PDF"\n3. Toque "Guardar"');
            } else {
                // Desktop - auto-print
                window.print();
            }
        }
        
        // Auto-trigger on mobile, manual on desktop
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            // Don't auto-print on mobile
        } else {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 1000);
            };
        }
    </script>
</body>
</html>