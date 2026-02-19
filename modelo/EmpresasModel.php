<?php
require_once __DIR__ . "/DbComidApp.php";
require_once __DIR__ . "/SchemaHelper.php";

class EmpresasModel {
    private PDO $pdo;
    private string $table = "empresa";
    private ?string $pk;

    private ?string $colNombre;
    private ?string $colMail;
    private ?string $colRUT;
    private ?string $colDireccion;
    private ?string $colActivo;

    public function __construct() {
        $this->pdo = DbComidApp::pdo();
        $this->pk = SchemaHelper::primaryKey($this->pdo, $this->table);
        $cols = SchemaHelper::columns($this->pdo, $this->table);
        $this->colNombre = SchemaHelper::pick($cols, ["Nombre","nombre","NombreEmp","Empresa"]);
        $this->colMail = SchemaHelper::pick($cols, ["Mail","mail","Email","email"]);
        $this->colRUT = SchemaHelper::pick($cols, ["RUT","rut"]);
        $this->colDireccion = SchemaHelper::pick($cols, ["Direccion","dirección","Dir","direccion"]);
        $this->colActivo = SchemaHelper::pick($cols, ["Activo","activo","Estado","estado"]);
    }

    public function meta(): array {
        return ['table'=>$this->table,'pk'=>$this->pk,'nombre'=>$this->colNombre,'mail'=>$this->colMail,'rut'=>$this->colRUT,'direccion'=>$this->colDireccion,'activo'=>$this->colActivo];
    }

    public function list(string $q=""): array {
        $params = [];
        $sql = "SELECT * FROM {$this->table}";
        if ($q !== "" && $this->colNombre) {
            $sql .= " WHERE {$this->colNombre} LIKE :q";
            $params[':q'] = "%$q%";
        }
        $sql .= $this->pk ? " ORDER BY {$this->pk} DESC" : "";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get(int $id): ?array {
        if (!$this->pk) return null;
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->pk} = :id");
        $stmt->execute([':id'=>$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function create(array $data): int {
        $fields=[]; $params=[];
        foreach (['nombre'=>$this->colNombre,'mail'=>$this->colMail,'rut'=>$this->colRUT,'direccion'=>$this->colDireccion,'activo'=>$this->colActivo] as $k=>$col) {
            if ($col && isset($data[$k]) && $data[$k] !== "") {
                $fields[] = $col; $params[":$k"] = $data[$k];
            }
        }
        if (!$fields) throw new Exception("No hay campos para insertar en empresa.");
        $sql = "INSERT INTO {$this->table} (" . implode(",", $fields) . ") VALUES (" . implode(",", array_keys($params)) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void {
        if (!$this->pk) throw new Exception("No se detectó PK en empresa.");
        $sets=[]; $params=[':id'=>$id];
        foreach (['nombre'=>$this->colNombre,'mail'=>$this->colMail,'rut'=>$this->colRUT,'direccion'=>$this->colDireccion,'activo'=>$this->colActivo] as $k=>$col) {
            if ($col && array_key_exists($k,$data)) {
                $sets[] = "$col = :$k";
                $params[":$k"] = $data[$k];
            }
        }
        if (!$sets) return;
        $sql = "UPDATE {$this->table} SET " . implode(",", $sets) . " WHERE {$this->pk} = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function softDelete(int $id): void {
        if (!$this->pk) throw new Exception("No se detectó PK en empresa.");
        if ($this->colActivo) {
            $stmt = $this->pdo->prepare("UPDATE {$this->table} SET {$this->colActivo} = 0 WHERE {$this->pk} = :id");
            $stmt->execute([':id'=>$id]);
        } else {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$this->pk} = :id");
            $stmt->execute([':id'=>$id]);
        }
    }
}
?>