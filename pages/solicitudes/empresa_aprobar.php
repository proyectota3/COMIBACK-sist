<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../modelo/connectionComidApp.php";
require_once __DIR__ . "/../modelo/generarPassword.php"; // si ya lo tenés
require_once __DIR__ . "/../vendor/autoload.php"; // o tu ruta de PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION["id_admin"])) {
  header("Location: ../index.php");
  exit();
}

$idSolicitud = (int)($_POST["id"] ?? 0);
if ($idSolicitud <= 0) {
  header("Location: ../pages/solicitudes/empresas.php?e=ID inválido");
  exit();
}

$db = new DatabaseComidApp();
$pdo = $db->getConnection();

try {
  $pdo->beginTransaction();

  // 1) Traer la solicitud
  $st = $pdo->prepare("SELECT * FROM solicitud WHERE ID = ? FOR UPDATE");
  $st->execute([$idSolicitud]);
  $sol = $st->fetch(PDO::FETCH_ASSOC);

  if (!$sol) throw new Exception("Solicitud no encontrada");
  if (strtolower($sol["Estado"]) !== strtolower("Pendiente")) {
    throw new Exception("La solicitud no está en estado Pendiente");
  }

  $mail = trim($sol["Mail"]);
  $nombre = trim($sol["Nombre"]);
  $rut = trim($sol["RUT"]);

  // 2) Verificar si ya existe usuario con ese mail (evitar duplicados)
  $st = $pdo->prepare("SELECT IDUsu FROM usuariosweb WHERE Mail = ? LIMIT 1");
  $st->execute([$mail]);
  $existe = $st->fetchColumn();

  if ($existe) {
    throw new Exception("Ya existe un usuario con ese Mail en usuariosweb");
  }

  // 3) Generar password temporal
  $passPlano = generarPassword(10); // tu función
  $passHash  = password_hash($passPlano, PASSWORD_BCRYPT);

  // 4) Crear usuario empresa (ajustar campos reales)
  // Suponiendo: usuariosweb(Mail, Nombre, Rol, Pass, DebeCambiarPass, Activo)
  $rolEmpresa = 2; // ej: 2=Empresa (ajustar a tu sistema)
  $st = $pdo->prepare("
    INSERT INTO usuariosweb (Nombre, Mail, Rol, Pass, DebeCambiarPass, Activo)
    VALUES (?, ?, ?, ?, 1, 1)
  ");
  $st->execute([$nombre, $mail, $rolEmpresa, $passHash]);

  // (opcional) Si tenés tabla empresa y querés crear empresa acá:
  // INSERT INTO empresa (RUT, Nombre, Direccion, Mail, Telefono, Activo) VALUES...

  // 5) Marcar solicitud como Validada
  $st = $pdo->prepare("UPDATE solicitud SET Estado='Validada' WHERE ID=?");
  $st->execute([$idSolicitud]);

  // 6) Enviar mail
  $mailOk = enviarMailAprobacion($mail, $nombre, $passPlano);

  if (!$mailOk) {
    throw new Exception("No se pudo enviar el correo");
  }

  $pdo->commit();
  header("Location: ../pages/solicitudes/empresas.php?ok=Solicitud aprobada y mail enviado");
  exit();

} catch (Exception $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  header("Location: ../pages/solicitudes/empresas.php?e=" . urlencode($e->getMessage()));
  exit();
}

function enviarMailAprobacion($destino, $nombre, $passPlano) {
  try {
    $mail = new PHPMailer(true);

    // CONFIG SMTP (ajustar)
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "TU_GMAIL";
    $mail->Password = "TU_APP_PASSWORD";
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom("TU_GMAIL", "ComidAPP");
    $mail->addAddress($destino, $nombre);

    $mail->isHTML(true);
    $mail->Subject = "Solicitud aprobada - Acceso a ComidAPP";
    $mail->Body = "
      <h3>Hola " . htmlspecialchars($nombre) . "</h3>
      <p>Tu solicitud fue aprobada. Ya podés ingresar a ComidAPP.</p>
      <p><b>Usuario:</b> " . htmlspecialchars($destino) . "</p>
      <p><b>Contraseña temporal:</b> " . htmlspecialchars($passPlano) . "</p>
      <p>Al ingresar, el sistema te pedirá cambiar la contraseña.</p>
      <p>Login: <a href='https://comidapp.local/loginApp.php'>Ingresar</a></p>
    ";

    $mail->send();
    return true;

  } catch (Exception $e) {
    return false;
  }
}
