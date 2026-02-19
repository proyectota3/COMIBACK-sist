<?php
require_once __DIR__ . "/DbComidApp.php";
require_once __DIR__ . "/SchemaHelper.php";

class UsuariosWebModel {
    private PDO $pdo;
    private string $table = "usuariosweb";
    private ?string $pk;

    private ?string $colNombre;
    private ?string $colMail;
    private ?string $colRol;
    private ?string $colDireccion;
    private ?string $colActivo;

    private ?string $colPass;
    private ?string $colDebeCambiarPass;

    public function __construct() {
        $this->pdo = DbComidApp::pdo();
        $this->pk = SchemaHelper::primaryKey($this->pdo, $this->table);
        $cols = SchemaHelper::columns($this->pdo, $this->table);

        $this->colNombre    = SchemaHelper::pick($cols, ["Nombre","nombre","NomUsu","usuario","User"]);
        $this->colMail      = SchemaHelper::pick($cols, ["Mail","mail","Email","email","Correo"]);
        $this->colRol       = SchemaHelper::pick($cols, ["idRol","IDRol","rol","Rol","RolID"]);
        $this->colDireccion = SchemaHelper::pick($cols, ["Direccion","dirección","DireccionUsu","Domicilio","Address"]);
        $this->colActivo    = SchemaHelper::pick($cols, ["Activo","activo","Estado","estado","Habilitado"]);

        $this->colPass            = SchemaHelper::pick($cols, ["pass","Pass","password","Password","Clave","Contrasena","Contraseña","pwd"]);
        $this->colDebeCambiarPass = SchemaHelper::pick($cols, ["DebeCambiarPass","debecambiarpass","DebeCambiar","CambiarPass","cambiarpass","CambioPass","DebeCambiarClave","DebeCambiarContrasena"]);
    }

    public function meta(): array {
        return [
            'table' => $this->table,
            'pk' => $this->pk,
            'nombre' => $this->colNombre,
            'mail' => $this->colMail,
            'rol' => $this->colRol,
            'direccion' => $this->colDireccion,
            'activo' => $this->colActivo,
            'pass' => $this->colPass,
            'debeCambiarPass' => $this->colDebeCambiarPass
        ];
    }

    private function hashDefault1234(): string {
        return password_hash("1234", PASSWORD_DEFAULT);
    }

    private function hashRandom(): string {
        return password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    }

    public function existsByMail(string $mail): ?int {
        if (!$this->colMail) return null;
        $stmt = $this->pdo->prepare("SELECT {$this->pk} FROM {$this->table} WHERE {$this->colMail} = :m LIMIT 1");
        $stmt->execute([':m'=>$mail]);
        $id = $stmt->fetchColumn();
        return $id !== false ? (int)$id : null;
    }

    public function list(string $q = "", string $rol = "", string $activo = ""): array {
        $where = [];
        $params = [];
        if ($q !== "" && $this->colNombre && $this->colMail) {
            $where[] = "({$this->colNombre} LIKE :q OR {$this->colMail} LIKE :q)";
            $params[':q'] = "%$q%";
        } elseif ($q !== "" && $this->colMail) {
            $where[] = "{$this->colMail} LIKE :q";
            $params[':q'] = "%$q%";
        }
        if ($rol !== "" && $this->colRol) {
            $where[] = "{$this->colRol} = :rol";
            $params[':rol'] = $rol;
        }
        if ($activo !== "" && $this->colActivo) {
            $where[] = "{$this->colActivo} = :act";
            $params[':act'] = $activo;
        }
        $sql = "SELECT * FROM {$this->table}";
        if ($where) $sql .= " WHERE " . implode(" AND ", $where);
        $sql .= $this->pk ? " ORDER BY {$this->pk} DESC" : "";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get(int $id): ?array {
        if (!$this->pk) return null;
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->pk} = :id");
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function create(array $data): int {
        $fields = [];
        $params = [];

        // Campos básicos
        if ($this->colNombre) {
            $fields[] = $this->colNombre;
            $params[':nombre'] = (string)($data['nombre'] ?? '');
        }
        if ($this->colMail) {
            $fields[] = $this->colMail;
            $params[':mail'] = (string)($data['mail'] ?? '');
        }
        if ($this->colRol) {
            $fields[] = $this->colRol;
            $params[':rol'] = (string)($data['rol'] ?? '');
        }
        if ($this->colDireccion) {
            $fields[] = $this->colDireccion;
            $params[':direccion'] = (string)($data['direccion'] ?? '');
        }
        if ($this->colActivo) {
            $fields[] = $this->colActivo;
            $params[':activo'] = (string)($data['activo'] ?? '1');
        }

        // Password por defecto (usuariosweb.pass suele ser NOT NULL)
        if ($this->colPass) {
            $fields[] = $this->colPass;
            $params[':pass'] = $this->hashDefault1234();
        }
        if ($this->colDebeCambiarPass) {
            $fields[] = $this->colDebeCambiarPass;
            $params[':cambio'] = 1;
        }

        if (!$fields) throw new Exception("No hay campos para insertar en usuariosweb.");

        $sql = "INSERT INTO {$this->table} (" . implode(",", $fields) . ")
                VALUES (" . implode(",", array_keys($params)) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Crea un usuario con una contraseña específica (para enviar por mail) y fuerza el cambio al primer login.
     */
    public function createWithPassword(array $data, string $passPlano, bool $forzarCambio = true): int {
        $fields = [];
        $params = [];

        if ($this->colNombre) {
            $fields[] = $this->colNombre;
            $params[':nombre'] = (string)($data['nombre'] ?? '');
        }
        if ($this->colMail) {
            $fields[] = $this->colMail;
            $params[':mail'] = (string)($data['mail'] ?? '');
        }
        if ($this->colRol) {
            $fields[] = $this->colRol;
            $params[':rol'] = (string)($data['rol'] ?? '');
        }
        if ($this->colDireccion) {
            $fields[] = $this->colDireccion;
            $params[':direccion'] = (string)($data['direccion'] ?? '');
        }
        if ($this->colActivo) {
            $fields[] = $this->colActivo;
            $params[':activo'] = (string)($data['activo'] ?? '1');
        }

        if (!$this->colPass) throw new Exception("No se detectó la columna pass/password en usuariosweb.");
        $fields[] = $this->colPass;
        $params[':pass'] = password_hash($passPlano, PASSWORD_BCRYPT);

        if ($this->colDebeCambiarPass && $forzarCambio) {
            $fields[] = $this->colDebeCambiarPass;
            $params[':cambio'] = 1;
        } elseif ($this->colDebeCambiarPass) {
            $fields[] = $this->colDebeCambiarPass;
            $params[':cambio'] = 0;
        }

        $sql = "INSERT INTO {$this->table} (" . implode(",", $fields) . ") VALUES (" . implode(",", array_keys($params)) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void {
        if (!$this->pk) throw new Exception("No se detectó PK en usuariosweb.");
        $sets = [];
        $params = [':id'=>$id];

        foreach ([
            'nombre' => $this->colNombre,
            'mail' => $this->colMail,
            'rol' => $this->colRol,
            'direccion' => $this->colDireccion,
            'activo' => $this->colActivo
        ] as $k=>$col) {
            if ($col && array_key_exists($k, $data)) {
                $sets[] = "$col = :$k";
                $params[":$k"] = (string)$data[$k];
            }
        }

        if (!$sets) return;
        $sql = "UPDATE {$this->table} SET " . implode(",", $sets) . " WHERE {$this->pk} = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function resetPassword1234(int $id): void {
        if (!$this->pk) throw new Exception("No se detectó PK en usuariosweb.");
        if (!$this->colPass) throw new Exception("No se detectó la columna pass/password en usuariosweb.");

        $sets = ["{$this->colPass} = :pass"];
        $params = [
            ':pass' => $this->hashDefault1234(),
            ':id' => $id
        ];

        if ($this->colDebeCambiarPass) {
            $sets[] = "{$this->colDebeCambiarPass} = 1";
        }

        $sql = "UPDATE {$this->table} SET " . implode(",", $sets) . " WHERE {$this->pk} = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    public function disableLogin(int $id): void {
        if (!$this->pk) throw new Exception("No se detectó PK en usuariosweb.");
        if (!$this->colPass) throw new Exception("No se detectó la columna pass/password en usuariosweb.");

        $sets = ["{$this->colPass} = :pass"];
        $params = [
            ':pass' => $this->hashRandom(),
            ':id' => $id
        ];

        if ($this->colDebeCambiarPass) {
            $sets[] = "{$this->colDebeCambiarPass} = 1";
        }

        $sql = "UPDATE {$this->table} SET " . implode(",", $sets) . " WHERE {$this->pk} = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * "Baja" de usuario.
     * - Si existe columna Activo/Estado: marca inactivo.
     * - Si NO existe: intenta borrar. Si hay FK (cliente/empresa), no se puede borrar -> bloquea login cambiando la contraseña.
     *
     * @return string  'inactivated' | 'deleted' | 'blocked'
     */
    public function softDelete(int $id): string {
        if (!$this->pk) throw new Exception("No se detectó PK en usuariosweb.");

        // 1) Soft-delete real si existe Activo/Estado
        if ($this->colActivo) {
            $stmt = $this->pdo->prepare("UPDATE {$this->table} SET {$this->colActivo} = 0 WHERE {$this->pk} = :id");
            $stmt->execute([':id'=>$id]);
            return 'inactivated';
        }

        // 2) Si no existe Activo, intentamos borrar (solo funciona si no hay referencias por FK)
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$this->pk} = :id");
            $stmt->execute([':id'=>$id]);
            if ($stmt->rowCount() > 0) {
                return 'deleted';
            }
        } catch (PDOException $e) {
            // Integrity constraint (FK) -> no se puede borrar, seguimos al bloqueo
        }

        // 3) Si NO se pudo borrar, bloqueamos el login cambiando la contraseña (sin tocar Mail/Nombre)
        if ($this->colPass) {
            $this->disableLogin($id);
            return 'blocked';
        }

        // Último recurso: si no hay pass ni Activo y no se pudo borrar
        throw new Exception("No se pudo dar de baja el usuario (sin columna Activo y sin columna pass).\n");
    }
}
?>