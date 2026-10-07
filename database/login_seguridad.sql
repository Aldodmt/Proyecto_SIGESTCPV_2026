-- Migración de seguridad del login (idempotente: se puede ejecutar varias veces)
USE sysweb;

-- Columnas de control de bloqueo en usuarios
ALTER TABLE usuarios
  ADD COLUMN IF NOT EXISTS intentos_fallidos INT NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS bloqueado_fecha DATETIME NULL;
UPDATE usuarios SET intentos_fallidos = 0 WHERE intentos_fallidos IS NULL;
UPDATE usuarios SET status = 'activo' WHERE status IS NULL OR status = '';

-- Registro de todos los intentos de acceso (la contraseña se guarda enmascarada, nunca en claro)
CREATE TABLE IF NOT EXISTS log_accesos (
  id_log BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user INT NULL,
  username VARCHAR(150) NOT NULL,
  password_enmascarada VARCHAR(40) NOT NULL DEFAULT '',
  fecha_hora DATETIME NOT NULL,
  ip VARCHAR(45) NOT NULL,
  user_agent VARCHAR(255) NOT NULL DEFAULT '',
  resultado VARCHAR(20) NOT NULL,      -- EXITOSO | FALLIDO | BLOQUEADO | RECUPERACION
  motivo VARCHAR(120) NOT NULL DEFAULT '',
  tiempo_ms INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id_log),
  KEY idx_fecha (fecha_hora),
  KEY idx_username (username),
  KEY idx_resultado (resultado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tokens de recuperación de contraseña (se guarda solo el hash SHA-256 del token)
CREATE TABLE IF NOT EXISTS password_resets (
  id_reset INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user INT NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expira DATETIME NOT NULL,
  usado TINYINT(1) NOT NULL DEFAULT 0,
  creado DATETIME NOT NULL,
  ip VARCHAR(45) NOT NULL DEFAULT '',
  PRIMARY KEY (id_reset),
  UNIQUE KEY uq_token (token_hash),
  KEY idx_user (id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
