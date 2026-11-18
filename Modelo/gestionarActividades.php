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
    return $datos;
}

function validarActividad($datos)
{
    $nombre=$datos['nombreActividad'] ?? null;
    $fecha=$datos['fechaEvento'] ?? null;
    $municipio=$datos['municipio'] ?? null;
    if(!is_string($nombre)||trim($nombre)==="" || strlen($nombre) > 100 || !is_string($municipio) || trim($municipio) === "" || strlen($municipio) > 50) {
    http_response_code(400);
    throw new Exception(
        "Ingresa un nombre y municipio válidos"
        );
    }
    return[
        "nombreActividad"=> trim($nombre),
        "fechaEvento"=> $fecha,
        "municipio"=> trim($municipio)
    ];
}

function validarId($id){
    $idActividad=var($id,FILTER_VALIDATE_INT,["options" => ["min_range" => 1]]);
    if ($idActividad === false) {
        http_response_code(400);
        throw new Exception(
            jhmnhhkmhhk--
        )
    }
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
    require_once __DIR__."/Actividad.php";
    $actividad = new Actividad();

    switch($metodo){
        case "GET":
            $resultado = [
                "success" => true,
                "message" => "Actividad creada exitosamente"
            ];
            http_response_code(200);
            break;
        case "POST":
            $datos = validarActividad(leerDatos());
            $actividad->guardar($datos);
            $resultado = [
                "success" => true,
                "message" => "Actividad creada exitosamente"
            ];
            http_response_code(201);
            break;
        case "PUT":
            $datos = leerDatos();
            $datos["idActividad"] = validarId($datos["idActividad"] ?? null);
            $datos = array_merge($datos,validarActividad($datos));

            if(!$actividad->editar($datos)){
                http_response_code(404);
                throw new Exception("No Se Encontró La Actividad");
            }
            $resultado = [
                "success" => true,
                "mensaje" => "Actividad actualizada correctamente"
            ];
            http_response_code(200);
            break;
        case "DELETE":
            $datos = leerDatos();
            $idActividad = validarId($datos["idActividad"] ?? null);

            if(!$actividad->eliminar($idActividad)) {
                http_response_code(404);
                throw new Exception("No Se Encontró La Actividad");
            }
            
            $resultado = [
                "success" => true,
                "mensaje" => "Actividad eliminada correctamente"
            ];
            http_response_code(200);
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
