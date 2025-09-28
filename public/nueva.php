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

$template_mode = false;
$template_data = null;
$template_items = [];

// Check if we're using a template
if (isset($_GET['template']) && !empty($_GET['template'])) {
    $template_id = (int)$_GET['template'];
    
    try {
        $pdo = get_pdo_connection();
        
        // Fetch the proforma to use as template
        $sql = "SELECT p.*, c.nombre AS cliente_nombre, c.telefono, c.email, c.id_clientes
                FROM proformas p
                LEFT JOIN clientes c ON p.id_c_p = c.id_clientes
                WHERE p.id_proformas = :id LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $template_id]);
        $template_data = $stmt->fetch();
        
        if ($template_data) {
            $template_mode = true;
            
            // Fetch items for this proforma
            $items_sql = "SELECT i.detalle, i.ancho, i.alto, i.area
                         FROM proformas_items pi
                         JOIN items i ON pi.id_item = i.id_items
                         WHERE pi.id_proforma = :id
                         ORDER BY i.id_items";
            
            $items_stmt = $pdo->prepare($items_sql);
            $items_stmt->execute([':id' => $template_id]);
            $template_items = $items_stmt->fetchAll();
        }
    } catch (Throwable $e) {
        // Silently fail - will just not pre-fill data
        error_log("Error loading proforma template: " . $e->getMessage());
    }
}

// In your PHP section, after fetching $precioRow
$discountConfig = [
    'discount_3' => $precioRow['descuento_3'] ?? 0,
    'discount_2' => $precioRow['descuento_2'] ?? 0,
    'discount_1' => $precioRow['descuento_1'] ?? 0,
    'a_1' => $precioRow['m2_1'] ?? 0,
    'a_2' => $precioRow['m2_2'] ?? 0,
    'a_3' => $precioRow['m2_3'] ?? 0
];
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
  <!-- No hidden field needed since we're creating new -->

  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title">Cliente</h5>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Cliente existente</label>
          <select id="clienteSelect" class="form-select">
            <option value="">-- seleccionar --</option>
            <?php foreach ($clients as $c): ?>
              <option value="<?php echo htmlspecialchars($c['id_clientes']); ?>" 
                      data-nombre="<?php echo htmlspecialchars($c['nombre']); ?>" 
                      data-telefono="<?php echo htmlspecialchars($c['telefono']); ?>" 
                      data-email="<?php echo htmlspecialchars($c['email']); ?>"
                      <?php if ($template_mode && $template_data['id_clientes'] == $c['id_clientes']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($c['nombre']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <label class="form-label mt-2">Nombre</label>
          <input id="clienteNombre" name="clienteNombre" class="form-control" 
                 placeholder="Nombre del cliente" required
                 value="<?php echo $template_mode ? htmlspecialchars($template_data['cliente_nombre'] ?? '') : ''; ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Teléfono</label>
          <input id="clienteTelefono" name="clienteTelefono" class="form-control" 
                 placeholder="Teléfono" required
                 value="<?php echo $template_mode ? htmlspecialchars($template_data['telefono'] ?? '') : ''; ?>">
          <label class="form-label mt-2">Email</label>
          <input id="clienteEmail" name="clienteEmail" class="form-control" 
                 placeholder="Email"
                 value="<?php echo $template_mode ? htmlspecialchars($template_data['email'] ?? '') : ''; ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Dirección</label>
          <input id="direccion" name="direccion" class="form-control" 
                 placeholder="Dirección de obra" required
                 value="<?php echo $template_mode ? htmlspecialchars($template_data['direccion_proformas'] ?? '') : ''; ?>">
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
          <input id="fecha" name="fecha" type="date" class="form-control" 
                 value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Precio (por unidad / m2)</label>
          <input id="precio" name="precio" type="number" class="form-control" 
                 value="<?php echo $template_mode ? htmlspecialchars($template_data['precio'] ?? ($precioRow['precio'] ?? '')) : htmlspecialchars($precioRow['precio'] ?? ''); ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Descuento (%)</label>
          <input id="descuento" name="descuento" type="number" class="form-control"
                 value="<?php echo $template_mode ? htmlspecialchars($template_data['descuento'] ?? '') : ''; ?>">
        </div>
        <div class="col-md-2 col-sm-6">
          <label class="form-label">Área total (m2)</label>
          <input id="area_total" name="area_total" type="text" class="form-control" readonly style="background-color: #f8f9fa;"
                 value="<?php echo $template_mode ? htmlspecialchars($template_data['area_total'] ?? '') : ''; ?>">
        </div>
        <div class="col-md-2 col-sm-6">
          <label class="form-label">Descuento sugerido (%)</label>
          <input id="descuento_sugerido" name="descuento_sugerido" type="text" class="form-control" readonly style="background-color: #f8f9fa;">
        </div>
        <div class="col-md-8"></div>
        <div class="col-md-4">
          <label class="form-label">Escaleras</label>
          <input id="escaleras" name="escaleras" type="text" class="form-control"
                 value="<?php echo $template_mode ? htmlspecialchars($template_data['escaleras'] ?? '') : ''; ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Arnes</label>
          <input id="arnes" name="arnes" type="number" class="form-control" value="0"
                 value="<?php echo $template_mode ? htmlspecialchars($template_data['arnes'] ?? '0') : '0'; ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Costo adicional</label>
          <input id="costo_adicional" name="costo_adicional" type="number" class="form-control"
                 value="<?php echo $template_mode ? htmlspecialchars($template_data['costo_adicional'] ?? '') : ''; ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Descripción costo adicional</label>
          <textarea id="descripcion_costo_adicional" name="descripcion_costo_adicional" class="form-control" rows="4"><?php echo $template_mode ? htmlspecialchars($template_data['descripcion_costo_adicional'] ?? '') : ''; ?></textarea>
        </div>
        <div class="col-12">
          <label class="form-label">Detalles/Observaciones adicionales</label>
          <textarea id="detalles_adicionales" name="detalles_adicionales" class="form-control" rows="4"><?php echo $template_mode ? htmlspecialchars($template_data['detalles'] ?? '') : ''; ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title">Items</h5>
      <div id="itemsContainer">
        <?php if ($template_mode && !empty($template_items)): ?>
          <?php foreach ($template_items as $index => $item): ?>
            <div class="row g-3 mb-2 item-row">
              <div class="col-md-6">
                <input name="detalle[]" class="form-control" placeholder="Detalle del ítem" required
                       value="<?php echo htmlspecialchars($item['detalle'] ?? ''); ?>">
              </div>
              <div class="col-md-2">
                <input name="ancho[]" type="number" class="form-control" placeholder="Ancho" required step="0.01"
                       value="<?php echo htmlspecialchars($item['ancho'] ?? ''); ?>">
              </div>
              <div class="col-md-2">
                <input name="alto[]" type="number" class="form-control" placeholder="Alto" required step="0.01"
                       value="<?php echo htmlspecialchars($item['alto'] ?? ''); ?>">
              </div>
              <div class="col-md-2 d-flex align-items-center">
                <button type="button" class="btn btn-danger btn-sm remove-item">Eliminar</button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <!-- Default empty item row -->
          <div class="row g-3 mb-2 item-row">
            <div class="col-md-6">
              <input name="detalle[]" class="form-control" placeholder="Detalle del ítem" required>
            </div>
            <div class="col-md-2">
              <input name="ancho[]" type="number" class="form-control" placeholder="Ancho" required step="0.01">
            </div>
            <div class="col-md-2">
              <input name="alto[]" type="number" class="form-control" placeholder="Alto" required step="0.01">
            </div>
            <div class="col-md-2 d-flex align-items-center">
              <button type="button" class="btn btn-danger btn-sm remove-item">Eliminar</button>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <div class="mt-2">
        <button id="addItem" type="button" class="btn btn-outline-primary btn-sm">Agregar ítem</button>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2">
    <button id="save" class="btn btn-success" type="button">Crear Proforma</button>
  </div>
</form>
</div>

<script>
// Pass discount configuration from PHP to JavaScript
const discountConfig = <?php echo json_encode($discountConfig); ?>;

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
  
  // Update calculations after adding new item
  updateCalculations();
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
    } else {
      row.remove();
    }
    
    // Update calculations after removal
    updateCalculations();
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
      alert('Proforma creada con éxito. ID: ' + result.proforma_id);
    } else {
      alert('Error: ' + result.error);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Error al guardar la proforma');
  });
});
// Function to calculate total area and suggested discount using DB thresholds
function updateCalculations() {
  const rows = itemsContainer.querySelectorAll('.item-row');
  let totalArea = 0;
  
  // Calculate total area
  rows.forEach(row => {
    const ancho = parseFloat(row.querySelector('input[name="ancho[]"]').value) || 0;
    const alto = parseFloat(row.querySelector('input[name="alto[]"]').value) || 0;
    totalArea += ancho * alto;
  });
  
  // Update area total field
  document.getElementById('area_total').value = totalArea.toFixed(2) + ' m²';
  
  // Calculate suggested discount based on area using DB thresholds
  let suggestedDiscount = 0;
  
  // Use the discount thresholds from your database
  if (totalArea >= discountConfig.a_3) {
    suggestedDiscount = discountConfig.discount_3 || 15; // Fallback to 15 if not set
  } else if (totalArea >= discountConfig.a_2) {
    suggestedDiscount = discountConfig.discount_2 || 10; // Fallback to 10 if not set
  } else if (totalArea >= discountConfig.a_1) {
    suggestedDiscount = discountConfig.discount_1 || 5; // Fallback to 5 if not set
  }
  
  // Update suggested discount field
  document.getElementById('descuento_sugerido').value = suggestedDiscount + '%';
  
  // Optional: Auto-fill the discount field with the suggested value
  document.getElementById('descuento').value = suggestedDiscount;
}

// Call updateCalculations function:
itemsContainer.addEventListener('input', function(e) {
  if (e.target && (e.target.name === 'ancho[]' || e.target.name === 'alto[]')) {
    updateCalculations();
  }
});

// Also call updateCalculations on page load
document.addEventListener('DOMContentLoaded', updateCalculations);
</script>
<script src="../CSS/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>