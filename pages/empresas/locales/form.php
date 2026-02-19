<?php
require_once __DIR__ . "/../../../includes/authAdmin.php";
require_once __DIR__ . "/../../../modelo/LocalesModel.php";

$model = new LocalesModel();
$meta = $model->meta();

$empresaId = isset($_GET['empresa_id']) ? (int)$_GET['empresa_id'] : (int)($_POST['empresa_id'] ?? 0);
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$row = $id ? $model->get($id) : null;

$title = ($id ? "Editar local" : "Nuevo local") . " | ComiBack";
require_once __DIR__ . "/../../../includes/header.php";
?>

<h1 class="h4 mb-3"><?php echo $id ? "Editar local" : "Nuevo local"; ?></h1>

<form class="card shadow-sm p-3" method="POST" action="save.php">
  <input type="hidden" name="id" value="<?php echo (int)$id; ?>">
  <input type="hidden" name="empresa_id" value="<?php echo (int)$empresaId; ?>">

  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Nombre</label>
      <input class="form-control" name="nombre" value="<?php echo htmlspecialchars($meta['nombre'] && $row ? ($row[$meta['nombre']] ?? '') : ''); ?>">
    </div>

    <div class="col-md-6">
      <label class="form-label">Dirección</label>
      <input class="form-control" name="direccion" value="<?php echo htmlspecialchars($meta['direccion'] && $row ? ($row[$meta['direccion']] ?? '') : ''); ?>">
    </div>

    <div class="col-md-3">
      <label class="form-label">Activo</label>
      <?php $val = $meta['activo'] && $row ? (string)($row[$meta['activo']] ?? '') : '1'; ?>
      <select class="form-select" name="activo">
        <option value="1" <?php echo ($val==='1')?'selected':''; ?>>Sí</option>
        <option value="0" <?php echo ($val==='0')?'selected':''; ?>>No</option>
      </select>
    </div>
  </div>

  <div class="d-flex gap-2 mt-3">
    <button class="btn btn-danger">Guardar</button>
    <a class="btn btn-outline-secondary" href="../view.php?id=<?php echo (int)$empresaId; ?>">Volver</a>
  </div>
</form>

<?php require_once __DIR__ . "/../../../includes/footer.php"; ?>