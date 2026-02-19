<?php
require_once __DIR__ . "/connectionComidApp.php";

class DbComidApp {
    public static function pdo(): PDO {
        $db = new DatabaseComidApp();
        return $db->getConnection();
    }
}
?>