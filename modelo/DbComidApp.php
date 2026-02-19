<?php
require_once __DIR__ . "/connectionComidApp.php";

class DbComidApp {
    private static ?PDO $pdo = null;

    public static function pdo(): PDO {
        // Importante: devolver SIEMPRE la misma conexión PDO dentro del mismo request.
        // Si cada modelo crea su propia conexión, las transacciones/locks no se comparten
        // y se producen errores tipo "Lock wait timeout".
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $db = new DatabaseComidApp();
        self::$pdo = $db->getConnection();
        return self::$pdo;
    }
}
?>