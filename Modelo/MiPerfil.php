<?php
require_once __DIR__ . '/../Config/conexion.php';

class Perfil {
    private $conexion;

    public function __construct() {
        $conexionDB = new Conexion();
        $this->conexion = $conexionDB->conectar();
    }

    public function obtener($documento) {
        $sql = "SELECT documento, nombres, apellidos, email, rol
                FROM asociados WHERE documento = :documento";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":documento" => $documento]);
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($documento, $datos) {
        $sql = "UPDATE asociados
                SET nombres = :nombres, apellidos = :apellidos, email = :email
                WHERE documento = :documento";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([
            ":nombres"   => $datos["nombres"],
            ":apellidos" => $datos["apellidos"],
            ":email"     => $datos["email"],
            ":documento" => $documento
        ]);
    }
}