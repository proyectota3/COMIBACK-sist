<?php
require_once __DIR__ . "/DbComidApp.php";
require_once __DIR__ . "/SchemaHelper.php";

class DashboardModel {
    private PDO $pdo;
    public function __construct() { $this->pdo = DbComidApp::pdo(); }

    private function countTable(string $table): ?int {
        try {
            if (!SchemaHelper::tableExists($this->pdo, $table)) return null;
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM `$table`");
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) { return null; }
    }

    public function counts(): array {
        return [
            'usuariosweb' => $this->countTable('usuariosweb'),
            'empresa' => $this->countTable('empresa'),
            'local' => $this->countTable('local'),
            'solicitud_empresa_pend' => $this->countSolicitudes('solicitud_empresa'),
            'solicitud_local_pend' => $this->countSolicitudes('solicitud_local'),
        ];
    }

    private function countSolicitudes(string $table): ?int {
        try {
            if (!SchemaHelper::tableExists($this->pdo, $table)) return null;
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE Estado = 'PENDIENTE'");
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) { return null; }
    }
}
?>