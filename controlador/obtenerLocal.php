<?php
require_once "../modelo/connectionComidApp.php";

try {
    // Conexión a la base de datos
    $db = new DatabaseComidApp();
    $conexion = $db->getConnection();

    if (!$conexion) {
        throw new Exception("No se pudo establecer la conexión a la base de datos.");
    }

    // Consulta a la base de datos
    $sql = $conexion->query("SELECT ID, Nombre, Direccion, Delivery, Foto FROM Local");
    $locales = $sql->fetchAll(PDO::FETCH_OBJ);

    if (!$locales) {
        throw new Exception("No se encontraron datos en la tabla Local.");
    }

    // Generar filas de la tabla
    foreach ($locales as $local): ?>
        <tr>
        <td><?php echo htmlspecialchars($local->ID); ?></td>
            <td><?php echo htmlspecialchars($local->Nombre); ?></td>
            <td><?php echo htmlspecialchars($local->Direccion); ?></td>
            <td><?php echo htmlspecialchars($local->Delivery); ?></td>
            <td><img src="<?php echo htmlspecialchars($local->Foto); ?>" alt="Imagen del local" width="100"></td>
        </tr>
    <?php endforeach;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
