<?php
require_once __DIR__ . "/../../../includes/authAdmin.php";
require_once __DIR__ . "/../../../modelo/LocalesModel.php";

$model = new LocalesModel();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$empresaId = isset($_GET['empresa_id']) ? (int)$_GET['empresa_id'] : 0;

try {
  if ($id > 0) $model->softDelete($id);
  header("Location: ../view.php?id=".$empresaId);
  exit();
} catch (Exception $e) {
  http_response_code(500);
  echo "Error: " . htmlspecialchars($e->getMessage());
}
?>