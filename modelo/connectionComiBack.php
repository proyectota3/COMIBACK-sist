<?php
class DatabaseComidBack
{
    private $host = "localhost";
    private $dbName = "comiback";
    private $username = "root";
    private $password = "";
    private $connB;

    public function __construct()
    {
        $dsnB = "mysql:host=$this->host;dbname=$this->dbName"; // Cambié Database por dbname
        try {
            $this->connB = new PDO($dsnB, $this->username, $this->password);
            $this->connB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
    }

    public function getConnection()
    {
        return $this->connB;
    }
}
?>
