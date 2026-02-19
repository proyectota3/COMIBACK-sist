<?php
require_once "../modelo/connectionComiback.php";

try {
    // Conexión a la base de datos
    $db = new DatabaseComidBack();
    $conexion = $db->getConnection();

    if (!$conexion) {
        throw new Exception("No se pudo establecer la conexión a la base de datos.");
    }

    // Consulta a la base de datos
    $sql = $conexion->query("SELECT * FROM usuarios");
    $usuarios = $sql->fetchAll(PDO::FETCH_OBJ);

    // Generar filas de la tabla
    foreach ($usuarios as $usuario): ?>
        <tr>
            <td><?php echo htmlspecialchars($usuario->IDUsuario); ?></td>
            <td><?php echo htmlspecialchars($usuario->IDRol); ?></td>
            <td><?php echo htmlspecialchars($usuario->Mail); ?></td>
            <td><?php echo htmlspecialchars($usuario->Contrasena); ?></td>
            <td><?php echo htmlspecialchars($usuario->Activo); ?></td>
        </tr>
    <?php endforeach;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
