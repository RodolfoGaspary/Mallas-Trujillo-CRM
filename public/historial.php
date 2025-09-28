<?php
require_once __DIR__ . '/../Backend/db_connect.php';

// Basic input handling
$q = trim($_GET['q'] ?? '');

try {
        $pdo = get_pdo_connection();
} catch (Throwable $e) {
        echo "<h1>Mallas Trujillo</h1>";
        echo "<p>Database connection: <strong style='color:red'>FAILED</strong></p>";
        if (!empty($GLOBALS['SHOW_DB_ERRORS'])) {
                echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        }
        exit;
}

// Query: join clientes and proformas and filter by cliente nombre (case-insensitive), order by created_at desc, limit 10
$sql = "SELECT p.id_proformas AS id, c.nombre AS cliente_nombre, c.telefono, p.direccion_proformas, p.fecha, p.precio, p.created_at
                FROM proformas p
                LEFT JOIN clientes c ON p.id_c_p = c.id_clientes
                WHERE (:q = '' OR c.nombre LIKE :like_q)
                ORDER BY p.created_at DESC
                LIMIT 10";

$stmt = $pdo->prepare($sql);
$like = '%' . $q . '%';
$stmt->bindValue(':q', $q, PDO::PARAM_STR);
$stmt->bindValue(':like_q', $like, PDO::PARAM_STR);
$stmt->execute();
$rows = $stmt->fetchAll();

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Historial de Proformas</title>
    <link rel="stylesheet" href="../CSS/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <style>
        /* small local overrides */
        body { font-family: Arial, sans-serif; }
        table { margin-top: 12px; }
        th, td { vertical-align: middle; }
    </style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Historial de Proformas</h1>
    </div>
    <hr>
    <div class="mb-3">
        <div class="mb-2">
            <label for="q" class="form-label mb-1">Buscar cliente (nombre):</label>
            <input id="q" name="q" type="text" class="form-control" value="<?php echo htmlspecialchars($q); ?>" placeholder="Nombre del cliente...">
        </div>
        <div class="d-flex align-items-center">
            <label for="sort" class="me-2 mb-0">Ordenar por:</label>
            <select id="sort" name="sort" class="form-select w-auto text-start">
                <option value="fecha_desc" selected>Fecha (más recientes)</option>
                <option value="fecha_asc">Fecha (más antiguas)</option>
                <option value="cliente_asc">Cliente (A → Z)</option>
                <option value="cliente_desc">Cliente (Z → A)</option>
            </select>
        </div>
    </div>
    <div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Cliente</th>
                <th scope="col">Teléfono</th>
                <th scope="col">Dirección Proforma</th>
                <th scope="col">Fecha</th>
                <th scope="col">Precio</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($rows) === 0): ?>
            <tr><td colspan="6">No se encontraron resultados.</td></tr>
        <?php else: ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <th scope="row"><?php echo htmlspecialchars($r['id'] ?? ''); ?></th>
                    <td><?php echo htmlspecialchars($r['cliente_nombre'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($r['telefono'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($r['direccion_proformas'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($r['fecha'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars(number_format($r['precio'] ?? 0, 2)); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
        <script>
        // Debounce helper
        function debounce(fn, wait) {
            let t;
            return function(...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), wait);
            };
        }

        const input = document.getElementById('q');
        const tbody = document.querySelector('table tbody');

        async function fetchResults(q) {
            const sortVal = document.getElementById('sort').value;
            const resp = await fetch('search.php?q=' + encodeURIComponent(q) + '&sort=' + encodeURIComponent(sortVal));
            if (!resp.ok) return;
            const rows = await resp.json();
            render(rows);
        }

        function render(rows) {
            tbody.innerHTML = '';
            if (!rows || rows.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6">No se encontraron resultados.</td></tr>';
                return;
            }
            for (const r of rows) {
                const tr = document.createElement('tr');
                tr.style.cursor = 'pointer';
                tr.addEventListener('click', () => {
                    if (r.id) window.location.href = 'proforma.php?id=' + encodeURIComponent(r.id);
                });
                tr.innerHTML = `<th scope="row">${escape(r.id)}</th>` +
                                `<td>${escape(r.cliente_nombre)}</td>` +
                                `<td>${escape(r.telefono)}</td>` +
                                `<td>${escape(r.direccion_proformas)}</td>` +
                                `<td>${escape(r.fecha)}</td>` +
                                `<td>${escape(Number(r.precio || 0).toFixed(2))}</td>`;
                tbody.appendChild(tr);
            }
        }

        function escape(s){ return (s===null||s===undefined)?'':String(s).replace(/[&<>"']/g, function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c];}); }

    const debounced = debounce(function(){ fetchResults(input.value); }, 250);
    input.addEventListener('input', debounced);
    // re-fetch when user changes sort order immediately (no debounce needed)
    document.getElementById('sort').addEventListener('change', function(){ fetchResults(input.value); });

        // initial fetch
        fetchResults(input.value);
        </script>
    <script src="../CSS/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</div>
</body>
</html>