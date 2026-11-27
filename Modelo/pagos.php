<?php
require_once __DIR__ . '/../Config/conexion.php';

class Pagos {
    private $conexion;

    public function __construct() {
        $conexionDB = new Conexion();
        $this->conexion = $conexionDB->conectar();
    }

    public function obtenerPrestamos($documento) {
        $sql = "SELECT pr.idPrestamo, pr.valorPrestamo, pr.tasaInteres, pr.numeroCuotas, pr.fechaPrestamo,
                       COALESCE(SUM(p.valorCuota), 0) AS totalAbonado,
                       COUNT(p.idPago) AS cuotasPagadas
                FROM prestamos pr
                INNER JOIN asociados a ON a.idAsociado = pr.idAsociado
                LEFT JOIN pagos_cuotas p ON p.idPrestamo = pr.idPrestamo
                WHERE a.documento = :documento
                GROUP BY pr.idPrestamo, pr.valorPrestamo, pr.tasaInteres, pr.numeroCuotas, pr.fechaPrestamo
                ORDER BY pr.fechaPrestamo DESC, pr.idPrestamo DESC";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":documento" => $documento]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPagos($documento) {
        $sql = "SELECT p.idPago, p.valorCuota, p.fechaPago, p.idPrestamo
                FROM pagos_cuotas p
                INNER JOIN prestamos pr ON pr.idPrestamo = p.idPrestamo
                INNER JOIN asociados a ON a.idAsociado = pr.idAsociado
                WHERE a.documento = :documento
                ORDER BY p.idPago DESC";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":documento" => $documento]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarPago($documento, $idPrestamo, $valorCuota) {
        $sqlVerificar = "SELECT pr.idPrestamo, pr.valorPrestamo, pr.tasaInteres,
                                COALESCE(SUM(p.valorCuota), 0) AS totalAbonado
                         FROM prestamos pr
                         INNER JOIN asociados a ON a.idAsociado = pr.idAsociado
                         LEFT JOIN pagos_cuotas p ON p.idPrestamo = pr.idPrestamo
                         WHERE a.documento = :documento AND pr.idPrestamo = :idPrestamo
                         GROUP BY pr.idPrestamo, pr.valorPrestamo, pr.tasaInteres";
        $stmt = $this->conexion->prepare($sqlVerificar);
        $stmt->execute([
            ":documento"  => $documento,
            ":idPrestamo" => $idPrestamo
        ]);
        $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prestamo) {
            throw new Exception("El préstamo no pertenece a tu cuenta o no existe");
        }

        $totalConInteres = (float) $prestamo["valorPrestamo"] * (1 + ((float) $prestamo["tasaInteres"] / 100));
        $saldoPendiente  = round($totalConInteres - (float) $prestamo["totalAbonado"], 2);

        if ($saldoPendiente <= 0) {
            throw new Exception("Este préstamo ya se encuentra pagado en su totalidad");
        }
        if ($valorCuota > ($saldoPendiente + 1)) {
            throw new Exception("El abono supera el saldo pendiente de $" . number_format($saldoPendiente, 0, ',', '.'));
        }

        $sqlInsert = "INSERT INTO pagos_cuotas (valorCuota, fechaPago, idPrestamo)
                      VALUES (:valorCuota, CURDATE(), :idPrestamo)";
        $insert = $this->conexion->prepare($sqlInsert);
        return $insert->execute([
            ":valorCuota" => $valorCuota,
            ":idPrestamo" => $idPrestamo
        ]);
    }
}