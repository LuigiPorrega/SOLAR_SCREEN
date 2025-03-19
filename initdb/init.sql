-- ======================================
-- 1. Creación de la Base de Datos
-- ======================================
CREATE DATABASE IF NOT EXISTS solar_screen;
USE solar_screen;

-- ======================================
-- 2. Funciones
-- ======================================
DELIMITER $$

CREATE FUNCTION IF NOT EXISTS calcular_edad(p_fecha_nacimiento DATE)
RETURNS INT DETERMINISTIC
BEGIN
    RETURN TIMESTAMPDIFF(YEAR, p_fecha_nacimiento, CURDATE());
END$$

CREATE FUNCTION IF NOT EXISTS calcular_energia(
    p_condicion_luz VARCHAR(50), 
    p_tiempo_minutos INT
)
RETURNS DECIMAL(10, 2) DETERMINISTIC
BEGIN
    RETURN CASE 
        WHEN p_condicion_luz = 'Luz solar directa' THEN p_tiempo_minutos * 5
        WHEN p_condicion_luz = 'Luz artificial' THEN p_tiempo_minutos * 2
        ELSE 0
    END;
END$$

DELIMITER ;

-- ======================================
-- 3. Tablas e Índices Optimizados
-- ======================================
CREATE TABLE IF NOT EXISTS Usuarios (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Correo VARCHAR(150) NOT NULL UNIQUE,
    FechaNacimiento DATE,
    GoogleID VARCHAR(255) UNIQUE,
    Username VARCHAR(50) UNIQUE NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    Fecha_Registro DATE DEFAULT (CURRENT_DATE),
    Rol VARCHAR(20) DEFAULT 'usuario' NOT NULL,
    CHECK (Rol IN ('usuario', 'admin'))
);

CREATE TABLE IF NOT EXISTS Simulaciones (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    UsuarioID INT NOT NULL,
    CondicionLuz VARCHAR(50) NOT NULL,
    EnergiaGenerada DECIMAL(10, 2) NOT NULL,
    Fecha DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (UsuarioID) REFERENCES Usuarios(ID) ON DELETE CASCADE,
    INDEX idx_simulaciones_combo (UsuarioID, CondicionLuz)
);

CREATE TABLE IF NOT EXISTS LoginLog (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    UsuarioID INT NULL,
    FechaHora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success TINYINT(1) NOT NULL DEFAULT 0,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    attempts INT NOT NULL DEFAULT 0,
    reason VARCHAR(255),
    FOREIGN KEY (UsuarioID) REFERENCES Usuarios(ID) ON DELETE SET NULL,
    INDEX idx_log_accesos (UsuarioID, FechaHora)
);

CREATE TABLE IF NOT EXISTS Ideas (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    UsuarioID INT NOT NULL,
    Titulo VARCHAR(200) NOT NULL,
    Descripcion TEXT NOT NULL,
    FechaCreacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UsuarioID) REFERENCES Usuarios(ID),
    INDEX idx_ideas_recientes (FechaCreacion DESC)
);

-- ======================================
-- 4. Procedimientos Almacenados Completos
-- ======================================
DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS registrar_usuario(
    IN p_nombre VARCHAR(100),
    IN p_correo VARCHAR(150),
    IN p_fecha_nacimiento DATE,
    IN p_username VARCHAR(50),
    IN p_password VARCHAR(255),
    IN p_rol VARCHAR(20)
)
BEGIN
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        ROLLBACK;
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error: El correo o nombre de usuario ya existe';
    END;

    START TRANSACTION;
    
    INSERT INTO Usuarios (
        Nombre, 
        Correo, 
        FechaNacimiento, 
        Username, 
        PasswordHash, 
        Rol
    ) VALUES (
        p_nombre,
        p_correo,
        p_fecha_nacimiento,
        p_username,
        SHA2(p_password, 256),
        COALESCE(p_rol, 'usuario')
    );
    
    COMMIT;
END$$

DELIMITER ;

-- ======================================
-- 5. Datos de Prueba con Hashing Real
-- ======================================
INSERT IGNORE INTO Usuarios (
    Nombre, 
    Correo, 
    FechaNacimiento, 
    GoogleID, 
    Username, 
    PasswordHash, 
    Rol
) VALUES
  ('Juan Pérez', 'juan.perez@example.com', '1985-05-12', 'GID123', 'juanp', SHA2('password123', 256), 'usuario'),
  ('Ana López', 'angela.lopez@example.com', '1990-03-22', 'GID456', 'angelal', SHA2('securePass!', 256), 'admin'),
  ('Carlos Ruiz', 'carlos.ruiz@example.com', '1978-11-30', NULL, 'carlosr', SHA2('MySecret123', 256), 'usuario'),
  ('Luigi Porrega', 'luis.porrega@solvam.es', '1984-06-24', 'GID467', 'luigip', SHA2('Solvam1234', 256), 'admin');

INSERT IGNORE INTO Simulaciones (UsuarioID, CondicionLuz, EnergiaGenerada)
VALUES
  (1, 'Luz solar directa', calcular_energia('Luz solar directa', 30)),
  (2, 'Luz artificial', calcular_energia('Luz artificial', 45)),
  (3, 'Luz solar directa', calcular_energia('Luz solar directa', 60));

INSERT IGNORE INTO Ideas (UsuarioID, Titulo, Descripcion)
VALUES
  (1, 'Mejora en paneles', 'Optimización del diseño de paneles solares'),
  (2, 'App móvil', 'Desarrollo de aplicación móvil para monitoreo');

-- ======================================
-- 6. Configuración de Seguridad
-- ======================================
CREATE USER IF NOT EXISTS 'solar_screen'@'%' IDENTIFIED BY 'Solvam1234';
GRANT ALL PRIVILEGES ON solar_screen.* TO 'solar_screen'@'%';
FLUSH PRIVILEGES;
