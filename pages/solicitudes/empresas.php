<?php
require_once __DIR__ . "/../../includes/authAdmin.php";
require_once __DIR__ . "/../../includes/header.php";
require_once __DIR__ . "/../../modelo/DbComidApp.php";
require_once __DIR__ . "/../../modelo/SchemaHelper.php";

$pdo = DbComidApp::pdo();

$estado = strtoupper(trim($_GET['estado'] ?? 'PENDIENTE'));
if (!in_array($estado, ['PENDIENTE','APROBADA','RECHAZADA','TODAS'], true)) $estado='PENDIENTE';

$use_new = SchemaHelper::tableExists($pdo, 'solicitud_empresa');

$msg = "";
$err = "";

/**
 * Fallback a tabla legacy: `solicitud`
 * Columnas esperadas (según tu verSolicitud.php): id, usuario_id, fecha_solicitud, estado
 * Si alguna columna no existe, igual intentamos resolver.
 */
function resolve_cols(PDO $pdo, string $table, array $candidates): array {
    $cols = SchemaHelper::columns($pdo, $table);
    $map = [];
    foreach ($candidates as $alias => $names) {
        foreach ($names as $n) {
            foreach ($cols as $c) {
                if (strcasecmp($c, $n) === 0) { $map[$alias] = $c; break 2; }
            }
        }
    }
    return $map;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    try {
        if ($id <= 0) throw new Exception("ID inválido.");

        if ($use_new) {
            // Nuevo esquema: solicitud (si existe)
            $pk = SchemaHelper::primaryKey($pdo, 'solicitud');
            if (!$pk) throw new Exception("No se detectó PK en solicitud.");

            if ($action === 'aprobar') {
                $st = $pdo->prepare("UPDATE solicitud SET Estado='APROBADA', MotivoRechazo=NULL, FechaResolucion=NOW(), ResueltoPor=:rp WHERE $pk=:id");
                $st->execute([':rp'=>($_SESSION['admin_mail'] ?? 'admin'), ':id'=>$id]);
                $msg = "Solicitud aprobada.";
            } elseif ($action === 'rechazar') {
                $motivo = trim($_POST['motivo'] ?? '');
                $st = $pdo->prepare("UPDATE solicitud SET Estado='RECHAZADA', MotivoRechazo=:m, FechaResolucion=NOW(), ResueltoPor=:rp WHERE $pk=:id");
                $st->execute([':m'=>$motivo, ':rp'=>($_SESSION['admin_mail'] ?? 'admin'), ':id'=>$id]);
                $msg = "Solicitud rechazada.";
            }
        } else {
            // Legacy: tabla solicitud (solo cambiamos estado)
            if (!SchemaHelper::tableExists($pdo, 'solicitud')) throw new Exception("No existe la tabla solicitud en comidapp.");
            $map = resolve_cols($pdo, 'solicitud', [
                'id' => ['id','ID','IDSolicitud','IDSol','IDSolicitudEmp'],
                'estado' => ['estado','Estado'],
            ]);
            $idc = $map['id'] ?? 'id';
            $ec  = $map['estado'] ?? 'estado';

            if ($action === 'aprobar') {
                $st = $pdo->prepare("UPDATE solicitud SET $ec='aprobada' WHERE $idc=:id");
                $st->execute([':id'=>$id]);
                $msg = "Solicitud aprobada (estado=aprobada).";
            } elseif ($action === 'rechazar') {
                $st = $pdo->prepare("UPDATE solicitud SET $ec='rechazada' WHERE $idc=:id");
                $st->execute([':id'=>$id]);
                $msg = "Solicitud rechazada (estado=rechazada).";
            }
        }
    } catch (Throwable $e) {
        $err = $e->getMessage();
    }
}

// Listado
$rows = [];
try {
    if ($use_new) {
        $sql = "SELECT * FROM solicitud";
        $params = [];
        if ($estado !== 'TODAS') { $sql .= " WHERE Estado = :e"; $params[':e']=$estado; }
        $pk = SchemaHelper::primaryKey($pdo, 'solicitud');
        if ($pk) $sql .= " ORDER BY $pk DESC";
        $st = $pdo->prepare($sql);
        $st->execute($params);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    } else {
        if (!SchemaHelper::tableExists($pdo, 'solicitud')) throw new Exception("No existe la tabla solicitud en comidapp.");
        $map = resolve_cols($pdo, 'solicitud', [
            'id'    => ['id','ID','IDSolicitud','IDSol','IDSolicitudEmp'],
            'user'  => ['usuario_id','IDUsu','IDUsuario','usuarioId'],
            'fecha' => ['fecha_solicitud','Fecha','FechaSolicitud','fecha'],
            'estado'=> ['estado','Estado'],
        ]);
        $idc = $map['id'] ?? 'id';
        $uc  = $map['user'] ?? 'usuario_id';
        $fc  = $map['fecha'] ?? 'fecha_solicitud';
        $ec  = $map['estado'] ?? 'estado';

        $sql = "SELECT $idc AS _id, $uc AS _user, $fc AS _fecha, $ec AS _estado FROM solicitud";
        $params = [];
        if ($estado !== 'TODAS') { 
            // legacy suele ser 'pendiente/aprobada/rechazada'
            $want = strtolower($estado);
            $sql .= " WHERE LOWER($ec) = :e";
            $params[':e'] = $want;
        }
        $sql .= " ORDER BY _id DESC";
        $st = $pdo->prepare($sql);
        $st->execute($params);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    $err = $err ?: $e->getMessage();
}

?>
<h1 class="h3 mb-3">Solicitud Empresa</h1>

<?php if ($msg): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($err): ?>
  <div class="alert alert-warning">
    <div class="fw-semibold">Solicitudes no disponibles</div>
    <div class="small"><?php echo htmlspecialchars($err); ?></div>
    <div class="small mt-2">
      Si todavía no implementaste solicitudes, podés dejarlo así. Cuando tengas tiempo, ejecutás el SQL en <code>/sql/COMIBACK_solicitudes_migracion.sql</code> o adaptás ComidAPP para guardar en <code>solicitud_empresa</code>.
    </div>
  </div>
<?php endif; ?>

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
        <thead class="table-light">
          <tr>
            <th style="width:120px;">ID</th>
            <?php if ($use_new): ?>
              <th>Nombre</th>
              <th>Mail</th>
              <th>RUT</th>
              <th>Estado</th>
              <th style="width:240px;">Acciones</th>
            <?php else: ?>
              <th>ID Usuario</th>
              <th>Fecha</th>
              <th>Estado</th>
              <th style="width:240px;">Acciones</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="6" class="text-muted p-4">Sin registros.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <?php if ($use_new): 
              $id = (int)($r[SchemaHelper::primaryKey($pdo,'solicitud_empresa')] ?? 0);
              $est = (string)($r['Estado'] ?? '');
          ?>
          <tr>
            <td><?php echo (int)$id; ?></td>
            <td><?php echo htmlspecialchars((string)($r['Nombre'] ?? $r['RazonSocial'] ?? '')); ?></td>
            <td><?php echo htmlspecialchars((string)($r['Mail'] ?? $r['Email'] ?? '')); ?></td>
            <td><?php echo htmlspecialchars((string)($r['RUT'] ?? '')); ?></td>
            <td><span class="badge text-bg-<?php echo ($est==='PENDIENTE')?'warning':(($est==='APROBADA')?'success':'secondary'); ?>"><?php echo htmlspecialchars($est); ?></span></td>
            <td>
              <?php if ($est==='PENDIENTE'): ?>
                <form method="post" class="d-inline">
                  <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
                  <input type="hidden" name="action" value="aprobar">
                  <button class="btn btn-sm btn-success" type="submit">Aprobar</button>
                </form>
                <form method="post" class="d-inline">
                  <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
                  <input type="hidden" name="action" value="rechazar">
                  <input type="hidden" name="motivo" value="">
                  <button class="btn btn-sm btn-outline-secondary" type="submit">Rechazar</button>
                </form>
              <?php else: ?>
                <span class="text-muted small">Sin acciones</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php else: ?>
          <tr>
            <td><?php echo (int)($r['_id'] ?? $r['id'] ?? 0); ?></td>
            <td><?php echo htmlspecialchars((string)($r['_user'] ?? '')); ?></td>
            <td><?php echo htmlspecialchars((string)($r['_fecha'] ?? '')); ?></td>
            <td><?php echo htmlspecialchars((string)($r['_estado'] ?? '')); ?></td>
            <td>
              <?php $est = strtolower((string)($r['_estado'] ?? '')); ?>
              <?php if ($est==='pendiente'): ?>
                <form method="post" class="d-inline">
                  <input type="hidden" name="id" value="<?php echo (int)($r['_id'] ?? 0); ?>">
                  <input type="hidden" name="action" value="aprobar">
                  <button class="btn btn-sm btn-success" type="submit">Aprobar</button>
                </form>
                <form method="post" class="d-inline">
                  <input type="hidden" name="id" value="<?php echo (int)($r['_id'] ?? 0); ?>">
                  <input type="hidden" name="action" value="rechazar">
                  <button class="btn btn-sm btn-outline-secondary" type="submit">Rechazar</button>
                </form>
              <?php else: ?>
                <span class="text-muted small">Sin acciones</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endif; ?>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
