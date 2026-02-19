<?php
require_once __DIR__ . "/../../includes/authAdmin.php";
require_once __DIR__ . "/../../includes/header.php";

require_once __DIR__ . "/../../modelo/DbComidApp.php";
require_once __DIR__ . "/../../modelo/SolicitudesEmpresaModel.php";
require_once __DIR__ . "/../../modelo/UsuariosWebModel.php";
require_once __DIR__ . "/../../modelo/EmpresasModel.php";
require_once __DIR__ . "/../../modelo/generarPassword.php";
require_once __DIR__ . "/../../config/mailer.php";

$pdo = DbComidApp::pdo();
$solModel = new SolicitudesEmpresaModel();
$userModel = new UsuariosWebModel();
$empModel = new EmpresasModel();

$estado = trim($_GET['estado'] ?? 'Pendiente');
$estado = ucfirst(strtolower($estado));
$validEstados = ['Pendiente','Validada','Rechazada','Todas'];
if (!in_array($estado, $validEstados, true)) $estado = 'Pendiente';

$msg = "";
$err = "";

function normalizarTelefono(?string $tel): ?int {
    if ($tel === null) return null;
    $digits = preg_replace('/\D+/', '', $tel);
    if ($digits === '') return null;
    // MariaDB int(11) no soporta números muy largos.
    if (strlen($digits) > 11) $digits = substr($digits, -11);
    return (int)$digits;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    try {
        if ($id <= 0) throw new Exception("ID inválido.");
        if (!$solModel->exists()) throw new Exception("No existe la tabla solicitud en comidapp.");

        if ($action === 'aprobar') {
            $pdo->beginTransaction();

            $st = $pdo->prepare("SELECT * FROM solicitud WHERE ID = :id FOR UPDATE");
            $st->execute([':id' => $id]);
            $sol = $st->fetch(PDO::FETCH_ASSOC);
            if (!$sol) throw new Exception("Solicitud no encontrada.");

            $estadoActual = (string)($sol['Estado'] ?? '');
            if (strcasecmp($estadoActual, 'Pendiente') !== 0) {
                throw new Exception("La solicitud no está en estado Pendiente.");
            }

            $mail = trim((string)($sol['Mail'] ?? ''));
            $nombre = trim((string)($sol['Nombre'] ?? ''));
            $direccion = trim((string)($sol['Direccion'] ?? ''));
            $rut = trim((string)($sol['RUT'] ?? ''));
            $telefono = normalizarTelefono($sol['Telefono'] ?? null);

            if ($mail === '' || $nombre === '' || $direccion === '' || $rut === '') {
                throw new Exception("La solicitud no tiene datos completos (Mail/Nombre/Dirección/RUT)." );
            }

            // Evitar duplicados
            $yaExisteMail = $userModel->existsByMail($mail);
            if ($yaExisteMail) {
                throw new Exception("Ya existe un usuario con ese Mail en usuariosweb (ID {$yaExisteMail}).");
            }

            $st = $pdo->prepare("SELECT IDEmp FROM empresa WHERE RUT = :rut LIMIT 1");
            $st->execute([':rut'=>$rut]);
            $yaExisteRut = $st->fetchColumn();
            if ($yaExisteRut) {
                throw new Exception("Ya existe una empresa con ese RUT (IDEmp {$yaExisteRut}).");
            }

            // 1) Crear usuario empresa
            $passPlano = generarPassword(10);
            $idUsuario = $userModel->createWithPassword([
                'nombre' => $nombre,
                'mail' => $mail,
                'rol' => 2, // 2 = Empresa
                'direccion' => $direccion,
            ], $passPlano, true);

            // 2) Crear empresa (IDEmp = ID del usuario)
            $empModel->create([
                'id' => $idUsuario,
                'rut' => $rut,
                'direccion' => $direccion,
                'mail' => $mail,
                'nombre' => $nombre,
                'validacion' => 1
            ]);

            // 3) Teléfono (opcional)
            if ($telefono !== null) {
                $st = $pdo->prepare("INSERT IGNORE INTO telefonosempresa (IDEmp, Telefono) VALUES (:id, :tel)");
                $st->execute([':id'=>$idUsuario, ':tel'=>$telefono]);
            }

            // 4) Marcar solicitud como validada
            $solModel->markApproved($id);

            // 5) Mail
            $loginUrl = "https://comidapp.local/loginApp.php";
            $body = "
                <h3>Solicitud aprobada ✅</h3>
                <p>Hola <b>" . htmlspecialchars($nombre) . "</b>, tu empresa ya fue validada en ComidAPP.</p>
                <p><b>Usuario:</b> " . htmlspecialchars($mail) . "</p>
                <p><b>Contraseña temporal:</b> " . htmlspecialchars($passPlano) . "</p>
                <p>Al ingresar, el sistema te pedirá cambiar la contraseña.</p>
                <p>Ingresar: <a href='" . htmlspecialchars($loginUrl) . "'>" . htmlspecialchars($loginUrl) . "</a></p>
            ";

            $r = enviarMail($mail, "ComidAPP - Solicitud aprobada", $body);
            if ($r !== true) {
                throw new Exception("No se pudo enviar el correo: " . (string)$r);
            }

            $pdo->commit();
            $msg = "Solicitud aprobada. Se creó el usuario y se envió el mail.";
        }

        if ($action === 'rechazar') {
            $solModel->markRejected($id);
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
    if (!$solModel->exists()) throw new Exception("No existe la tabla solicitud en comidapp.");
    if ($estado === 'Todas') {
        $rows = $solModel->list("");
    } else {
        $rows = $solModel->list($estado);
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
            <th>Nombre</th>
            <th>Mail</th>
            <th>RUT</th>
            <th>Fecha</th>
            <th>Estado</th>
            <th style="width:240px;">Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="7" class="text-muted p-4">Sin registros.</td></tr>
        <?php else: foreach ($rows as $r): ?>
          <?php
            $id = (int)($r['ID'] ?? 0);
            $est = (string)($r['Estado'] ?? '');
            $isPendiente = (strcasecmp($est, 'Pendiente') === 0);
          ?>
          <tr>
            <td><?php echo $id; ?></td>
            <td><?php echo htmlspecialchars((string)($r['Nombre'] ?? '')); ?></td>
            <td><?php echo htmlspecialchars((string)($r['Mail'] ?? '')); ?></td>
            <td><?php echo htmlspecialchars((string)($r['RUT'] ?? '')); ?></td>
            <td><?php echo htmlspecialchars((string)($r['Fecha'] ?? '')); ?></td>
            <td>
              <span class="badge text-bg-<?php
                echo $isPendiente ? 'warning' : ((strcasecmp($est,'Validada')===0) ? 'success' : 'secondary');
              ?>"><?php echo htmlspecialchars($est); ?></span>
            </td>
            <td>
              <?php if ($isPendiente): ?>
                <form method="post" class="d-inline" onsubmit="return confirm('¿Aprobar esta solicitud y crear el usuario empresa?');">
                  <input type="hidden" name="id" value="<?php echo $id; ?>">
                  <input type="hidden" name="action" value="aprobar">
                  <button class="btn btn-sm btn-success" type="submit">Aprobar</button>
                </form>
                <form method="post" class="d-inline" onsubmit="return confirm('¿Rechazar esta solicitud?');">
                  <input type="hidden" name="id" value="<?php echo $id; ?>">
                  <input type="hidden" name="action" value="rechazar">
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
