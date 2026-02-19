<?php
require_once __DIR__ . "/includes/authAdmin.php";
require_once __DIR__ . "/modelo/DashboardModel.php";

$model = new DashboardModel();
$c = $model->counts();

$title = "Dashboard | ComiBack";
require_once __DIR__ . "/includes/header.php";
?>

<h1 class="h3 mb-3">Dashboard</h1>

<div class="row g-3">
  <div class="col-md-3">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted small">Usuarios (usuariosweb)</div>
        <div class="fs-4"><?php echo ($c['usuariosweb'] === null) ? "N/A" : (int)$c['usuariosweb']; ?></div>
        <a class="btn btn-sm btn-outline-danger mt-2" href="/COMIBACK/pages/usuarios/index.php">Administrar</a>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted small">Empresas</div>
        <div class="fs-4"><?php echo ($c['empresa'] === null) ? "N/A" : (int)$c['empresa']; ?></div>
        <a class="btn btn-sm btn-outline-danger mt-2" href="/COMIBACK/pages/empresas/index.php">Administrar</a>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted small">Locales</div>
        <div class="fs-4"><?php echo ($c['local'] === null) ? "N/A" : (int)$c['local']; ?></div>
        <a class="btn btn-sm btn-outline-danger mt-2" href="/COMIBACK/pages/empresas/index.php">Ver por empresa</a>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted small">Solicitudes pendientes</div>
        <div class="fs-4">
          <?php
            if ($c['solicitud_empresa_pend'] === null && $c['solicitud_local_pend'] === null) echo "N/A";
            else echo (int)($c['solicitud_empresa_pend'] ?? 0) + (int)($c['solicitud_local_pend'] ?? 0);
          ?>
        </div>
        <a class="btn btn-sm btn-outline-danger mt-2" href="/COMIBACK/pages/solicitudes/empresas.php">Ver</a>
      </div>
    </div>
  </div>
</div>

<div class="alert alert-warning mt-4">
  <P>Rodrigo - Gaston - Martin - Agustin - Santiago</p>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>