<?php
require_once __DIR__ . "/../../includes/authAdmin.php";
require_once __DIR__ . "/../../modelo/EmpresasModel.php";

$model = new EmpresasModel();
$meta = $model->meta();

$q = trim($_GET['q'] ?? '');
$rows = $model->list($q);

$title = "Empresas | ComiBack";
require_once __DIR__ . "/../../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 m-0">Empresas</h1>
  <a class="btn btn-danger" href="form.php">Nueva</a>
</div>

<form class="row g-2 mb-3" method="GET">
  <div class="col-md-10">
    <input class="form-control" name="q" placeholder="Buscar por nombre" value="<?php echo htmlspecialchars($q); ?>">
  </div>
  <div class="col-md-2 d-grid">
    <button class="btn btn-outline-danger">Buscar</button>
  </div>
</form>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-striped align-middle m-0">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Mail</th>
          <th>RUT</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <?php $id = $meta['pk'] ? (int)($r[$meta['pk']] ?? 0) : 0; ?>
          <tr>
            <td><?php echo htmlspecialchars($meta['pk'] ? ($r[$meta['pk']] ?? '') : ''); ?></td>
            <td><?php echo htmlspecialchars($meta['nombre'] ? ($r[$meta['nombre']] ?? '') : ''); ?></td>
            <td><?php echo htmlspecialchars($meta['mail'] ? ($r[$meta['mail']] ?? '') : ''); ?></td>
            <td><?php echo htmlspecialchars($meta['rut'] ? ($r[$meta['rut']] ?? '') : ''); ?></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="view.php?id=<?php echo $id; ?>">Abrir</a>
              <a class="btn btn-sm btn-outline-secondary" href="form.php?id=<?php echo $id; ?>">Editar</a>
              <a class="btn btn-sm btn-outline-danger" href="delete.php?id=<?php echo $id; ?>" onclick="return confirm('¿Eliminar / desactivar empresa?');">Baja</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
          <tr><td colspan="5" class="text-center text-muted py-4">Sin registros</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>