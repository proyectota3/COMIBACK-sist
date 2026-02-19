<?php
require_once __DIR__ . "/../../includes/authAdmin.php";
require_once __DIR__ . "/../../modelo/UsuariosWebModel.php";

$model = new UsuariosWebModel();

$id = (int)($_POST['id'] ?? 0);
$data = [
    'nombre' => trim($_POST['nombre'] ?? ''),
    'mail' => trim($_POST['mail'] ?? ''),
    'rol' => trim($_POST['rol'] ?? ''),
    'direccion' => trim($_POST['direccion'] ?? ''),
    'activo' => trim($_POST['activo'] ?? '1'),
];

try {
    if ($id > 0) {
        $model->update($id, $data);
    } else {
        // create() setea pass=1234(hasheada) + DebeCambiarPass=1 si existe
        $model->create($data);
    }
    header("Location: index.php");
    exit();
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . htmlspecialchars($e->getMessage());
}
?>