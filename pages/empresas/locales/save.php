<?php
require_once __DIR__ . "/../../../includes/authAdmin.php";
require_once __DIR__ . "/../../../modelo/LocalesModel.php";

$model = new LocalesModel();

$id = (int)($_POST['id'] ?? 0);
$empresaId = (int)($_POST['empresa_id'] ?? 0);

$data = [
  'empresa' => $empresaId,
  'nombre' => trim($_POST['nombre'] ?? ''),
  'direccion' => trim($_POST['direccion'] ?? ''),
  'activo' => trim($_POST['activo'] ?? '1'),
];

try {
  if ($id > 0) $model->update($id, $data);
  else $model->create($data);

  header("Location: ../view.php?id=".$empresaId);
  exit();
} catch (Exception $e) {
  http_response_code(500);
  echo "Error: " . htmlspecialchars($e->getMessage());
}
?>