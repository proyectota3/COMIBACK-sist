<?php
// Incluye el archivo de conexión
require_once "../modelo/connectionComiBack.php";

// Obtener la conexión
$database = new DatabaseComidBack();
$bd = $database->getConnection();

// Verifica si se enviaron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnregistrar'])) {
    // Variables del formulario
    $Mail = isset($_POST['Mail']) ? $_POST['Mail'] : "";
    $Contrasena = isset($_POST['Contrasena']) ? $_POST['Contrasena'] : "";
    $Activo = isset($_POST['Activo']) ? $_POST['Activo'] : "";
    $IDRol = isset($_POST['IDRol']) ? $_POST['IDRol'] : "";

    // Inserción en la base de datos
    try {
        $sql = $bd->prepare("INSERT INTO usuarios (Mail, Contrasena, Activo, IDRol) VALUES (:Mail, :Contrasena, :Activo, :IDRol)");
        
        $sql->bindParam(':Mail', $Mail);
        $sql->bindParam(':Contrasena', $Contrasena);
        $sql->bindParam(':Activo', $Activo);
        $sql->bindParam(':IDRol', $IDRol);
        
        $sql->execute();

        // Redirecciona después de la inserción
        header('Location: ../pages/users.php');
        exit;

    } catch (PDOException $e) {
        echo "Error al insertar: " . $e->getMessage();
    }
}
?>
