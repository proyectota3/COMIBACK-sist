<?php
require_once __DIR__ . "/../../includes/authAdmin.php";
require_once __DIR__ . "/../../includes/header.php";

require_once __DIR__ . "/../../modelo/DbComidApp.php";
require_once __DIR__ . "/../../modelo/SolicitudesLocalModel.php";
require_once __DIR__ . "/../../modelo/LocalesModel.php";
require_once __DIR__ . "/../../config/mailer.php";

$pdo = DbComidApp::pdo();
$solModel = new SolicitudesLocalModel();
$locModel = new LocalesModel();

$estado = trim($_GET['estado'] ?? 'Pendiente');
$estado = ucfirst(strtolower($estado));
$validEstados = ['Pendiente','Validada','Rechazada','Todas'];
if (!in_array($estado, $validEstados, true)) $estado = 'Pendiente';

$msg = "";
$err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $motivo = trim($_POST['motivo'] ?? '');

    try {
        if ($id <= 0) throw new Exception("ID inválido.");
        if (!$solModel->exists()) throw new Exception("No existe la tabla solicitudlocal en comidapp.");

        if ($action === 'aprobar') {
            $pdo->beginTransaction();

            $st = $pdo->prepare("SELECT * FROM solicitudlocal WHERE ID = :id FOR UPDATE");
            $st->execute([':id' => $id]);
            $sol = $st->fetch(PDO::FETCH_ASSOC);
            if (!$sol) throw new Exception("Solicitud no encontrada.");

            $estadoActual = (string)($sol['Estado'] ?? '');
            if (strcasecmp($estadoActual, 'Pendiente') !== 0) {
                throw new Exception("La solicitud no está en estado Pendiente.");
            }

            $idEmp = (int)($sol['IDEmp'] ?? 0);
            if ($idEmp <= 0) throw new Exception("IDEmp inválido en la solicitud.");

            $nombre = trim((string)($sol['Nombre'] ?? ''));
            $direccion = trim((string)($sol['Direccion'] ?? ''));
            $foto = trim((string)($sol['Foto'] ?? ''));
            $delivery = (int)($sol['Delivery'] ?? 0);

            if ($nombre === '' || $direccion === '') {
                throw new Exception("La solicitud no tiene datos completos (Nombre/Dirección)." );
            }

            // 1) Crear Local
            $idLoc = $locModel->create([
                'empresa' => $idEmp,
                'nombre' => $nombre,
                'direccion' => $direccion,
                'foto' => $foto,
                'delivery' => $delivery
            ]);

            // 2) Marcar solicitud como validada y linkear ID del local creado
            $solModel->markApproved($id, $idLoc);

            // 3) Aviso por mail (si logramos obtener el mail de la empresa)
            $st = $pdo->prepare("SELECT Mail, Nombre FROM empresa WHERE IDEmp = :id LIMIT 1");
            $st->execute([':id'=>$idEmp]);
            $emp = $st->fetch(PDO::FETCH_ASSOC);
            if ($emp && !empty($emp['Mail'])) {
                $to = (string)$emp['Mail'];
                $empNombre = (string)($emp['Nombre'] ?? '');
                $body = "
                    <h3>Solicitud de Local aprobada ✅</h3>
                    <p>Hola <b>" . htmlspecialchars($empNombre) . "</b>, tu solicitud de local fue aprobada.</p>
                    <p><b>Local:</b> " . htmlspecialchars($nombre) . "</p>
                    <p><b>Dirección:</b> " . htmlspecialchars($direccion) . "</p>
                ";
                $r = enviarMail($to, "ComidAPP - Solicitud de local aprobada", $body);
                // Si falla el mail, NO frenamos la validación del local (ya está creado)
            }

            $pdo->commit();
            $msg = "Solicitud aprobada. Local creado (ID {$idLoc}).";
        }

        if ($action === 'rechazar') {
            if ($motivo === '') $motivo = 'Rechazado por administración.';
            $solModel->markRejected($id, $motivo);
            $msg = "Solicitud rechazada.";
        }

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $err = $e->getMessage();
    }
}

// Listado
$rows = [];
try {
    if (!$solModel->exists()) throw new Exception("No existe la tabla solicitudlocal en comidapp.");
    if ($estado === 'Todas') {
        $rows = $solModel->list("");
    } else {
        $rows = $solModel->list($estado);
    }
} catch (Throwable $e) {
    $err = $err ?: $e->getMessage();
}
?>

<h1 class="h3 mb-3">Solicitud Local</h1>

<?php if ($msg): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($err): ?>
  <div class="alert alert-warning"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<div class="d-flex gap-2 mb-3">
  <a class="btn btn-sm <?php echo ($estado==='Pendiente')?'btn-danger':'btn-outline-danger'; ?>" href="?estado=Pendiente">Pendientes</a>
  <a class="btn btn-sm <?php echo ($estado==='Validada')?'btn-danger':'btn-outline-danger'; ?>" href="?estado=Validada">Validadas</a>
  <a class="btn btn-sm <?php echo ($estado==='Rechazada')?'btn-danger':'btn-outline-danger'; ?>" href="?estado=Rechazada">Rechazadas</a>
  <a class="btn btn-sm <?php echo ($estado==='Todas')?'btn-danger':'btn-outline-danger'; ?>" href="?estado=Todas">Todas</a>
</div>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0 align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:90px;">ID</th>
            <th>IDEmp</th>
            <th>Nombre</th>
            <th>Dirección</th>
            <th>Delivery</th>
            <th>Estado</th>
            <th>ID Loc Creado</th>
            <th style="width:300px;">Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="8" class="text-muted p-4">Sin registros.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <?php
            $id = (int)($r['ID'] ?? 0);
            $est = (string)($r['Estado'] ?? '');
            $isPendiente = (strcasecmp($est, 'Pendiente') === 0);
          ?>
          <tr>
            <td><?php echo $id; ?></td>
            <td><?php echo htmlspecialchars((string)($r['IDEmp'] ?? '')); ?></td>
            <td><?php echo htmlspecialchars((string)($r['Nombre'] ?? '')); ?></td>
            <td><?php echo htmlspecialchars((string)($r['Direccion'] ?? '')); ?></td>
            <td><?php echo ((int)($r['Delivery'] ?? 0)) ? 'Sí' : 'No'; ?></td>
            <td>
              <span class="badge text-bg-<?php
                echo $isPendiente ? 'warning' : ((strcasecmp($est,'Validada')===0) ? 'success' : 'secondary');
              ?>"><?php echo htmlspecialchars($est); ?></span>
            </td>
            <td><?php echo htmlspecialchars((string)($r['IDLocCreado'] ?? '')); ?></td>
            <td>
              <?php if ($isPendiente): ?>
                <form method="post" class="d-inline" onsubmit="return confirm('¿Aprobar esta solicitud y crear el local?');">
                  <input type="hidden" name="id" value="<?php echo $id; ?>">
                  <input type="hidden" name="action" value="aprobar">
                  <button class="btn btn-sm btn-success" type="submit">Aprobar</button>
                </form>

                <form method="post" class="d-inline" onsubmit="return confirm('¿Rechazar esta solicitud?');">
                  <input type="hidden" name="id" value="<?php echo $id; ?>">
                  <input type="hidden" name="action" value="rechazar">
                  <input type="hidden" name="motivo" value="Rechazado por administración.">
                  <button class="btn btn-sm btn-outline-secondary" type="submit">Rechazar</button>
                </form>
              <?php else: ?>
                <span class="text-muted small">Sin acciones</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>
