<?php

header("Content-Type: application/json; charset-utf-8");// Muestra el contenido en formato JSON y con codificación UTF-8

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    // Saltar a catch y mostrar el mensaje de error
    throw new Exception("Método HTTP no permitido");

}
session_start();
$_SESSION=[];

if(ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

session_destroy();

echo json_encode([
    "success" => true,
]);
    