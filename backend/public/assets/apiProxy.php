<?php
// apiProxy.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Configuración de la URL base de la API backend
$apiBaseUrl = "http://65.108.85.99:8000/api"; // URL de tu backend CI dentro del contenedor

// Obtener el endpoint de la URL (por ejemplo: /api/condiciones_meteorologicas)
$path = $_SERVER['REQUEST_URI'];
$path = str_replace('/apiProxy.php', '', $path); // Eliminar el archivo script de la URL
$path = trim($path, '/'); // Limpiar cualquier barra al principio o al final

// Asegurarse de que el endpoint no esté vacío
if (empty($path)) {
    http_response_code(400);
    echo json_encode(["error" => "Falta el parámetro 'endpoint'."]);
    exit;
}

// Construir la URL completa para la API de CodeIgniter
$url = rtrim($apiBaseUrl, '/') . '/' . $path;

// Inicializar cURL
$ch = curl_init($url);

// Establecer el método HTTP basado en el tipo de solicitud
$method = $_SERVER['REQUEST_METHOD']; // Obtener el método (GET, POST, PUT, DELETE)
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); // Establecer el método HTTP

// Si el método es POST, PUT o PATCH, agregar el cuerpo de la solicitud
if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
    $body = file_get_contents("php://input");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($body),
    ]);
}

// Si el método es GET, añadir parámetros de paginación (si se proporcionan)
if ($method == 'GET') {
    $queryParams = $_GET;
    if (!empty($queryParams)) {
        $urlWithParams = $url . '?' . http_build_query($queryParams);
        curl_setopt($ch, CURLOPT_URL, $urlWithParams);
    }
}

// Ejecutar la solicitud cURL y obtener la respuesta
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Obtener el código de estado HTTP
curl_close($ch);

// Verificar si hubo algún error en la ejecución de cURL
if ($response === false) {
    http_response_code(500);
    echo json_encode(["error" => "Error al realizar la solicitud."]);
    exit;
}

// Devolver la respuesta de la API backend con el código de estado adecuado
http_response_code($httpcode);
echo $response;
