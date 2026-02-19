-- Ejecutar en la BD comidapp
-- Crea tablas de solicitudes para ComiBack (admin aprueba/rechaza)

CREATE TABLE IF NOT EXISTS solicitud_empresa (
  IDSolicitudEmp INT AUTO_INCREMENT PRIMARY KEY,
  RUT VARCHAR(32) NULL,
  Direccion VARCHAR(255) NULL,
  Mail VARCHAR(255) NULL,
  Nombre VARCHAR(255) NULL,
  Estado ENUM('PENDIENTE','APROBADA','RECHAZADA') NOT NULL DEFAULT 'PENDIENTE',
  MotivoRechazo VARCHAR(255) NULL,
  FechaCreacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FechaResolucion DATETIME NULL,
  ResueltoPor VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS solicitud_local (
  IDSolicitudLoc INT AUTO_INCREMENT PRIMARY KEY,
  IDEmpresa INT NULL,
  Nombre VARCHAR(255) NULL,
  Direccion VARCHAR(255) NULL,
  Estado ENUM('PENDIENTE','APROBADA','RECHAZADA') NOT NULL DEFAULT 'PENDIENTE',
  MotivoRechazo VARCHAR(255) NULL,
  FechaCreacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FechaResolucion DATETIME NULL,
  ResueltoPor VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Si querés, agregá FK a empresa cuando confirmes que el PK de empresa es IDEmpresa/IDEmp
-- ALTER TABLE solicitud_local ADD CONSTRAINT fk_solloc_empresa FOREIGN KEY (IDEmpresa) REFERENCES empresa(IDEmpresa);
