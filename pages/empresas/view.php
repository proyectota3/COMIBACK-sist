<?php
require_once __DIR__ . "/../../includes/authAdmin.php";
require_once __DIR__ . "/../../modelo/EmpresasModel.php";
require_once __DIR__ . "/../../modelo/LocalesModel.php";

$empModel = new EmpresasModel();
$locModel = new LocalesModel();

$empMeta = $empModel->meta();
$locMeta = $locModel->meta();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$empresa = $id ? $empModel->get($id) : null;
$locales = $id ? $locModel->listByEmpresa($id) : [];

$title = "Empresa | ComiBack";
require_once __DIR__ . "/../../includes/header.php";
?>

<?php if (!$empresa): ?>
  <div class="alert alert-danger">Empresa no encontrada.</div>
  <a class="btn btn-outline-secondary" href="index.php">Volver</a>
<?php else: ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Empresa: <?php echo htmlspecialchars($empMeta['nombre'] ? ($empresa[$empMeta['nombre']] ?? '') : ''); ?></h1>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="form.php?id=<?php echo $id; ?>">Editar</a>
      <a class="btn btn-outline-danger" href="index.php">Volver</a>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <div class="row g-2">
        <div class="col-md-4"><strong>ID:</strong> <?php echo htmlspecialchars($empresa[$empMeta['pk']] ?? ''); ?></div>
        <div class="col-md-4"><strong>Mail:</strong> <?php echo htmlspecialchars($empMeta['mail'] ? ($empresa[$empMeta['mail']] ?? '') : ''); ?></div>
        <div class="col-md-4"><strong>RUT:</strong> <?php echo htmlspecialchars($empMeta['rut'] ? ($empresa[$empMeta['rut']] ?? '') : ''); ?></div>
        <div class="col-md-12"><strong>Dirección:</strong> <?php echo htmlspecialchars($empMeta['direccion'] ? ($empresa[$empMeta['direccion']] ?? '') : ''); ?></div>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-between align-items-center mb-2">
    <h2 class="h5 m-0">Locales</h2>
    <a class="btn btn-danger btn-sm" href="locales/form.php?empresa_id=<?php echo $id; ?>">Nuevo local</a>
  </div>

  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-striped align-middle m-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Dirección</th>
            <th>Activo</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($locales as $l): ?>
            <?php $lid = $locMeta['pk'] ? (int)($l[$locMeta['pk']] ?? 0) : 0; ?>
            <tr>
              <td><?php echo htmlspecialchars($locMeta['pk'] ? ($l[$locMeta['pk']] ?? '') : ''); ?></td>
              <td><?php echo htmlspecialchars($locMeta['nombre'] ? ($l[$locMeta['nombre']] ?? '') : ''); ?></td>
              <td><?php echo htmlspecialchars($locMeta['direccion'] ? ($l[$locMeta['direccion']] ?? '') : ''); ?></td>
              <td>
                <?php
                  if (!$locMeta['activo']) echo "—";
                  else {
                    $a=(string)($l[$locMeta['activo']] ?? '');
                    echo ($a==='1') ? "Sí" : "No";
                  }
                ?>
              </td>
              <td class="text-end">
                <a class="btn btn-sm btn-outline-secondary" href="locales/form.php?id=<?php echo $lid; ?>&empresa_id=<?php echo $id; ?>">Editar</a>
                <a class="btn btn-sm btn-outline-danger" href="locales/delete.php?id=<?php echo $lid; ?>&empresa_id=<?php echo $id; ?>" onclick="return confirm('¿Eliminar / desactivar local?');">Baja</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$locales): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">Sin locales</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>