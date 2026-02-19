<?php
require_once __DIR__ . "/../../includes/authAdmin.php";
require_once __DIR__ . "/../../modelo/UsuariosWebModel.php";

$model = new UsuariosWebModel();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    if ($id > 0) {
        $model->resetPassword1234($id);
    }
    header("Location: index.php");
    exit();
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . htmlspecialchars($e->getMessage());
}
?>