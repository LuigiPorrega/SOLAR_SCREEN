
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
    p_tiempo_minutos DECIMAL(10,2),
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
    Cantidad INT DEFAULT 0,                       
    Precio DECIMAL(10,2) NOT NULL, 
    TipoFunda ENUM('fija', 'expandible') NOT NULL,-- Tipo de funda (fija o expandible)
    FechaCreacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--Tabla Proveedores
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
    Tiempo DECIMAL(10,2) NOT NULL,
    Fecha DATE DEFAULT (CURRENT_DATE),
    CondicionesMeteorologicasID INT NOT NULL,  
    FundaID INT NOT NULL,
    FOREIGN KEY (UsuarioID) REFERENCES Usuarios(ID) ON DELETE CASCADE,
    FOREIGN KEY (CondicionesMeteorologicasID) REFERENCES CondicionesMeteorologicas(ID) ON DELETE CASCADE,
    FOREIGN KEY (FundaID) REFERENCES ModelosFundas(ID) ON DELETE CASCADE,
    INDEX idx_simulaciones_combo (UsuarioID, CondicionLuz)
);

--Tabla Carrito
CREATE TABLE Carrito (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    UsuarioId INT NOT NULL,
    ModelosFundasId INT NOT NULL,
    Cantidad INT NOT NULL DEFAULT 1,
    Precio DECIMAL(10,2) NOT NULL,
    Creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_carrito_usuario FOREIGN KEY (UsuarioId) REFERENCES Usuarios(ID) ON DELETE CASCADE,
    CONSTRAINT fk_carrito_modelo FOREIGN KEY (ModelosFundasId) REFERENCES ModelosFundas(ID) ON DELETE CASCADE,
    CONSTRAINT uc_usuario_modelo UNIQUE (UsuarioId, ModelosFundasId)
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


-- Inserción de modelos de fundas fijas 
INSERT IGNORE INTO ModelosFundas 
(Nombre, Tamaño, CapacidadCarga, Expansible, ImagenURL, TipoFunda, Cantidad, Precio) 
VALUES
  ('A ADDTOP Cargador Solar', '15x8 cm', 25000, FALSE, 'assets/imagenes/fundas_fijas/F1.jpeg', 'fija', 15, 39.99),
  ('Saraupup Cargador Solar', '15x8 cm', 38800, FALSE, 'assets/imagenes/fundas_fijas/F2.jpeg', 'fija', 10, 49.99),
  ('Hiluckey Power Bank Solar', '17x9 cm', 26800, FALSE, 'assets/imagenes/fundas_fijas/F3.jpeg', 'fija', 12, 45.50),
  ('Riapow Power Bank Solar', '16x9 cm', 27000, FALSE, 'assets/imagenes/fundas_fijas/F4.jpeg', 'fija', 8, 42.00),
  ('Goal Zero Sherpa 100AC', '24x10 cm', 25600, FALSE, 'assets/imagenes/fundas_fijas/F5.jpeg', 'fija', 5, 109.90),
  ('Anker PowerCore Solar 20000', '15x8 cm', 20000, FALSE, 'assets/imagenes/fundas_fijas/F6.jpeg', 'fija', 50, 66.90),
  ('RAVPower Solar Power Bank', '16x9 cm', 25000, FALSE, 'assets/imagenes/fundas_fijas/F7.jpeg', 'fija', 30, 49.99),
  ('Nekteck Solar Charger', '18x9 cm', 22000, FALSE, 'assets/imagenes/fundas_fijas/F8.jpeg', 'fija', 22, 52.99),
  ('Blavor Solar Power Bank', '18x10 cm', 26800, FALSE, 'assets/imagenes/fundas_fijas/F9.jpeg', 'fija', 30, 125.00),
  ('Aeiusny Solar Power Bank', '16x8 cm', 25000, FALSE, 'assets/imagenes/fundas_fijas/F10.jpeg', 'fija', 21, 120.35),
  ('Maxoak K2 Solar Power Bank', '21x11 cm', 50000, FALSE, 'assets/imagenes/fundas_fijas/F11.jpeg', 'fija', 60, 49.99),
  ('X-DRAGON Solar Power Bank', '16x9 cm', 25000, FALSE, 'assets/imagenes/fundas_fijas/F12.jpeg', 'fija', 55, 98.99),
  ('Solpex Power Bank Solar', '17x8 cm', 30000, FALSE, 'assets/imagenes/fundas_fijas/F13.jpeg', 'fija', 28, 57.25),
  ('Chgeek Solar Power Bank', '17x8 cm', 25000, FALSE, 'assets/imagenes/fundas_fijas/F14.jpeg', 'fija', 26, 64.75),
  ('ECO-WORTHY Solar Power Bank', '15x8 cm', 26800, FALSE, 'assets/imagenes/fundas_fijas/F15.jpeg', 'fija', 32, 59.90),
  ('Poweradd Apollo 2 Solar', '15x7 cm', 20000, FALSE, 'assets/imagenes/fundas_fijas/F16.jpeg', 'fija', 18, 44.99),
  ('iMuto Portable Solar Charger', '16x9 cm', 30000, FALSE, 'assets/imagenes/fundas_fijas/F17.jpeg', 'fija', 14, 79.99),
  ('Tacklife Solar Power Bank', '18x9 cm', 25000, FALSE, 'assets/imagenes/fundas_fijas/F18.jpeg', 'fija', 20, 51.50),
  ('Leechi Solar Power Bank', '16x8 cm', 24000, FALSE, 'assets/imagenes/fundas_fijas/F19.jpeg', 'fija', 11, 48.00),
  ('Vinsic Solar Power Bank', '15x7 cm', 20000, FALSE, 'assets/imagenes/fundas_fijas/F20.jpeg', 'fija', 17, 39.99),
  ('Varta Power Bank Solar', '17x9 cm', 25000, FALSE, 'assets/imagenes/fundas_fijas/F21.jpeg', 'fija', 19, 47.00),
  ('Intocircuit Solar Power Bank', '15x8 cm', 26000, FALSE, 'assets/imagenes/fundas_fijas/F22.jpeg', 'fija', 23, 61.80),
  ('Tera Grand Solar Power Bank', '17x8 cm', 27000, FALSE, 'assets/imagenes/fundas_fijas/F23.jpeg', 'fija', 14, 58.00),
  ('Zeefo Solar Power Bank', '16x9 cm', 23000, FALSE, 'assets/imagenes/fundas_fijas/F24.jpeg', 'fija', 13, 53.75),
  ('Oukitel Solar Power Bank', '19x10 cm', 30000, FALSE, 'assets/imagenes/fundas_fijas/F25.jpeg', 'fija', 9, 68.90),
  ('Vintar Solar Charger', '16x9 cm', 27000, FALSE, 'assets/imagenes/fundas_fijas/F26.jpeg', 'fija', 7, 50.50),
  ('Tommy Solar Power Bank', '15x8 cm', 25000, FALSE, 'assets/imagenes/fundas_fijas/F27.jpeg', 'fija', 15, 47.30),
  ('Yunseity Solar Power Bank', '15x8 cm', 22000, FALSE, 'assets/imagenes/fundas_fijas/F28.jpeg', 'fija', 16, 42.75),
  ('DOKO Solar Power Bank', '17x9 cm', 28000, FALSE, 'assets/imagenes/fundas_fijas/F29.jpeg', 'fija', 13, 60.40),
  ('Awei Solar Charger', '16x9 cm', 24000, FALSE, 'assets/imagenes/fundas_fijas/F30.jpeg', 'fija', 18, 55.99);


-- Inserción de modelos de fundas expandibles 
INSERT IGNORE INTO ModelosFundas 
(Nombre, Tamaño, CapacidadCarga, Expansible, ImagenURL, TipoFunda, Cantidad, Precio) 
VALUES
  ('FEELLE Cargador Solar Power Bank', '20x10 cm', 25000, TRUE, 'assets/imagenes/fundas_expandibles/E1.jpeg', 'expandible', 7, 59.99),
  ('Riapow Power Bank Solar', '20x10 cm', 27000, TRUE, 'assets/imagenes/fundas_expandibles/E2.jpeg', 'expandible', 6, 54.50),
  ('Hiluckey 15W Power Bank Solar', '20x10 cm', 30000, TRUE, 'assets/imagenes/fundas_expandibles/E3.jpeg', 'expandible', 10, 58.75),
  ('Mesuvida 60W Solar Panel', '30x15 cm', 50000, TRUE, 'assets/imagenes/fundas_expandibles/E4.jpeg', 'expandible', 3, 139.00),
  ('Suaoki Solar Power Bank', '18x8 cm', 20000, TRUE, 'assets/imagenes/fundas_expandibles/E5.jpeg', 'expandible', 9, 49.95),
  ('ECO-WORTHY Solar Charger', '25x12 cm', 35000, TRUE, 'assets/imagenes/fundas_expandibles/E6.jpeg', 'expandible', 4, 72.00),
  ('Blavor Solar Power Bank', '18x9 cm', 25000, TRUE, 'assets/imagenes/fundas_expandibles/E7.jpeg', 'expandible', 8, 61.50),
  ('Nekteck Solar Charger', '19x10 cm', 22000, TRUE, 'assets/imagenes/fundas_expandibles/E8.jpeg', 'expandible', 12, 55.00),
  ('Aoyama Solar Power Bank', '18x8 cm', 27000, TRUE, 'assets/imagenes/fundas_expandibles/E9.jpeg', 'expandible', 7, 60.00),
  ('Anker PowerCore Solar 20000', '15x8 cm', 20000, TRUE, 'assets/imagenes/fundas_expandibles/E10.jpeg', 'expandible', 11, 66.90),
  ('RAVPower Solar Power Bank', '19x9 cm', 24000, TRUE, 'assets/imagenes/fundas_expandibles/E11.jpeg', 'expandible', 10, 63.00),
  ('Goal Zero Yeti Solar Generator', '50x25 cm', 100000, TRUE, 'assets/imagenes/fundas_expandibles/E12.jpeg', 'expandible', 2, 299.99),
  ('Outxe Solar Power Bank', '20x10 cm', 30000, TRUE, 'assets/imagenes/fundas_expandibles/E13.jpeg', 'expandible', 6, 68.75),
  ('X-DRAGON Solar Power Bank', '18x8 cm', 25000, TRUE, 'assets/imagenes/fundas_expandibles/E14.jpeg', 'expandible', 9, 64.00),
  ('Tacklife Solar Power Bank', '16x8 cm', 24000, TRUE, 'assets/imagenes/fundas_expandibles/E15.jpeg', 'expandible', 8, 62.20),
  ('Tera Grand Solar Power Bank', '20x10 cm', 25000, TRUE, 'assets/imagenes/fundas_expandibles/E16.jpeg', 'expandible', 7, 60.80),
  ('Maxoak Solar Power Bank', '21x11 cm', 50000, TRUE, 'assets/imagenes/fundas_expandibles/E17.jpeg', 'expandible', 6, 84.50),
  ('Solpex Solar Charger', '18x9 cm', 30000, TRUE, 'assets/imagenes/fundas_expandibles/E18.jpeg', 'expandible', 10, 66.30),
  ('Oukitel Solar Power Bank', '19x10 cm', 30000, TRUE, 'assets/imagenes/fundas_expandibles/E19.jpeg', 'expandible', 5, 72.99),
  ('Tommy Solar Power Bank', '16x8 cm', 25000, TRUE, 'assets/imagenes/fundas_expandibles/E20.jpeg', 'expandible', 9, 60.50),
  ('Vinsic Solar Power Bank', '18x9 cm', 22000, TRUE, 'assets/imagenes/fundas_expandibles/E21.jpeg', 'expandible', 11, 54.99),
  ('DOKO Solar Power Bank', '16x8 cm', 24000, TRUE, 'assets/imagenes/fundas_expandibles/E22.jpeg', 'expandible', 13, 58.80),
  ('Yunseity Solar Power Bank', '15x7 cm', 22000, TRUE, 'assets/imagenes/fundas_expandibles/E23.jpeg', 'expandible', 10, 52.00),
  ('Leechi Solar Power Bank', '18x9 cm', 23000, TRUE, 'assets/imagenes/fundas_expandibles/E24.jpeg', 'expandible', 6, 57.10),
  ('Chgeek Solar Power Bank', '17x8 cm', 26000, TRUE, 'assets/imagenes/fundas_expandibles/E25.jpeg', 'expandible', 14, 60.25),
  ('Awei Solar Charger', '16x9 cm', 24000, TRUE, 'assets/imagenes/fundas_expandibles/E26.jpeg', 'expandible', 12, 59.00),
  ('ECO-WORTHY Solar Power Bank', '20x10 cm', 25000, TRUE, 'assets/imagenes/fundas_expandibles/E27.jpeg', 'expandible', 5, 61.90),
  ('Zeefo Solar Power Bank', '18x9 cm', 23000, TRUE, 'assets/imagenes/fundas_expandibles/E28.jpeg', 'expandible', 6, 58.00),
  ('Varta Solar Power Bank', '17x8 cm', 25000, TRUE, 'assets/imagenes/fundas_expandibles/E29.jpeg', 'expandible', 10, 64.75),
  ('Intocircuit Solar Power Bank', '16x8 cm', 25000, TRUE, 'assets/imagenes/fundas_expandibles/E30.jpeg', 'expandible', 8, 60.00),
  ('Blavor Solar Charger', '20x10 cm', 30000, TRUE, 'assets/imagenes/fundas_expandibles/E31.jpeg', 'expandible', 10, 69.90);


-- Inserción de proveedores
INSERT IGNORE INTO Proveedores (
  Nombre, Pais, ContactoNombre, ContactoTelefono, ContactoEmail, SitioWeb, Direccion, Descripcion, FechaCreacion, Activo
) VALUES
  ('SunProtect Solutions', 'España', 'Lucía Ramírez', '34912345678', 'lucia@sunprotect.es', 'https://sunprotect.es', 'Calle Sol 21, Madrid', 'Especialistas en fundas solares y cobertores para instalaciones fotovoltaicas', NOW(), TRUE),
  ('SolarShield Manufacturing', 'Alemania', 'Hans Becker', '4915212345678', 'hans@solarshield.de', 'https://solarshield.de', 'Energieweg 45, Berlín', 'Fabricante líder de cubiertas y fundas resistentes UV para paneles solares', NOW(), TRUE),
  ('HelioCover Tech', 'Estados Unidos', 'Sarah Thompson', '12025550123', 'sarah@heliocover.com', 'https://heliocover.com', '500 Green Energy Dr, Austin, TX', 'Proveedor de fundas solares inteligentes con sensores de protección', NOW(), TRUE),
  ('SolCare Italia', 'Italia', 'Marco Bianchi', '390612345670', 'marco@solcare.it', 'https://solcare.it', 'Via Energia 10, Roma', 'Distribuidor italiano de accesorios y fundas solares para proyectos residenciales', NOW(), TRUE),
  ('Fotoprotect China Ltd.', 'China', 'Mei Zhang', '8613812345678', 'mei@fotoprotect.cn', 'https://fotoprotect.cn', 'Tech Park 88, Shenzhen', 'Fabricante de fundas protectoras solares exportadas a más de 40 países', NOW(), TRUE),
  ('GreenWrap UK', 'Reino Unido', 'James Evans', '441632960960', 'james@greenwrap.co.uk', 'https://greenwrap.co.uk', 'Solar Lane 7, Manchester', 'Proveedor de fundas ecológicas para paneles solares en climas lluviosos', NOW(), TRUE),
  ('EcoSun Coberturas', 'Brasil', 'Ana Souza', '5521998765432', 'ana@ecosun.com.br', 'https://ecosun.com.br', 'Rua do Sol 100, São Paulo', 'Empresa enfocada en soluciones de protección solar para sistemas rurales', NOW(), TRUE),
  ('SunGuard France', 'Francia', 'Clément Lefèvre', '33123456789', 'clement@sunguard.fr', 'https://sunguard.fr', 'Rue du Photovoltaïque 55, Lyon', 'Distribuidor de fundas con aislamiento térmico para techos solares', NOW(), TRUE),
  ('SolarWrap Canada Inc.', 'Canadá', 'Emily Wilson', '14165550111', 'emily@solarwrap.ca', 'https://solarwrap.ca', 'Innovation Park 12, Vancouver', 'Proveedor norteamericano de cubiertas para paneles en ambientes extremos', NOW(), TRUE),
  ('Kyokai Solar Covers', 'Japón', 'Haruki Tanaka', '81334567890', 'tanaka@kyokaisolar.jp', 'https://kyokaisolar.jp', 'Akihabara Tech Plaza, Tokio', 'Desarrollador japonés de fundas solares automatizadas y resistentes al tifón', NOW(), TRUE);


-- Relación de fundas con proveedores
INSERT IGNORE INTO Fundas_Proveedores (FundaID, ProveedorID) VALUES
  (1, 1), (2, 2), (3, 3), (4, 4), (5, 5),
  (6, 6), (7, 7), (8, 8), (9, 9), (10, 10),
  (11, 1), (12, 2), (13, 3), (14, 4), (15, 5),
  (16, 6), (17, 7), (18, 8), (19, 9), (20, 10),
  (21, 1), (22, 2), (23, 3), (24, 4), (25, 5),
  (26, 6), (27, 7), (28, 8), (29, 9), (30, 10);


-- Inserción en Simulaciones 
INSERT IGNORE INTO Simulaciones (UsuarioID, CondicionLuz, EnergiaGenerada, Tiempo, CondicionesMeteorologicasID, FundaID)
VALUES
  (1, 'Luz solar directa', calcular_energia('Luz solar directa', 30, 300, 25, 60, 15), 30, 1, 1),  -- Funda fija ID 1
  (2, 'Luz artificial', calcular_energia('Luz artificial', 45, 200, 23, 80, 10), 45, 2, 5),     -- Funda expandible ID 5
  (3, 'Luz solar directa', calcular_energia('Luz solar directa', 60, 400, 30, 50, 20), 60, 3, 7),  -- Funda expandible ID 7
  (4, 'Luz solar directa', calcular_energia('Luz solar directa', 30, 350, 28, 55, 12), 30, 1, 2),  -- Funda fija ID 2
  (1, 'Luz artificial', calcular_energia('Luz artificial', 50, 220, 22, 75, 15), 50, 2, 4),     -- Funda expandible ID 4
  (2, 'Luz solar directa', calcular_energia('Luz solar directa', 60, 450, 32, 60, 18), 60, 3, 6),  -- Funda expandible ID 6
  (3, 'Luz solar directa', calcular_energia('Luz solar directa', 45, 300, 30, 60, 10), 45, 1, 3),  -- Funda fija ID 3
  (4, 'Luz artificial', calcular_energia('Luz artificial', 40, 210, 25, 80, 12), 40, 2, 8);     -- Funda expandible ID 8


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


--Inserción en Carrito
INSERT IGNORE INTO Carrito (UsuarioId, ModelosFundasId, Cantidad, Precio)
VALUES 
(1, 15, 2, 59.99),
(1, 26, 1, 50.50);


-- ======================================
-- 6. Configuración de Seguridad
-- ======================================
CREATE USER IF NOT EXISTS 'solar_screen'@'%' IDENTIFIED BY 'Solvam1234';
GRANT ALL PRIVILEGES ON solar_screen.* TO 'solar_screen'@'%';
FLUSH PRIVILEGES;
