<?php
require_once __DIR__ . "/../../includes/authAdmin.php";
require_once __DIR__ . "/../../modelo/EmpresasModel.php";

$model = new EmpresasModel();
$id = (int)($_POST['id'] ?? 0);

$data = [
  'nombre' => trim($_POST['nombre'] ?? ''),
  'mail' => trim($_POST['mail'] ?? ''),
  'rut' => trim($_POST['rut'] ?? ''),
  'direccion' => trim($_POST['direccion'] ?? ''),
  'activo' => trim($_POST['activo'] ?? '1'),
];

try {
  if ($id > 0) $model->update($id, $data);
  else $model->create($data);

  header("Location: index.php");
  exit();
} catch (Exception $e) {
  http_response_code(500);
  echo "Error: " . htmlspecialchars($e->getMessage());
}
?>