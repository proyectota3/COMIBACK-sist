<?php
include "modelo/connectionComiback.php";

$db = new DatabaseComidBack();
$conexion = $db->getConnection();

if ($conexion) {
    echo "Conexión exitosa a la base de datos.";
} else {
    echo "No se pudo establecer la conexión.";
}
?>
