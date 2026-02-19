<?php
require_once __DIR__ . "/DbComidApp.php";
require_once __DIR__ . "/SchemaHelper.php";

class SolicitudesEmpresaModel {
    private PDO $pdo;
    private string $table = "solicitud";
    private ?string $pk;

    public function __construct() {
        $this->pdo = DbComidApp::pdo();
        $this->pk = SchemaHelper::primaryKey($this->pdo, $this->table);
    }

    public function exists(): bool { return SchemaHelper::tableExists($this->pdo, $this->table); }

    /**
     * En ComidAPP (base real) los estados suelen ser: Pendiente | Validada | Rechazada
     */
    public function list(string $estado="Pendiente"): array {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        if ($estado !== "") { $sql .= " WHERE Estado = :e"; $params[':e']=$estado; }
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

    public function markApproved(int $id): void {
        if (!$this->pk) throw new Exception("No se detectó PK en solicitud.");
        // En tu esquema de ComidAPP: solicitud(Estado)
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET Estado='Validada' WHERE {$this->pk}=:id");
        $stmt->execute([':id'=>$id]);
    }

    public function markRejected(int $id): void {
        if (!$this->pk) throw new Exception("No se detectó PK en solicitud.");
        // La tabla no tiene columna para motivo en tu dump actual
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET Estado='Rechazada' WHERE {$this->pk}=:id");
        $stmt->execute([':id'=>$id]);
    }
}
?>