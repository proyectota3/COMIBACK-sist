<?php
require_once __DIR__ . "/DbComidApp.php";
require_once __DIR__ . "/SchemaHelper.php";

class SolicitudesLocalModel {
    private PDO $pdo;
    private string $table = "solicitudLocal";
    private ?string $pk;

    public function __construct() {
        $this->pdo = DbComidApp::pdo();
        $this->pk = SchemaHelper::primaryKey($this->pdo, $this->table);
    }

    public function exists(): bool { return SchemaHelper::tableExists($this->pdo, $this->table); }

    public function list(string $estado="PENDIENTE"): array {
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

    public function markApproved(int $id, string $resueltoPor): void {
        if (!$this->pk) throw new Exception("No se detectó PK en solicitudLocal.");
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET Estado='APROBADA', MotivoRechazo=NULL, FechaResolucion=NOW(), ResueltoPor=:rp WHERE {$this->pk}=:id");
        $stmt->execute([':rp'=>$resueltoPor, ':id'=>$id]);
    }

    public function markRejected(int $id, string $motivo, string $resueltoPor): void {
        if (!$this->pk) throw new Exception("No se detectó PK en solicitudLocal.");
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET Estado='RECHAZADA', MotivoRechazo=:m, FechaResolucion=NOW(), ResueltoPor=:rp WHERE {$this->pk}=:id");
        $stmt->execute([':m'=>$motivo, ':rp'=>$resueltoPor, ':id'=>$id]);
    }
}
?>