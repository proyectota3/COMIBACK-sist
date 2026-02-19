<?php
require_once __DIR__ . "/DbComidApp.php";
require_once __DIR__ . "/SchemaHelper.php";

class SolicitudesLocalModel {
    private PDO $pdo;
    // En tu dump real la tabla se llama `solicitudlocal` (todo en minúscula)
    private string $table = "solicitudlocal";
    private ?string $pk;

    public function __construct() {
        $this->pdo = DbComidApp::pdo();
        $this->pk = SchemaHelper::primaryKey($this->pdo, $this->table);
    }

    public function exists(): bool { return SchemaHelper::tableExists($this->pdo, $this->table); }

    /**
     * Estados usados en tu esquema: Pendiente | Validada | Rechazada
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

    public function markApproved(int $id, int $idLocCreado): void {
        if (!$this->pk) throw new Exception("No se detectó PK en solicitudlocal.");
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET Estado='Validada', ComentarioAdmin=NULL, IDLocCreado=:loc WHERE {$this->pk}=:id");
        $stmt->execute([':loc'=>$idLocCreado, ':id'=>$id]);
    }

    public function markRejected(int $id, string $motivo): void {
        if (!$this->pk) throw new Exception("No se detectó PK en solicitudlocal.");
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET Estado='Rechazada', ComentarioAdmin=:m WHERE {$this->pk}=:id");
        $stmt->execute([':m'=>$motivo, ':id'=>$id]);
    }
}
?>