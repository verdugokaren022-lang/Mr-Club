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
    if (strtolower((string) $_SESSION["rol"]) !== "admin") {
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
                       GREATEST(pr.valorPrestamo - COALESCE(SUM(p.valorCuota), 0), 0) AS saldoPendiente,
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
    $fecha      = !empty($entrada["fechaPrestamo"]) ? $entrada["fechaPrestamo"] : date("Y-m-d");

    if ($idAsociado === false) {
        http_response_code(400);
        throw new Exception("Selecciona un asociado válido");
    }
    if (!is_numeric($valor) || (float) $valor <= 0 || (float) $valor > 80000000) {
        http_response_code(400);
        throw new Exception("El valor del préstamo debe ser mayor a 0 y no superar los $80.000.000");
    }
    if ($cuotas === false) {
        http_response_code(400);
        throw new Exception("Ingresa un plazo válido (1 a 360 meses)");
    }
    if (!is_numeric($tasa) || (float) $tasa < 0 || (float) $tasa > 100) {
        http_response_code(400);
        throw new Exception("Ingresa una tasa de interés válida");
    }

    $verificar = $conexion->prepare("SELECT 1 FROM asociados WHERE idAsociado = :idAsociado");
    $verificar->execute([":idAsociado" => $idAsociado]);
    if (!$verificar->fetchColumn()) {
        http_response_code(404);
        throw new Exception("El asociado no existe");
    }

    // Validar máximo 3 préstamos y tope acumulado de $80.000.000 por asociado
    $sqlLimites = "SELECT COUNT(*) AS totalPrestamos,
                          COALESCE(SUM(valorPrestamo), 0) AS sumaPrestamos
                   FROM prestamos
                   WHERE idAsociado = :idAsociado";
    $stmtLimites = $conexion->prepare($sqlLimites);
    $stmtLimites->execute([":idAsociado" => $idAsociado]);
    $limites = $stmtLimites->fetch(PDO::FETCH_ASSOC);

    if ((int) $limites["totalPrestamos"] >= 3) {
        http_response_code(400);
        throw new Exception("El asociado ya alcanzó el límite máximo de 3 préstamos");
    }

    $nuevoValor = (float) $valor;
    if (((float) $limites["sumaPrestamos"] + $nuevoValor) > 80000000) {
        http_response_code(400);
        throw new Exception("El monto supera el límite máximo permitido de $80.000.000 por asociado");
    }

    $sql = "INSERT INTO prestamos (valorPrestamo, tasaInteres, numeroCuotas, fechaPrestamo, idAsociado)
            VALUES (:valor, :tasa, :cuotas, :fecha, :idAsociado)";
    $consulta = $conexion->prepare($sql);
    $consulta->execute([
        ":valor"      => $nuevoValor,
        ":tasa"       => (float) $tasa,
        ":cuotas"     => $cuotas,
        ":fecha"      => $fecha,
        ":idAsociado" => $idAsociado
    ]);

    http_response_code(201);
    echo json_encode(["success" => true, "mensaje" => "Préstamo aprobado y registrado correctamente"]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "mensaje" => "Error en la base de datos"]);
} catch (Exception $e) {
    if (http_response_code() < 400) {
        http_response_code(500);
    }
    echo json_encode(["success" => false, "mensaje" => $e->getMessage()]);
}<?php
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
    if (strtolower((string) $_SESSION["rol"]) !== "admin") {
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
                       GREATEST(pr.valorPrestamo - COALESCE(SUM(p.valorCuota), 0), 0) AS saldoPendiente,
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
    $fecha      = !empty($entrada["fechaPrestamo"]) ? $entrada["fechaPrestamo"] : date("Y-m-d");

    if ($idAsociado === false) {
        http_response_code(400);
        throw new Exception("Selecciona un asociado válido");
    }
    if (!is_numeric($valor) || (float) $valor <= 0 || (float) $valor > 80000000) {
        http_response_code(400);
        throw new Exception("El valor del préstamo debe ser mayor a 0 y no superar los $80.000.000");
    }
    if ($cuotas === false) {
        http_response_code(400);
        throw new Exception("Ingresa un plazo válido (1 a 360 meses)");
    }
    if (!is_numeric($tasa) || (float) $tasa < 0 || (float) $tasa > 100) {
        http_response_code(400);
        throw new Exception("Ingresa una tasa de interés válida");
    }

    $verificar = $conexion->prepare("SELECT 1 FROM asociados WHERE idAsociado = :idAsociado");
    $verificar->execute([":idAsociado" => $idAsociado]);
    if (!$verificar->fetchColumn()) {
        http_response_code(404);
        throw new Exception("El asociado no existe");
    }

    // Validar máximo 3 préstamos y tope acumulado de $80.000.000 por asociado
    $sqlLimites = "SELECT COUNT(*) AS totalPrestamos,
                          COALESCE(SUM(valorPrestamo), 0) AS sumaPrestamos
                   FROM prestamos
                   WHERE idAsociado = :idAsociado";
    $stmtLimites = $conexion->prepare($sqlLimites);
    $stmtLimites->execute([":idAsociado" => $idAsociado]);
    $limites = $stmtLimites->fetch(PDO::FETCH_ASSOC);

    if ((int) $limites["totalPrestamos"] >= 3) {
        http_response_code(400);
        throw new Exception("El asociado ya alcanzó el límite máximo de 3 préstamos");
    }

    $nuevoValor = (float) $valor;
    if (((float) $limites["sumaPrestamos"] + $nuevoValor) > 80000000) {
        http_response_code(400);
        throw new Exception("El monto supera el límite máximo permitido de $80.000.000 por asociado");
    }

    $sql = "INSERT INTO prestamos (valorPrestamo, tasaInteres, numeroCuotas, fechaPrestamo, idAsociado)
            VALUES (:valor, :tasa, :cuotas, :fecha, :idAsociado)";
    $consulta = $conexion->prepare($sql);
    $consulta->execute([
        ":valor"      => $nuevoValor,
        ":tasa"       => (float) $tasa,
        ":cuotas"     => $cuotas,
        ":fecha"      => $fecha,
        ":idAsociado" => $idAsociado
    ]);

    http_response_code(201);
    echo json_encode(["success" => true, "mensaje" => "Préstamo aprobado y registrado correctamente"]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "mensaje" => "Error en la base de datos"]);
} catch (Exception $e) {
    if (http_response_code() < 400) {
        http_response_code(500);
    }
    echo json_encode(["success" => false, "mensaje" => $e->getMessage()]);
}