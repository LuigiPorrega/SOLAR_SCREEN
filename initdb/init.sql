-- ======================================
-- 1. Creación de la Base de Datos
-- ======================================
CREATE DATABASE IF NOT EXISTS solar_screen;
USE solar_screen;

-- ======================================
-- 2. Funciones
-- ======================================
DELIMITER $$

-- Función para calcular la edad de un usuario
CREATE FUNCTION IF NOT EXISTS calcular_edad(p_fecha_nacimiento DATE)
RETURNS INT DETERMINISTIC
BEGIN
    RETURN TIMESTAMPDIFF(YEAR, p_fecha_nacimiento, CURDATE());
END$$

-- Función para calcular la energía generada en función de la luz, tiempo y condiciones meteorológicas
CREATE FUNCTION IF NOT EXISTS calcular_energia(
    p_condicion_luz VARCHAR(50), 
    p_tiempo_minutos INT,
    p_luz_solar DECIMAL(10, 2),
    p_temperatura DECIMAL(5, 2),
    p_humedad DECIMAL(5, 2),
    p_viento DECIMAL(5, 2)
)
RETURNS DECIMAL(10, 2) DETERMINISTIC
BEGIN
    DECLARE energia DECIMAL(10, 2);
    
    -- Calcular la energía básica en función de la luz solar
    SET energia = CASE 
        WHEN p_condicion_luz = 'Luz solar directa' THEN p_tiempo_minutos * p_luz_solar
        WHEN p_condicion_luz = 'Luz artificial' THEN p_tiempo_minutos * 2
        ELSE 0
    END;

    -- Ajustar la energía en función de la temperatura, humedad y viento
    -- Ejemplo de ajustes basados en condiciones meteorológicas
    SET energia = energia * (1 - (p_temperatura - 25) / 100);  -- La temperatura puede reducir la eficiencia
    SET energia = energia * (1 - p_humedad / 100);  -- La humedad también reduce la eficiencia
    SET energia = energia * (1 + p_viento / 100);  -- El viento podría mejorar la eficiencia

    RETURN energia;
END$$

DELIMITER ;

-- ======================================
-- 3. Tablas e Índices Optimizados
-- ======================================

-- Tabla Usuarios 
CREATE TABLE IF NOT EXISTS Usuarios (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Correo VARCHAR(150) NOT NULL,
    FechaNacimiento DATE,
    GoogleID VARCHAR(255),
    Username VARCHAR(50) UNIQUE NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    Fecha_Registro DATE DEFAULT (CURRENT_DATE),
    Rol VARCHAR(20) DEFAULT 'usuario' NOT NULL,
    CHECK (Rol IN ('usuario', 'admin'))
);

-- Tabla Condiciones Meteorológicas
CREATE TABLE IF NOT EXISTS CondicionesMeteorologicas (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Fecha DATE NOT NULL,
    LuzSolar DECIMAL(10, 2) NOT NULL,  -- Intensidad de luz solar en un día determinado
    Temperatura DECIMAL(5, 2),  -- Temperatura ambiente
    Humedad DECIMAL(5, 2),  -- Humedad relativa
    Viento DECIMAL(5, 2)  -- Velocidad del viento
);

-- Tabla Simulaciones 
CREATE TABLE IF NOT EXISTS Simulaciones (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    UsuarioID INT NOT NULL,
    CondicionLuz VARCHAR(50) NOT NULL,
    EnergiaGenerada DECIMAL(10, 2) NOT NULL,
    Fecha DATE DEFAULT (CURRENT_DATE),
    CondicionesMeteorologicasID INT NOT NULL,  -- Relacionar con la tabla CondicionesMeteorologicas
    FOREIGN KEY (UsuarioID) REFERENCES Usuarios(ID) ON DELETE CASCADE,
    FOREIGN KEY (CondicionesMeteorologicasID) REFERENCES CondicionesMeteorologicas(ID) ON DELETE CASCADE,
    INDEX idx_simulaciones_combo (UsuarioID, CondicionLuz)
);

-- Tabla LoginLog 
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

-- Tabla Ideas
CREATE TABLE IF NOT EXISTS Ideas (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    UsuarioID INT NOT NULL,
    Titulo VARCHAR(200) NOT NULL,
    Descripcion TEXT NOT NULL,
    FechaCreacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UsuarioID) REFERENCES Usuarios(ID),
    INDEX idx_ideas_recientes (FechaCreacion DESC)
);

-- Nueva tabla ModelosFundas 
CREATE TABLE IF NOT EXISTS ModelosFundas (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Tamaño VARCHAR(50) NOT NULL,  -- Dimensiones de la funda (ej. "10x5 cm")
    CapacidadCarga DECIMAL(10, 2) NOT NULL,  -- Capacidad de carga en mAh o similar
    Expansible BOOLEAN DEFAULT FALSE,  -- Si la funda puede expandirse
    FechaCreacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Nueva tabla Proveedores 
CREATE TABLE IF NOT EXISTS Proveedores (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,
    Pais VARCHAR(100),  -- País del proveedor
    Contacto VARCHAR(150),  -- Información de contacto
    FechaCreacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Relación muchos a muchos entre Fundas y Proveedores
CREATE TABLE IF NOT EXISTS Fundas_Proveedores (
    FundaID INT NOT NULL,
    ProveedorID INT NOT NULL,
    FOREIGN KEY (FundaID) REFERENCES ModelosFundas(ID) ON DELETE CASCADE,
    FOREIGN KEY (ProveedorID) REFERENCES Proveedores(ID) ON DELETE CASCADE,
    PRIMARY KEY (FundaID, ProveedorID)
);

-- ======================================
-- 4. Procedimientos Almacenados
-- ======================================

DELIMITER $$

-- Procedimiento para registrar un usuario
CREATE PROCEDURE IF NOT EXISTS registrar_usuario(
    IN p_nombre VARCHAR(100),
    IN p_correo VARCHAR(150),
    IN p_fecha_nacimiento DATE,
    IN p_googleid VARCHAR(255),
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
        GoogleID,
        Username, 
        PasswordHash, 
        Rol
    ) VALUES (
        p_nombre,
        p_correo,
        p_fecha_nacimiento,
        p_googleid,
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

-- Inserción de usuarios
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

-- Inserción de condiciones meteorológicas (para relacionar en Simulaciones)
INSERT IGNORE INTO CondicionesMeteorologicas (Fecha, LuzSolar, Temperatura, Humedad, Viento)
VALUES
  ('2025-03-01', 300, 25, 60, 15), -- Para el primer usuario
  ('2025-03-02', 200, 23, 80, 10), -- Para el segundo usuario
  ('2025-03-03', 400, 30, 50, 20); -- Para el tercer usuario

-- Inserción en Simulaciones con la relación de las condiciones meteorológicas
INSERT IGNORE INTO Simulaciones (UsuarioID, CondicionLuz, EnergiaGenerada, CondicionesMeteorologicasID)
VALUES
  (1, 'Luz solar directa', calcular_energia('Luz solar directa', 30, 300, 25, 60, 15), 1), 
  (2, 'Luz artificial', calcular_energia('Luz artificial', 45, 200, 23, 80, 10), 2),
  (3, 'Luz solar directa', calcular_energia('Luz solar directa', 60, 400, 30, 50, 20), 3);

-- Inserción en Ideas
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
