<?php
include "modelo/connectionComidApp.php";

$db = new DatabaseComidApp();
$conexion = $db->getConnection();

if ($conexion) {
    echo "Conexión exitosa a la base de datos.";
} else {
    echo "No se pudo establecer la conexión.";
}
?>
