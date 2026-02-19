<?php
class SchemaHelper {

    public static function tableExists(PDO $pdo, string $table): bool {
        $stmt = $pdo->prepare("SHOW TABLES LIKE :t");
        $stmt->execute([':t' => $table]);
        return (bool)$stmt->fetchColumn();
    }

    public static function columns(PDO $pdo, string $table): array {
        $stmt = $pdo->query("DESCRIBE `$table`");
        $cols = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $cols[] = $r['Field'];
        }
        return $cols;
    }

    public static function primaryKey(PDO $pdo, string $table): ?string {
        $stmt = $pdo->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ? $r['Column_name'] : null;
    }

    public static function pick(array $cols, array $candidates): ?string {
        $map = array_flip(array_map('strtolower', $cols));
        foreach ($candidates as $cand) {
            $lc = strtolower($cand);
            if (isset($map[$lc])) return $cols[$map[$lc]];
        }
        // partial match
        foreach ($cols as $c) {
            $lc = strtolower($c);
            foreach ($candidates as $cand) {
                $needle = strtolower($cand);
                if ($needle !== "" && str_contains($lc, $needle)) return $c;
            }
        }
        return null;
    }
}
?>