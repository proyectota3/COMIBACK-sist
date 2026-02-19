<?php
require_once '../modelo/connectionComiBack.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit();
}

$user = trim($_POST['user'] ?? '');
$pass = trim($_POST['pass'] ?? '');

if ($user === '' || $pass === '') {
    header("Location: ../index.php");
    exit();
}

$db = new DatabaseComidBack();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE Mail = :user LIMIT 1");
$stmt->execute([':user' => $user]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$u) {
    echo '<script>alert("Correo no válido."); window.location.href = "../index.php";</script>';
    exit();
}

$rol = (int)($u['IDRol'] ?? 0);
$activo = (int)($u['Activo'] ?? 0);
$stored = (string)($u['Contrasena'] ?? '');

if ($stored !== $pass) { // (si más adelante lo pasás a hash: password_verify)
    echo '<script>alert("Contraseña incorrecta."); window.location.href = "../index.php";</script>';
    exit();
}

if ($rol !== 3) {
    echo '<script>alert("Rol no válido."); window.location.href = "../index.php";</script>';
    exit();
}

if ($activo !== 1) {
    echo '<script>alert("Tu cuenta no está activa."); window.location.href = "../index.php";</script>';
    exit();
}

// ✅ sesión admin
$_SESSION['comiback_logged_in'] = true;
$_SESSION['admin_mail'] = $u['Mail'] ?? $user;
$_SESSION['admin_id'] = $u['IDUsu'] ?? ($u['id'] ?? null); // por si cambia el campo
$_SESSION['admin_rol'] = $rol;

header("Location: ../comiBack.php");
exit();
?>