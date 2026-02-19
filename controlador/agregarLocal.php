<?php
// Incluye el archivo de conexión
require_once "../modelo/connectionComidApp.php";

// Obtener la conexión
$database = new DatabaseComidApp();
$bd = $database->getConnection();

// Verifica si se enviaron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnregistrar'])) {
    // Variables del formulario
    $Nombre = isset($_POST['Nombre']) ? $_POST['Nombre'] : "";
    $Direccion = isset($_POST['Direccion']) ? $_POST['Direccion'] : "";
    $Delivery = isset($_POST['Delivery']) ? $_POST['Delivery'] : "";
    $Foto = isset($_POST['Foto']) ? $_POST['Foto'] : "";

    // Inserción en la base de datos
    try {
        $sql = $bd->prepare("INSERT INTO Local (Nombre, Direccion, Delivery, Foto) VALUES (:Nombre, :Direccion, :Delivery, :Foto)");
        
        $sql->bindParam(':Nombre', $Nombre);
        $sql->bindParam(':Direccion', $Direccion);
        $sql->bindParam(':Delivery', $Delivery);
        $sql->bindParam(':Foto', $Foto);
        
        $sql->execute();

        // Redirecciona después de la inserción
        header('Location: ../pages/local.php');
        exit;

    } catch (PDOException $e) {
        echo "Error al insertar: " . $e->getMessage();
    }
}
?>
