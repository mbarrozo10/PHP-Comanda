<?php

class Mesa{

    public $id;
    public $codigo;
    public $estado;

    public function ComprobarCodigo($codigo){ 
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas where codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo);
        $consulta->execute();

        if($consulta->rowCount() > 0)return false;
        else return true;
    }

    public function CargarMesa(){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (codigo, estado) VALUES (:codigo, :estado)");
        $consulta->bindValue(':codigo', $this->codigo);
        $consulta->bindValue(':estado', $this->estado);
        $consulta->execute();
    }

    public static function TraerMesas(){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas");
        $consulta->execute();

        $mesas= array();

        while($fila = $consulta->fetch(PDO::FETCH_ASSOC)){
            $mesa= new Mesa();
            $mesa->id=$fila['id'];
            $mesa->codigo=$fila['codigo'];
            $mesa->fecha=$fila['estado'];
            array_push($mesas,$mesa);
        }
        return $mesas;
    }
}
?>