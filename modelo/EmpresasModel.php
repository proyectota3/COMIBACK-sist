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
    private ?string $colValidacion;

    public function __construct() {
        $this->pdo = DbComidApp::pdo();
        $this->pk = SchemaHelper::primaryKey($this->pdo, $this->table);
        $cols = SchemaHelper::columns($this->pdo, $this->table);
        $this->colNombre = SchemaHelper::pick($cols, ["Nombre","nombre","NombreEmp","Empresa"]);
        $this->colMail = SchemaHelper::pick($cols, ["Mail","mail","Email","email"]);
        $this->colRUT = SchemaHelper::pick($cols, ["RUT","rut"]);
        $this->colDireccion = SchemaHelper::pick($cols, ["Direccion","dirección","Dir","direccion"]);
        $this->colActivo = SchemaHelper::pick($cols, ["Activo","activo","Estado","estado"]);
        // En tu esquema real, el campo se llama Validacion
        $this->colValidacion = SchemaHelper::pick($cols, ["Validacion","validacion","Validado","validado"]);
    }

    public function meta(): array {
        return ['table'=>$this->table,'pk'=>$this->pk,'nombre'=>$this->colNombre,'mail'=>$this->colMail,'rut'=>$this->colRUT,'direccion'=>$this->colDireccion,'activo'=>$this->colActivo,'validacion'=>$this->colValidacion];
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
        // Si el PK no es auto-increment (como IDEmp en ComidAPP), lo soportamos como 'id'
        if ($this->pk && isset($data['id']) && $data['id'] !== "") {
            $fields[] = $this->pk;
            $params[':id'] = (int)$data['id'];
        }

        foreach (['nombre'=>$this->colNombre,'mail'=>$this->colMail,'rut'=>$this->colRUT,'direccion'=>$this->colDireccion,'activo'=>$this->colActivo,'validacion'=>$this->colValidacion] as $k=>$col) {
            if ($col && isset($data[$k]) && $data[$k] !== "") {
                $fields[] = $col; $params[":$k"] = $data[$k];
            }
        }
        if (!$fields) throw new Exception("No hay campos para insertar en empresa.");
        $sql = "INSERT INTO {$this->table} (" . implode(",", $fields) . ") VALUES (" . implode(",", array_keys($params)) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        // Si el PK fue provisto, devolvemos ese ID; si no, intentamos lastInsertId
        return isset($data['id']) ? (int)$data['id'] : (int)$this->pdo->lastInsertId();
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
        $this->setActivo($id, 0);
    }

    public function restore(int $id): void {
        $this->setActivo($id, 1);
    }

    /**
     * Desactivar/activar empresa de forma segura (sin borrar filas con FK).
     * - empresa.Activo
     * - usuariosweb.Activo
     * - local.Activo (todos los locales de esa empresa)
     */
    public function setActivo(int $id, int $activo): void {
        if (!$this->pk) throw new Exception("No se detectó PK en empresa.");

        // Si no existe el campo Activo en empresa, NO borramos (rompe FKs).
        if (!$this->colActivo) {
            throw new Exception(
                "La tabla 'empresa' no tiene el campo 'Activo'. Ejecutá la migración (ALTER TABLE empresa ADD Activo...)."
            );
        }

        $this->pdo->beginTransaction();
        try {
            // 1) empresa.Activo
            $stmt = $this->pdo->prepare("UPDATE {$this->table} SET {$this->colActivo} = :a WHERE {$this->pk} = :id");
            $stmt->execute([':a'=>$activo, ':id'=>$id]);

            // 2) usuariosweb.Activo (si existe tabla/columna)
            if (SchemaHelper::tableExists($this->pdo, 'usuariosweb')) {
                $colsU = SchemaHelper::columns($this->pdo, 'usuariosweb');
                $colUA = SchemaHelper::pick($colsU, ['Activo','activo']);
                $colUPK = SchemaHelper::primaryKey($this->pdo, 'usuariosweb');
                if ($colUA && $colUPK) {
                    $stU = $this->pdo->prepare("UPDATE usuariosweb SET {$colUA} = :a WHERE {$colUPK} = :id");
                    $stU->execute([':a'=>$activo, ':id'=>$id]);
                }
            }

            // 3) local.Activo (todos los locales de la empresa)
            if (SchemaHelper::tableExists($this->pdo, 'local')) {
                $colsL = SchemaHelper::columns($this->pdo, 'local');
                $colLE = SchemaHelper::pick($colsL, ['IDEmp','idemp','IDEmpresa','idempresa']);
                $colLA = SchemaHelper::pick($colsL, ['Activo','activo']);
                if ($colLE && $colLA) {
                    $stL = $this->pdo->prepare("UPDATE local SET {$colLA} = :a WHERE {$colLE} = :id");
                    $stL->execute([':a'=>$activo, ':id'=>$id]);
                }
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
?>