<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aviso de Privacidad - ComidApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet"> <!-- Enlace a tu CSS -->
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
        <a class="navbar-brand" href="../comiback.php">ComiBACK</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="users.php">GESTION DE USUARIOS</a></li>
                <li class="nav-item"><a class="nav-link" href="local.php">GESTION DE COMERCIOS</a></li>
                <li class="nav-item"><a class="nav-link" href="solicitudes.php">GESTION DE SOLICITUDES</a></li>
                </ul>
                <form class="d-flex" role="search">
                    <input class="form-control me-2" type="search" placeholder="Buscar" aria-label="Search">
                    <button class="btn btn-outline-success" type="submit">Buscar</button>
                </form>
            </div>
        </div>
    </nav>
    <header class="bg-light py-2">
    </header>

    <main class="container my-5 flex-grow-1">
        <php>
<h1 class="text-center p-3">CARRITOS OPERATIVOS</h1>
<div class="container-fluid row">

<form class="col-4 p-3" method="POST" action="../controlador/agregarLocal.php">
        <h3>Registro de carrito</h3>
        <div class="mb-3">
        <label for="exampleInputEmail1" class="form-label">Nombre</label>
        <input type="text" class="form-control" name="Nombre">
        <div id="emailHelp" class="form-text"></div>
        </div>
        <div class="mb-3">
            <label for="exampleInputEmail1" class="form-label">Direccion</label>
            <input type="text" class="form-control" name="Direccion">
            <div id="emailHelp" class="form-text"></div>
        </div>
        <div class="mb-3">
            <label for="exampleInputEmail1" class="form-label">Delivery</label>
            <input type="text" class="form-control" name="Delivery">
            <div id="emailHelp" class="form-text"></div>
        </div>
        <button type="submit" class="btn btn-primary" name="btnregistrar">Registrar</button>
</form>

<!-- Tabla de usuarios -->
<div class="col-8 p-4">
    <table class="table">
        <thead class="bg-info">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Nombre</th>
                <th scope="col">Direccion</th>
                <th scope="col">Delivery</th>
                <th scope="col">Foto</th>
            </tr>
        </thead>
        <tbody class="table-group-divider">
            <?php include "../controlador/obtenerLocal.php"; ?>
        </tbody>
    </table>
</div>





</div>






        </php>
    </main>

    <footer class="footer bg-body-tertiary text-center text-lightblue py-3">
        <div class="container">
            <p class="mb-0">© 2024 ComidApp. Derechos Reservados, Uruguay.</p>
        </div>
    </footer>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
