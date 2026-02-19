<?php
require_once __DIR__ . "/../../includes/authAdmin.php";
require_once __DIR__ . "/../../modelo/DbComidApp.php";
require_once __DIR__ . "/../../modelo/SolicitudesEmpresaModel.php";
require_once __DIR__ . "/../../modelo/EmpresasModel.php";

$pdo = DbComidApp::pdo();
$solModel = new SolicitudesEmpresaModel();
$empModel = new EmpresasModel();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sol = $id ? $solModel->get($id) : null;

if (!$sol) { die("Solicitud no encontrada."); }

try {
  $pdo->beginTransaction();

  // Crear empresa real
  $empModel->create([
    'nombre' => (string)($sol['Nombre'] ?? ''),
    'mail' => (string)($sol['Mail'] ?? ''),
    'rut' => (string)($sol['RUT'] ?? ''),
    'direccion' => (string)($sol['Direccion'] ?? ''),
    'activo' => '1'
  ]);

  // Marcar solicitud aprobada
  $solModel->markApproved($id, (string)($_SESSION['admin_mail'] ?? 'admin'));

  $pdo->commit();
  header("Location: empresas.php");
  exit();
} catch (Exception $e) {
  $pdo->rollBack();
  http_response_code(500);
  echo "Error: " . htmlspecialchars($e->getMessage());
}
?>