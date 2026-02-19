<?php
class DatabaseComidBack
{
     private $host = "10.0.30.10";
    private $dbName = "ComiBack";
    private $username = "comidapp";
    private $password = "comidapp12";
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
