<?php
// Incluye el archivo de conexión
require_once "../modelo/connectionComidApp.php";

// Obtener la conexión
try {
    $database = new DatabaseComidApp();
    $bd = $database->getConnection();
} catch (Exception $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}

// Verifica si se enviaron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['solicitud'])) {
    // Sanitización de datos
    $RUT = isset($_POST['RUT']) ? trim(htmlspecialchars($_POST['RUT'])) : "";
    $Direccion = isset($_POST['Direccion']) ? trim(htmlspecialchars($_POST['Direccion'])) : "";
    $Mail = isset($_POST['Mail']) ? trim(filter_var($_POST['Mail'], FILTER_VALIDATE_EMAIL)) : "";
    $Nombre = isset($_POST['Nombre']) ? trim(htmlspecialchars($_POST['Nombre'])) : "";

    if (!$RUT || !$Mail || !$Nombre) {
        die("Error: Todos los campos son obligatorios.");
    }

    try {
        // Iniciar transacción
        $bd->beginTransaction();

        // Verificar si el usuario ya existe
        $sql_check = $bd->prepare("SELECT id FROM usuarios WHERE email = :Mail");
        $sql_check->bindParam(':Mail', $Mail);
        $sql_check->execute();
        $usuario_existente = $sql_check->fetch(PDO::FETCH_ASSOC);

        if ($usuario_existente) {
            // Si el usuario ya existe, obtenemos su ID
            $usuario_id = $usuario_existente['id'];
        } else {
            // Insertar nuevo usuario
            $sql_usuario = $bd->prepare("INSERT INTO usuarios (Nombre, email) VALUES (:Nombre, :Mail)");
            $sql_usuario->bindParam(':Nombre', $Nombre);
            $sql_usuario->bindParam(':Mail', $Mail);
            $sql_usuario->execute();
            $usuario_id = $bd->lastInsertId();
        }

        // Insertar en la tabla `empresa`
        $sql_empresa = $bd->prepare("INSERT INTO empresa (RUT, Direccion, Mail, Nombre, IDEmp) VALUES (:RUT, :Direccion, :Mail, :Nombre, :IDEmp)");
        $sql_empresa->bindParam(':RUT', $RUT);
        $sql_empresa->bindParam(':Direccion', $Direccion);
        $sql_empresa->bindParam(':Mail', $Mail);
        $sql_empresa->bindParam(':Nombre', $Nombre);
        $sql_empresa->bindParam(':IDEmp', $usuario_id);
        $sql_empresa->execute();

        // Insertar solicitud en la tabla `solicitud`
        $sql_solicitud = $bd->prepare("INSERT INTO solicitud (usuario_id, fecha_solicitud, estado) VALUES (:usuario_id, NOW(), 'pendiente')");
        $sql_solicitud->bindParam(':usuario_id', $usuario_id);
        $sql_solicitud->execute();

        // Confirmar la transacción
        $bd->commit();

        // Redireccionar después de la inserción
        header('Location: ../COMIDAPP/pages/contacto.php');
        exit;
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $bd->rollBack();
        echo "Error al procesar la solicitud: " . $e->getMessage();
    }
}
?>
