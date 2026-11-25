<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../Config/conexion.php';

try {
    if (!isset($_SESSION["documento"], $_SESSION["rol"])) {
        http_response_code(401);
        throw new Exception("Inicia sesión para continuar");
    }
    if ($_SESSION["rol"] !== "admin") {
        http_response_code(403);
        throw new Exception("No tienes permisos para ver esta lista");
    }

    $conexionDB = new Conexion();
    $conexion = $conexionDB->conectar();

    $sql = "SELECT idAsociado, documento, nombres, apellidos
            FROM asociados
            ORDER BY nombres ASC, apellidos ASC";
    $consulta = $conexion->prepare($sql);
    $consulta->execute();

    echo json_encode([
        "success" => true,
        "asociados" => $consulta->fetchAll(PDO::FETCH_ASSOC)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "mensaje" => "Error en la base de datos"]);
} catch (Exception $e) {
    if (http_response_code() < 400) {
        http_response_code(500);
    }
    echo json_encode(["success" => false, "mensaje" => $e->getMessage()]);
}