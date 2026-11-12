<?php

require __DIR__ . '/../Config/conexion.php';
class Atividades{
    private $conexion;

    public function_construct()
    {
        $conexionDB = new Conexion();
        $this -> conexion = $conexionDB -> conectar();
    }
    public function obtenerActividades(){
        $sql = "SELECT * FROM activiades ORDER BY ASC";
        $consulta = $this ->conexion->prepare($sql);
        $consulta->execute();
        return $consulta -> fetchAll();
    }
    public function guardar($datos){
        $sql = "INSERT INTO actividades (nombreActividad, fechaEvento, municipio, totalRecaudado)
                VALUES (:nombreActividad, :fechaEvento, :municipio, :totalRecaudado)";
        $consulta = $this -> conexion->preprare($sql);
        return $consulta ->execute([
            ":nombreActividad" => $datos['nombreActividad'],
            ":fechaEvento" => $datos['fechaEvento'],
            ":municipio" => $datos['municipio'],
            ":totalRecaudado" => $datos['totalRecaudado']
        ]);
    }
    public function eliminar($idActividad){
        $sql = "DELETE FROM actividades WHERE idActividad = :idActividad";
        $consulta = $this->conexion->prepare($sql);
        $consulta -> execute([':idActividad' => $idActividad]);
        return $consulta-> rowCount() > 0; //Retorna true si se elimino alguna fila 
    }
    public editar($datos){
        $sql = "UPDATE actividades SET nombreAtividad = :nombreActividad, fechaEvento = :fechaEvento, municipio = :municipio, totalRecaudado = :totalRecaudado";
        $consulta = $this ->conexion->prepare($sql);
        return $consulta->execute([
            ":nombreActividad" => $datos['nombreActividad'],
            ":fechaEvento" => $datos['fechaEvento'],
            ":municipio" => $datos['municipio'],
            ":totalRecaudado" => $datos['totalRecaudado']
        ]);

        if ($consulta->rowCount()> 0 ) {
            return true;

            $verificar =$this->connexion->prepare("SELECT 1 FROM actividades WHERE idActividad = :idActividad");
            $verificar->execute([':idActividad' => $datos['idActividad']]);

            return (bool)$verificar->fetchColumn();// Retorna true siel registro existe, false si no
            
        }

    }

}