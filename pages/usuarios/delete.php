<?php
require_once __DIR__ . "/../../includes/authAdmin.php";
require_once __DIR__ . "/../../modelo/UsuariosWebModel.php";

$model = new UsuariosWebModel();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $msg = "";
    if ($id > 0) {
        $status = $model->softDelete($id);
        if ($status === 'deleted') {
            $msg = "Usuario eliminado.";
        } elseif ($status === 'inactivated') {
            $msg = "Usuario marcado como inactivo.";
        } else {
            // blocked
            $msg = "No se pudo eliminar por relaciones (FK). Se bloqueó el login cambiando la contraseña.";
        }
    }
    header("Location: index.php" . ($msg ? ("?msg=" . urlencode($msg)) : ""));
    exit();
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . htmlspecialchars($e->getMessage());
}
?>