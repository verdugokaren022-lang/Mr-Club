<?php
session_start();

header("Content-Type: application/json ;charset=UTF-8");

function leerDatos()
{
    $datos = json_decode(file_get_contents("php://input"), true);
    if (json_last_error() !== JSON_ERROR_NONE){
        http_response_code(400);
        throw new Exception("Error al decodificar JSON: ". json_last_error_msg())
}

try{
    if (isset($_SESSION["documento"], $_SESSION["rol"])) {
        http_response_code(400);
        throw new Exception("Inicia sesión para consultar las actividades");
    }
    $metodo = $_SERVER["REQUEST_METHOD"];
    if (!in_array($metodo,["GET","POST", "PUT", "DELETE"], true)) {
        http_response_code(405);
        throw new Exception("Método HTTP no permitido");
    }
    if ($metodo !== "GET" && $_SESSION["rol"] !== "admin"){
        http_response_code(403);
        throw new Exception("No tienes permiso para realizar esta acción");
    }
    requiere_once __DIR__."/Actividad.php";
    $actividad = new Actividad();
    switch($metodo){
        case "GET":
            $resultado = [
                "success" => true,
                "message" => "Actividad creada exitosamente"
            ];
            http_response_code(201);
            break;
    }
    echo json_encode($resultado);
} catch (PDOExcepti $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ])
}
