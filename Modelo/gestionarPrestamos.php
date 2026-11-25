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
        throw new Exception("No tienes permisos para gestionar préstamos");
    }

    $metodo = $_SERVER["REQUEST_METHOD"];
    if (!in_array($metodo, ["GET", "POST"], true)) {
        http_response_code(405);
        throw new Exception("Método HTTP no permitido");
    }

    $conexionDB = new Conexion();
    $conexion = $conexionDB->conectar();

    if ($metodo === "GET") {
        $sql = "SELECT pr.idPrestamo, a.nombres, a.apellidos, a.documento,
                       pr.valorPrestamo, pr.tasaInteres, pr.numeroCuotas, pr.fechaPrestamo,
                       COALESCE(SUM(p.valorCuota), 0) AS totalPagado,
                       COUNT(p.idPago) AS cuotasPagadas
                FROM prestamos pr
                INNER JOIN asociados a ON a.idAsociado = pr.idAsociado
                LEFT JOIN pagos_cuotas p ON p.idPrestamo = pr.idPrestamo
                GROUP BY pr.idPrestamo, a.nombres, a.apellidos, a.documento,
                         pr.valorPrestamo, pr.tasaInteres, pr.numeroCuotas, pr.fechaPrestamo
                ORDER BY pr.fechaPrestamo DESC, pr.idPrestamo DESC";
        $consulta = $conexion->prepare($sql);
        $consulta->execute();

        echo json_encode([
            "success" => true,
            "prestamos" => $consulta->fetchAll(PDO::FETCH_ASSOC)
        ]);
        exit;
    }

    // POST: registrar préstamo
    $entrada = json_decode(file_get_contents("php://input"), true);
    if (!is_array($entrada)) {
        http_response_code(400);
        throw new Exception("Datos inválidos");
    }

    $idAsociado = filter_var($entrada["idAsociado"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
    $valor      = $entrada["valorPrestamo"] ?? null;
    $cuotas     = filter_var($entrada["numeroCuotas"] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 360]]);
    $tasa       = $entrada["tasaInteres"] ?? null;

    if ($idAsociado === false) {
        http_response_code(400);
        throw new Exception("Selecciona un asociado válido");
    }
    if (!is_numeric($valor) || $valor <= 0 || $valor > 9999999999) {
        http_response_code(400);
        throw new Exception("Ingresa un monto válido");
    }
    if ($cuotas === false) {
        http_response_code(400);
        throw new Exception("Ingresa un plazo válido (1 a 360 meses)");
    }
    if (!is_numeric($tasa) || $tasa < 0 || $tasa > 999) {
        http_response_code(400);
        throw new Exception("Ingresa una tasa de interés válida");
    }

    $verificar = $conexion->prepare("SELECT 1 FROM asociados WHERE idAsociado = :idAsociado");
    $verificar->execute([":idAsociado" => $idAsociado]);
    if (!$verificar->fetchColumn()) {
        http_response_code(404);
        throw new Exception("El asociado no existe");
    }

    $sql = "INSERT INTO prestamos (valorPrestamo, tasaInteres, numeroCuotas, fechaPrestamo, idAsociado)
            VALUES (:valor, :tasa, :cuotas, CURDATE(), :idAsociado)";
    $consulta = $conexion->prepare($sql);
    $consulta->execute([
        ":valor"      => (float) $valor,
        ":tasa"       => (float) $tasa,
        ":cuotas"     => $cuotas,
        ":idAsociado" => $idAsociado
    ]);

    http_response_code(201);
    echo json_encode(["success" => true, "mensaje" => "Préstamo aprobado correctamente"]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "mensaje" => "Error en la base de datos"]);
} catch (Exception $e) {
    if (http_response_code() < 400) {
        http_response_code(500);
    }
    echo json_encode(["success" => false, "mensaje" => $e->getMessage()]);
}