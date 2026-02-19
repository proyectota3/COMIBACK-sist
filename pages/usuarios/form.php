<?php
require_once __DIR__ . "/../../includes/authAdmin.php";
require_once __DIR__ . "/../../modelo/UsuariosWebModel.php";

$model = new UsuariosWebModel();
$meta = $model->meta();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$row = $id ? $model->get($id) : null;

$title = ($id ? "Editar usuario" : "Nuevo usuario") . " | ComiBack";
require_once __DIR__ . "/../../includes/header.php";
?>

<h1 class="h4 mb-3"><?php echo $id ? "Editar usuario" : "Nuevo usuario"; ?></h1>

<?php if ($id && !$row): ?>
  <div class="alert alert-danger">Usuario no encontrado.</div>
<?php endif; ?>

<form class="card shadow-sm p-3" method="POST" action="save.php">
  <input type="hidden" name="id" value="<?php echo (int)$id; ?>">

  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Nombre</label>
      <input class="form-control" name="nombre" value="<?php echo htmlspecialchars($meta['nombre'] && $row ? ($row[$meta['nombre']] ?? '') : ''); ?>">
      <div class="form-text">Columna detectada: <code><?php echo htmlspecialchars($meta['nombre'] ?? 'N/A'); ?></code></div>
    </div>

    <div class="col-md-6">
      <label class="form-label">Mail</label>
      <input class="form-control" name="mail" value="<?php echo htmlspecialchars($meta['mail'] && $row ? ($row[$meta['mail']] ?? '') : ''); ?>">
      <div class="form-text">Columna detectada: <code><?php echo htmlspecialchars($meta['mail'] ?? 'N/A'); ?></code></div>
    </div>

    <?php if ($meta['direccion']): ?>
      <div class="col-md-12">
        <label class="form-label">Dirección</label>
        <input class="form-control" name="direccion" value="<?php echo htmlspecialchars($row ? ($row[$meta['direccion']] ?? '') : ''); ?>">
        <div class="form-text">Columna: <code><?php echo htmlspecialchars($meta['direccion']); ?></code></div>
      </div>
    <?php else: ?>
      <input type="hidden" name="direccion" value="">
    <?php endif; ?>

    <div class="col-md-3">
      <label class="form-label">Rol</label>
      <input class="form-control" name="rol" value="<?php echo htmlspecialchars($meta['rol'] && $row ? ($row[$meta['rol']] ?? '') : ''); ?>">
      <div class="form-text">Columna: <code><?php echo htmlspecialchars($meta['rol'] ?? 'N/A'); ?></code></div>
    </div>

    <div class="col-md-3">
      <label class="form-label">Activo</label>
      <select class="form-select" name="activo">
        <?php
          $val = $meta['activo'] && $row ? (string)($row[$meta['activo']] ?? '') : '1';
        ?>
        <option value="1" <?php echo ($val==='1')?'selected':''; ?>>Sí</option>
        <option value="0" <?php echo ($val==='0')?'selected':''; ?>>No</option>
      </select>
      <div class="form-text">Columna: <code><?php echo htmlspecialchars($meta['activo'] ?? 'N/A'); ?></code></div>
    </div>

    <div class="col-md-6">
      <label class="form-label">Contraseña (hash)</label>
      <input class="form-control" value="<?php echo htmlspecialchars($meta['pass'] && $row ? ($row[$meta['pass']] ?? '') : ''); ?>" readonly>
      <div class="form-text">
        No se muestra en claro. 
        <?php if ($meta['pass']): ?>
          Podés resetearla a <code>1234</code> (queda hasheada).
        <?php else: ?>
          No se detectó columna de password.
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="d-flex gap-2 mt-3">
    <button class="btn btn-danger">Guardar</button>
    <?php if ($id && $meta['pass']): ?>
      <a class="btn btn-outline-danger" href="resetPass.php?id=<?php echo (int)$id; ?>" onclick="return confirm('¿Resetear contraseña a 1234 para este usuario?');">Reset 1234</a>
    <?php endif; ?>
    <a class="btn btn-outline-secondary" href="index.php">Volver</a>
  </div>
</form>

<?php require_once __DIR__ . "/../../includes/footer.php"; ?>