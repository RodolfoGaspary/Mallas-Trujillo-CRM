<?php
require_once __DIR__ . '/../Backend/db_connect.php';

// Fetch existing clients for the dropdown
try {
    $pdo = get_pdo_connection();
    $stmt = $pdo->prepare('SELECT * FROM precios LIMIT 1');
    $stmt->execute();
    $precio = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if any rows were returned
    if (empty($precio)) {
        echo "No records found.";
    }
    
} catch (PDOException $e) {
    echo "Error fetching records: " . $e->getMessage();
}

?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Modificar Precios</title>
  <link rel="stylesheet" href="../CSS/bootstrap-5.3.8-dist/css/bootstrap.min.css">
</head>
<body> 
<?php include '../calls/nav.php'; ?>
<div class="container py-4">
  <div class="mb-3">
    <h1 class="text-center">Precios</h1>
  </div>
<div class="container py-4 d-flex justify-content-center">
<div class="card mb-3 col-lg-6 col-md-12 col-sm-12">
    <div class="card-body">
      <form id="precioForm">
        <div class="mb-3">
          <label for="precio" class="form-label">Precio (S/. x m2)</label>
          <input type="text" class="form-control" id="precio" name="precio" value="<?php echo htmlspecialchars($precio['precio'] ?? ''); ?>">
        </div>
        <div class="row">
        <div class="mb-3 col-md-6">
          <label for="descuento1" class="form-label">Descuento 1 (%)</label>
          <input type="text" class="form-control" id="descuento1" name="descuento1" value="<?php echo htmlspecialchars($precio['descuento_1'] ?? ''); ?>">
        </div>
        <div class="mb-3 col-md-6">
          <label for="m2_1" class="form-label">Area de Descuento 1 (m2)</label>
          <input type="text" class="form-control" id="m2_1" name="m2_1" value="<?php echo htmlspecialchars($precio['m2_1'] ?? ''); ?>">
        </div>
        <div class="mb-3 col-md-6">
          <label for="descuento2" class="form-label">Descuento 2 (%)</label>
          <input type="text" class="form-control" id="descuento2" name="descuento2" value="<?php echo htmlspecialchars($precio['descuento_2'] ?? ''); ?>">
        </div>
        <div class="mb-3 col-md-6">
          <label for="m2_2" class="form-label">Area de Descuento 2 (m2)</label>
          <input type="text" class="form-control" id="m2_2" name="m2_2" value="<?php echo htmlspecialchars($precio['m2_2'] ?? ''); ?>">
        </div>
        <div class="mb-3 col-md-6">
          <label for="descuento3" class="form-label">Descuento 3 (%)</label>
          <input type="text" class="form-control" id="descuento3" name="descuento3" value="<?php echo htmlspecialchars($precio['descuento_3'] ?? ''); ?>">
        </div>
        <div class="mb-3 col-md-6">
          <label for="m2_3" class="form-label">Area de Descuento 3 (m2)</label>
          <input type="text" class="form-control" id="m2_3" name="m2_3" value="<?php echo htmlspecialchars($precio['m2_3'] ?? ''); ?>">
        </div>
        <div class="row mb-3">

        </div>
        <div class="d-flex justify-content-center"><button id="guardarPrecio" type="submit" class="btn btn-success">Guardar</button></div>
      </form>
    </div>
  </div>
</div>
</div>
<script src="../CSS/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('precioForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get values directly from inputs
    const data = {
        precio: document.getElementById('precio').value,
        descuento1: document.getElementById('descuento1').value,
        descuento2: document.getElementById('descuento2').value,
        descuento3: document.getElementById('descuento3').value,
        m2_1: document.getElementById('m2_1').value,
        m2_2: document.getElementById('m2_2').value,
        m2_3: document.getElementById('m2_3').value
    };

    console.log('Sending data:', data);

    fetch('../calls/update_precio.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        console.log('Response:', result);
        if (result.success) {
            location.reload();
        } else {
            alert('Error: ' + (result.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar los precios');
    });
});
</script>
</body>
</html>