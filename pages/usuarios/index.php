<?php
require_once __DIR__ . "/../../includes/authAdmin.php";
require_once __DIR__ . "/../../modelo/UsuariosWebModel.php";

$model = new UsuariosWebModel();
$meta = $model->meta();

$q = trim($_GET['q'] ?? '');
$rol = trim($_GET['rol'] ?? '');
$activo = trim($_GET['activo'] ?? '');

$rows = $model->list($q, $rol, $activo);

$title = "Usuarios | ComiBack";
require_once __DIR__ . "/../../includes/header.php";
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 m-0">Usuarios (usuariosweb)</h1>
  <a class="btn btn-danger" href="form.php">Nuevo</a>
</div>

<?php if (!$meta['pk']): ?>
  <div class="alert alert-danger">No se pudo detectar la PK de <code>usuariosweb</code>. Revisá la tabla.</div>
<?php endif; ?>

<form class="row g-2 mb-3" method="GET">
  <div class="col-md-5">
    <input class="form-control" name="q" placeholder="Buscar por nombre/mail" value="<?php echo htmlspecialchars($q); ?>">
  </div>
  <div class="col-md-3">
    <input class="form-control" name="rol" placeholder="Rol (ej: 1,2,3)" value="<?php echo htmlspecialchars($rol); ?>">
  </div>
  <div class="col-md-2">
    <select class="form-select" name="activo">
      <option value="" <?php echo $activo===''?'selected':''; ?>>Activo: Todos</option>
      <option value="1" <?php echo $activo==='1'?'selected':''; ?>>Activos</option>
      <option value="0" <?php echo $activo==='0'?'selected':''; ?>>Inactivos</option>
    </select>
  </div>
  <div class="col-md-2 d-grid">
    <button class="btn btn-outline-danger">Filtrar</button>
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
          <th>Contraseña (hash)</th>
          <th>Rol</th>
          <th>Activo</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?php echo htmlspecialchars($meta['pk'] ? ($r[$meta['pk']] ?? '') : ''); ?></td>
            <td><?php echo htmlspecialchars($meta['nombre'] ? ($r[$meta['nombre']] ?? '') : ''); ?></td>
            <td><?php echo htmlspecialchars($meta['mail'] ? ($r[$meta['mail']] ?? '') : ''); ?></td>
            <td>
              <?php
                if (!$meta['pass']) { echo "—"; }
                else {
                  $h = (string)($r[$meta['pass']] ?? '');
                  $short = (strlen($h) > 16) ? (substr($h, 0, 16) . "…") : $h;
                  echo '<span title="' . htmlspecialchars($h) . '">' . htmlspecialchars($short) . '</span>';
                }
              ?>
            </td>
            <td><?php echo htmlspecialchars($meta['rol'] ? ($r[$meta['rol']] ?? '') : ''); ?></td>
            <td>
              <?php
                if (!$meta['activo']) { echo "—"; }
                else {
                  $a = (string)($r[$meta['activo']] ?? '');
                  echo ($a === '1' || strtolower($a)==='activo') ? "Sí" : "No";
                }
              ?>
            </td>
            <td class="text-end">
              <?php $id = $meta['pk'] ? (int)($r[$meta['pk']] ?? 0) : 0; ?>
              <?php if ($meta['pk'] && $meta['pass']): ?>
                <a class="btn btn-sm btn-outline-danger" href="resetPass.php?id=<?php echo $id; ?>" onclick="return confirm('¿Resetear contraseña a 1234 para este usuario?');">Reset 1234</a>
              <?php endif; ?>
              <a class="btn btn-sm btn-outline-secondary" href="form.php?id=<?php echo $id; ?>">Editar</a>
              <?php if ($meta['pk']): ?>
                <a class="btn btn-sm btn-outline-danger" href="delete.php?id=<?php echo $id; ?>" onclick="return confirm('¿Dar de baja / bloquear este usuario?');">Baja</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Sin registros</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>