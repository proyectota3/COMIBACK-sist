<?php
class DatabaseComidApp
{
    private $host = "localhost";
    private $dbName = "comidapp";
    private $username = "root";
    private $password = "";
    private $connB;

    public function __construct()
    {
        // PDO DSN correcto: usar dbname + charset
        $dsnB = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
        try {
            $this->connB = new PDO($dsnB, $this->username, $this->password);
            $this->connB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
    }

    public function getConnection()
{
    // Por compatibilidad, volvemos a forzar USE (no hace daño)
    $this->connB->exec("USE `{$this->dbName}`");
    return $this->connB;
}

}

?>
