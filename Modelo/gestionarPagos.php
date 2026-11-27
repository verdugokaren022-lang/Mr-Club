<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Content-Type: application/json; charset=UTF-8");

try {
    if (!isset($_SESSION["documento"])) {
        http_response_code(401);
        throw new Exception("Inicia sesión para consultar tus pagos");
    }

    $metodo = $_SERVER["REQUEST_METHOD"];
    if (!in_array($metodo, ["GET", "POST"], true)) {
        http_response_code(405);
        throw new Exception("Método HTTP no permitido");
    }

    require_once __DIR__ . "/pagos.php";
    $pagos = new Pagos();
    $documento = $_SESSION["documento"];

    if ($metodo === "GET") {
        echo json_encode([
            "success"   => true,
            "prestamos" => $pagos->obtenerPrestamos($documento),
            "pagos"     => $pagos->obtenerPagos($documento)
        ]);
        exit;
    }

    // POST: registrar abono / cuota
    $entrada = json_decode(file_get_contents("php://input"), true);
    if (!is_array($entrada)) {
        http_response_code(400);
        throw new Exception("Datos inválidos");
    }

    $idPrestamo = filter_var($entrada["idPrestamo"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
    $valorCuota = $entrada["valorCuota"] ?? null;

    if ($idPrestamo === false) {
        http_response_code(400);
        throw new Exception("Selecciona un préstamo válido");
    }
    if (!is_numeric($valorCuota) || (float) $valorCuota <= 0) {
        http_response_code(400);
        throw new Exception("Ingresa un valor de cuota válido mayor a 0");
    }

    $pagos->registrarPago($documento, $idPrestamo, (float) $valorCuota);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "mensaje" => "Pago registrado correctamente en la base de datos"
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