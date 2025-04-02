
-- Habilitar el Event Scheduler
SET GLOBAL event_scheduler = ON;

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

-- Tabla LoginLog 
CREATE TABLE IF NOT EXISTS LoginLog (
    ID INT AUTO_INCREMENT PRIMARY KEY,                          -- ID del registro
    UsuarioID INT NULL,                                          -- ID del usuario (referencia a la tabla Usuarios)
    FechaHora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,               -- Fecha y hora del intento
    Success TINYINT(1) NOT NULL DEFAULT 0,                       -- 1 para éxito, 0 para fallo
    IpAddress VARCHAR(45) NOT NULL,                              -- IP desde la que se realiza el intento
    UserAgent TEXT NOT NULL,                                     -- Información sobre el navegador y sistema operativo
    AttemptDate DATE NOT NULL DEFAULT CURRENT_DATE,              -- Fecha en la que se realizó el intento
    Attempts INT NOT NULL DEFAULT 1,                              -- Intentos fallidos (solo usado si Success = 0)
    Reason VARCHAR(255),                                         -- Razón del fallo (por ejemplo, "Contraseña incorrecta")
    FOREIGN KEY (UsuarioID) REFERENCES Usuarios(ID) ON DELETE SET NULL,  -- Relación con la tabla Usuarios
    INDEX idx_log_accesos (UsuarioID, FechaHora)                 -- Índice para facilitar las consultas por Usuario y Fecha
);

-- Crear un evento programado para eliminar registros de login de más de 3 años
CREATE EVENT IF NOT EXISTS eliminar_registros_antiguos
ON SCHEDULE EVERY 1 DAY  -- Ejecutará esta operación cada día
DO
    DELETE FROM LoginLog
    WHERE AttemptDate < CURDATE() - INTERVAL 3 YEAR;


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

-- Tabla ModelosFundas 
CREATE TABLE IF NOT EXISTS ModelosFundas (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,                 -- Nombre del modelo
    Tamaño VARCHAR(50) NOT NULL,                  -- Dimensiones de la funda (ej. "10x5 cm")
    CapacidadCarga DECIMAL(10, 2) NOT NULL,       -- Capacidad de carga en mAh o similar
    Expansible BOOLEAN DEFAULT FALSE,             -- Si la funda puede expandirse (TRUE o FALSE)
    ImagenURL VARCHAR(255),                       -- Ruta de la imagen de la funda
    TipoFunda ENUM('fija', 'expandible') NOT NULL,-- Tipo de funda (fija o expandible)
    FechaCreacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Proveedores (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100) NOT NULL,              -- Nombre del proveedor
    Pais VARCHAR(100),                        -- País del proveedor
    ContactoNombre VARCHAR(100),              -- Nombre del contacto dentro del proveedor
    ContactoTelefono VARCHAR(20),             -- Número de teléfono del contacto
    ContactoEmail VARCHAR(150),               -- Correo electrónico del contacto
    SitioWeb VARCHAR(255),                    -- URL del sitio web del proveedor
    Direccion VARCHAR(255),                   -- Dirección física del proveedor
    Descripcion TEXT,                         -- Descripción general o información adicional sobre el proveedor
    FechaCreacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Fecha de creación del proveedor
    Activo BOOLEAN DEFAULT TRUE               -- Estado si el proveedor está activo o no (puede ser desactivado)
);

-- Relación muchos a muchos entre Fundas y Proveedores
CREATE TABLE IF NOT EXISTS Fundas_Proveedores (
    ID INT AUTO_INCREMENT PRIMARY KEY,  
    FundaID INT NOT NULL,
    ProveedorID INT NOT NULL,
    FOREIGN KEY (FundaID) REFERENCES ModelosFundas(ID) ON DELETE CASCADE,
    FOREIGN KEY (ProveedorID) REFERENCES Proveedores(ID) ON DELETE CASCADE,
    UNIQUE KEY `unique_relation` (`FundaID`, `ProveedorID`)  -- Asegura que no haya duplicados
);

-- Tabla Simulaciones 
CREATE TABLE IF NOT EXISTS Simulaciones (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    UsuarioID INT NOT NULL,
    CondicionLuz VARCHAR(50) NOT NULL,
    EnergiaGenerada DECIMAL(10, 2) NOT NULL,
    Fecha DATE DEFAULT (CURRENT_DATE),
    CondicionesMeteorologicasID INT NOT NULL,  
    FundaID INT NOT NULL,
    FOREIGN KEY (UsuarioID) REFERENCES Usuarios(ID) ON DELETE CASCADE,
    FOREIGN KEY (CondicionesMeteorologicasID) REFERENCES CondicionesMeteorologicas(ID) ON DELETE CASCADE,
    FOREIGN KEY (FundaID) REFERENCES ModelosFundas(ID) ON DELETE CASCADE,
    INDEX idx_simulaciones_combo (UsuarioID, CondicionLuz)
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
  ('Carlos Ruiz', 'carlos.ruiz@example.com', '1978-11-30', NULL, 'luigi', SHA2('Solvam1234', 256), 'usuario'),
  ('Luigi Porrega', 'luis.porrega@solvam.es', '1984-06-24', 'GID467', 'luigip', SHA2('Solvam1234', 256), 'admin');

-- Inserción de condiciones meteorológicas (para relacionar en Simulaciones)
INSERT IGNORE INTO CondicionesMeteorologicas (Fecha, LuzSolar, Temperatura, Humedad, Viento)
VALUES
  ('2025-03-04', 350, 28, 65, 12),  -- Día con luz solar moderada, temperatura agradable
  ('2025-03-05', 250, 22, 85, 8),   -- Día nublado con mucha humedad
  ('2025-03-06', 450, 32, 45, 18),  -- Día soleado con alta temperatura y viento moderado
  ('2025-03-07', 500, 34, 40, 25),  -- Día soleado con mucho viento
  ('2025-03-08', 300, 27, 55, 10),  -- Día soleado con condiciones agradables
  ('2025-03-09', 150, 20, 90, 5),   -- Día lluvioso con baja luz solar y alta humedad
  ('2025-03-10', 370, 29, 60, 20),  -- Día soleado con viento fuerte
  ('2025-03-11', 200, 24, 70, 7),   -- Día nublado con temperaturas moderadas
  ('2025-03-12', 400, 31, 50, 15),  -- Día soleado con algo de viento
  ('2025-03-13', 280, 26, 65, 14),  -- Día con cielo parcialmente nublado
  ('2025-03-14', 500, 36, 35, 22),  -- Día muy soleado con viento fuerte
  ('2025-03-15', 100, 18, 95, 4),   -- Día con condiciones lluviosas y frías
  ('2025-03-16', 420, 30, 55, 11),  -- Día soleado y cálido con viento suave
  ('2025-03-17', 350, 28, 60, 9),   -- Día soleado con humedad moderada
  ('2025-03-18', 550, 38, 30, 20),  -- Día extremadamente soleado y cálido
  ('2025-03-19', 220, 21, 80, 6),   -- Día nublado con temperaturas suaves
  ('2025-03-20', 480, 33, 45, 17),  -- Día soleado con viento fuerte
  ('2025-03-21', 360, 29, 50, 13),  -- Día soleado con temperatura moderada y viento suave
  ('2025-03-22', 270, 23, 75, 9),   -- Día nublado con alta humedad y poco viento
  ('2025-03-23', 410, 31, 50, 20);  -- Día soleado con viento moderado


-- Inserción de modelos de fundas fijas con rutas de imagen 
INSERT IGNORE INTO ModelosFundas (Nombre, Tamaño, CapacidadCarga, Expansible, ImagenURL, TipoFunda) VALUES
  ('A ADDTOP Cargador Solar', '15x8 cm', 25000, FALSE, 'assets/imagenes/fundas_fijas/F1.jpeg', 'fija'),
  ('Saraupup Cargador Solar', '15x8 cm', 38800, FALSE, 'assets/imagenes/fundas_fijas/F2.jpeg', 'fija'),
  ('Hiluckey Power Bank Solar', '17x9 cm', 26800, FALSE, 'assets/imagenes/fundas_fijas/F3.jpeg', 'fija'),
  ('Riapow Power Bank Solar', '16x9 cm', 27000, FALSE, 'assets/imagenes/fundas_fijas/F4.jpeg', 'fija'),
  ('Goal Zero Sherpa 100AC', '24x10 cm', 25600, FALSE, 'assets/imagenes/fundas_fijas/F5.jpeg', 'fija'),
  ('Anker PowerCore Solar 20000', '15x8 cm', 20000, FALSE, 'assets/imagenes/fundas_fijas/F6.jpeg', 'fija'),
  ('RAVPower Solar Power Bank', '16x9 cm', 25000, FALSE, 'assets/imagenes/fundas_fijas/F7.jpeg', 'fija'),
  ('Nekteck Solar Charger', '18x9 cm', 22000, FALSE, 'assets/imagenes/fundas_fijas/F8.jpeg', 'fija'),
  ('Blavor Solar Power Bank', '18x10 cm', 26800, FALSE, 'assets/imagenes/fundas_fijas/F9.jpeg', 'fija'),
  ('Aeiusny Solar Power Bank', '16x8 cm', 25000, FALSE, 'assets/imagenes/fundas_fijas/F10.jpeg', 'fija'),
  ('Maxoak K2 Solar Power Bank', '21x11 cm', 50000, FALSE, 'assets/imagenes/fundas_fijas/F11.jpeg', 'fija'),
  ('X-DRAGON Solar Power Bank', '16x9 cm', 25000, FALSE, 'assets/imagenes/fundas_fijas/F12.jpeg', 'fija'),
  ('Solpex Power Bank Solar', '17x8 cm', 30000, FALSE, 'assets/imagenes/fundas_fijas/F13.jpeg', 'fija'),
  ('Chgeek Solar Power Bank', '17x8 cm', 25000, FALSE, 'assets/imagenes/fundas_fijas/F14.jpeg', 'fija'),
  ('ECO-WORTHY Solar Power Bank', '15x8 cm', 26800, FALSE, 'assets/imagenes/fundas_fijas/F15.jpeg', 'fija'),
  ('Poweradd Apollo 2 Solar', '15x7 cm', 20000, FALSE, 'assets/imagenes/fundas_fijas/F16.jpeg', 'fija'),
  ('iMuto Portable Solar Charger', '16x9 cm', 30000, FALSE, 'assets/imagenes/fundas_fijas/F17.jpeg', 'fija'),
  ('Tacklife Solar Power Bank', '18x9 cm', 25000, FALSE, 'assets/imagenes/fundas_fijas/F18.jpeg', 'fija'),
  ('Leechi Solar Power Bank', '16x8 cm', 24000, FALSE, 'assets/imagenes/fundas_fijas/F19.jpeg', 'fija'),
  ('Vinsic Solar Power Bank', '15x7 cm', 20000, FALSE, 'assets/imagenes/fundas_fijas/F20.jpeg', 'fija'),
  ('Varta Power Bank Solar', '17x9 cm', 25000, FALSE, 'assets/imagenes/fundas_fijas/F21.jpeg', 'fija'),
  ('Intocircuit Solar Power Bank', '15x8 cm', 26000, FALSE, 'assets/imagenes/fundas_fijas/F22.jpeg', 'fija'),
  ('Tera Grand Solar Power Bank', '17x8 cm', 27000, FALSE, 'assets/imagenes/fundas_fijas/F23.jpeg', 'fija'),
  ('Zeefo Solar Power Bank', '16x9 cm', 23000, FALSE, 'assets/imagenes/fundas_fijas/F24.jpeg', 'fija'),
  ('Oukitel Solar Power Bank', '19x10 cm', 30000, FALSE, 'assets/imagenes/fundas_fijas/F25.jpeg', 'fija'),
  ('Vintar Solar Charger', '16x9 cm', 27000, FALSE, 'assets/imagenes/fundas_fijas/F26.jpeg', 'fija'),
  ('Tommy Solar Power Bank', '15x8 cm', 25000, FALSE, 'assets/imagenes/fundas_fijas/F27.jpeg', 'fija'),
  ('Yunseity Solar Power Bank', '15x8 cm', 22000, FALSE, 'assets/imagenes/fundas_fijas/F28.jpeg', 'fija'),
  ('DOKO Solar Power Bank', '17x9 cm', 28000, FALSE, 'assets/imagenes/fundas_fijas/F29.jpeg', 'fija'),
  ('Awei Solar Charger', '16x9 cm', 24000, FALSE, 'assets/imagenes/fundas_fijas/F30.jpeg', 'fija');


-- Inserción de modelos de fundas expandibles con rutas de imagen corregidas
INSERT IGNORE INTO ModelosFundas (Nombre, Tamaño, CapacidadCarga, Expansible, ImagenURL, TipoFunda) VALUES
  ('FEELLE Cargador Solar Power Bank', '20x10 cm', 25000, TRUE, 'assets/imagenes/fundas_expandibles/E1.jpeg', 'expandible'),
  ('Riapow Power Bank Solar', '20x10 cm', 27000, TRUE, 'assets/imagenes/fundas_expandibles/E2.jpeg', 'expandible'),
  ('Hiluckey 15W Power Bank Solar', '20x10 cm', 30000, TRUE, 'assets/imagenes/fundas_expandibles/E3.jpeg', 'expandible'),
  ('Mesuvida 60W Solar Panel', '30x15 cm', 50000, TRUE, 'assets/imagenes/fundas_expandibles/E4.jpeg', 'expandible'),
  ('Suaoki Solar Power Bank', '18x8 cm', 20000, TRUE, 'assets/imagenes/fundas_expandibles/E5.jpeg', 'expandible'),
  ('ECO-WORTHY Solar Charger', '25x12 cm', 35000, TRUE, 'assets/imagenes/fundas_expandibles/E6.jpeg', 'expandible'),
  ('Blavor Solar Power Bank', '18x9 cm', 25000, TRUE, 'assets/imagenes/fundas_expandibles/E7.jpeg', 'expandible'),
  ('Nekteck Solar Charger', '19x10 cm', 22000, TRUE, 'assets/imagenes/fundas_expandibles/E8.jpeg', 'expandible'),
  ('Aoyama Solar Power Bank', '18x8 cm', 27000, TRUE, 'assets/imagenes/fundas_expandibles/E9.jpeg', 'expandible'),
  ('Anker PowerCore Solar 20000', '15x8 cm', 20000, TRUE, 'assets/imagenes/fundas_expandibles/E10.jpeg', 'expandible'),
  ('RAVPower Solar Power Bank', '19x9 cm', 24000, TRUE, 'assets/imagenes/fundas_expandibles/E11.jpeg', 'expandible'),
  ('Goal Zero Yeti Solar Generator', '50x25 cm', 100000, TRUE, 'assets/imagenes/fundas_expandibles/E12.jpeg', 'expandible'),
  ('Outxe Solar Power Bank', '20x10 cm', 30000, TRUE, 'assets/imagenes/fundas_expandibles/E13.jpeg', 'expandible'),
  ('X-DRAGON Solar Power Bank', '18x8 cm', 25000, TRUE, 'assets/imagenes/fundas_expandibles/E14.jpeg', 'expandible'),
  ('Tacklife Solar Power Bank', '16x8 cm', 24000, TRUE, 'assets/imagenes/fundas_expandibles/E15.jpeg', 'expandible'),
  ('Tera Grand Solar Power Bank', '20x10 cm', 25000, TRUE, 'assets/imagenes/fundas_expandibles/E16.jpeg', 'expandible'),
  ('Maxoak Solar Power Bank', '21x11 cm', 50000, TRUE, 'assets/imagenes/fundas_expandibles/E17.jpeg', 'expandible'),
  ('Solpex Solar Charger', '18x9 cm', 30000, TRUE, 'assets/imagenes/fundas_expandibles/E18.jpeg', 'expandible'),
  ('Oukitel Solar Power Bank', '19x10 cm', 30000, TRUE, 'assets/imagenes/fundas_expandibles/E19.jpeg', 'expandible'),
  ('Tommy Solar Power Bank', '16x8 cm', 25000, TRUE, 'assets/imagenes/fundas_expandibles/E20.jpeg', 'expandible'),
  ('Vinsic Solar Power Bank', '18x9 cm', 22000, TRUE, 'assets/imagenes/fundas_expandibles/E21.jpeg', 'expandible'),
  ('DOKO Solar Power Bank', '16x8 cm', 24000, TRUE, 'assets/imagenes/fundas_expandibles/E22.jpeg', 'expandible'),
  ('Yunseity Solar Power Bank', '15x7 cm', 22000, TRUE, 'assets/imagenes/fundas_expandibles/E23.jpeg', 'expandible'),
  ('Leechi Solar Power Bank', '18x9 cm', 23000, TRUE, 'assets/imagenes/fundas_expandibles/E24.jpeg', 'expandible'),
  ('Chgeek Solar Power Bank', '17x8 cm', 26000, TRUE, 'assets/imagenes/fundas_expandibles/E25.jpeg', 'expandible'),
  ('Awei Solar Charger', '16x9 cm', 24000, TRUE, 'assets/imagenes/fundas_expandibles/E26.jpeg', 'expandible'),
  ('ECO-WORTHY Solar Power Bank', '20x10 cm', 25000, TRUE, 'assets/imagenes/fundas_expandibles/E27.jpeg', 'expandible'),
  ('Zeefo Solar Power Bank', '18x9 cm', 23000, TRUE, 'assets/imagenes/fundas_expandibles/E28.jpeg', 'expandible'),
  ('Varta Solar Power Bank', '17x8 cm', 25000, TRUE, 'assets/imagenes/fundas_expandibles/E29.jpeg', 'expandible'),
  ('Intocircuit Solar Power Bank', '16x8 cm', 25000, TRUE, 'assets/imagenes/fundas_expandibles/E30.jpeg', 'expandible'),
  ('Blavor Solar Charger', '20x10 cm', 30000, TRUE, 'assets/imagenes/fundas_expandibles/E31.jpeg', 'expandible');


-- Inserción de proveedores
INSERT IGNORE INTO Proveedores (
  Nombre, Pais, ContactoNombre, ContactoTelefono, ContactoEmail, SitioWeb, Direccion, Descripcion, FechaCreacion, Activo
) VALUES
  ('Proveedora Solar 1', 'España', 'Juan Pérez', '912345678', 'contacto@solar1.com', 'https://solar1.com', 'Calle Falsa 123, Madrid', 'Proveedor líder en paneles solares', NOW(), TRUE),
  ('Proveedora Solar 2', 'China', 'Li Wei', '861234567890', 'contacto@solar2.com', 'https://solar2.com', 'Calle Real 456, Pekín', 'Proveedor especializado en paneles solares fotovoltaicos', NOW(), TRUE),
  ('Proveedora Solar 3', 'EE. UU.', 'Michael Johnson', '3051234567', 'contacto@solar3.com', 'https://solar3.com', '1234 Sunshine Blvd, Florida', 'Proveedor con más de 20 años de experiencia', NOW(), TRUE),
  ('Proveedora Solar 4', 'Alemania', 'Klaus Müller', '4915123456789', 'contacto@solar4.com', 'https://solar4.com', 'Berliner Str. 10, Berlín', 'Proveedor de soluciones energéticas sostenibles', NOW(), TRUE),
  ('Proveedora Solar 5', 'México', 'Carlos López', '5551234567', 'contacto@solar5.com', 'https://solar5.com', 'Avenida Solar 88, Ciudad de México', 'Proveedor en expansión en América Latina', NOW(), TRUE),
  ('Proveedora Solar 6', 'Reino Unido', 'Emma Watson', '441632960961', 'contacto@solar6.com', 'https://solar6.com', 'Solar St. 50, Londres', 'Innovador en paneles solares de alta eficiencia', NOW(), TRUE),
  ('Proveedora Solar 7', 'Francia', 'Pierre Dubois', '331234567890', 'contacto@solar7.com', 'https://solar7.com', 'Rue de le energie 32, París', 'Especialista en energías renovables', NOW(), TRUE),
  ('Proveedora Solar 8', 'Italia', 'Giovanni Rossi', '390612345678', 'contacto@solar8.com', 'https://solar8.com', 'Via del Sole 12, Roma', 'Empresa con proyectos solares en Europa y África', NOW(), TRUE),
  ('Proveedora Solar 9', 'Canadá', 'Sara McDonald', '14161234567', 'contacto@solar9.com', 'https://solar9.com', 'Maple Rd. 22, Toronto', 'Famosos por su tecnología solar avanzada', NOW(), TRUE),
  ('Proveedora Solar 10', 'Japón', 'Taro Yamada', '81312345678', 'contacto@solar10.com', 'https://solar10.com', 'Tokyo Tower 3F, Tokio', 'Proveedor de paneles solares de última generación', NOW(), TRUE);



  -- Relación de fundas con proveedores
INSERT IGNORE INTO Fundas_Proveedores (FundaID, ProveedorID) VALUES
  (1, 1), (2, 2), (3, 3), (4, 4), (5, 5),
  (6, 6), (7, 7), (8, 8), (9, 9), (10, 10),
  (11, 1), (12, 2), (13, 3), (14, 4), (15, 5),
  (16, 6), (17, 7), (18, 8), (19, 9), (20, 10),
  (21, 1), (22, 2), (23, 3), (24, 4), (25, 5),
  (26, 6), (27, 7), (28, 8), (29, 9), (30, 10);


-- Inserción en Simulaciones 
INSERT IGNORE INTO Simulaciones (UsuarioID, CondicionLuz, EnergiaGenerada, CondicionesMeteorologicasID, FundaID)
VALUES
  (1, 'Luz solar directa', calcular_energia('Luz solar directa', 30, 300, 25, 60, 15), 1, 1),  -- Funda fija ID 1
  (2, 'Luz artificial', calcular_energia('Luz artificial', 45, 200, 23, 80, 10), 2, 5),     -- Funda expandible ID 5
  (3, 'Luz solar directa', calcular_energia('Luz solar directa', 60, 400, 30, 50, 20), 3, 7),  -- Funda expandible ID 7
  (4, 'Luz solar directa', calcular_energia('Luz solar directa', 30, 350, 28, 55, 12), 1, 2),  -- Funda fija ID 2
  (1, 'Luz artificial', calcular_energia('Luz artificial', 50, 220, 22, 75, 15), 2, 4),     -- Funda expandible ID 4
  (2, 'Luz solar directa', calcular_energia('Luz solar directa', 60, 450, 32, 60, 18), 3, 6),  -- Funda expandible ID 6
  (3, 'Luz solar directa', calcular_energia('Luz solar directa', 45, 300, 30, 60, 10), 1, 3),  -- Funda fija ID 3
  (4, 'Luz artificial', calcular_energia('Luz artificial', 40, 210, 25, 80, 12), 2, 8);     -- Funda expandible ID 8


-- Inserción de nuevas ideas inteligentes
INSERT IGNORE INTO Ideas (UsuarioID, Titulo, Descripcion)
VALUES
  (1, 'Panel solar modular', 'Desarrollo de paneles solares modulares que se conectan entre sí para aumentar la capacidad de generación de energía de manera flexible.'),
  (2, 'Cargador solar multifuncional', 'Diseño de un cargador solar que no solo sirva para cargar dispositivos, sino que también sea un sistema de energía portátil para emergencias.'),
  (3, 'Mejorar eficiencia de baterías solares', 'Investigar nuevas tecnologías para mejorar la eficiencia y duración de las baterías utilizadas en cargadores solares.'),
  (4, 'Aplicación de monitoreo inteligente', 'Desarrollo de una aplicación móvil que permita monitorear la producción de energía solar en tiempo real y optimizar la carga de los dispositivos.'),
  (1, 'Panel solar con autolimpieza', 'Diseño de paneles solares con un sistema de autolimpieza que mantenga la máxima eficiencia sin necesidad de mantenimiento constante.'),
  (2, 'Funda solar con almacenamiento energético', 'Creación de fundas solares que puedan almacenar energía y utilizarla para cargar dispositivos cuando no haya luz solar directa.'),
  (2, 'Sistemas híbridos solares y eólicos', 'Desarrollar un sistema híbrido que combine paneles solares y generadores eólicos portátiles para áreas con condiciones meteorológicas cambiantes.'),
  (1, 'Sistema de energía solar para vehículos eléctricos', 'Diseño de un sistema solar que cargue vehículos eléctricos de manera autónoma mientras se encuentran estacionados al sol.'),
  (3, 'Solar powerbank con tecnología de carga rápida', 'Incorporar tecnología de carga rápida en powerbanks solares para mejorar la eficiencia en la carga de dispositivos.'),
  (4, 'Incorporación de inteligencia artificial para optimización energética', 'Uso de inteligencia artificial para gestionar de forma eficiente la energía producida por los paneles solares, optimizando el uso y almacenamiento de energía.');


-- ======================================
-- 6. Configuración de Seguridad
-- ======================================
CREATE USER IF NOT EXISTS 'solar_screen'@'%' IDENTIFIED BY 'Solvam1234';
GRANT ALL PRIVILEGES ON solar_screen.* TO 'solar_screen'@'%';
FLUSH PRIVILEGES;
