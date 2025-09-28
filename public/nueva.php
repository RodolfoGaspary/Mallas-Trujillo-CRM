<?php
require_once __DIR__ . '/../Backend/db_connect.php';

// Fetch existing clients for the dropdown
try {
    $pdo = get_pdo_connection();
    $clientsStmt = $pdo->query('SELECT id_clientes, nombre, telefono, email FROM clientes ORDER BY nombre');
    $clients = $clientsStmt->fetchAll();
  // fetch latest precios row (to provide default precio and discount options)
  $precioRow = null;
  $stmtPrecio = $pdo->query('SELECT * FROM precios where id = 1');
  $precioRow = $stmtPrecio->fetch();
} catch (Throwable $e) {
    $clients = [];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Nueva Proforma</title>
  <link rel="stylesheet" href="../CSS/bootstrap-5.3.8-dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Nueva proforma</h1>
  </div>
  <form id="proformaForm">
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Cliente</h5>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Cliente existente</label>
            <select id="clienteSelect" class="form-select">
              <option value="">-- seleccionar o escribir --</option>
              <?php foreach ($clients as $c): ?>
                <option value="<?php echo htmlspecialchars($c['id_clientes']); ?>" data-nombre="<?php echo htmlspecialchars($c['nombre']); ?>" data-telefono="<?php echo htmlspecialchars($c['telefono']); ?>" data-email="<?php echo htmlspecialchars($c['email']); ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
              <?php endforeach; ?>
            </select>
            <label class="form-label mt-2">Nombre</label>
            <input id="clienteNombre" name="clienteNombre" class="form-control" placeholder="Nombre del cliente" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Teléfono</label>
            <input id="clienteTelefono" name="clienteTelefono" class="form-control" placeholder="Teléfono" required>
            <label class="form-label mt-2">Email</label>
            <input id="clienteEmail" name="clienteEmail" class="form-control" placeholder="Email">
          </div>
          <div class="col-12">
            <label class="form-label">Dirección</label>
            <input id="direccion" name="direccion" class="form-control" placeholder="Dirección de obra" required>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Proforma</h5>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Fecha</label>
            <input id="fecha" name="fecha" type="date" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Precio (por unidad / m2)</label>
            <input id="precio" name="precio" type="number" class="form-control" value="<?php echo htmlspecialchars($precioRow['precio'] ?? ''); ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Descuento (%)</label>
            <select id="descuento" name="descuento" class="form-select">
              <option value="0">0%</option>
              <?php if (!empty($precioRow)): ?>
              <?php for ($i=1;$i<=3;$i++):
                $col = 'descuento_' . $i;
                if (!empty($precioRow[$col]) || $precioRow[$col] === '0' || $precioRow[$col] === 0):
              ?>
                <option value="<?php echo htmlspecialchars($precioRow[$col]); ?>"><?php echo htmlspecialchars($precioRow[$col]); ?>%</option>
              <?php endif; endfor; ?>
              <?php endif; ?>
            </select>
          </div>
            <div class="col-md-4">
              <label class="form-label">Escaleras</label>
              <input id="escaleras" name="escaleras" type="text" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Arnes</label>
              <input id="arnes" name="arnes" type="number" class="form-control" value="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Costo adicional</label>
              <input id="costo_adicional" name="costo_adicional" type="number" class="form-control">
            </div>
          </div>
          <div class="col-12">
            <label class="form-label">Descripción costo adicional</label>
            <textarea id="descripcion_costo_adicional" name="descripcion_costo_adicional" class="form-control" rows="4"></textarea>
          </div>
            <div class="col-12">
              <label class="form-label">Detalles de instalación</label>
              <textarea id="detalles_adicionales" name="detalles_adicionales" class="form-control" rows="4"></textarea>
            </div>
        </div>
      </div>

    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Items</h5>
        <div id="itemsContainer">
          <div class="row g-3 mb-2 item-row">
            <div class="col-md-6">
              <input name="detalle[]" class="form-control" placeholder="Detalle del ítem" required>
            </div>
            <div class="col-md-2">
              <input name="alto[]" type="number" class="form-control" placeholder="Alto" required>
            </div>
            <div class="col-md-2">
              <input name="ancho[]" type="number" class="form-control" placeholder="Ancho" required>
            </div>
            <div class="col-md-2 d-flex align-items-center">
              <button type="button" class="btn btn-danger btn-sm remove-item">Eliminar</button>
            </div>
          </div>
        </div>
        <div class="mt-2">
          <button id="addItem" type="button" class="btn btn-outline-primary btn-sm">Agregar ítem</button>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button id="save" class="btn btn-success" type="button">Crear proforma</button>
    </div>
  </form>
</div>

<script>
// Populate editable client fields when a client is selected
document.getElementById('clienteSelect').addEventListener('change', function(e){
  const opt = this.options[this.selectedIndex];
  const nombre = opt.getAttribute('data-nombre') || '';
  const telefono = opt.getAttribute('data-telefono') || '';
  const email = opt.getAttribute('data-email') || '';
  document.getElementById('clienteNombre').value = nombre;
  document.getElementById('clienteTelefono').value = telefono;
  document.getElementById('clienteEmail').value = email;
});

// Items add/remove logic
const itemsContainer = document.getElementById('itemsContainer');
document.getElementById('addItem').addEventListener('click', function(){
  const template = document.querySelector('.item-row');
  const clone = template.cloneNode(true);
  // clear inputs
  clone.querySelectorAll('input').forEach(i => i.value = '');
  itemsContainer.appendChild(clone);
});

itemsContainer.addEventListener('click', function(e){
  if (e.target && e.target.classList.contains('remove-item')) {
    const row = e.target.closest('.item-row');
    if (!row) return;
    // don't remove the last row
    const rows = itemsContainer.querySelectorAll('.item-row');
    if (rows.length <= 1) {
      // clear fields instead
      row.querySelectorAll('input').forEach(i => i.value = '');
      return;
    }
    row.remove();
  }
});

// Save Proforma
document.getElementById('save').addEventListener('click', function(){
  // Validate required fields
  const requiredFields = [
    {id: 'clienteNombre', name: 'Nombre del cliente'},
    {id: 'clienteTelefono', name: 'Teléfono'},
    {id: 'direccion', name: 'Dirección'},
    {id: 'fecha', name: 'Fecha'},
    {id: 'precio', name: 'Precio'}
  ];

  let isValid = true;
  let errorMessage = 'Por favor, complete los siguientes campos requeridos:\n';

  // Check basic required fields
  requiredFields.forEach(field => {
    const element = document.getElementById(field.id);
    if (!element.value.trim()) {
      isValid = false;
      errorMessage += `- ${field.name}\n`;
      element.classList.add('is-invalid');
    } else {
      element.classList.remove('is-invalid');
    }
  });

  // Validate items
  const rows = itemsContainer.querySelectorAll('.item-row');
  let hasValidItems = false;
  
  rows.forEach((row, index) => {
    const detalle = row.querySelector('input[name="detalle[]"]').value.trim();
    const alto = row.querySelector('input[name="alto[]"]').value.trim();
    const ancho = row.querySelector('input[name="ancho[]"]').value.trim();
    
    // Check if at least one item has all required fields
    if (detalle && alto && ancho) {
      hasValidItems = true;
      // Remove invalid styling
      row.querySelectorAll('input').forEach(input => input.classList.remove('is-invalid'));
    } else if (detalle || alto || ancho) {
      // If some fields are filled but not all, show error
      isValid = false;
      errorMessage += `- Ítem ${index + 1} está incompleto (detalle, alto y ancho son requeridos)\n`;
      row.querySelectorAll('input').forEach(input => input.classList.add('is-invalid'));
    }
  });

  if (!hasValidItems) {
    isValid = false;
    errorMessage += '- Al menos un ítem completo es requerido\n';
  }

  if (!isValid) {
    alert(errorMessage);
    return;
  }

  // If validation passed, proceed with saving
  const fd = new FormData(document.getElementById('proformaForm'));
  const data = {};
  for (const [k,v] of fd.entries()) {
    if (k.endsWith('[]')) continue;
    if (k in data) {
      if (!Array.isArray(data[k])) data[k] = [data[k]];
      data[k].push(v);
    } else data[k] = v;
  }

  // Collect items
  data.items = [];
  rows.forEach(r => {
    const detalle = r.querySelector('input[name="detalle[]"]').value;
    const alto = r.querySelector('input[name="alto[]"]').value;
    const ancho = r.querySelector('input[name="ancho[]"]').value;
    if (detalle && alto && ancho) {
      data.items.push({detalle, alto, ancho});
    }
  });

  // Send data to server using AJAX
  fetch('save_proforma.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(result => {
    if (result.success) {
      console.log('Proforma ID:', result.proforma_id);
      // Redirect to proforma view page with the new ID
      window.location.href = 'proforma.php?id=' + result.proforma_id;
    } else {
      alert('Error: ' + result.error);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Error al guardar la proforma');
  });
});
</script>
<script src="../CSS/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>