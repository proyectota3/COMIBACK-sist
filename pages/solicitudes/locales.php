<?php
require_once __DIR__ . "/../../includes/authAdmin.php";
require_once __DIR__ . "/../../includes/header.php";
require_once __DIR__ . "/../../modelo/DbComidApp.php";
require_once __DIR__ . "/../../modelo/SchemaHelper.php";

$pdo = DbComidApp::pdo();
$exists = SchemaHelper::tableExists($pdo, 'solicitudlocal');
?>
<h1 class="h3 mb-3">Solicitud Local</h1>

<?php if (!$exists): ?>
  <div class="alert alert-info">
    <div class="fw-semibold">Todavía no está implementado en tu flujo actual</div>
    <div class="small">
      Esta pantalla queda lista. Cuando implementes “Solicitar local” en <code>misLocales.php</code> (ComidAPP),
      podés guardar la solicitud en <code>solicitudlocal</code> ejecutando el SQL de <code>/sql/COMIBACK_solicitudes_migracion.sql</code>.
    </div>
  </div>
<?php else: ?>
  <?php
    $estado = strtoupper(trim($_GET['estado'] ?? 'PENDIENTE'));
    if (!in_array($estado, ['PENDIENTE','APROBADA','RECHAZADA','TODAS'], true)) $estado='PENDIENTE';
    $pk = SchemaHelper::primaryKey($pdo, 'solicitudlocal');

    $sql = "SELECT * FROM solicitudlocal";
    $params = [];
    if ($estado !== 'TODAS') { $sql .= " WHERE Estado = :e"; $params[':e']=$estado; }
    if ($pk) $sql .= " ORDER BY $pk DESC";
    $st = $pdo->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
  ?>

  <div class="d-flex gap-2 mb-3">
    <a class="btn btn-sm <?php echo ($estado==='PENDIENTE')?'btn-danger':'btn-outline-danger'; ?>" href="?estado=PENDIENTE">Pendientes</a>
    <a class="btn btn-sm <?php echo ($estado==='APROBADA')?'btn-danger':'btn-outline-danger'; ?>" href="?estado=APROBADA">Aprobadas</a>
    <a class="btn btn-sm <?php echo ($estado==='RECHAZADA')?'btn-danger':'btn-outline-danger'; ?>" href="?estado=RECHAZADA">Rechazadas</a>
    <a class="btn btn-sm <?php echo ($estado==='TODAS')?'btn-danger':'btn-outline-danger'; ?>" href="?estado=TODAS">Todas</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-0 align-middle">
          <thead class="table-light"><tr><th>ID</th><th>IDEmpresa</th><th>Nombre</th><th>Estado</th></tr></thead>
          <tbody>
            <?php if (!$rows): ?>
              <tr><td colspan="4" class="text-muted p-4">Sin registros.</td></tr>
            <?php else: foreach ($rows as $r): ?>
              <tr>
                <td><?php echo htmlspecialchars((string)($pk ? $r[$pk] : '')); ?></td>
                <td><?php echo htmlspecialchars((string)($r['IDEmpresa'] ?? '')); ?></td>
                <td><?php echo htmlspecialchars((string)($r['NombreLocal'] ?? $r['Nombre'] ?? '')); ?></td>
                <td><?php echo htmlspecialchars((string)($r['Estado'] ?? '')); ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
