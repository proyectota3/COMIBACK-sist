<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOLICITUDES - ComidApp</title>
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

    <div class="col-8 p-4">
    <table class="table">
        <thead class="bg-info">
        <tr>
            <th scope="col">ID solicitud</th>
            <th scope="col">ID usuario</th>
            <th scope="col">fecha solicitud
            <th scope="col">estado
          
        </tr>
        </thead>
        <tbody class="table-group-divider">
        <?php include "../controlador/verSolicitud.php"; ?>

</tbody>
    </table>






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
