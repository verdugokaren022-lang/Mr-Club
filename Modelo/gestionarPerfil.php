<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

try {
    if (!isset($_SESSION["documento"])) {
        http_response_code(401);
        throw new Exception("Inicia sesión para ver tu perfil");
    }

    $metodo = $_SERVER["REQUEST_METHOD"];
    if (!in_array($metodo, ["GET", "PUT"], true)) {
        http_response_code(405);
        throw new Exception("Método HTTP no permitido");
    }

    require_once __DIR__ . "/Perfil.php";
    $perfil = new Perfil();
    $documento = $_SESSION["documento"];

    if ($metodo === "GET") {
        $datos = $perfil->obtener($documento);
        if (!$datos) {
            http_response_code(404);
            throw new Exception("No se encontró el perfil");
        }
        echo json_encode(["success" => true, "perfil" => $datos]);
        exit;
    }

    // PUT
    $entrada = json_decode(file_get_contents("php://input"), true);
    if (!is_array($entrada)) {
        http_response_code(400);
        throw new Exception("Datos inválidos");
    }

    $nombres   = trim($entrada["nombres"] ?? "");
    $apellidos = trim($entrada["apellidos"] ?? "");
    $email     = trim($entrada["email"] ?? "");

    if ($nombres === "" || strlen($nombres) > 100 || $apellidos === "" || strlen($apellidos) > 100) {
        http_response_code(400);
        throw new Exception("Ingresa nombres y apellidos válidos");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        http_response_code(400);
        throw new Exception("Ingresa un correo válido");
    }

    $perfil->actualizar($documento, [
        "nombres" => $nombres,
        "apellidos" => $apellidos,
        "email" => $email
    ]);

    echo json_encode(["success" => true, "mensaje" => "Perfil actualizado correctamente"]);

} catch (PDOException $e) {
    // 23000 = violación de restricción (por ejemplo, correo duplicado)
    if ($e->getCode() === "23000") {
        http_response_code(409);
        echo json_encode(["success" => false, "mensaje" => "Ese correo ya está en uso"]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "mensaje" => "Error en la base de datos"]);
    }
} catch (Exception $e) {
    if (http_response_code() < 400) {
        http_response_code(500);
    }
    echo json_encode(["success" => false, "mensaje" => $e->getMessage()]);
}