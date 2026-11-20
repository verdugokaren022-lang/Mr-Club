<?php

require __DIR__ . '/../Config/conexion.php';
class Actividad{
    private $conexion;

    public function __construct()
    {
        $conexionDB = new Conexion();
        $this -> conexion = $conexionDB -> conectar();
    }
    public function obtenerActividades(){
        $sql = "SELECT * FROM actividades ORDER BY idActividades ASC";
        $consulta = $this ->conexion->prepare($sql);
        $consulta->execute();
        return $consulta -> fetchAll();
    }
    public function guardar($datos){
        $sql = "INSERT INTO actividades (nombreActividad, fechaEvento, municipio, totalRecaudado)
                VALUES (:nombreActividad, :fechaEvento, :municipio, :totalRecaudado)";
        $consulta = $this -> conexion->prepare($sql);
        return $consulta ->execute([
            ":nombreActividad" => $datos['nombreActividad'],
            ":fechaEvento" => $datos['fechaEvento'],
            ":municipio" => $datos['municipio'],
           
        ]);
    }
    public function eliminar($idActividad){
        $sql = "DELETE FROM actividades WHERE idActividad = :idActividad";
        $consulta = $this->conexion->prepare($sql);
        $consulta -> execute([':idActividad' => $idActividad]);
        return $consulta-> rowCount() > 0; //Retorna true si se elimino alguna fila 
    }
    public function editar($datos){
        $sql = "UPDATE actividades SET nombreActividad = :nombreActividad, fechaEvento = :fechaEvento, municipio = :municipio, totalRecaudado = :totalRecaudado WHERE idActividad = :idActividad";
        $consulta = $this ->conexion->prepare($sql);
        $ejecuto = $consulta->execute([
            ":nombreActividad" => $datos['nombreActividad'],
            ":fechaEvento" => $datos['fechaEvento'],
            ":municipio" => $datos['municipio'],
            ":totalRecaudado" => $datos['totalRecaudado']
        ]);

        if (!$ejecuto) {
            return false;
        }

        if ($consulta->rowCount()> 0 ) {
            return true;

            $verificar =$this->connexion->prepare("SELECT 1 FROM actividades WHERE idActividad = :idActividad");
            $verificar->execute([':idActividad' => $datos['idActividad']]);

            return (bool)$verificar->fetchColumn();// Retorna true siel registro existe, false si no
            
        }

    }

}