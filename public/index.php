<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mallas Trujillo</title>
    <!-- Use CDN for now to ensure it works -->
   <link rel="stylesheet" href="../CSS/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <style>
        /* small local overrides */
        body { font-family: Arial, sans-serif; }
        table { margin-top: 12px; }
        th, td { vertical-align: middle; }
    </style>
</head>
<body>
<?php include '../calls/nav.php'; ?>
<div class="container py-4">
    <div class="text-center mb-3"><h1>Mallas Trujillo</h1></div>
    <hr>
    <div class="mb-3 d-grid gap-2 col-md-4 mx-auto">
        <button type="button" class="btn btn-primary" onclick="window.location.href='nueva.php'">Nueva proforma</button>
        <button type="button" class="btn btn-primary" onclick="window.location.href='historial.php'">Historial de Proformas</button>
        <button type="button" class="btn btn-primary" onclick="window.location.href='precios.php'">Modificar Precios</button>
    </div>
</div>

<!-- Bootstrap JS at the bottom -->
<script src="../CSS/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>