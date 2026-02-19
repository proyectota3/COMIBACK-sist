<?php
require_once __DIR__ . "/../../includes/authAdmin.php";
require_once __DIR__ . "/../../modelo/SolicitudesLocalModel.php";

$model = new SolicitudesLocalModel();
$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
$sol = $id ? $model->get($id) : null;

$title = "Rechazar solicitud | ComiBack";
require_once __DIR__ . "/../../includes/header.php";
?>

<h1 class="h4 mb-3">Rechazar solicitud local</h1>

<?php if (!$sol): ?>
  <div class="alert alert-danger">Solicitud no encontrada.</div>
  <a class="btn btn-outline-secondary" href="locales.php">Volver</a>
<?php else: ?>
  <div class="card shadow-sm p-3">
    <div class="mb-2"><strong><?php echo htmlspecialchars($sol['Nombre'] ?? ''); ?></strong> — Empresa: <?php echo htmlspecialchars($sol['IDEmpresa'] ?? ''); ?></div>

    <form method="POST">
      <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
      <label class="form-label">Motivo</label>
      <input class="form-control" name="motivo" required>
      <div class="d-flex gap-2 mt-3">
        <button class="btn btn-danger">Rechazar</button>
        <a class="btn btn-outline-secondary" href="locales.php">Cancelar</a>
      </div>
    </form>
  </div>

  <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $motivo = trim($_POST['motivo'] ?? '');
      if ($motivo !== '') {
        $model->markRejected($id, $motivo, (string)($_SESSION['admin_mail'] ?? 'admin'));
        header("Location: locales.php");
        exit();
      }
    }
  ?>
<?php endif; ?>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>