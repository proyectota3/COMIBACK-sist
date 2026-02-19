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
        $dsnB = "mysql:host=$this->host;Database=$this->dbName";
        try {
            $this->connB = new PDO($dsnB, $this->username, $this->password);
            $this->connB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
    }

    public function getConnection()
{
    $this->connB->exec("USE $this->dbName");
    return $this->connB;
}

}

?>
