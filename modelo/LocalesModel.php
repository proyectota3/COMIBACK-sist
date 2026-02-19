<?php
require_once __DIR__ . "/DbComidApp.php";
require_once __DIR__ . "/SchemaHelper.php";

class LocalesModel {
    private PDO $pdo;
    private string $table = "local";
    private ?string $pk;

    private ?string $colEmpresa;
    private ?string $colNombre;
    private ?string $colDireccion;
    private ?string $colActivo;

    private ?string $colFoto;
    private ?string $colDelivery;

    public function __construct() {
        $this->pdo = DbComidApp::pdo();
        $this->pk = SchemaHelper::primaryKey($this->pdo, $this->table);
        $cols = SchemaHelper::columns($this->pdo, $this->table);

        $this->colEmpresa = SchemaHelper::pick($cols, ["IDEmpresa","IDEmp","Empresa","idempresa","idemp"]);
        $this->colNombre = SchemaHelper::pick($cols, ["Nombre","NombreLocal","nom","local"]);
        $this->colDireccion = SchemaHelper::pick($cols, ["Direccion","dirección","Dir","direccion"]);
        $this->colActivo = SchemaHelper::pick($cols, ["Activo","activo","Estado","estado"]);

        $this->colFoto = SchemaHelper::pick($cols, ["Foto","foto","Imagen","img","url"]);
        $this->colDelivery = SchemaHelper::pick($cols, ["Delivery","delivery","Envia","envia"]);
    }

    public function meta(): array {
        return ['table'=>$this->table,'pk'=>$this->pk,'empresa'=>$this->colEmpresa,'nombre'=>$this->colNombre,'direccion'=>$this->colDireccion,'activo'=>$this->colActivo,'foto'=>$this->colFoto,'delivery'=>$this->colDelivery];
    }

    public function listByEmpresa(int $empresaId): array {
        if (!$this->colEmpresa) return [];
        $sql = "SELECT * FROM {$this->table} WHERE {$this->colEmpresa} = :e" . ($this->pk ? " ORDER BY {$this->pk} DESC" : "");
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':e'=>$empresaId]);
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
        foreach (['empresa'=>$this->colEmpresa,'nombre'=>$this->colNombre,'direccion'=>$this->colDireccion,'activo'=>$this->colActivo,'foto'=>$this->colFoto,'delivery'=>$this->colDelivery] as $k=>$col) {
            if ($col && isset($data[$k]) && $data[$k] !== "") {
                $fields[] = $col; $params[":$k"] = $data[$k];
            }
        }
        if (!$fields) throw new Exception("No hay campos para insertar en local.");
        $sql = "INSERT INTO {$this->table} (" . implode(",", $fields) . ") VALUES (" . implode(",", array_keys($params)) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void {
        if (!$this->pk) throw new Exception("No se detectó PK en local.");
        $sets=[]; $params=[':id'=>$id];
        foreach (['empresa'=>$this->colEmpresa,'nombre'=>$this->colNombre,'direccion'=>$this->colDireccion,'activo'=>$this->colActivo] as $k=>$col) {
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
        if (!$this->pk) throw new Exception("No se detectó PK en local.");
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