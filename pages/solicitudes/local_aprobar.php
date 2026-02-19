<?php
require_once __DIR__ . "/../../includes/authAdmin.php";
require_once __DIR__ . "/../../modelo/DbComidApp.php";
require_once __DIR__ . "/../../modelo/SolicitudesLocalModel.php";
require_once __DIR__ . "/../../modelo/LocalesModel.php";

$pdo = DbComidApp::pdo();
$solModel = new SolicitudesLocalModel();
$locModel = new LocalesModel();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sol = $id ? $solModel->get($id) : null;

if (!$sol) { die("Solicitud no encontrada."); }

$empresaId = (int)($sol['IDEmpresa'] ?? 0);
if ($empresaId <= 0) { die("IDEmpresa inválido en la solicitud."); }

try {
  $pdo->beginTransaction();

  $locModel->create([
    'empresa' => $empresaId,
    'nombre' => (string)($sol['Nombre'] ?? ''),
    'direccion' => (string)($sol['Direccion'] ?? ''),
    'activo' => '1'
  ]);

  $solModel->markApproved($id, (string)($_SESSION['admin_mail'] ?? 'admin'));

  $pdo->commit();
  header("Location: locales.php");
  exit();
} catch (Exception $e) {
  $pdo->rollBack();
  http_response_code(500);
  echo "Error: " . htmlspecialchars($e->getMessage());
}
?>