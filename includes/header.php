<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$title = $title ?? "ComiBack";
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($title); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/COMIBACK/styles.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
  <div class="container-fluid">
    <a class="navbar-brand" href="/COMIBACK/comiBack.php">ComiBack</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/COMIBACK/pages/usuarios/index.php">Usuarios</a></li>
        <li class="nav-item"><a class="nav-link" href="/COMIBACK/pages/empresas/index.php">Empresas</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Solicitudes</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/COMIBACK/pages/solicitudes/empresas.php">Solicitud Empresa</a></li>
            <li><a class="dropdown-item" href="/COMIBACK/pages/solicitudes/locales.php">Solicitud Local</a></li>
          </ul>
        </li>
      </ul>
      <div class="d-flex gap-2">
        <span class="navbar-text text-white small">
          <?php echo isset($_SESSION['admin_mail']) ? htmlspecialchars($_SESSION['admin_mail']) : "Admin"; ?>
        </span>
        <a class="btn btn-outline-light btn-sm" href="/COMIBACK/controlador/logout.php">Salir</a>
      </div>
    </div>
  </div>
</nav>
<main class="container py-4">
