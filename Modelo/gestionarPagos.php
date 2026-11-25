<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

try {
    if (!isset($_SESSION["documento"])) {
        http_response_code(401);
        throw new Exception("Inicia sesión para consultar tus pagos");
    }

    if ($_SERVER["REQUEST_METHOD"] !== "GET") {
        http_response_code(405);
        throw new Exception("Método HTTP no permitido");
    }

    require_once __DIR__ . "/Pagos.php";
    $pagos = new Pagos();
    $documento = $_SESSION["documento"];

    echo json_encode([
        "success"   => true,
        "prestamos" => $pagos->obtenerPrestamos($documento),
        "pagos"     => $pagos->obtenerPagos($documento)
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